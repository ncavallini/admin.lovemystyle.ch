<?php
error_reporting(E_ALL & ~E_DEPRECATED); // Suppress deprecated warnings (Brevo's fault)
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

// Authorization check
Auth::require_owner();


$dbconnection = DBConnection::get_db_connection();
$sql = "SELECT email FROM customers WHERE customer_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$_GET['customer_id']]);
$email = $stmt->fetchColumn();
if(!$email) {
    Utils::print_error("Cliente non trovato.", true);
    die;
}
$sql = "DELETE FROM customers WHERE customer_id = ?";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([$_GET['customer_id']]);
if(!$res) {
    Utils::print_error("Impossibile eliminare il Cliente. È possibile eliminare un cliente solo se questi non ha alcuna vendita registrata.");
    die;
} 

Brevo::delete_customer($email); 
header(header: "Location: /index.php?page=customers_view")

?>
