<?php
/** Dettagli vendita — discount applies only to items with is_discounted = false
 *  and discounted items cannot be returned (status = negative).
 */

$dbconnection = DBConnection::get_db_connection();

$saleId = $_GET['sale_id'] ?? null;

$sql = "SELECT * FROM sales WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$sale = $stmt->fetch();

if(!$sale) {
    Utils::print_error("Vendita non trovata");
    goto end;
}

/** Load items + product/brand + variant in one query */
$sql = "
    SELECT i.*, 
           p.name AS product_name, 
           p.brand_id, 
           b.name AS brand_name,
           pv.color, pv.size
    FROM sales_items i
    JOIN products p USING(product_id)
    JOIN brands b USING(brand_id)
    LEFT JOIN product_variants pv 
        ON pv.product_id = i.product_id AND pv.variant_id = i.variant_id
    WHERE i.sale_id = ?
";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

/** BUSINESS RULE: discounted items cannot be returned */
$isReturn = ($sale['status'] === 'negative');
if ($isReturn) {
    foreach ($items as $it) {
        $isDiscounted = !empty($it['is_discounted']) && (int)$it['is_discounted'] === 1;
        if ($isDiscounted) {
            Utils::print_error("Operazione non consentita: gli articoli scontati non possono essere restituiti.");
            goto end;
        }
    }
}

$factor = $isReturn ? -1 : 1;

/** Compute totals using absolute amounts first */
$abs_subtotal_discountable = 0.0;       // items with is_discounted == false
$abs_subtotal_non_discountable = 0.0;   // items with is_discounted == true
$abs_subtotal_all = 0.0;
$pieces = 0;

$receiptItems = [];

foreach ($items as $item) {
    $qty = (int)$item['quantity'];
    $line_total_abs = (float)$item['price'] * $qty;
    $isDiscounted = !empty($item['is_discounted']) && (int)$item['is_discounted'] === 1;

    if ($isDiscounted) {
        $abs_subtotal_non_discountable += $line_total_abs;
    } else {
        $abs_subtotal_discountable += $line_total_abs;
    }
    $abs_subtotal_all += $line_total_abs;
    $pieces += $qty;

    $receiptItems[] = [
        "sku"      => InternalNumbers::get_sku($item['product_id'], $item['variant_id']),
        "name"     => $item['product_name'],
        "price"    => Utils::format_price(((float)$item['price']) * $factor),
        "quantity" => $qty,
        "total"    => Utils::format_price($line_total_abs * $factor),
    ];
}

// Apply sale-level discount ONLY to discountable bucket
$discount_value = (float)($sale['discount'] ?? 0);
$discount_type  = $sale['discount_type'] ?? null;

$abs_discounted_part = Utils::compute_discounted_price(
    $abs_subtotal_discountable,
    $discount_value,
    $discount_type
);
$abs_grand_total = $abs_discounted_part + $abs_subtotal_non_discountable;

$subtotal_signed     = $factor * $abs_subtotal_all;
$grand_total_signed  = $factor * $abs_grand_total;
$abs_discount_applied = max(0, $abs_subtotal_discountable - $abs_discounted_part);

// Load customer if any
$customer = null;
if (!empty($sale['customer_id'])) {
    $sql = "SELECT first_name, last_name, birth_date, customer_number FROM customers WHERE customer_id = ?";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([$sale['customer_id']]);
    $customer = $stmt->fetch();
}
?>

<h1>Dettagli vendita</h1>

<?php if ($sale['status'] === "totally_canceled"): ?>
    <div class="alert alert-danger b" style="font-size: 2em;">Vendita cancellata</div>
<?php endif; ?>

<ul>
    <li><b>Operatore:</b> <?php echo Auth::get_fullname_by_username($sale['username']); ?></li>
    <li><b>Data e ora di vendita:</b> <?php echo Utils::format_datetime($sale['closed_at']); ?></li>
    <li><b>Pagato con:</b> <?php echo Utils::format_payment_method($sale['payment_method'] ?? "-"); ?></li>
</ul>

<br>

<h2>Voci di vendita</h2>
<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>Codice Articolo</th>
                <th>Nome</th>
                <th>Brand</th>
                <th>Variante</th>
                <th class="text-end">Quantità</th>
                <th class="text-end">Prezzo unit. (CHF)</th>
                <th class="text-end">Prezzo totale (CHF)</th>
                <th class="text-center">Già scontato?</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): 
                $sku = InternalNumbers::get_sku($item['product_id'], $item['variant_id']);
                $qty = (int)$item['quantity'];
                $unit_price_signed = ((float)$item['price']) * $factor;
                $line_total_signed = ((float)$item['price'] * $qty) * $factor;
                $variantLabel = trim(($item['color'] ?? '') . " / " . ($item['size'] ?? ''));
                $isDiscounted = !empty($item['is_discounted']) && (int)$item['is_discounted'] === 1;
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($sku); ?></td>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($item['brand_name']); ?></td>
                    <td><?php echo htmlspecialchars($variantLabel); ?></td>
                    <td class="text-end"><?php echo $qty; ?></td>
                    <td class="text-end"><?php echo Utils::format_price($unit_price_signed); ?></td>
                    <td class="text-end"><?php echo Utils::format_price($line_total_signed); ?></td>
                    <td class="text-center">
                        <?php if ($isDiscounted): ?>
                            <span class="badge bg-secondary">Sì</span>
                        <?php else: ?>
                            <span class="badge bg-success">No</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p><?php echo (int)$pieces; ?> pezzi.</p>

