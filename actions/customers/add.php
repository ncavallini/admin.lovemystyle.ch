<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$sql = "INSERT INTO customers VALUES (UUID(), :customer_number, :first_name, :last_name, :birth_date, :street, :postcode, :city, :country, :tel, :email, NOW(), NOW(), :is_newsletter_allowed)";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([
    ":customer_number" => InternalNumbers::get_customer_number(),
    ":first_name" => $_POST['first_name'],
    ":last_name" => $_POST["last_name"],
    ":birth_date" => $_POST["birth_date"] ?? null,
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

$sql = "SELECT customer_id FROM customers ORDER BY created_at DESC LIMIT 1";
$stmt = $dbconnection->prepare($sql);
$stmt->execute();
$customer_id = $stmt->fetchColumn();

?>

<script>
    fetch("/actions/customers/send_new_customer_email.php?tablet=1&customer_id=<?php echo $customer_id ?>", {
        method: "GET"
    })
</script>


<?php

if($_POST['tablet'] == 1) {
    Utils::redirect("/index.php?page=customers_add-success&tablet=1");
} 
else {
    Utils::redirect("/index.php?page=customers_view");
}

?>