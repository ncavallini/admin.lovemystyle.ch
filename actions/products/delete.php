<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$sql = "DELETE FROM products WHERE product_id = ?";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([$_GET['product_id']]);
if(!$res) {
    Utils::print_error("Impossibile eliminare il prodotto");
    die;
} 
header(header: "Location: /index.php?page=products_view")

?>