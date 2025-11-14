<?php
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

$dbconnection = DBConnection::get_db_connection();
$sql = "UPDATE users SET tel = :tel, email = :email, street = :street, postcode = :postcode, city = :city, country = :country, iban = :iban, role = :role WHERE username = :username";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([
    'tel' => $_POST['tel'],
    'email' => $_POST['email'],
    'street' => $_POST['street'],
    'postcode' => $_POST['postcode'],
    'city' => $_POST['city'],
    'country' => $_POST['country'],
    'iban' => $_POST['iban'],
    'role' => $_POST['role'],
    'username' => $_POST['username']
]);
if(!$res) {
    Utils::print_error("Errore durante il salvataggio delle modifiche.", needs_bootstrap: true);
    die;
}
header("Location: /index.php?page=users_view");
