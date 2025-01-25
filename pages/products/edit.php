<?php
    $dbconnection = DBConnection::get_db_connection();
    
    $productId = $_GET['product_id'] ?? "";

    $sql = "SELECT p.*, s.supplier_id, s.name  AS supplier_name FROM products p JOIN suppliers s USING(supplier_id) WHERE product_id = ?";
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
    <label for="supplier">Fornitore</label>
    <select name="supplier" id="supplier-select" class="form-select"></select>
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
        $("#supplier-select").select2({
            language: "it",
            theme: "bootstrap-5",
            ajax: {
                url: '/actions/suppliers/list.php',
                dataType: 'json'
            },
           
        })
    })
    const selectedSupplier = {id: "<?php echo $product['supplier_id'] ?>", text: "<?php echo $product['supplier_name'] ?>"};
    $("#supplier-select").append(new Option(selectedSupplier.text, selectedSupplier.id, true, true)).trigger('change');
</script>

<?php end: ?>