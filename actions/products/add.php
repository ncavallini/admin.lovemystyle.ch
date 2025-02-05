<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$sql = "INSERT INTO products VALUES (:product_id, :name, :supplier, :price, :vat, NOW())";
$stmt = $dbconnection->prepare($sql);
$productId = InternalNumbers::get_product_number();
$res = $stmt->execute([
    ":product_id" => $productId,
    ":name" => $_POST['name'],
    ":supplier" => $_POST['supplier'],
    ":price" => Utils::price_to_db($_POST['price']),
    ":vat" => $_POST['vat']

]);

if(!$res) {
    Utils::print_error("Errore nell'inserimento del prodotto. " . $stmt->errorInfo()[2]);
    die;
}

header(header: "Location: /index.php?page=variants_view&product_id=$productId");
?>
