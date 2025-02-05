<?php
$dbconnection = DBConnection::get_db_connection();
$saleId = $_GET['sale_id'] ?? "";
$sql = "SELECT * FROM sales WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$sale = $stmt->fetch();
if (!$sale) {
    Utils::print_error("Vendita non trovata");
    goto end;
}

$total = 0;

$sql = "SELECT price, quantity FROM sales_items WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$items = $stmt->fetchAll();

foreach ($items as $item) {
    $total += $item["price"] * $item["quantity"];
}

if($sale["discount_type"] === "CHF") {
    $total -= ($sale['discount'] * 100);
}
else {
    $total = (1 - $sale['discount'] / 100) * $total;
}

?>

<h1>Pagamento</h1>
<p class="display-6 underline">Totale da pagare: <?php echo Utils::format_price($total) ?> CHF</p>

<form action="actions/sales/pay.php" method="POST">
    <input type="hidden" name="sale_id" value="<?php echo $saleId ?>">
    <input type="hidden" name="total" value="<?php echo $total ?>">
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="fa-solid fa-money-bill"></i></span>
        <input type="number" step="0.01" min="0" name="amount" class="form-control" required placeholder="Importo pagato" id="amount">
        <span class="input-group-text">CHF</span>
    </div>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="fa-solid fa-credit-card"></i></span>
        <select name="payment_method" class="form-select" required>
            <option value="CASH">Contanti</option>
            <option value="POS">Carta di credito / TWINT</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Conferma pagamento</button>
</form>

<? end: ?>