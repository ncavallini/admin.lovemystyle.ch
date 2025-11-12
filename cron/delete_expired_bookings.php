<?php
// Cron: every day at 23:59

require_once __DIR__ . "/../inc/inc.php";
$dbconnection = DBConnection::get_db_connection();

if(!isset($_GET['key']) || $_GET['key'] != $CONFIG['CRON_KEY']) {
    echo "Invalid key!";
    http_response_code(403);
    exit;
}

$sql = "SELECT * FROM bookings WHERE to_datetime < NOW()";
$stmt = $dbconnection->prepare($sql);
$stmt->execute();
$expiredBookings = $stmt->fetchAll();

foreach ($expiredBookings as $booking) {
    // Restock the product variant
    $sql = "UPDATE product_variants SET stock = stock + 1 WHERE product_id = :product_id AND variant_id = :variant_id";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([
        ":product_id" => $booking['product_id'],
        ":variant_id" => $booking['variant_id']
    ]);
}

$sql = "DELETE FROM bookings WHERE to_datetime < NOW()";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute();
?>