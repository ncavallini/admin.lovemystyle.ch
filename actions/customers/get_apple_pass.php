<?php
require_once __DIR__ . "/../../inc/inc.php";
$dbconnection = DBConnection::get_db_connection();
$customer_id = $_GET['customer_id'] ?? "";

$pass = new LoyaltyCard($customer_id);
header('Content-Type: application/vnd.apple.pkpass');
header('Content-Disposition: attachment; filename="pass.pkpass"');

$str = $pass->get_apple_pass();

header("Content-Length: " . strlen($str));

if(!$str) {
    Utils::print_error("Errore durante la creazione del pass.", true);
    die;

}

echo $str;



