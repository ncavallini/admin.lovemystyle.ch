<?php


    $dbconnection = DBConnection::get_db_connection();
    $stmt = $dbconnection->prepare("SELECT * FROM customers WHERE customer_id = ?");
    $stmt->execute([$_GET['customer_id'] ?? ""]);
    $customer = $stmt->fetch();
    if(!$customer) {
        Utils::print_error("Cliente non trovato.");
        goto end;
    }
?>

<h1>Carta Fedeltà Cliente &mdash; <i><?php echo $customer['first_name'] . " " . $customer['last_name'] ?></i></h1>
<?php echo BarcodeGenerator::generateBarcode($customer['customer_number'], ssr: false) ?>

<?php
end:
?>