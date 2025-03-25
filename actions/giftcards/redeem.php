<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$card_id = $_POST["card_id"];
$amount = $_POST["amount"] * 100;

$sql = "SELECT * FROM gift_cards WHERE card_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$card_id]);
$giftcard = $stmt->fetch();

if (!$giftcard) {
    Utils::print_error("Carta non trovata", true);
    return;
}
if ($giftcard["balance"] < $amount) {
    Utils::print_error("Saldo insufficiente", true);
    return;
}

if ($giftcard["expires_at"] < date("Y-m-d")) {
    Utils::print_error("Carta regalo scaduta", true);
    return;
}

$sql = "UPDATE gift_cards SET balance = balance - ? WHERE card_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$amount, $card_id]);

$sql = "UPDATE sales SET discount = discount + ?, discount_type = 'CHF' WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$amount / 100, $_POST["sale_id"]]);

?>

<script>
    window.opener.location.reload();  
    window.close();  
</script>