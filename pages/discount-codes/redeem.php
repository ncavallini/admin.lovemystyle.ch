<h1>Riscatta Codice Sconto</h1>

<?php
$dbconnection = DBConnection::get_db_connection();
$saleId = $_GET['sale_id'];
$sql = "SELECT * FROM sales WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$sale = $stmt->fetch();

?>

<form action="actions/discount-codes/redeem.php" method="POST">
    <input type="hidden" name="sale_id" value="<?php echo $saleId ?>">
    <input type="hidden" name="customer_id" value="<?php echo $sale['customer_id'] ?>">
    <label for="code">Codice Sconto *</label>
    <input type="text" name="code" class="form-control" required maxlength="8">
    <br>
    <button type="submit" class="btn btn-primary">Riscatta</button>
</form>
