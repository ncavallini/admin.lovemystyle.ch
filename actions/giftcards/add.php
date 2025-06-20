<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$cardId = InternalNumbers::get_gift_card_number();

$duration = $CONFIG["GIFT_CARD_DURATION"];


if(isset($_POST["customer_id"])) {
    $sql = "INSERT INTO gift_cards (card_id, amount, balance, customer_id, created_at, expires_at) VALUES (:card_id, :amount, :balance, :customer_id, NOW(), DATE(DATE_ADD(NOW(), INTERVAL $duration YEAR)))";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([
        ":card_id" => $cardId,
        ":amount" => $_POST["value"] * 100,
        ":balance" => $_POST["value"] * 100,
        ":customer_id" => $_POST["customer_id"]
    ]);
}

else {
    $sql = "INSERT INTO gift_cards (card_id, amount, balance, first_name, last_name, created_at, starts_at, expires_at) VALUES (:card_id, :amount, :balance, :first_name, :last_name, NOW(), :starts_at, :expires_at)";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute(params: [
        ":card_id" => $cardId,
        ":first_name" => $_POST["first_name"],
        ":last_name" => $_POST["last_name"],
        ":starts_at" => $_POST["starts_at"],
        ":amount" => $_POST["value"] * 100,
        ":balance" => $_POST["value"] * 100,
        ":expires_at" => date("Y-m-d", strtotime("+$duration year", strtotime($_POST["starts_at"]))),
    ]);
}

$sql = "INSERT INTO sales (sale_id, created_at, customer_id, username) VALUES (UUID(), NOW(), :customer_id, :username)";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
    ":customer_id" => $_POST["customer_id"] ?? null,
    ":username" => Auth::get_username()
]);

$sql = "SELECT sale_id FROM sales ORDER BY created_at DESC LIMIT 1";
$stmt = $dbconnection->prepare($sql);
$stmt->execute();
$saleId = $stmt->fetch(PDO::FETCH_ASSOC)["sale_id"];

$sql = "INSERT INTO sales_items (sale_id, product_id, variant_id, quantity, price) VALUES (:sale_id, :product_id, 1, 1, :price)";

$padLength = 8 - strlen($_POST['value']) - 1;
$productId = "1" . str_repeat("0", $padLength) . $_POST['value'];

$stmt = $dbconnection->prepare($sql);
$stmt->execute([
    ":sale_id" => $saleId,
    ":product_id" => $productId,
    ":price" => $_POST["value"] * 100,
]);

$sql = "UPDATE gift_cards SET sale_id = ? WHERE card_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId, $cardId]);


Utils::redirect("/index.php?page=sales_add&sale_id=$saleId");