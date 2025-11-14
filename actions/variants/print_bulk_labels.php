<?php
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

$productId = $_POST['product_id'] ?? "";
$dbconnection = DBConnection::get_db_connection();
$sql = "SELECT v.variant_id, v.stock FROM product_variants v WHERE product_id = ?"; 
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$productId]);
$variants = $stmt->fetchAll();
$printerName = $_POST['printerName'] ?? "";
if (!$printerName) {
    Utils::print_error("Stampante non selezionata.", true);
    return;
}
if(!$variants) {
    Utils::print_error("Nessuna variante trovata.", true);
    return;
}
$client = POSHttpClient::get_http_client();

foreach($variants as $variant) {
    $label = ProductTagLabel::get_from_variant($productId, $variant['variant_id']);
    $label->print($printerName, $variant['stock']);
    // wait 2 seconds between prints to avoid printer overload
    //sleep(seconds: 1);
}

Utils::redirect("/index.php?page=variants_view&product_id=" . $productId);
?>
