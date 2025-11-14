<?php
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

$dbconnection = DBConnection::get_db_connection();
$saleId = $_GET['sale_id'] ?? "";
$sku = $_GET['sku'];
list($productId, $variantId) = InternalNumbers::parse_sku($sku);
$sql = "DELETE FROM sales_items WHERE sale_id = :sale_id AND product_id = :product_id AND variant_id = :variant_id";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
    ":sale_id" => $saleId,
    ":product_id"=> $productId,
    ":variant_id"=> $variantId
]);
header("Location: /index.php?page=sales_add&sale_id=$saleId&negative=" . $_GET['negative']);

?>
