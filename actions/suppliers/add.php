<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$sql = "INSERT INTO suppliers VALUES (UUID(), :name, :street, :postcode, :city, :country, :tel, :email, :vat_number)";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([
    ":name" => $_POST['name'],
    ':street'=> $_POST['street'],
    ':postcode'=> $_POST['postcode'],
    ':city'=> $_POST['city'],
    ":country" => $_POST['country'],
    ":tel" => $_POST['tel'] ?? null,
    ":email" => $_POST['email'],
    ":vat_number" => $_POST['vat_number'] ?? null,
]);

if(!$res) {
    Utils::print_error("Errore durante l'inserimento del fornitore. " . $stmt->errorInfo()[2], true);
    die;
}

header(header: "Location: /index.php?page=suppliers_view");