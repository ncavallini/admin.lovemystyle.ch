<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$saleId = $_POST['sale_id'] ?? "";
$sku = $_POST['sku'];
$action = $_POST['action'] ?? 'new'; // 'inc', 'new'

try {
    list($productId, $variantId) = InternalNumbers::parse_sku($sku);
} catch(Exception $e) {
    header("Location: /index.php?page=sales_add&sale_id=$saleId");
    die;
}


if (empty($productId) || empty($variantId)) {
    Utils::print_error("Impossible aggiungere l'articolo. SKU $sku non valido.", true);
    die;
}

$sql = "SELECT * FROM sales_items WHERE sale_id = :sale_id AND product_id = :product_id AND variant_id = :variant_id";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
    ":sale_id" => $saleId,
    ":product_id" => $productId,
    ":variant_id" => $variantId
]);
if($stmt->rowCount() > 0) {
    $action = "inc";
}


if($action === "new") {

    $sql = "SELECT price FROM products WHERE product_id = ?";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([$productId]);
    $price = $stmt->fetchColumn(0);

    $sql = "INSERT INTO sales_items (sale_id, product_id, variant_id, quantity, price) VALUES (:sale_id, :product_id, :variant_id, 1, :price)";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([
        ":sale_id" => $saleId,
        ":product_id" => $productId,
        ":variant_id" => $variantId,
        ":price" => $price
    ]);
}

else if($action === "inc") {
    $sql = "UPDATE sales_items SET quantity = quantity + 1 WHERE sale_id = :sale_id AND product_id = :product_id AND variant_id = :variant_id";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([
        ":sale_id" => $saleId,
        ":product_id" => $productId,
        ":variant_id" => $variantId
    ]);
}

else {}

header("Location: /index.php?page=sales_add&sale_id=$saleId&negative=" . $_POST['negative']);
?>