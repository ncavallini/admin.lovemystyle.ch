<?php
$productId = $_GET['product_id'] ?? "";
$dbconnection = DBConnection::get_db_connection();
$sql = "SELECT * FROM product_variants WHERE product_id = ?";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([$productId]);

if(!$res) {
    Utils::print_error("Prodotto non trovato.");
    goto end;
}
$variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM products WHERE product_id = ?";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([$productId]);
$product = $stmt->fetch();

?>
<h1>Varianti &mdash; <small><i><?php echo $product['name'] . "</i> (<span class='tt'>" . $productId . "</span>)" ?></small> </h1>
<br>
<a href="/index.php?page=variants_add&product_id=<?php echo $productId ?>" class="btn btn-primary"><i class="fa-solid fa-plus"></i></a>
<p>&nbsp;</p>
<form action="/actions/variants/print_bulk_labels.php" method="POST">
    <div class="row">
        <div class="col-6">
            <label for="printerName">Stampante</label>
            <select name="printerName" class="form-select" required>
    <?php
    $canPrint = true;
    try {
        $client = POSHttpClient::get_http_client();
        $printer = json_decode($client->get("/label/printers")->getBody()->getContents(), true);
        if($printer['IsConnected'] == "True") echo "<option value='" . $printer['Name'] . "'>" . $printer['Name'] . "</option>";
    } catch (Exception $e) {
        echo "<option value=''>Nessuna stampante trovata</option>";
        $canPrint = false;
    }
        ?>
    </select>
        </div>
        <div class="col-6">
            <p>&nbsp;</p>
            <button type="submit" <?php if(!$canPrint) echo "disabled" ?> class="btn btn-secondary">Stampa tutte le etichette</button>
        </div>
    </div>
    <input type="hidden" name="product_id" value="<?php echo $productId ?>">
    
</form>
<p>&nbsp;</p>
<?php
    if(count($variants) == 0) {
        echo "<div class='alert alert-primary' role='alert'>Nessuna variante trovata. Creane una con il pulsante + sopra.</div>";
        
    }
?>
<?php
    foreach ($variants as $variant) {
        $variantId = $variant["variant_id"];
        $sku = InternalNumbers::get_sku($productId, $variant['variant_id']);
        $stock = $product["is_infinite"] ? "&infin;" : $variant['stock'];
        $stock_class = $stock == 0 ? "b red" : "";
        $barcode = BarcodeGenerator::generateBarcode($sku, 11);
        echo <<<EOD
<div class="card">
  <div class="card-header card-title h5">
    Colore: {$variant['color']} &ndash; Taglia: {$variant['size']} (<span class='tt'>{$sku}</span>)
  </div>
  <div class="card-body">
   <ul>
        <li>Stock: <span class='$stock_class'>{$stock}</span></li>
    </ul>
    $barcode
    <br>
    <hr>
        <a href="/index.php?page=variants_edit&product_id={$productId}&variant_id={$variant['variant_id']}" class="btn btn-primary"><i class="fa-solid fa-pencil"></i></a>
        <a href="/index.php?page=variants_label&product_id={$productId}&variant_id={$variant['variant_id']}" class="btn btn-primary"><i class="fa-solid fa-tag"></i></a>

        <a onclick="deleteVariant('$productId', '$variantId')" class="btn btn-danger"><i class="fa-solid fa-trash"></i></a>
  </div>
  </div>
        
<p>&nbsp;</p>
EOD;
    }
?>

<script>
    function deleteVariant(product_id, variant_id) {
        bootbox.confirm({
            title: "Elimina Variante",
            message: "Sei sicuro di voler eliminare la variante?",
            
            callback: function(result) {
                if(result) {
                    window.location.href = "/actions/variants/delete.php?product_id=" + product_id + "&variant_id=" + variant_id;
                }
            }
        })
    }
</script>

<?php end: ?>