<?php
// Cron: every day at 23:59
require_once __DIR__ . "/../inc/inc.php";
$db = DBConnection::get_db_connection();

if (!isset($_GET['key']) || $_GET['key'] != $CONFIG['CRON_KEY']) {
    echo "Invalid key!";
    http_response_code(403);
    exit;
}

// Use provided date or today's date (avoid CURDATE() drift during long runs)
$today = $_GET['date'] ?? date('Y-m-d');

// 1) Determine cash content to print for the requested date
if ($today === date("Y-m-d")) {
    $sql = "SELECT content FROM cash_content WHERE id = 1";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $content = (int)$stmt->fetchColumn();
} else {
    $sql = "SELECT content FROM closings WHERE date = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$today]);
    $content = (int)$stmt->fetchColumn();
}

// 2) Fetch sales for that date (parameterized)
$sql = "SELECT sale_id, status, discount, discount_type
        FROM sales
        WHERE DATE(closed_at) = ?
          AND (status = 'completed' OR status = 'negative')";
$stmt = $db->prepare($sql);
$stmt->execute([$today]);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3) Compute daily income in cents, with new rules:
//    - Apply sale-level discount ONLY to items with is_discounted = 0
//    - Discounted items CANNOT be returned; if such a (invalid) return exists, ignore it defensively
$income = 0;

foreach ($sales as $sale) {
    $sign = ($sale['status'] === 'negative') ? -1 : 1;

    // Load line items (cents)
    $sql = "SELECT quantity, price, total_price, is_discounted
            FROM sales_items
            WHERE sale_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$sale['sale_id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $absSubtotalDiscountable    = 0; // is_discounted = 0
    $absSubtotalNonDiscountable = 0; // is_discounted = 1
    $containsDiscounted         = false;

    foreach ($items as $it) {
        $line = (int)$it['price'] * (int)$it['quantity'];
        if (!empty($it['is_discounted'])) {
            $containsDiscounted = true;
            $absSubtotalNonDiscountable += $line;
        } else {
            $absSubtotalDiscountable += $line;
        }
    }

    // Defensive: if a return includes discounted items, ignore its impact
    if ($sale['status'] === 'negative' && $containsDiscounted) {
        continue;
    }

    // Apply sale-level discount ONLY to discountable bucket
    $discountVal  = (float)($sale['discount'] ?? 0);
    $discountType = (string)($sale['discount_type'] ?? 'CHF');

    $discountCentsApplied = 0;
    if ($discountVal > 0 && $absSubtotalDiscountable > 0) {
        if ($discountType === 'CHF') {
            $discountCentsApplied = min((int)round($discountVal * 100), $absSubtotalDiscountable);
        } else { // "%"
            $discountCentsApplied = (int) floor($absSubtotalDiscountable * ($discountVal / 100));
        }
    }

    $absGrandTotal = ($absSubtotalDiscountable - $discountCentsApplied) + $absSubtotalNonDiscountable;
    $income += $sign * $absGrandTotal;
}

// 4) Optionally persist the closing row (unless no_update is requested)
if (!isset($_GET['no_update'])) {
    $sql = "INSERT INTO closings (date, content, income) VALUES (:date, :content, :income)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':date'    => $today,
        ':content' => (int)$content,
        ':income'  => (int)$income
    ]);
}

// 5) Print the closing receipt
$posClient = POSHttpClient::get_http_client();
$posClient->post('/receipt/close', [
    'json' => [
        'date'    => (new DateTime($today))->format('d/m/Y'),
        'content' => Utils::format_price((int)$content),
        'income'  => Utils::format_price((int)$income)
    ]
]);
