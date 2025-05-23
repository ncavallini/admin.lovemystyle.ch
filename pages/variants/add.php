<?php
    $productId = $_GET['product_id'] ?? "";
    $dbconnection = DBConnection::get_db_connection();
    $sql = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$product) {
        Utils::print_error("Prodotto non trovato.");
        goto end;
    }
?>

<h1>Aggiungi Variante <small>(Prodotto: <i><?php echo $product['name'] ?></i> - <span class="tt"><?php echo $product['product_id'] ?></span>)</small> </h1>

<form action="actions/variants/add.php" method="POST">
    <input type="hidden" name="product_id" value="<?php echo $productId ?>">
    
    <label for="color">Colore</label>
    <input type="text" name="color" class="form-control" placeholder="Colore">
    <br>

    <label for="size">Taglia</label>
    <select name="size" id="size-select" class="form-select">
        <option></option> <!-- DO NOT REMOVE. FOR PLACEHOLDER -->
    </select>
    <br>

    <label for="stock">Pezzi a magazzino (stock)</label>
    <input type="number" name="stock" class="form-control" placeholder="Pezzi a magazzino (stock)" min="0" required>
    <br>

    <button type="submit" class="btn btn-primary">Aggiungi variante</button>
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