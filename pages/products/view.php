<?php
$connection = DBConnection::get_db_connection();
$q = $_GET['q'] ?? "";
$orderBy = $_GET["order_by"]  ?? "name";
$orderDir = $_GET["order_dir"]  ?? ($orderBy === "name" ? "ASC" : "DESC");
$searchQuery = Pagination::build_search_query($q, ["product_id", "name"]);
$sql = "SELECT * FROM products WHERE " . $searchQuery['text'] . " ORDER BY $orderBy $orderDir";
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
                <th>IVA (%)</th>
                <th>Varianti</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody></tbody>
        </table>
        </div>
    