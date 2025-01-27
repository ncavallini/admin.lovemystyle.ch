<?php
$productId = $_GET['product_id'] ?? "";
$variantId = $_GET['variant_id'] ?? "";
$dbconnection = DBConnection::get_db_connection();
$sql = "SELECT v.*, p.*, s.name AS supplier_name FROM product_variants v JOIN products p USING(product_id) JOIN suppliers s USING(supplier_id) WHERE product_id = ? AND variant_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$productId, $variantId]);
$variant = $stmt->fetch();
$sku = InternalNumbers::get_sku($productId, $variantId);
if (!$variant) {
    Utils::print_error("Variante non trovata.");
    goto end;
}
?>

<h1>Etichetta &ndash; <?php echo $variant['name'] . " @ " . $variant['color'] . ", " . $variant['size'] ?> (<span class='tt'><?php echo $sku ?></span>) </h1>
<p>&nbsp;</p>
<h2>Anteprima di stampa</h2>
<?php
    $label = new Label($variant['name'], $variant["supplier_name"], $variant['color'], $variant['size'], $sku, $variant['price']);
    echo "<div class='d-flex justify-content-center'>";
    echo $label->preview();
    echo "</div>";
?>
<p>&nbsp;</p>
<h2>Stampa e scarica</h2>

<?php end: ?>