<hr>

<!-- Subtotals block -->
<div class="row g-3">
    <div class="col-md-4">
        <div class="p-3 border rounded">
            <div class="b">Subtotale (tutti gli articoli)</div>
            <div class="display-6"><?php echo Utils::format_price($subtotal_signed); ?> CHF</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-3 border rounded">
            <div class="b">Quota scontabile</div>
            <div class="h3 m-0"><?php echo Utils::format_price($factor * $abs_subtotal_discountable); ?> CHF</div>
            <small class="text-muted">Gli articoli con <i>Già scontato? = No</i></small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-3 border rounded">
            <div class="b">Quota non scontabile</div>
            <div class="h3 m-0"><?php echo Utils::format_price($factor * $abs_subtotal_non_discountable); ?> CHF</div>
            <small class="text-muted">Gli articoli già scontati non ricevono lo sconto vendita</small>
        </div>
    </div>
</div>

<p>&nbsp;</p>

<h2>Sconto</h2>
<div class="row d-flex align-items-center">
    <div class="col-12 col-md-8">
        <label for="discount-input" class="form-label">Sconto (solo sulla quota scontabile)</label>
        <div class="input-group mb-2">
            <input readonly type="number" class="form-control" 
                   value="<?php echo number_format($discount_value, 2); ?>" 
                   min="0" step="0.01" name="discount" id="discount-input" required>
            <span class="input-group-text" id="discount-input-group-text"><?php echo htmlspecialchars($discount_type); ?></span>
        </div>
        <small class="text-muted">
            Base sconto: <?php echo Utils::format_price($factor * $abs_subtotal_discountable); ?> CHF
            <?php if ($discount_value > 0 && $abs_subtotal_discountable > 0): ?>
                · Sconto applicato: <?php echo Utils::format_price($factor * $abs_discount_applied); ?> CHF
            <?php endif; ?>
        </small>
    </div>
</div>

<p>&nbsp;</p>

<h2>Cliente</h2>
<?php if (!$customer): ?>
    <p><i>Esterno</i></p>
<?php else: ?>
    <p>
        <?php echo htmlspecialchars($customer['first_name'] . " " . $customer['last_name']); ?>
        (<?php echo Utils::format_date($customer['birth_date']); ?>) - 
        N. Cliente: <?php echo htmlspecialchars($customer['customer_number']); ?>
    </p>
<?php endif; ?>

<hr>

<h2>Dettagli Pagamento</h2>
<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>Metodo</th>
                <th class="text-end">Importo (CHF)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $paymentRows = [
                [ "label" => "Contanti", "amount" => Utils::format_price($factor * ($sale['paid_cash'] ?? 0)) ],
                [ "label" => "POS",      "amount" => Utils::format_price($factor * ($sale['paid_pos'] ?? 0))  ],
            ];
            if ($isReturn && ($sale['change'] ?? 0) > 0) {
                $paymentRows[] = [ "label" => "Resto", "amount" => Utils::format_price($sale['change']) ];
            }
            $paymentRows[] = [ "label" => "Totale", "amount" => Utils::format_price($grand_total_signed) ];

            foreach ($paymentRows as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['label']); ?></td>
                    <td class="text-end"><?php echo $row['amount']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<p>&nbsp;</p>

<p class="display-6 underline b" id="grand-total">
    Totale: <?php echo Utils::format_price($grand_total_signed); ?> CHF
</p>



<button class="btn btn-secondary" onclick="initReprint()">Ristampa scontrino</button>

<script>
function initReprint() {
    const formData = new FormData();
    const receipt = {
        saleId: "<?php echo $saleId ?>",
        subtotal: "<?php echo Utils::format_price($subtotal_signed) ?>",
        total: "<?php echo Utils::format_price($grand_total_signed) ?>",
        discount: "<?php echo ($discount_value != 0 && $abs_subtotal_discountable > 0) ? ($discount_value . ' ' . $discount_type) : '' ?>",
        paymentMethod: "<?php echo $sale['payment_method'] ?>",
        username: "<?php echo Auth::get_fullname_by_username($sale['username']) ?>",
        customer: "<?php echo $customer ? ($customer['first_name'] . ' ' . $customer['last_name']) : 'Esterno' ?>",
        datetime: "<?php echo $sale['closed_at'] ?>",
        items: <?php echo json_encode($receiptItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
        type: "copy"
    };
    formData.append("receipt", JSON.stringify(receipt));

    fetch("actions/sales/reprint_receipt.php", {
        method: "POST",
        body: formData,
        credentials: "include"
    });
}
</script>

<?php end: ?>
