<?php
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

$dbconnection = DBConnection::get_db_connection();

$productId = $_POST['product_id'] ?? "";
$sql = "SELECT * FROM products WHERE product_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$productId]);
if( $stmt->rowCount() == 0 ) {
    Utils::print_error("Prodotto non trovato.", true);
    die;
}

$sql = "SELECT MAX(variant_id) FROM product_variants WHERE product_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$productId]);
$variantId = $stmt->fetchColumn() + 1;

$sql = "INSERT INTO product_variants (product_id, variant_id, color, size, stock) VALUES (:product_id, :variant_id, :color, :size, :stock)";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
    ":product_id" => $productId, 
    ":variant_id" => $variantId,
    ":color" => $_POST['color'] ?? null, 
    ":size" => $_POST['size'] ?? null, 
    ":stock" => $_POST['stock'],
]);

header("Location: /index.php?page=variants_view&product_id=$productId");
