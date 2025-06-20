<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();

$parsedSku = InternalNumbers::parse_sku($_POST['sku']);
if(!$parsedSku) {
    Utils::print_error("Numero d'articolo non valido.", true);
    die;
}
$product_id = $parsedSku[0];
$variant_id = $parsedSku[1];

$sql = "SELECT * FROM product_variants WHERE product_id = :product_id AND variant_id = :variant_id";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
    ":product_id" => $product_id,
    ":variant_id" => $variant_id
]);
$variant = $stmt->fetch();
if(!$variant) {
    Utils::print_error("Numero d'articolo non valido.", true);
    die;
}

$to_datetime = Utils::get_next_closing_datetime();

$sql = "INSERT INTO bookings VALUES (UUID(), :customer_id, NOW(), :to_datetime, :product_id, :variant_id)";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([
    ":customer_id" => $_POST['customer_id'],
    ":to_datetime" => $to_datetime,
    ":product_id" => $product_id,
    ":variant_id" => $variant_id
]);
if(!$res) {
    Utils::print_error("Errore durante l'inserimento della prenotazione. " . $stmt->errorInfo()[2], true);
    die;
}

$sql = "UPDATE product_variants SET stock = stock - 1 WHERE product_id = :product_id AND variant_id = :variant_id";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
    ":product_id" => $product_id,
    ":variant_id" => $variant_id
]);


Utils::redirect("/index.php?page=bookings_view");