<?php
    $productId = $_GET['product_id'] ?? "";
    $variantId = $_GET['variant_id'] ?? "";
    $dbconnection = DBConnection::get_db_connection();
    $sql = "SELECT * FROM product_variants WHERE product_id = ? AND variant_id = ?";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([$productId, $variantId]);
    $variant = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$variant) {
        Utils::print_error("Variante non trovata.");
        goto end;
    }
?>

<h1>Modifica Variante <small><span class="tt">(<?php echo InternalNumbers::get_sku($productId, $variantId) ?>)</span></small> </h1>

<form action="actions/variants/edit.php" method="POST">
    <input type="hidden" name="product_id" value="<?php echo $productId ?>">
    <input type="hidden" name="variant_id" value="<?php echo $variantId ?>">
    
    <label for="color">Colore</label>
    <input type="text" name="color" class="form-control" placeholder="Colore" value="<?php echo $variant["color"] ?>">
    <br>

    <label for="size">Taglia</label>
    <select name="size" id="size-select" class="form-select">
        <option value="<?php echo $variant["size"] ?>"><?php echo $variant["size"] ?></option>
    </select>
    <br>

    <label for="stock">Pezzi a magazzino (stock)</label>
    <input type="number" name="stock" class="form-control" placeholder="Pezzi a magazzino (stock)" min="0"  value="<?php echo $variant["stock"] ?>" required>
    <br>

    <button type="submit" class="btn btn-primary">Salva variante</button>
</form>

<script>
    $(document).ready(() => {
        const sizes = <?php echo json_encode($CONFIG['COMMON_SIZES']) ?>;
        const sizeSelect = $("#size-select");
        sizeSelect.select2({
            language: "it",
            theme: "bootstrap-5",
            data: sizes.map((size) => ({id: size, text: size})),
            tags: true,
            placeholder: "Scegli una taglia tra quelle proposte o inserisci un valore personalizzato",
            allowClear: true

        })
    })
</script>

<?php end: ?>