<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$sql = "UPDATE customers SET first_name = :first_name, last_name = :last_name, birth_date = :birth_date, street = :street, postcode = :postcode, city = :city, country = :country, tel = :tel, email = :email, is_newsletter_allowed = :is_newsletter_allowed WHERE customer_id = :customer_id";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([
    ":customer_id" => $_POST['customer_id'],
    ":first_name" => $_POST["first_name"],
    ":last_name"=> $_POST["last_name"],
    ":birth_date"=> $_POST["birth_date"],
    ":street"=> $_POST["street"] ?? null,
    ":postcode"=> $_POST["postcode"] ?? null,
    ":city" => $_POST["city"] ?? null,
    ":country" => $_POST["country"] ?? null,
    ":tel" => $_POST["tel"] ?? null,
    ":email" => $_POST["email"],
    ":is_newsletter_allowed" => $_POST["is_newsletter_allowed"] === "on",
]);

if(!$res) {
    Utils::print_error("Errore durante la modifica del cliente. " . $stmt->errorInfo()[2], true);
    die;
}

header(header: "Location: /index.php?page=customers_view")
?>