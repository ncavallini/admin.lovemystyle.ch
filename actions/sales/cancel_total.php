<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$saleId = $_GET['sale_id'] ?? "";


$sql = "SELECT * FROM sales WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$sale = $stmt->fetch();


$sql = "UPDATE sales SET status = 'totally_canceled' WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);



$sql = "SELECT i.*, p.name FROM sales_items i JOIN products p USING(product_id) WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$items = $stmt->fetchAll();

$receiptItems = [];
foreach ($items as $item) {
    $receiptItems[] = [
        "sku" => InternalNumbers::get_sku($item['product_id'], $item['variant_id']),
        "name" => $item['name'],
        "price" => Utils::format_price(-$item['price']),
        "quantity" => $item['quantity'],
        "total" => Utils::format_price(-$item['price'] * $item['quantity'])
    ];
}

$status = $sale['status'];
$subtotal = array_reduce($items, function($carry, $item) {
    return $carry + $item['price'] * $item['quantity'];
}, 0);  
$total = Utils::compute_discounted_price($subtotal, $sale['discount'], $sale['discount_type']);


if(strtoupper($sale['payment_method']) == "CASH") {
    $sql = "UPDATE cash_content SET content = content - ?, last_updated_at = NOW(), last_updated_by = ? WHERE id = 1";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([$total, Auth::get_username()]);
}

if($status !== "open") {
    foreach ($items as $item) {
        $sql = "UPDATE product_variants SET stock = stock + ? WHERE product_id = ? AND variant_id = ?";
        $stmt = $dbconnection->prepare($sql);
        $stmt->execute([$item['quantity'], $item['product_id'], $item['variant_id']]);
    }
}

$receipt = [
    "saleId" => $saleId,
    "subtotal" => Utils::format_price(-$subtotal),
    "total" => Utils::format_price($total * -1),
    "discount" => ($sale['discount']) != 0 ? $sale['discount'] . " " . $sale['discount_type'] : "",
    "paymentMethod" => $sale['payment_method'],
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


header("Location: /index.php?page=sales_view");