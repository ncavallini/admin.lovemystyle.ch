<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$sql = "UPDATE brands SET name = :name, supplier_id = :supplier_id WHERE brand_id = :brand_id";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
    ":name" => $_POST['name'],
    ":supplier_id" => $_POST['supplier'],
    ":brand_id" => $_POST['brand_id']
]);

header("Location: /index.php?page=brands_view");