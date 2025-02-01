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
      $label = Label::get_from_variant($productId, $variantId);
    echo "<div class='d-flex justify-content-center'>";
    echo $label->preview();
    echo "</div>";
?>
<p>&nbsp;</p>
<h2>Stampa e scarica</h2>

<form action="actions/variants/print_label.php" method="POST">
    <input type="hidden" name="product_id" value="<?php echo $productId ?>"></input>
    <input type="hidden" name="variant_id" value="<?php echo $variantId ?>"></input>
    <div class="d-flex align-items-center gap-3">
    <label for="copies">Copie *</label>
    <input type="number" name="copies" min="1" max="100" value="1" placeholder="N. copie" required class="form-control w-auto">

    <label for="printer">Stampante *</label>
    <select name="printer" class="form-select w-auto" required>
        <?php
            $client = POSHttpClient::get_http_client();
            $printer = json_decode($client->get("/label/printers")->getBody()->getContents(), true);
                if($printer['IsConnected'] == "True") echo "<option value='" . $printer['Name'] . "'>" . $printer['Name'] . "</option>";
            
        ?>
    </select>
</div>


   <br>
   <table>
    <tbody>
        <tr>
            <td><button type="submit" class="btn btn-primary">Stampa</button></td>
            <td><a href="actions/variants/download_label.php?product_id=<?php echo $productId ?>&variant_id=<?php echo $variantId ?>" class="btn btn-secondary" target="_blank">Scarica</a></td>
        </tr>
    </tbody>
   </table>
</form>
<p>&nbsp;</p>

<?php end: ?>
