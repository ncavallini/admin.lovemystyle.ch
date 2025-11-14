<?php
error_reporting(E_ALL & ~E_DEPRECATED); // Suppress deprecated warnings (Brevo's fault)
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

// Authorization check
Auth::require_owner();


$dbconnection = DBConnection::get_db_connection();

$sql = "DELETE FROM discount_codes WHERE code = ?";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([$_GET['code']]);

header(header: "Location: /index.php?page=discount-codes_view");

?>
