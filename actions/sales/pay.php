<?php
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();


$db = DBConnection::get_db_connection();

$saleId   = $_POST['sale_id'] ?? "";
$negative = isset($_POST['negative']) && $_POST['negative'] == 1;
$factor   = $negative ? -1 : 1;

// === NUOVI CAMPI DAL FORM =====================================================
// Effettivamente incassato (split) in CHF decimali
$cashEffectiveDec = isset($_POST['cash_effective_paid_chf']) ? (float)$_POST['cash_effective_paid_chf'] : 0.0;
$posEffectiveDec  = isset($_POST['pos_paid_chf'])            ? (float)$_POST['pos_paid_chf']            : 0.0;

// Contanti consegnati (tendered) e valuta (per il calcolo del resto)
$cashTenderedDec = isset($_POST['cash_tendered']) ? (float)$_POST['cash_tendered'] : 0.0; // decimali
$cashCurrency    = $_POST['cash_currency'] ?? 'CHF';

// Converti in centesimi
$cashEffectiveCents = (int) round($cashEffectiveDec * 100);
$posEffectiveCents  = (int) round($posEffectiveDec  * 100);

// 1) Chiudi la vendita (rimuoviamo payment_method)
$sql  = "UPDATE sales SET closed_at = NOW() WHERE sale_id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$saleId]);

// 2) Carica righe vendita (con nome prodotto, flag sconto)
$sql = "SELECT i.*, p.name 
        FROM sales_items i 
        JOIN products p USING(product_id) 
        WHERE i.sale_id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$saleId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- BUSINESS RULE: gli scontati non si possono rendere ----------------------
if ($negative) {
    foreach ($items as $it) {
        if (!empty($it['is_discounted'])) {
            Utils::print_error("Operazione non consentita: gli articoli già scontati non possono essere restituiti.");
            ?>
            <script>window.location.href = "/index.php?page=sales_view";</script>
            <?php
            exit;
        }
    }
}
// -----------------------------------------------------------------------------

// 3) Subtotali assoluti (positivi) in centesimi
$absSubtotalDiscountable    = 0;
$absSubtotalNonDiscountable = 0;
$absSubtotalAll             = 0;

foreach ($items as $it) {
    $lineCents = (int)$it['price'] * (int)$it['quantity']; // price in CENTESIMI
    if (!empty($it['is_discounted'])) {
        $absSubtotalNonDiscountable += $lineCents;
    } else {
        $absSubtotalDiscountable += $lineCents;
    }
    $absSubtotalAll += $lineCents;
}

// 4) Dati testata vendita (sconto, cliente, user)
$sql  = "SELECT s.*, c.customer_id, c.first_name, c.last_name 
         FROM sales s 
         LEFT JOIN customers c USING(customer_id) 
         WHERE s.sale_id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$saleId]);
$sale = $stmt->fetch(PDO::FETCH_ASSOC);

// 5) Applica sconto livello vendita alla sola bucket scontabile
$discountVal  = (float)($sale['discount'] ?? 0);
$discountType = (string)($sale['discount_type'] ?? 'CHF');

$discountCentsApplied = 0;
if ($discountVal > 0 && $absSubtotalDiscountable > 0) {
    if ($discountType === 'CHF') {
        $discountCentsApplied = min((int)round($discountVal * 100), $absSubtotalDiscountable);
    } else { // "%"
        $discountCentsApplied = (int) floor($absSubtotalDiscountable * ($discountVal / 100));
    }
}

$absGrandTotal = ($absSubtotalDiscountable - $discountCentsApplied) + $absSubtotalNonDiscountable; // centesimi, positivo

// 6) Muovi stock + build receipt items
$stockSign = $negative ? "+" : "-";
$receiptItems = [];

foreach ($items as $item) {
    $sql = "UPDATE product_variants 
            SET stock = stock {$stockSign} :quantity 
            WHERE product_id = :product_id AND variant_id = :variant_id";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        'quantity'   => (int)$item['quantity'],
        'product_id' => $item['product_id'],
        'variant_id' => $item['variant_id']
    ]);

    $receiptItems[] = [
        "sku"          => InternalNumbers::get_sku($item['product_id'], $item['variant_id']),
        "name"         => $item['name'],
        "price"        => Utils::format_price(((int)$item['price']) * $factor),
        "quantity"     => (int)$item['quantity'],
        "total"        => Utils::format_price(((int)$item['price'] * (int)$item['quantity']) * $factor),
        "isDiscounted" => (bool)$item['is_discounted'],
    ];
}

// 7) Totali per stampa (firmati)
$subtotalSigned   = $factor * $absSubtotalAll;
$grandTotalSigned = $factor * $absGrandTotal;

// Etichetta sconto (se applicabile)
$discountLabel = ($discountVal != 0 && $absSubtotalDiscountable > 0)
    ? ($discountVal . " " . $discountType)
    : "";

// === CAMBIO (RESTO) ==========================================================
// Recupera il tasso per convertire eventuali EUR contanti in CHF per il controllo resto
// Assunzione: exchange_rates.rate = EUR per 1 CHF (come nel tuo file di pagina).
$sql = "SELECT rate FROM exchange_rates WHERE currency = 'EUR'";
$stmt = $db->prepare($sql);
$stmt->execute();
$eur_per_chf = (float)$stmt->fetchColumn();
if ($eur_per_chf <= 0) { $eur_per_chf = 1.0; }
$chf_per_eur = 1.0 / $eur_per_chf;

