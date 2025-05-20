<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$sql = "INSERT INTO clockings VALUES(UUID(), :username, NOW(), :type)";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
  'username' => Auth::get_username(),
  'type' => $_POST['type']
]);

$sql = "SELECT COUNT(*) FROM clockings WHERE type = 'in'";
$stmt = $dbconnection->prepare($sql);
$stmt->execute();
$open_count = $stmt->fetchColumn();

$sql = "SELECT COUNT(*) FROM clockings WHERE type = 'out'";
$stmt = $dbconnection->prepare($sql);
$stmt->execute();
$close_count = $stmt->fetchColumn();

$isWorking = $open_count > $close_count;

if(!$isWorking) {
  Email::send("trigger@applet.ifttt.com", "#arm", "arm", from: "admin@lovemystyle.ch");
}

header("Location: /");