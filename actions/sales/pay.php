<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$saleId = $_POST['sale_id'] ?? "";
$total = $_POST['total'] ?? 0;
$paymentMethod = $_POST['payment_method'] ?? "CASH";

$sql = "UPDATE sales SET payment_method = ?, closed_at = NOW() WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$paymentMethod, $saleId]);

$sql = "SELECT i.*, p.name FROM sales_items i JOIN products p USING(product_id) WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$items = $stmt->fetchAll();

$subtotal = 0;
$receiptItems = [];

foreach ($items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $sql = "UPDATE product_variants SET stock = stock - :quantity WHERE product_id = :product_id AND variant_id = :variant_id";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([
        'quantity' => $item['quantity'],
        'product_id' => $item['product_id'],
        'variant_id' => $item['variant_id']
    ]);

    $receiptItems[] = [
        "sku" => InternalNumbers::get_sku($item['product_id'], $item['variant_id']),
        "name" => $item['name'],
        "price" => Utils::format_price($item['price']),
        "quantity" => $item['quantity'],
        "total" => Utils::format_price($item['price'] * $item['quantity'])
    ];
}

$sql = "SELECT s.*, c.customer_id, c.first_name, c.last_name FROM sales s LEFT JOIN customers c USING(customer_id) WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$sale = $stmt->fetch();

$total = Utils::compute_discounted_price($subtotal, $sale['discount'] ?? 0, $sale['discount_type'] ?? "CHF");



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
    "type" => "sale",
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

$sql = "UPDATE sales SET status = 'completed' WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);

if(strtoupper($paymentMethod) == "CASH") {
    $stmt = "UPDATE cash_content SET content = content + :content, last_updated_at = NOW(), last_updated_by = :username WHERE id = 1";
    $stmt = $dbconnection->prepare($stmt);
    $stmt->execute([
    ':content' => $total,
    ':username' => Auth::get_username()
]);
}

header("Location: /index.php?page=sales_view");
?>