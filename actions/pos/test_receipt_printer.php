<?php
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

$client = POSHttpClient::get_http_client();
$client->get("/receipt/test");
?>
<script>
    window.history.back();
</script>
