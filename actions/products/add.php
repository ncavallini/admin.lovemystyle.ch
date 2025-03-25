<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$sql = "INSERT INTO products VALUES (:product_id, :name, :brand_id, :price, :vat, NOW(), FALSE)";
$stmt = $dbconnection->prepare($sql);
$productId = InternalNumbers::get_product_number();
$res = $stmt->execute([
    ":product_id" => $productId,
    ":name" => $_POST['name'],
    ":brand_id" => $_POST['brand'],
    ":price" => Utils::price_to_db($_POST['price']),
    ":vat" => $_POST['vat']

]);

if(!$res) {
    Utils::print_error("Errore nell'inserimento del prodotto. " . $stmt->errorInfo()[2]);
    die;
}

header(header: "Location: /index.php?page=variants_view&product_id=$productId");
?>
