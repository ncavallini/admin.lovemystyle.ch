<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$saleId = $_GET['sale_id'] ?? "";


$sql = "SELECT * FROM sales WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$sale = $stmt->fetch();


$sql = "UPDATE sales SET status = 'totally_canceled' WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);



$sql = "SELECT * FROM sales_items WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$items = $stmt->fetchAll();

$status = $sale['status'];
$subtotal = array_reduce($items, function($carry, $item) {
    return $carry + $item['price'] * $item['quantity'];
}, 0);  
$total = Utils::compute_discounted_price($subtotal, $sale['discount'], $sale['discount_type']);


if(strtoupper($sale['payment_method']) == "CASH") {
    $sql = "UPDATE cash_content SET content = content - ?, last_updated_at = NOW(), last_updated_by = ? WHERE id = 1";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([$total, Auth::get_username()]);
}

if($status !== "open") {
    foreach ($items as $item) {
        $sql = "UPDATE product_variants SET stock = stock + ? WHERE product_id = ? AND variant_id = ?";
        $stmt = $dbconnection->prepare($sql);
        $stmt->execute([$item['quantity'], $item['product_id'], $item['variant_id']]);
    }
}


header("Location: /index.php?page=sales_view");