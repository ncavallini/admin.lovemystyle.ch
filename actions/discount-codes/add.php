<?php
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

$dbconnection = DBConnection::get_db_connection();


    $sql = "INSERT INTO discount_codes (code, from_date, to_date, discount, discount_type) VALUES (:code, :from_date, :to_date, :discount, :discount_type)";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([
        ":code" => strtoupper($_POST["code"]),
        ":from_date" => $_POST["from_date"],
        ":to_date" => $_POST["to_date"],
        ":discount" => $_POST["discount"],
        ":discount_type" => $_POST["discount_type"],
    ]);



Utils::redirect("/index.php?page=discount-codes_view");
