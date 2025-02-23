<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$saleId = $_POST['sale_id'] ?? "";
$customerNumber = $_POST['customer_number'] ?? NULL;

$sql = "UPDATE sales SET customer_id = (SELECT customer_id FROM customers WHERE customer_number = :customer_number) WHERE sale_id = :sale_id";
$stmt = $dbconnection->prepare($sql);   
$stmt->execute([
    ":sale_id" => $saleId,
    ":customer_number" => $customerNumber
]);