// Calcola i contanti consegnati in CHF centesimi
$cashTenderedCents = (int) round($cashTenderedDec * 100);
if (strtoupper($cashCurrency) === 'EUR') {
    $cashTenderedCents = (int) round($cashTenderedCents * $chf_per_eur);
}

// Nelle vendite (non resi), il resto è l’eccedenza di contanti consegnati sulla quota contanti effettiva
$changeCents = 0;
if (!$negative) {
    $changeCents = max($cashTenderedCents - $cashEffectiveCents, 0);
} else {
    $changeCents = 0; // nessun resto nei resi
}

// === VALIDAZIONI SERVER-SIDE =================================================
// 1) split combacia col totale (tolleranza ±1 cent)
$sumSplit = $cashEffectiveCents + $posEffectiveCents;
$diff     = $sumSplit - $absGrandTotal;
if (abs($diff) > 1) {
    Utils::print_error("La somma Contanti + POS non combacia con il totale. (diff: " . Utils::format_price($diff) . ")");
    ?>
    <script>window.history.back();</script>
    <?php
    exit;
}

// 2) se vendita, contanti consegnati devono coprire la quota contanti effettiva
if (!$negative && $cashTenderedCents < $cashEffectiveCents) {
    Utils::print_error("Importo in contanti insufficiente a coprire la quota assegnata ai contanti.");
    ?>
    <script>window.history.back();</script>
    <?php
    exit;
}

// 8) Tabella pagamenti per la ricevuta
$paymentRows = [
    [ "label" => "Contanti", "amount" => Utils::format_price($factor * $cashEffectiveCents) ],
    [ "label" => "POS",      "amount" => Utils::format_price($factor * $posEffectiveCents)  ],
];

// Mostra riga Resto solo nelle vendite e se > 0
if (!$negative && $changeCents > 0) {
    $paymentRows[] = [ "label" => "Resto", "amount" => Utils::format_price($changeCents) ];
}

// Riga Totale sempre presente
$paymentRows[] = [ "label" => "Totale", "amount" => Utils::format_price($factor * $absGrandTotal) ];

// 9) Costruisci ricevuta
$type = $negative ? "return" : "sale";

$receipt = [
    "saleId"             => $saleId,
    "subtotal"           => Utils::format_price($subtotalSigned),
    "discountedSubtotal" => Utils::format_price($grandTotalSigned),
    "total"              => Utils::format_price($grandTotalSigned),
    "discount"           => $discountLabel,
    "username"           => Utils::format_pos(Auth::get_fullname_by_username($sale['username'])),
    "customer"           => ($sale['customer_id']) !== null ? ($sale['first_name'] . " " . $sale['last_name']) : "Esterno",
    "datetime"           => $sale['closed_at'],
    "items"              => $receiptItems,
    "type"               => $type,
    "payments"           => $paymentRows,     // <-- tabella pagamenti per la stampa
    "change"             => Utils::format_price($changeCents)  // <-- resto (solo per info)
];

// 10) Stampa ricevuta (best-effort)
$posClient = POSHttpClient::get_http_client();
try {
    $posClient->post("/receipt/print", [
        "json" => [ "receipt" => $receipt ]
    ]);
} catch (Exception $e) {
    // log non bloccante
    error_log("Receipt print error: " . $e->getMessage());
}

// 11) Stato della vendita
$sql  = $negative ? "UPDATE sales SET status = 'negative' WHERE sale_id = ?" 
                  : "UPDATE sales SET status = 'completed' WHERE sale_id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$saleId]);

// 12) Cassetto contanti: aggiorna solo la quota contanti effettiva (non il resto)
if ($cashEffectiveCents !== 0) {
    $cashSign = $negative ? "-" : "+"; // vendita: +; reso: -
    $stmtSql = "UPDATE cash_content 
                SET content = content {$cashSign} :content, 
                    last_updated_at = NOW(), 
                    last_updated_by = :username 
                WHERE id = 1";
    $stmt = $db->prepare($stmtSql);
    $stmt->execute([
        ':content'  => $cashEffectiveCents, // centesimi
        ':username' => Auth::get_username()
    ]);
}

// 13) Ssalva split in 'sales' se hai colonne dedicate
try {
    $stmt = $db->prepare("UPDATE sales 
                          SET paid_cash = :cash_cents, paid_pos = :pos_cents
                          WHERE sale_id = :sale_id");
    $stmt->execute([
        ':cash_cents'   => $cashEffectiveCents,
        ':pos_cents'    => $posEffectiveCents,
        ':sale_id'      => $saleId
    ]);
} catch (Exception $e) {
    error_log("Could not persist payment split on sales: " . $e->getMessage());
}

?>
<script>
try {
  localStorage.removeItem("last_sale_customer_number");
} catch (e) {
  console.error("Could not remove last_sale_customer_number from localStorage", e);
} finally {
  window.location.href = "/index.php?page=sales_view";
}
</script>
