<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$sql = "SELECT b.*, c.first_name, c.last_name, c.tel, c.email, p.name AS product_name FROM bookings b JOIN customers c USING(customer_id) JOIN products p USING(product_id) WHERE booking_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$_GET['booking_id']]);
$booking = $stmt->fetch();



$posClient = POSHttpClient::get_http_client();
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
Utils::redirect("/index.php?page=bookings_view");
