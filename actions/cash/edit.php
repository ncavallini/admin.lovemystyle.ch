<?php
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

$dbconnection = DBConnection::get_db_connection();
$sql = "UPDATE cash_content SET content = :content, last_updated_at = :last_updated_at, last_updated_by = :last_updated_by";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
    ":content" => $_POST["content"] * 100,
    ":last_updated_at" => date("Y-m-d H:i:s"),
    ":last_updated_by" => Auth::get_username()
]);
Logging::get_logger()->info("NEW cash content: " . ($_POST["content"]) . " CHF. Cash content updated by " . Auth::get_username());
header("Location: /index.php?page=admin_cash");
