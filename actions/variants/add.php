<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();

$productId = $_POST['product_id'] ?? "";
$sql = "SELECT * FROM products WHERE product_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$productId]);
if( $stmt->rowCount() == 0 ) {
    Utils::print_error("Prodotto non trovato.", true);
    die;
}

$sql = "INSERT INTO product_variants (product_id, color, size, stock) VALUES (:product_id, :color, :size, :stock)";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
    ":product_id" => $productId, 
    ":color" => $_POST['color'] ?? null, 
    ":size" => $_POST['size'] ?? null, 
    ":stock" => $_POST['stock'],
]);

header("Location: /index.php?page=variants_view&product_id=$productId");