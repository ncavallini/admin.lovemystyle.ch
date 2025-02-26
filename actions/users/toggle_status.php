<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$username = $_GET['username'] ?? null;
$sql = "SELECT role FROM users WHERE username = ?";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([$username]);
$role = $stmt->fetchColumn();
if(!$res || $role === "ADMIN") {
    Utils::print_error("Errore durante l'operazione", true);
    exit();
}
$sql = "UPDATE users SET is_enabled = NOT is_enabled WHERE username = ?";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([$username]);
if(!$res) {
    Utils::print_error("Errore durante l'operazione", true);
    exit();
}
Utils::redirect("/index.php?page=users_view");