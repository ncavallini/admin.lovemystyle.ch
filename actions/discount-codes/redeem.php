<?php
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

$dbconnection = DBConnection::get_db_connection();

$_POST['code'] = strtoupper($_POST["code"]);

$sql = "SELECT * FROM discount_codes WHERE code = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$_POST["code"]]);
$code = $stmt->fetch();

if (!$code) {
    Utils::print_error("Codice non trovato", true);
    return;
}

if ($code["from_date"] > date("Y-m-d") || $code["to_date"] < date("Y-m-d")) {
    Utils::print_error("Codice sconto non ancora valido o scaduto", true);
    return;
}

$sql = "SELECT COUNT(*) FROM used_discount_codes WHERE code = ? AND customer_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$_POST["code"], $_POST["customer_id"]]);
$count = $stmt->fetchColumn();
if ($count > 0) {
    Utils::print_error("Codice sconto già utilizzato", true);
    return;
}

$sql = "UPDATE sales SET discount =  ?, discount_type = ? WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
    $code["discount"],
    $code["discount_type"],
    $_POST["sale_id"]
]);

if (!empty($_POST['customer_id'])) {
    $sql = "INSERT INTO used_discount_codes (code, customer_id) VALUES (?, ?)";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([
        $_POST["code"],
        $_POST["customer_id"],
    ]);
}


?>

<script>
    window.opener.location.reload();
    window.close();
</script>
