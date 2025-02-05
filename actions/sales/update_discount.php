<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$saleId = $_POST['sale_id'] ?? "";
$discount = $_POST['discount'] ?? 0;
$discountType = $_POST['discount_type'] ??'CHF';
$sql = "UPDATE sales SET discount = :discount, discount_type = :discount_type WHERE sale_id = :sale_id";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
    ":sale_id" => $saleId,
    ":discount" => $discount,
    ":discount_type" => $discountType
]);

