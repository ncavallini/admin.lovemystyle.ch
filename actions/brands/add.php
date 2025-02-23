<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$sql = "INSERT INTO brands (brand_id, name, supplier_id) VALUES (UUID(), :name, :supplier_id)";
$stmt = $dbconnection->prepare($sql);  
$stmt->execute([
    'name' => $_POST['name'],
    'supplier_id' => $_POST['supplier']
]);

header("Location: /index.php?page=brands_view");