<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$card_id = $_POST["card_id"];

header("Content-Type: application/json");

$sql = "SELECT balance, expires_at FROM gift_cards WHERE card_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$card_id]);
$giftcard = $stmt->fetch();

if (!$giftcard) {
    echo json_encode(["error" => "Carta regalo non trovata"]);
    http_response_code(400);
    return;
}

echo json_encode([
    "card_id" => $card_id,
    "balance" => $giftcard["balance"] / 100,
    "expires_at" => $giftcard["expires_at"],
    "is_expired" => new DateTime($giftcard["expires_at"]) < new DateTime()
]);
