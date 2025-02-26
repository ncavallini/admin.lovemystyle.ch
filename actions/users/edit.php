<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$sql = "UPDATE users SET tel = :tel, email = :email, role = :role WHERE username = :username";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([
    'tel' => $_POST['tel'],
    'email' => $_POST['email'],
    'role' => $_POST['role'],
    'username' => $_POST['username']
]);
if(!$res) {
    Utils::print_error("Errore durante il salvataggio delle modifiche.", true);
    die;
}
header("Location: /index.php?page=users_view");