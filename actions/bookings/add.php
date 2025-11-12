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

$to_datetime = $_POST['to_datetime'];

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


$sql = "SELECT b.*, c.first_name, c.last_name, c.tel, c.email, p.name AS product_name FROM bookings b JOIN customers c USING(customer_id) JOIN products p USING(product_id) ORDER BY from_datetime DESC LIMIT 1";
$stmt = $dbconnection->prepare($sql);
$stmt->execute();
$booking = $stmt->fetch();


Utils::redirect("/index.php?page=bookings_view");

$posClient = POSHttpClient::get_http_client();
for($i = 0; $i < 2; $i++) {
    $posClient->post("/receipt/booking", [
    "json" => ["receipt" => [
        "firstName" => $booking['first_name'],
        "lastName" => $booking['last_name'],
        "tel" => Utils::format_phone_number($booking['tel'] ?? ""),
        "email" => $booking['email'],
        "sku" => InternalNumbers::get_sku($booking['product_id'], $booking['variant_id']),
        "productName" => $booking['product_name'],
        "start" => date("d/m/Y, H:i:s", strtotime($booking['from_datetime'])),
        "end" => date("d/m/Y, H:i:s", strtotime($booking['to_datetime'])),
        "bookingId" => $booking['booking_id'],
        ]]
]);
if($i == 0) {
    sleep(seconds: 1); // Wait for the first receipt to be processed
    }
}
