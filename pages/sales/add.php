<h1>Aggiungi vendita</h1>
<form action="actions/sales/add_item.php" method="POST">
    <label for="sku">Codice Articolo *</label>
    <div class="input-group mb-3">
    <span class="input-group-text"><i class="fa-solid fa-barcode"></i></span>
    <input type="text" minlength="13" maxlength="13" name="sku" class="form-control tt" style="font-size: 150%;" required id="sku-input"></input>
    <span class="input-group-text"><button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i></button></span>
</div>
</form>

<script>
    document.getElementById("sku-input").focus();
</script>