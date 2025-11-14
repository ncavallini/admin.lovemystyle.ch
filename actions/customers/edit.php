<?php
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

$dbconnection = DBConnection::get_db_connection();

$sql = "SELECT customer_number, email FROM customers WHERE customer_id = :customer_id";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
    ":customer_id" => $_POST['customer_id']
]);
$oldData = $stmt->fetch();
$oldEmail = $oldData['email'];
$customer_number = $oldData['customer_number'];

$sql = "UPDATE customers SET first_name = :first_name, last_name = :last_name, birth_date = :birth_date, street = :street, postcode = :postcode, city = :city, country = :country, tel = :tel, email = :email WHERE customer_id = :customer_id";
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
]);

if(!$res) {
    Utils::print_error("Errore durante la modifica del cliente. " . $stmt->errorInfo()[2], true);
    die;
}




if($_POST['is_newsletter_allowed'] === "off") {
    Brevo::delete_customer($oldEmail);
}
else {
    if($oldEmail !== $_POST['email']) {
        Brevo::delete_customer($oldEmail);
        Brevo::add_customer($customer_number, $_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['tel'] ?? "");
    }
}

header(header: "Location: /index.php?page=customers_view")
?>
