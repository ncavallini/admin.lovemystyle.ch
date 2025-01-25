<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$sql = "DELETE FROM suppliers WHERE supplier_id = ?";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([$_GET['supplier_id']]);
if(!$res) {
    Utils::print_error("Impossibile eliminare il Fornitore. È possibile eliminare un fornitore solo se questi non ha alcun prodotto associato.");
    die;
} 
header(header: "Location: /index.php?page=suppliers_view")

?>