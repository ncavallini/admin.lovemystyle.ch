<?php


    $dbconnection = DBConnection::get_db_connection();
    $stmt = $dbconnection->prepare("SELECT * FROM customers WHERE customer_id = ?");
    $stmt->execute([$_GET['customer_id'] ?? ""]);
    $customer = $stmt->fetch();
    if(!$customer) {
        Utils::print_error("Cliente non trovato.");
        goto end;
    }

    $googlePassLink = (new LoyaltyCard($customer['customer_id']))->get_google_pass_link();

?>

<style>
      .custom-card {
            max-width: 400px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            background: #f8f9fa;
        }

        .card-img-top {
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            object-fit: cover;
            height: 200px;
        }

        .card-body {
            text-align: center;
            padding: 20px;
        }

        .card-title {
            font-size: 1.4rem;
            font-weight: bold;
            color: #333;
        }

        .card-text {
            font-size: 1rem;
            color: #666;
        }

        @media (max-width: 768px) {
            .custom-card {
                max-width: 100%;
            }
        }
</style>

<h1>Carta Fedeltà Cliente &mdash; <i><?php echo $customer['first_name'] . " " . $customer['last_name'] ?></i></h1>

<div class="d-flex justify-content-center">
<div class="custom-card card">
        <img src="/assets/logo/logo_color.svg" class="card-img-top" alt="Love My Style">
        <div class="card-body">
            <h5 class="card-title"><?php  echo $customer['first_name'] . " " . $customer['last_name'] ?> </h5>
            <p class="card-text"><?php echo BarcodeGenerator::generateBarcode($customer['customer_number']) ?></p>
        </div>
    </div>
</div>

<a href="/actions/customers/send_new_customer_email.php?customer_id=<?php echo $_GET['customer_id'] ?>" class="btn btn-primary">Invia mail</a>
<img onclick="window.location.href='<?php echo $googlePassLink ?>'" src="/assets/components/it_add_to_google_wallet_wallet-button.png" alt="Aggiungi a Google Wallet" style="cursor: pointer;" />
<a href="/actions/customers/get_apple_pass.php?customer_id=<?php echo $_GET['customer_id'] ?>" class="btn btn-primary">Aggiungi ad Apple Wallet</a>


<?php
end:
?>