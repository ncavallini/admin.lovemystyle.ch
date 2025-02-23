<?php
    $dbconnection = DBConnection::get_db_connection();
    $sql = "SELECT * FROM vats WHERE from_date <= NOW() AND to_date >= NOW() ORDER BY DATEDIFF(NOW(), from_date) ASC";
    $result = $dbconnection->query($sql);
    $vats = $result->fetchAll(PDO::FETCH_ASSOC);
    
?>
<h1>Aggiungi Prodotto</h1>

<form action="actions/products/add.php" method="POST">
    <label for="name">Nome</label>
    <input type="text" class="form-control" name="name" placeholder="Nome" required>
    <br>
    <label for="brand">Brand</label>
    <select name="brand" id="brand-select" class="form-select"></select>
    <br>
    <div class="row">
        <div class="col-6">
            <label for="price">Prezzo</label>
            <div class="input-group mb-3">
                <input type="number" class="form-control" name="price" placeholder="Prezzo" min="0" step="0.01" required>
                <span class="input-group-text">CHF</span>

            </div>

        </div>
        <div class="col-6">
            <label for="vat">IVA</label>
            <select name="vat" class="form-select">
                <?php
                   foreach ($vats as $row) {
                    $from = Utils::format_date($row['from_date']);	
                    $to = Utils::format_date($row['to_date']);
                    echo "<option value='{$row['vat_id']}'>{$row['percentage']}% ({$from} - {$to})</option>";
                     }
                ?>
            </select>
        </div>
    </div>
    <br>
    <button type="submit" class="btn btn-primary">Aggiungi Prodotto</button>
</form>

<script>
    $(document).ready(() => {
        $("#brand-select").select2({
            language: "it",
            theme: "bootstrap-5",
            ajax: {
                url: '/actions/brands/list.php',
                dataType: 'json'
            }
        })
    })
</script>