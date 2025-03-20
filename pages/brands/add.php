<h1>Aggiungi Brand</h1>
<p>Tutti i campi sono obbligatori.</p>
<form action="actions/brands/add.php" method="POST">
    <label for="name">Nome</label>
    <input type="text" class="form-control" name="name" placeholder="Nome" required>
    <br>
    <label for="supplier">Fornitore</label>
    <select id="supplier-select" class="form-control" name="supplier" required>
</select>
<br>
<button type="submit" class="btn btn-primary">Aggiungi Brand</button>
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