<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$sql = "UPDATE products SET name = :name, supplier_id = :supplier_id, price = :price, vat_id = :vat_id WHERE product_id = :product_id";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([
    ":name" => $_POST["name"],
    ":supplier_id" => $_POST["supplier"],
    ":price" => Utils::price_to_db($_POST["price"]),
    ":vat_id" => $_POST["vat"],
    ":product_id" => $_POST["product_id"],
]);

if(!$res) {
    Utils::print_error("Errore durante la modifica del prodotto. " . $stmt->errorInfo()[2], true);
    die;
}
header("Location: /index.php?page=products_view");