<?php
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

$client = POSHttpClient::get_http_client();
$client->post("/label/test", [
    "json" => ["xml" => ProductTagLabel::get_test_label_xml(),
               "printerName" => "DYMO LabelWriter 5XL",
    ]
]);

?>
<script>
    window.history.back();
</script>
