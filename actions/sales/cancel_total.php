<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$saleId = $_GET['sale_id'] ?? "";


$sql = "SELECT status FROM sales WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$status = $stmt->fetchColumn(0);


$sql = "UPDATE sales SET status = 'totally_canceled' WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);

$sql = "SELECT * FROM sales_items WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$items = $stmt->fetchAll();

if($status !== "open") {
    foreach ($items as $item) {
        $sql = "UPDATE product_variants SET stock = stock + ? WHERE product_id = ? AND variant_id = ?";
        $stmt = $dbconnection->prepare($sql);
        $stmt->execute([$item['quantity'], $item['product_id'], $item['variant_id']]);
    }
}


header("Location: /index.php?page=sales_view");