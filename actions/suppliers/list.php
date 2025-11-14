<?php 
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();


$dbconnection = DBConnection::get_db_connection();
$term = $_GET['term'] ?? null;

$sql = "SELECT name, supplier_id FROM suppliers";
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
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$suppliers_out = array_map(function($supplier) {
    return [
        "id" => $supplier['supplier_id'],
        "text" => $supplier['name']
    ];
}, $suppliers);

header("Content-Type: application/json");
echo json_encode([
    "results" => $suppliers_out
]);
?>
