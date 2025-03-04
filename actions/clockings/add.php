<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$sql = "INSERT INTO clockings VALUES(UUID(), :username, NOW(), :type)";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
  'username' => Auth::get_username(),
  'type' => $_POST['type']
]);
header("Location: /");