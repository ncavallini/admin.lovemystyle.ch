<?php
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

$dbconnection = DBConnection::get_db_connection();
$sql = "UPDATE products SET name = :name, brand_id = :brand_id, full_price = :price, discounted_price = NULL, vat_id = :vat_id, last_edit_at = NOW() WHERE product_id = :product_id";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([
    ":name" => $_POST["name"],
    ":brand_id" => $_POST["brand"],
    ":price" => Utils::price_to_db($_POST["price"]),
    ":vat_id" => $_POST["vat"],
    ":product_id" => $_POST["product_id"],
]);

if(!$res) {
    Utils::print_error("Errore durante la modifica del prodotto. " . $stmt->errorInfo()[2], true);
    die;
}
header("Location: /index.php?page=products_view");
