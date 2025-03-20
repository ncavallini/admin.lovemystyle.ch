<?php
    $dbconnection = DBConnection::get_db_connection();
    $sql = "SELECT b.*, s.name AS supplier_name FROM brands b JOIN suppliers s WHERE brand_id = ?";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([$_GET['brand_id']]);

    $brand = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$brand) {
        Utils::print_error("Brand non trovato");
        goto end;
    }
?>

<h1>Modifica Brand</h1>
<p>Tutti i campi sono obbligatori.</p>
<form action="actions/brands/edit.php" method="POST">
    <input type="hidden" name="brand_id" value="<?php echo $_GET['brand_id'] ?>">
    <label for="name">Nome</label>
    <input type="text" class="form-control" name="name" placeholder="Nome" value="<?php echo $brand['name']; ?>" required>
    <br>
    <label for="supplier">Fornitore</label>
    <select id="supplier-select" class="form-control" name="supplier" required>
        <option value="<?php echo $brand['supplier_id'] ?>"><?php echo $brand['supplier_name'] ?></option>
</select>
<br>
<button type="submit" class="btn btn-primary">Salva Brand</button>
</form>

<script>
    $(document).ready(() => {
        $("#supplier-select").select2({
            language: "it",
            theme: "bootstrap-5",
            ajax: {
                url: '/actions/suppliers/list.php',
                dataType: 'json'
            }
        })
    })
</script>
<?php
end:
