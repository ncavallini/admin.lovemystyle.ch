<?php 
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();


$dbconnection = DBConnection::get_db_connection();
$term = $_GET['term'] ?? "";
$retVal = ["results" => []];

header("Content-Type: application/json");

if(strlen($term) < 3) {
    echo json_encode(value: $retVal);
    exit();
}

$sql = "SELECT p.product_id, p.name, p.brand_id, b.name AS brand_name FROM products p JOIN brands b ON p.brand_id = b.brand_id WHERE p.product_id = :product_id OR p.name LIKE :term OR b.name LIKE :term";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
    ":product_id" => $term,
    ":term" => "%$term%"
]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$retVal["results"] = $products;

echo json_encode($retVal);  
