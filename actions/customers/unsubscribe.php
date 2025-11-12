<?php
require_once __DIR__ . "/../../inc/inc.php";
error_reporting(error_level: E_ALL ^ E_DEPRECATED);

if(!isset($_GET['email'])) {
    die;
}
$dbconnection = DBConnection::get_db_connection();
$email = $_GET['email'];
$sql = "UPDATE customers SET is_newsletter_allowed = 0 WHERE email = :email";
$stmt = $dbconnection->prepare($sql);
$stmt->execute(['email' => $email]);
Brevo::delete_customer($email);
echo "<h1>Disiscrizione di $email avvenuta con successo</h1>";
