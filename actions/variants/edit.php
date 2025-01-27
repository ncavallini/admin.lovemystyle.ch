<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$sql = "UPDATE product_variants SET color = ?, size = ?, stock = ? WHERE product_id = ? AND variant_id = ?";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([
    $_POST['color'] ?? null,
    $_POST['size'] ?? null,
    $_POST['stock'],
    $_POST['product_id'],
    $_POST['variant_id'],
]);

if(!$res) {
    Utils::print_error("Impossibile aggiornare la variante.", true);
    die;
}

header("Location: /index.php?page=variants_view&product_id=" . $_POST['product_id']);
?>