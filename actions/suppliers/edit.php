<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
if(substr($_POST["tel"], 0, 2) == "00") {
    $_POST["tel"] = "+" . substr($_POST["tel"], 2);
}
$sql = "UPDATE suppliers SET name = :name, street = :street, postcode = :postcode, city = :city, country = :country, tel = :tel, email = :email, vat_number = :vat_number, iban = :iban WHERE supplier_id = :supplier_id";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([
    ":supplier_id" => $_POST['supplier_id'],
    ":name" => $_POST["name"],
    ":street"=> $_POST["street"],
    ":postcode"=> $_POST["postcode"],
    ":city" => $_POST["city"],
    ":country" => $_POST["country"],
    ":tel" => $_POST["tel"],
    ":email" => $_POST["email"],
    ":vat_number" => $_POST["vat_number"] ?? null,
    ":iban" => $_POST["iban"] ?? null
]);

if(!$res) {
    Utils::print_error("Errore durante la modifica del fornitore. " . $stmt->errorInfo()[2], true);
    die;
}

header(header: "Location: /index.php?page=suppliers_view")
?>