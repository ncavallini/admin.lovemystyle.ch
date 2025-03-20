<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$saleId = $_GET['sale_id'] ?? "";


$sql = "SELECT * FROM sales WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$sale = $stmt->fetch();


$sql = "UPDATE sales SET status = 'partially_canceled' WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);

$sql = "SELECT * FROM sales_items WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$items = $stmt->fetchAll();

$subtotal = array_reduce($items, function($carry, $item) {
    return $carry + $item['price'] * $item['quantity'];
}, 0);  
$total = Utils::compute_discounted_price($subtotal, $sale['discount'], $sale['discount_type']);


if(strtoupper($sale['payment_method']) == "CASH") {
    $sql = "UPDATE cash_content SET content = content - ?, last_updated_at = NOW(), last_updated_by = ? WHERE id = 1";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([$total, Auth::get_username()]);
}

if($sale['status'] !== "open") {
    foreach ($items as $item) {
        $sql = "UPDATE product_variants SET stock = stock + ? WHERE product_id = ? AND variant_id = ?";
        $stmt = $dbconnection->prepare($sql);
        $stmt->execute([$item['quantity'], $item['product_id'], $item['variant_id']]);
    }
}

$sql = "INSERT INTO sales VALUES (UUID(), NOW(), NULL, :customer_id, NULL, :username, :discount, :discount_type, 'open')";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
    'customer_id' => $sale['customer_id'],
    'username' => $sale['username'],
    'discount' => $sale['discount'],
    'discount_type' => $sale['discount_type']
]);

$sql = "SELECT sale_id FROM sales WHERE username = :username AND status = 'open' ORDER BY created_at DESC LIMIT 1";
$stmt = $dbconnection->prepare($sql);
$stmt->execute(['username' => $sale['username']]);
$newSaleId = $stmt->fetchColumn(0);


foreach($items as $item) {
    $sql = "INSERT INTO sales_items (sale_id, product_id, variant_id, quantity, price) VALUES (:sale_id, :product_id, :variant_id, :quantity, :price)";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([
        'sale_id' => $newSaleId,
        'product_id' => $item['product_id'],
        'variant_id' => $item['variant_id'],
        'quantity' => $item['quantity'],
        'price' => $item['price']
    ]);
}

$receipt = [
    "saleId" => $saleId,
    "subtotal" => Utils::format_price($subtotal),
    "total" => Utils::format_price($total),
    "discount" => ($sale['discount']) != 0 ? $sale['discount'] . " " . $sale['discount_type'] : "",
    "paymentMethod" => $paymentMethod,
    "username" => Auth::get_fullname_by_username($sale['username']),
    "customer" => ($sale['customer_id']) !== null ? $sale['first_name'] . " " . $sale['last_name'] : "Esterno",
    "datetime" => $sale['closed_at'],
    "items" => $receiptItems,
    "type" => "return",
];

$posClient = POSHttpClient::get_http_client();
try {
    $posClient->post("/receipt/print", [
        "json" => [
            "receipt" => $receipt
        ]
        ]);
} catch (Exception $e) {
    echo $e->getMessage();
}

header("Location: /index.php?page=sales_add&sale_id=" . $newSaleId);
?>
