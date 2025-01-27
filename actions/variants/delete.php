<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$sql = "DELETE FROM product_variants WHERE product_id = ? AND variant_id = ?";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([$_GET['product_id'], $_GET['variant_id']]);
if(!$res) {
    Utils::print_error("Impossibile eliminare la variante. È possibile eliminare una variante solo se non è associata a nessuna vendita.");
    die;
} 
header(header: "Location: /index.php?page=variants_view&product_id=" . $_GET['product_id'] );

?>