<?php
$connection = DBConnection::get_db_connection();
$q = $_GET['q'] ?? "";
$searchQuery = Pagination::build_search_query($q, ["sales_id", "username", "customer_id"]);
$sql = "SELECT * FROM sales WHERE " . $searchQuery['text'] . " ORDER BY datetime DESC ";
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$pagination = new Pagination($stmt->rowCount());
$sql .= $pagination->get_sql();
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$sales = $stmt->fetchAll();
?>

<h1>Vendite</h1>
<p></p>
<a href="/index.php?page=sales_add" class="btn btn-primary"><i class="fa-solid fa-plus"></i></a>
<p>&nbsp;</p>
<form action="/index.php" method="GET" style="display: flex; align-items: center;">
    <div class="input-group mb-3">
            <input type="text" class="form-control" name="q" placeholder="Cerca vendita" value="<?php echo $_GET['q'] ?? '' ?>" style="flex-grow: 1; margin-right: 8px;">
            <br>
            <button type="submit" class="btn btn-secondary "><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>
    <input type="hidden" name="page" value="<?php echo $_GET['page'] ?>">
</form>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Data e ora</th>
                <th>Cliente</th>
                <th>Totale (CHF)</th>
                <th>Metodo di pagamento</th>
                <th>Operatore</th>
                <th>Visualizza</th>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ($sales as $sale) {
        }
            ?>
        </tbody>
        </table>
        </div>