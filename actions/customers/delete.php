<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$sql = "DELETE FROM customers WHERE customer_id = ?";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([$_GET['customer_id']]);
if(!$res) {
    Utils::print_error("Impossibile eliminare il Cliente. È possibile eliminare un cliente solo se questi non ha alcuna vendita registrata.");
    die;
} 
header(header: "Location: /index.php?page=customers_view")

?>