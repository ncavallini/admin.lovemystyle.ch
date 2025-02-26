<?php

use ZxcvbnPhp\Zxcvbn;
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();

$zxcvbn = new Zxcvbn();
$passwordStrength = $zxcvbn->passwordStrength($_POST['password'], []);
if($passwordStrength['score'] < 3) {
    Utils::print_error("La password è troppo debole. Inserire una password più sicura.", true);
    die;
}



$sql = "INSERT INTO users (username, first_name, last_name, password_hash, tel, email, role, is_enabled) VALUES (:username, :first_name, :last_name, :password_hash, :tel, :email, :role, TRUE)";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([
    'username' => $_POST['username'],
    'first_name' => $_POST['first_name'],
    'last_name' => $_POST['last_name'],
    'password_hash' => password_hash($_POST['password'], PASSWORD_BCRYPT),
    'tel' => $_POST['tel'],
    'email' => $_POST['email'],
    'role' => $_POST['role']
]);

if(!$res) {
    Utils::print_error("Errore durante l'inserimento dell'utente. Verificare che il nome utente non sia già in uso.", true);
    die;
}
header("Location: /index.php?page=users_view");