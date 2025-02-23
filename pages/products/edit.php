<?php
    $dbconnection = DBConnection::get_db_connection();
    
    $productId = $_GET['product_id'] ?? "";

    $sql = "SELECT p.*, b.name  AS brand_name FROM products p JOIN brands b USING(brand_id) WHERE product_id = ?";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    if (!$product) {
        Utils::print_error("Prodotto non trovato");
        goto end;
    }

    $sql = "SELECT * FROM vats WHERE from_date <= NOW() AND to_date >= NOW() ORDER BY DATEDIFF(NOW(), from_date) ASC";
    $result = $dbconnection->query($sql);
    $vats = $result->fetchAll(PDO::FETCH_ASSOC);
?>
<h1>Modifica Prodotto</h1>

<form action="actions/products/edit.php" method="POST">
    <input type="hidden" name="product_id" value="<?php echo $product['product_id'] ?>">
    <label for="name">Nome</label>
    <input type="text" class="form-control" name="name" placeholder="Nome" value="<?php echo $product['name'] ?>" required>
    <br>
    <label for="brand">Brand</label>
    <select name="brand" id="brand-select" class="form-select"></select>
    <br>
    <div class="row">
        <div class="col-6">
            <label for="price">Prezzo</label>
            <div class="input-group mb-3">
                <input type="number" class="form-control" name="price" placeholder="Prezzo" min="0" step="0.01" value="<?php echo Utils::format_price($product['price']) ?>" required>
                <span class="input-group-text">CHF</span>

            </div>

        </div>
        <div class="col-6">
            <label for="vat">IVA</label>
            <select name="vat" class="form-select">
                <?php
                   foreach ($vats as $row) {
                    $sel = $row['vat_id'] == $product['vat_id'] ? "selected" : "";
                    $from = Utils::format_date($row['from_date']);	
                    $to = Utils::format_date($row['to_date']);
                    echo "<option $sel value='{$row['vat_id']}'>{$row['percentage']}% ({$from} - {$to})</option>";
                    }
                ?>
            </select>
        </div>
    </div>
    <br>
    <button type="submit" class="btn btn-primary">Salva Prodotto</button>
</form>

<script>
    $(document).ready(() => {
        $("#brand-select").select2({
            language: "it",
            theme: "bootstrap-5",
            ajax: {
                url: '/actions/brand/list.php',
                dataType: 'json'
            },
           
        })
    })
    const selectedBrand = {id: "<?php echo $product['brand_id'] ?>", text: "<?php echo $product['brand_name'] ?>"};
    $("#brand-select").append(new Option(selectedBrand.text, selectedBrand.id, true, true)).trigger('change');
</script>

<?php end: ?>