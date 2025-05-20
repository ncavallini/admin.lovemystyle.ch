<?php
$dbconnection = DBConnection::get_db_connection();

$saleId = $_GET['sale_id'];

$sql = "SELECT * FROM sales WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$sale = $stmt->fetch();

if(!$sale) {
    Utils::print_error("Vendita non trovata");
    goto end;
}

$sql = "SELECT i.*, p.name FROM sales_items i JOIN products p USING(product_id) WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>

<h1>Dettagli vendita</h1>

<?php
    if($sale['status'] === "totally_canceled") {
        echo "<div class='alert alert-danger b' style='font-size: 2em;'>Vendita cancellata</div>";
    }
?>

<ul>
    <li><b>Operatore:</b> <?php echo Auth::get_fullname_by_username($sale['username']) ?></li>
    <li><b>Data e ora di vendita:</b> <?php echo Utils::format_datetime($sale['closed_at']) ?></li>
    <li><b>Pagato con: </b> <?php echo strtoupper($sale['payment_method']) ?></li>    
</ul>

<br>
    <h2>Voci di vendita</h2>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Codice Articolo</th>
                    <th>Nome</th>
                    <th>Variante</th>
                    <th>Quantità</th>
                    <th>Prezzo unit. (CHF)</th>
                    <th>Prezzo totale (CHF)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $subtotal = 0;
                $pieces = 0;
                $receiptItems = [];
                foreach ($items as $item) {
                    $receiptItems[] = [
                        "sku" => InternalNumbers::get_sku($item['product_id'], $item['variant_id']),
                        "name" => $item['name'],
                        "price" => Utils::format_price($item['price']),
                        "quantity" => $item['quantity'],
                        "total" => Utils::format_price($item['price'] * $item['quantity'])
                    ];

                    $sku = InternalNumbers::get_sku($item["product_id"], $item["variant_id"]);
                    $sql = "SELECT color, size FROM product_variants WHERE product_id = ? AND variant_id = ?";
                    $stmt = $dbconnection->prepare($sql);
                    $stmt->execute([$item['product_id'], $item['variant_id']]);
                    $variantData = $stmt->fetch();
                    $subtotal += $item['price'] * $item['quantity'];
                    $pieces += $item['quantity'];
                    echo "<tr>";
                    Utils::print_table_row(InternalNumbers::get_sku($item['product_id'], $item['variant_id']));
                    Utils::print_table_row($item['name']);
                    Utils::print_table_row($variantData['color'] . " / " . $variantData['size']);
                    Utils::print_table_row($item['quantity']);
                    Utils::print_table_row(Utils::format_price($item['price']));
                    Utils::print_table_row(Utils::format_price($item['total_price']));
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
        <p><?php echo $pieces ?> pezzi.</p>
    <hr>
    <p class="display-6 underline">Subtotale: <?php echo Utils::format_price($subtotal) ?> CHF</p>
    <p>&nbsp;</p>
    <h2>Sconto</h2>
    <div class="row d-flex align-items-center">
    <div class="col-10">
    <label for="discount" class="form-label">Sconto</label>

        <div class="input-group mb-3">
            <input readonly type="number" class="form-control" value="<?php echo number_format( $sale["discount"] ?? 0, 2) ?>" min="0" step="0.01" name="discount" id="discount-input" required oninput="computeGrandTotal()">
            <span class="input-group-text" id="discount-input-group-text"><?php echo $sale['discount_type'] ?></span>
        </div>
    </div>
</div>
<p>&nbsp;</p>

<h2>Cliente</h2>
<?php
    if($sale['customer_id'] == null) {
        echo "<p><i>Esterno</i></p>";
    }
    else {
        $sql = "SELECT first_name, last_name, birth_date, customer_number FROM customers WHERE customer_id = ?";
        $stmt = $dbconnection->prepare($sql);
        $stmt->execute([$sale['customer_id']]);
        $customer = $stmt->fetch();
        echo "<p>" . $customer['first_name'] . " " . $customer['last_name'] . " (" . Utils::format_date($customer['birth_date']) . ") - N. Cliente: " . $customer['customer_number'] . "</p>";
    }
?>
<hr>
<p class="display-6 underline b" id="grand-total">Totale: <?php echo Utils::format_price( Utils::compute_discounted_price($subtotal, $sale['discount'], $sale['discount_type'])); ?> CHF</p>

<button class="btn btn-secondary" onclick="initReprint()">Ristampa scontrino</button>

<script>
    function initReprint() {
        const formData = new FormData();
        const receipt = {
            saleId: "<?php echo $saleId ?>",
            subtotal: "<?php echo Utils::format_price($subtotal) ?>",
            total: "<?php echo Utils::format_price(Utils::compute_discounted_price($subtotal, $sale['discount'], $sale['discount_type'])) ?>",
            discount: "<?php echo ($sale['discount']) != 0 ? $sale['discount'] . " " . $sale['discount_type'] : "" ?>",
            paymentMethod: "<?php echo $sale['payment_method'] ?>",
            username: "<?php echo Auth::get_fullname_by_username($sale['username']) ?>",
            customer: "<?php echo ($sale['customer_id']) !== null ? $customer['first_name'] . " " . $customer['last_name'] : "Esterno" ?>",
            datetime: "<?php echo $sale['closed_at'] ?>",
            items: <?php echo json_encode($receiptItems) ?>,
            type: "copy"
        };
        formData.append("receipt", JSON.stringify(receipt));

        fetch("actions/sales/reprint_receipt.php", {
            method: "POST",
            body: formData,
            credentials: "include"
        })
    }
</script>

<?php end: ?>