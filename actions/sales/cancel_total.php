<?php
require_once __DIR__ . "/../actions_init.php";

$db = DBConnection::get_db_connection();

$saleId = $_GET['sale_id'] ?? "";

// Carica la vendita con anagrafica cliente e (nuove) colonne split pagamenti
$sql = "SELECT s.*, c.customer_id, c.first_name, c.last_name
        FROM sales s
        LEFT JOIN customers c USING(customer_id)
        WHERE s.sale_id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$saleId]);
$sale = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sale) {
    Utils::print_error("Vendita non trovata");
    header("Location: /index.php?page=sales_view");
    exit;
}

// Imposta stato 'totally_canceled'
$sql = "UPDATE sales SET status = 'totally_canceled' WHERE sale_id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$saleId]);

// Carica righe vendita (prezzi in CENTESIMI)
$sql = "SELECT i.*, p.name 
        FROM sales_items i 
        JOIN products p USING(product_id) 
        WHERE i.sale_id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$saleId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Costruisci righe per ricevuta (firmate negative)
$receiptItems = [];
foreach ($items as $item) {
    $lineCents = (int)$item['price'] * (int)$item['quantity'];
    $receiptItems[] = [
        "sku"      => InternalNumbers::get_sku($item['product_id'], $item['variant_id']),
        "name"     => $item['name'],
        "price"    => Utils::format_price(-(int)$item['price']),  // prezzo unitario negativo
        "quantity" => (int)$item['quantity'],
        "total"    => Utils::format_price(-$lineCents),
    ];
}

// Subtotale in centesimi
$subtotalCents = array_reduce($items, function($carry, $it) {
    return $carry + ((int)$it['price'] * (int)$it['quantity']);
}, 0);

// Applica sconto livello vendita (usiamo la stessa logica lato pagina: compute_discounted_price lavora in CHF)
$subtotalChf = $subtotalCents / 100.0;
$totalChfAfterDiscount = Utils::compute_discounted_price($subtotalChf, $sale['discount'], $sale['discount_type']);
$totalCents = (int) round($totalChfAfterDiscount * 100);

// Aggiorna cassetto contanti: togli la quota CONTANTI effettiva incassata su questa vendita
// (se hai salvato paid_cash in centesimi; se non esiste, lascialo a 0)
$paidCashCents = isset($sale['paid_cash']) ? (int)$sale['paid_cash'] : 0;
if ($paidCashCents > 0) {
    $sql = "UPDATE cash_content 
            SET content = content - :cash, 
                last_updated_at = NOW(), 
                last_updated_by = :username
            WHERE id = 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':cash'     => $paidCashCents, // centesimi
        ':username' => Auth::get_username(),
    ]);
}

// Se la vendita NON era 'open', rimetti a magazzino
if ($sale['status'] !== "open") {
    foreach ($items as $item) {
        $sql = "UPDATE product_variants 
                SET stock = stock + :qty 
                WHERE product_id = :pid AND variant_id = :vid";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':qty' => (int)$item['quantity'],
            ':pid' => $item['product_id'],
            ':vid' => $item['variant_id'],
        ]);
    }
}

// Tabella pagamenti per la ricevuta (split). Usiamo le colonne salvate se presenti.
$paidPosCents  = isset($sale['paid_pos']) ? (int)$sale['paid_pos'] : max($totalCents - $paidCashCents, 0);

// Tutto firmato NEGATIVO (è un annullamento totale)
$paymentRows = [
    [ "label" => "Contanti", "amount" => Utils::format_price(-$paidCashCents) ],
    [ "label" => "POS",      "amount" => Utils::format_price(-$paidPosCents)  ],
    [ "label" => "Totale",   "amount" => Utils::format_price(-$totalCents)    ],
];

// Costruisci ricevuta (nessun paymentMethod; aggiungiamo “payments”)
$receipt = [
    "saleId"   => $saleId,
    "subtotal" => Utils::format_price(-$subtotalCents),
    "total"    => Utils::format_price(-$totalCents),
    "discount" => ($sale['discount'] ?? 0) != 0 ? ($sale['discount'] . " " . ($sale['discount_type'] ?? 'CHF')) : "",
    // "paymentMethod" rimosso
    "username" => Utils::format_pos(Auth::get_fullname_by_username($sale['username'])),
    "customer" => ($sale['customer_id']) !== null ? ($sale['first_name'] . " " . $sale['last_name']) : "Esterno",
    "datetime" => $sale['closed_at'],
    "items"    => $receiptItems,
    "type"     => "return",
    "payments" => $paymentRows,
];

// Stampa ricevuta (best-effort)
$posClient = POSHttpClient::get_http_client();
try {
    $posClient->post("/receipt/print", [
        "json" => [ "receipt" => $receipt ]
    ]);
} catch (Exception $e) {
    // log non bloccante
    error_log("Receipt print error (total cancel): " . $e->getMessage());
}

// Torna alla vista vendite
header("Location: /index.php?page=sales_view");
exit;
