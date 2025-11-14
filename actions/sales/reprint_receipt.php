<?php
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

$receipt = json_decode($_POST['receipt'], true);
$posClient = POSHttpClient::get_http_client();
echo $posClient->post("/receipt/print", [
    "json" => [
        "receipt" => $receipt
    ]
    ])->getBody()->getContents();
?>
