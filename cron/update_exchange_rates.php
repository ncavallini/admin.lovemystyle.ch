<?php
// Cron: every day at 02:00

require_once __DIR__ . "/../inc/inc.php";
$dbconnection = DBConnection::get_db_connection();

if(!isset($_GET['key']) || $_GET['key'] != $CONFIG['CRON_KEY']) {
    echo "Invalid key!";
    http_response_code(403);
    exit;
}

$today = date('Y-m-d');

$freecurrencyapi = new \FreeCurrencyApi\FreeCurrencyApi\FreeCurrencyApiClient($GLOBALS['CONFIG']['FREECURRENCY_API_KEY']);
$rate = $freecurrencyapi->latest([
    'base_currency' => 'CHF',
    'currencies' => 'EUR',
])['data']['EUR'] ?? 1;

$sql = "UPDATE exchange_rates SET rate = ROUND(?, 4), date = NOW()";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$rate]);


?>