<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$_POST = json_decode(file_get_contents("php://input"), true);
$discount = $_POST['discount'];
$productIds = $_POST['product_ids'];

foreach ($productIds as $productId) {
    $sql = "UPDATE products SET price = price - (price * :discount / 100) WHERE product_id = :product_id";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([":discount" => $discount, ":product_id" => $productId]);
}