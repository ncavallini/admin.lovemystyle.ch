<?php
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

$dbconnection = DBConnection::get_db_connection();
$sql = "SELECT * FROM customers WHERE customer_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$_GET['customer_id']]);
$customer = $stmt->fetch();

if(!$customer) {
    Utils::print_error("Cliente non trovato.", true);
    die;
}

$to = $customer['email'];

$loyalty = new LoyaltyCard($_GET['customer_id']);
$google_wallet_url = $loyalty->get_google_pass_link();
$apple_wallet_url = "https://admin.lovemystyle.ch/actions/customers/get_apple_pass.php?customer_id=" . $_GET['customer_id'];    


$html = file_get_contents(__DIR__ . "/../../templates/emails/new_customer.html");
$html = Utils::str_replace([
    "%first_name" => $customer['first_name'],
    "%last_name" => $customer['last_name'],
    "%google_wallet_url" => $google_wallet_url,
    "%apple_wallet_url" => $apple_wallet_url,
    "%barcode" => BarcodeGenerator::generateBarcode($customer['customer_number'], ssr: true, url_only: true)
], $html);

$res = Email::send($to, "Benvenuto in Love My Style", $html);

if($res) {
    Utils::create_toast("Email inviata con successo.", "Email inviata a " . $to, "success");
    echo "<script>window.history.back();</script>";
}
else {
    Utils::print_error("Errore durante l'invio dell'email.", true);
}
