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

$isSelf = ($_POST['username'] === $_SESSION['user']['username']);
if(!$isSelf && !Auth::is_owner(includeAdmin: true)) {
    Utils::print_error("Non hai i permessi per resettare la password di questo utente.", true);
    die;
}



$sql = "UPDATE users SET password_hash = :password_hash, needs_password_change = FALSE WHERE username = :username";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([
    'password_hash' => password_hash($_POST['password'], PASSWORD_BCRYPT),
    'username' => $_POST['username']
]);
if(!$res) {
    Utils::print_error("Errore durante il reset della password.", true);
    die;
}
header("Location: /index.php?page=users_view");