<?php
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

// Authorization check
Auth::require_owner();


$dbconnection = DBConnection::get_db_connection();
$sql = "DELETE FROM brands WHERE brand_id = ?";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([$_GET['brand_id']]);
if(!$res) {
    Utils::print_error("Impossibile eliminare il Brand. È possibile eliminare un brand solo se non vi è associato nessun prodotto", true);
} 
header(header: "Location: /index.php?page=brands_view")

?>
