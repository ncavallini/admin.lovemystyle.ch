<?php
$connection = DBConnection::get_db_connection();
$q = $_GET['q'] ?? "";
$orderBy = $_GET["order_by"]  ?? "name";
$orderDir = $_GET["order_dir"]  ?? ($orderBy === "name" ? "ASC" : "DESC");
$searchQuery = Pagination::build_search_query($q, ["product_id", "p.name"]);
$sql = "SELECT p.*, s.name AS supplier_name FROM products p JOIN suppliers s USING(supplier_id) WHERE " . $searchQuery['text'] . " ORDER BY p.$orderBy $orderDir";
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$pagination = new Pagination($stmt->rowCount());
$sql .= $pagination->get_sql();
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$products = $stmt->fetchAll();
?>
<h1>Prodotti</h1>
<p></p>
<a href="/index.php?page=products_add" class="btn btn-primary"><i class="fa-solid fa-plus"></i></a>
<p>&nbsp;</p>
<form action="/index.php" method="GET" style="display: flex; align-items: center;">
    <div class="input-group mb-3">
            <input type="text" class="form-control" name="q" placeholder="Cerca prodotto" value="<?php echo $_GET['q'] ?? '' ?>" style="flex-grow: 1; margin-right: 8px;">
            <button type="submit" class="btn btn-secondary "><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>
    <input type="hidden" name="page" value="<?php echo $_GET['page'] ?>">
</form>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID Prodotto</th>
                <th>Nome</th>
                <th>Fornitore</th>
                <th>Prezzo (CHF)</th>
                <th>Varianti</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ($products as $product) {
                    Utils::print_table_row("<span class='tt'>{$product['product_id']}</span>");
                    Utils::print_table_row($product['name']);
                    Utils::print_table_row($product['supplier_name']);
                    Utils::print_table_row(Utils::format_price($product['price']));
                    Utils::print_table_row("<a href='index.php?page=variants_view&product_id={$product['product_id']}'  class='btn btn-outline-primary btn-sm' title='Varianti'><i class='fa-solid fa-shirt'></i></a>");
                    Utils::print_table_row(data: <<<EOD
                    <a href="index.php?page=products_edit&product_id={$product['product_id']}"  class="btn btn-outline-primary btn-sm" title='Modifica'><i class="fa-solid fa-pen"></i></a>
                    <a onclick="deleteProduct('{$product['product_id']}')" class="btn btn-outline-danger btn-sm" title='Elimina'><i class="fa-solid fa-trash"></i></a>
    EOD
                    );
    
                }
            ?>
        </tbody>
        </table>
        </div>
    
<script>
    function deleteProduct(product_id) {
        bootbox.confirm({
            title: "Elimina Prodotto",
            message: "Sei sicuro di voler eliminare il prodotto e tutte le sue varianti?",
            
            callback: function(result) {
                if(result) {
                    window.location.href = "/actions/products/delete.php?product_id=" + product_id;
                }
            }
        })
    }
</script>