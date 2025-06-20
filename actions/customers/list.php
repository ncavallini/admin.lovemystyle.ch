<?php 
require_once __DIR__ . "/../actions_init.php";

$dbconnection = DBConnection::get_db_connection();
$term = $_GET['term'] ?? "";
$retVal = ["results" => []];

header("Content-Type: application/json");

if(strlen($term) < 3) {
    echo json_encode(value: $retVal);
    exit();
}

$sql = "SELECT customer_id, customer_number, first_name, last_name, birth_date, tel, email FROM customers WHERE customer_number = :customer_number OR last_name LIKE :term OR first_name LIKE :term";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
    ":customer_number" => $term,
    ":term" => "%$term%"
]);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
$retVal["results"] = $customers;

echo json_encode($retVal);  