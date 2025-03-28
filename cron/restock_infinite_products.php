<?php
// Cron: every day at 23:59

require_once __DIR__ . "/../inc/inc.php";
$dbconnection = DBConnection::get_db_connection();

if(!isset($_GET['key']) || $_GET['key'] != $CONFIG['CRON_KEY']) {
    echo "Invalid key!";
    http_response_code(403);
    exit;
}

const MYSQL_INT_MAX = 2147483647;

$sql = "UPDATE product_variants pv JOIN products p ON pv.product_id = p.product_id SET pv.stock = " . MYSQL_INT_MAX . " WHERE p.is_infinite = TRUE;";
$dbconnection->query($sql);

?>