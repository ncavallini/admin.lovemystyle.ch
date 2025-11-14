<?php
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

// Authorization check
Auth::require_owner();


$dbconnection = DBConnection::get_db_connection();


$sql = "SELECT product_id, variant_id FROM bookings WHERE booking_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$_GET['booking_id']]);
$product = $stmt->fetch();
$sql = "UPDATE product_variants SET stock = stock + 1 WHERE product_id = ? AND variant_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$product['product_id'], $product['variant_id']]);



$sql = "DELETE FROM bookings WHERE booking_id = ?";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([$_GET['booking_id']]);
if(!$res) {
    Utils::print_error("Impossibile eliminare la riservazione.");
}
    header(header: "Location: /index.php?page=bookings_view");

?>
