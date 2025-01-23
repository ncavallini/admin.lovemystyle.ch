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
<img src="https://barcodeapi.org/api/128/<?php echo $customer['customer_number'] ?>?height=15&font=3" alt="<?php echo $customer['customer_number'] ?>">

<?php
end:
?>