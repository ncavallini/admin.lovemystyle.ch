<?php
// Cron: every day at 23:59

require_once __DIR__ . "/../actions/actions_init.php";
$dbconnection = DBConnection::get_db_connection();

if(!isset($_GET['key']) || $_GET['key'] != $CONFIG['CRON_KEY']) {
    echo "Invalid key!";
    http_response_code(403);
    exit;
}

$today = date('Y-m-d'); // Do not use CURDATE() in case of switching day during loop

$sql = "SELECT * FROM users";
$stmt = $dbconnection->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll();

foreach($users as $user) {
    $sql = "SELECT COUNT(*) FROM clockings WHERE username = ? AND DATE(datetime) = ?";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([$user['username'], $today]);
    $count = $stmt->fetchColumn();
    if($count % 2 == 1) {
        $sql = "INSERT INTO clockings (username, datetime, type) VALUES (?, ?, 'out')";
        $stmt = $dbconnection->prepare($sql);
        $stmt->execute([$user['username'], "$today 23:59:59"]);
    }
}