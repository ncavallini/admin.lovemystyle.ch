<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$sql = "INSERT INTO customers VALUES (UUID(), :customer_number, :first_name, :last_name, :birth_date, :street, :postcode, :city, :country, :tel, :email, NOW(), NOW(), :is_newsletter_allowed)";
$stmt = $dbconnection->prepare($sql);
$customer_number = InternalNumbers::get_customer_number();
$res = $stmt->execute([
    ":customer_number" => $customer_number,
    ":first_name" => trim($_POST['first_name']),
    ":last_name" => trim($_POST["last_name"]),
    ":birth_date" => $_POST["birth_date"] ?? null,
    ":street" => $_POST["street"] ?? null,
    ":postcode" => $_POST["postcode"] ?? null,
    ":city" => $_POST["city"] ?? null,
    ":country" => $_POST["country"] ?? null,
    ":tel" => $_POST["tel"] ?? null,
    ":email" => $_POST["email"],
    ":is_newsletter_allowed" => $_POST["is_newsletter_allowed"] === "on"
]);

if($_POST['is_newsletter_allowed'] === "on") {
    try {
          Brevo::add_customer(
        InternalNumbers::get_customer_number(),
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['email'],
        $_POST['tel'] ?? "",
    );
    }
    catch(Exception $e) {}
    
}

if(!$res) {
    Utils::print_error("Errore durante l'inserimento del cliente. " . $stmt->errorInfo()[2], true);
    die;
}

$sql = "SELECT * FROM customers ORDER BY created_at DESC LIMIT 1";
$stmt = $dbconnection->prepare($sql);
$stmt->execute();
$customer = $stmt->fetch();

$to = $customer['email'];

$loyalty = new LoyaltyCard($customer['customer_id']);
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

if(new DateTime() >= new DateTime("2025-06-11") && new DateTime() <= new DateTime("2025-08-31")) {
    $res = $res && Email::send($to, "Il tuo omaggio di benvenuto", file_get_contents(__DIR__ . "/../../templates/emails/welcome_code.html"));
}

if(!$res) {
    Utils::print_error("Errore durante l'invio dell'email.", true);
}

?>



<?php

if($_POST['tablet'] == 1) {
    Utils::redirect("/index.php?page=customers_add-success&tablet=1");
} 
else {
    Utils::redirect("/index.php?page=customers_view");
}

?>