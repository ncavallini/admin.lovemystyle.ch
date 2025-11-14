<?php 
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();


$dbconnection = DBConnection::get_db_connection();
$term = $_GET['term'] ?? null;

$sql = "SELECT name, brand_id FROM brands";
if (!empty($term)) {
    $sql .= " WHERE name LIKE :term";
}
$sql .= " ORDER BY name";

$stmt = $dbconnection->prepare($sql);

// Add wildcards to the term if it's not empty
$params = [];
if (!empty($term)) {
    $params[":term"] = "%" . $term . "%";
}

$stmt->execute($params);
$brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

$brands_out = array_map(function($brand) {
    return [
        "id" => $brand['brand_id'],
        "text" => $brand['name']
    ];
}, $brands);

header("Content-Type: application/json");
echo json_encode([
    "results" => $brands_out
]);
?>
