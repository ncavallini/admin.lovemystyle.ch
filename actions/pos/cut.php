<?php
require_once __DIR__ . "/../actions_init.php";
$nextAction = $_GET['nextAction'] ?? 'back'; // "back" or "close"
$client = POSHttpClient::get_http_client();
$client->get("/receipt/cut");
?>
<script>
window.history.back()
</script>