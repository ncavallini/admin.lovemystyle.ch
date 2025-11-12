<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$saleId = $_GET['sale_id'] ?? "";


$sql = "SELECT * FROM sales WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$sale = $stmt->fetch();

$discount = $sale['discount'] ?? 0;
$discountType = $sale['discount_type'] ?? "CHF";
$customer_id = $sale['customer_id'] ?? null;

$sql = "INSERT INTO sales (sale_id, created_at, customer_id, username, discount, discount_type, status) VALUES (UUID(), NOW(), :customer_id, :username, :discount, :discount_type, 'negative')";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
    'customer_id' => $customer_id,
    'discount' => $discount,
    'discount_type' => $discountType,
    'username' => Auth::get_username()
]);

$sql = "SELECT sale_id FROM sales WHERE status = 'negative' ORDER BY created_at DESC LIMIT 1";
$stmt = $dbconnection->prepare($sql);
$stmt->execute();
$oldSaleId = $saleId;
$saleId = $stmt->fetchColumn();


header("Location: /index.php?page=sales_add&sale_id=$saleId&negative=1&old_sale_id=$oldSaleId");
?>
