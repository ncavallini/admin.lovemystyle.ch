<?php
$connection = DBConnection::get_db_connection();
$q = $_GET['q'] ?? "";
$searchQuery = Pagination::build_search_query($q, ["brand_id", "b.name", "s.name"]);

// First, get the total count for pagination
$countSql = "SELECT COUNT(*) FROM brands b JOIN suppliers s USING(supplier_id) WHERE " . $searchQuery['text'];
$countStmt = $connection->prepare($countSql);
$countStmt->execute($searchQuery['params']);
$totalRows = $countStmt->fetchColumn();

// Initialize pagination with the total count
$pagination = new Pagination($totalRows);

// Now get the actual data with pagination
$sql = "SELECT b.*, s.name AS supplier_name FROM brands b JOIN suppliers s USING(supplier_id) WHERE " . $searchQuery['text'] . " ORDER BY b.name" . $pagination->get_sql();
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$brands = $stmt->fetchAll();
?>
<h1>Brand</h1>
<p></p>
<a href="/index.php?page=brands_add" class="btn btn-primary"><i class="fa-solid fa-plus"></i></a>
<p>&nbsp;</p>
<form action="/index.php" method="GET" style="display: flex; align-items: center;">
    <div class="input-group mb-3">
            <input type="text" class="form-control" name="q" placeholder="Cerca brand" value="<?php echo $_GET['q'] ?? '' ?>" style="flex-grow: 1; margin-right: 8px;">
            <button type="submit" class="btn btn-secondary "><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>
    <input type="hidden" name="page" value="<?php echo $_GET['page'] ?>">
</form>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Fornitore</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($brands as $brand) { 
                echo "<tr>";
                Utils::print_table_row($brand['name']);
                Utils::print_table_row($brand['supplier_name']);
                Utils::print_table_row(data: <<<EOD
                <a href="index.php?page=brands_edit&brand_id={$brand['brand_id']}"  class="btn btn-outline-primary btn-sm" title='Modifica'><i class="fa-solid fa-pen"></i></a>
                <a onclick="deleteBrand('{$brand['brand_id']}')" class="btn btn-outline-danger btn-sm" title='Elimina'><i class="fa-solid fa-trash"></i></a>
EOD
                );

                echo "</tr>";
        }
            ?>
        </tbody>
        </table>
        </div>
        <small class="text-body-secondary"><?php echo $pagination->get_total_rows() ?> risultati.</small>

        <?php echo $pagination->get_page_links(); ?>

<script>
    function deleteBrand(brand_id) {
        bootbox.confirm({
            title: "Elimina Brand",
            message: "Sei sicuro di voler eliminare il brand?",
            
            callback: function(result) {
                if(result) {
                    window.location.href = "/actions/brands/delete.php?brand_id=" + brand_id;
                }
            }
        })
    }
</script>