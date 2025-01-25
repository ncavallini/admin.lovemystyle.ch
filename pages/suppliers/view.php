<?php
$connection = DBConnection::get_db_connection();
$q = $_GET['q'] ?? "";
$searchQuery = Pagination::build_search_query($q, ["supplier_id", "name", "street", "city", "postcode", "country", "tel", "email", "vat_number"]);
$sql = "SELECT * FROM suppliers WHERE " . $searchQuery['text'] . " ORDER BY name";
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$pagination = new Pagination($stmt->rowCount());
$sql .= $pagination->get_sql();
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$suppliers = $stmt->fetchAll();
?>
<h1>Fornitori</h1>
<p></p>
<a href="/index.php?page=suppliers_add" class="btn btn-primary"><i class="fa-solid fa-plus"></i></a>
<p>&nbsp;</p>
<form action="/index.php" method="GET" style="display: flex; align-items: center;">
    <div class="input-group mb-3">
            <input type="text" class="form-control" name="q" placeholder="Cerca fornitore" value="<?php echo $_GET['q'] ?? '' ?>" style="flex-grow: 1; margin-right: 8px;">
            <button type="submit" class="btn btn-secondary "><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>
    <input type="hidden" name="page" value="<?php echo $_GET['page'] ?>">
</form>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Indirizzo</th>
                <th>Telefono</th>
                <th>E-mail</th>
                <th>Partita IVA</th>
                <th>IBAN</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($suppliers as $supplier) { 
                echo "<tr>";
                Utils::print_table_row($supplier['name']);
                Utils::print_table_row(Utils::format_address($supplier['street'], $supplier['city'], $supplier['postcode'], $supplier['country']));
                Utils::print_table_row(Utils::format_phone_number($supplier['tel']));
                Utils::print_table_row($supplier['email']);
                Utils::print_table_row($supplier['vat_number']);
                Utils::print_table_row(Utils::format_iban($supplier['iban']));
                Utils::print_table_row(data: <<<EOD
                <a href="index.php?page=suppliers_edit&supplier_id={$supplier['supplier_id']}"  class="btn btn-outline-primary btn-sm" title='Modifica'><i class="fa-solid fa-pen"></i></a>
                <a href="mailto:{$supplier['email']}"  class="btn btn-outline-primary btn-sm" title='Contatta via e-mail'><i class="fa-solid fa-envelope"></i></a>
                <a href="tel:{$supplier['tel']}"  class="btn btn-outline-primary btn-sm" title='Contatta via telefono'><i class="fa-solid fa-phone"></i></a>
                <a onclick="deleteSupplier('{$supplier['supplier_id']}')" class="btn btn-outline-danger btn-sm" title='Elimina'><i class="fa-solid fa-trash"></i></a>
EOD
                );

                echo "</tr>";
        }
            ?>
        </tbody>
        </table>
        </div>

        <?php echo $pagination->get_page_links(); ?>

<script>
    function deleteSupplier(supplier_id) {
        bootbox.confirm({
            title: "Elimina Fornitore",
            message: "Sei sicuro di voler eliminare il fornitore?",
            
            callback: function(result) {
                if(result) {
                    window.location.href = "/actions/suppliers/delete.php?supplier_id=" + supplier_id;
                }
            }
        })
    }
</script>