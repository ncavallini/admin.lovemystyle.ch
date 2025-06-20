<?php
// Cron: every day at 23:59

require_once __DIR__ . "/../inc/inc.php";
$dbconnection = DBConnection::get_db_connection();

if(!isset($_GET['key']) || $_GET['key'] != $CONFIG['CRON_KEY']) {
    echo "Invalid key!";
    http_response_code(403);
    exit;
}

$today = $_GET['date'] ?? date('Y-m-d'); // Do not use CURDATE() in case of switching day during loop
$sql = "SELECT content FROM cash_content WHERE id = 1";
$stmt = $dbconnection->prepare($sql);
$stmt->execute();
$content = $stmt->fetchColumn();

$sql = "SELECT * FROM sales WHERE DATE(closed_at) = '$today' AND status = 'completed'";
$stmt = $dbconnection->prepare($sql);
$stmt->execute();
$sales = $stmt->fetchAll();

$income = 0;

foreach($sales as $sale) {
    $sql = "SELECT * FROM sales_items WHERE sale_id = ?";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([$sale['sale_id']]);
    $items = $stmt->fetchAll();
    $subtotal = array_reduce($items, function($carry, $item) {
        return $carry + $item['total_price'];
    }, 0);

    $income += Utils::compute_discounted_price($subtotal, $sale['discount'], $sale['discount_type']);
}


if(isset($_GET['no_update'])) {
   goto print_receipt;
}

$sql = "INSERT INTO closings VALUES(:date, :content, :income)";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
    ":date" => $today,
    ":content" => $content,
    ":income" => $income
]);

print_receipt: 
$posClient = POSHttpClient::get_http_client();
$posClient->post('/receipt/close', [
    'json' => [
        'date' => $today,
        'content' => Utils::format_price($content),
        'income' => Utils::format_price($income)
    ]
]);
