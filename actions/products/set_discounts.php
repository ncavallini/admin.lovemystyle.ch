<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$discountedPrices = json_decode(file_get_contents('php://input'), true);

foreach ($discountedPrices as $productId => $discountedPrice) {
    $sql = "UPDATE products SET discounted_price = :discounted_price WHERE product_id = :product_id";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([
        ":discounted_price" => $discountedPrice,
        ":product_id" => $productId
    ]);
}

http_response_code(200);