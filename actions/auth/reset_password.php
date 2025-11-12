<?php
require_once __DIR__ . "/../../inc/inc.php";
$token = $_POST['token'] ;
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
if ($password !== $confirm_password) {
    Utils::print_error(message: "Le password non corrispondono.", needs_bootstrap: true);
    echo "<p><a href='/index.php?page=forgot-password'>Torna alla pagina di reimpostazione password</a></p>";
    return;
}
$hash = password_hash($password, PASSWORD_BCRYPT);

$dbconnection = DBConnection::get_db_connection();
$sql = "SELECT * FROM password_reset_tokens WHERE token = ?;";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$token]);
$token_data = $stmt->fetch();
if (!$token_data) {
    Utils::print_error(message: "Token non valido o scaduto.", needs_bootstrap: true);
    echo "<p><a href='/index.php?page=forgot-password'>Torna alla pagina di reimpostazione password</a></p>";
    return;
}
if($token_data['expires_at'] < date("Y-m-d H:i:s")) {
    Utils::print_error(message: "Token non valido o scaduto.", needs_bootstrap: true);
    echo "<p><a href='/index.php?page=forgot-password'>Torna alla pagina di reimpostazione password</a></p>";
    return;
}

$sql = "UPDATE users SET password_hash = ?, needs_password_change = FALSE WHERE username = ?;";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$hash, $token_data['username']]);
$sql = "DELETE FROM password_reset_tokens WHERE token = ?;";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$token]);

Utils::create_toast("Password reimpostata con successo. Ora puoi accedere.", "Password reimpostata con successo. Ora puoi accedere.",  "success");
header("Location: /index.php?page=login");
?>