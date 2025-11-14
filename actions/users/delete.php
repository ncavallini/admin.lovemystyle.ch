<?php
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

// Authorization check
Auth::require_owner();


$dbconnection = DBConnection::get_db_connection();
$username = $_GET['username'] ?? null;
$sql = "DELETE FROM users WHERE username = ?";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([$username]);

if(!$res) {
    Utils::print_error("Impossibile eliminare l'utente. L'utente potrebbe avere vendite associate o non esistere.", true);
    die;
}

header("Location: /index.php?page=users_view");
exit;
