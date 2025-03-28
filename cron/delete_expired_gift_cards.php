<?php
// Cron: every 1st of the month at 02:00

require_once __DIR__ . "/../inc/inc.php";
$dbconnection = DBConnection::get_db_connection();

if(!isset($_GET['key']) || $_GET['key'] != $CONFIG['CRON_KEY']) {
    echo "Invalid key!";
    http_response_code(403);
    exit;
}

$sql = "DELETE FROM gift_cards WHERE expires_at < DATE(NOW());";
$dbconnection->query($sql);
