<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$sql = "INSERT INTO customers VALUES (UUID(), :customer_number, :first_name, :last_name, :gender, :birth_date, :street, :postcode, :city, :country, :tel, :email, NOW(), NOW(), :is_newsletter_allowed)";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([
    ":customer_number" => InternalNumbers::get_customer_number(),
    ":first_name" => $_POST['first_name'],
    ":last_name" => $_POST["last_name"],
    ":gender" => $_POST["gender"],
    ":birth_date" => $_POST["birth_date"],
    ":street" => $_POST["street"] ?? null,
    ":postcode" => $_POST["postcode"] ?? null,
    ":city" => $_POST["city"] ?? null,
    ":country" => $_POST["country"] ?? null,
    ":tel" => $_POST["tel"] ?? null,
    ":email" => $_POST["email"],
    ":is_newsletter_allowed" => $_POST["is_newsletter_allowed"] === "on"
]);

if(!$res) {
    Utils::print_error("Errore durante l'inserimento del cliente. " . $stmt->errorInfo()[2], true);
    die;
}

header(header: "Location: /index.php?page=customers_view")
?>