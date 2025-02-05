<?php
$connection = DBConnection::get_db_connection();
$q = $_GET['q'] ?? "";
$searchQuery = Pagination::build_search_query($q, ["sale_id", "username", "customer_id"]);
$sql = "DELETE FROM sales WHERE sale_id NOT IN (SELECT DISTINCT sale_id FROM sales_items)";
$stmt = $connection->prepare($sql);
$stmt->execute([]);

$sql = "SELECT * FROM sales WHERE " . $searchQuery['text'] . " ORDER BY closed_at DESC, created_at DESC";
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
                <th>Data e ora apertura</th>
                <th>Data e ora chiusura</th>
                <th>Cliente</th>
                <th>Totale (CHF)</th>
                <th>Metodo di pagamento</th>
                <th>Operatore</th>
                <th>Visualizza / Modifica</th>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ($sales as $sale) {
                    $saleId = $sale['sale_id'];
                    echo "<tr>";
                    Utils::print_table_row(Utils::format_datetime($sale["created_at"]));
                    Utils::print_table_row(empty($sale['closed_at']) ? "-" : "<b>" . Utils::format_date($sale['closed_at'])  . "</b>");
                    Utils::print_table_row($sale['customer_id'] ?? "<i>Esterno</i>");
                    Utils::print_table_row(data: '-');
                    Utils::print_table_row($sale['payment_method'] ?? "-");
                    Utils::print_table_row($sale['username']);
                    if($sale['is_finalized']) {
                        Utils::print_table_row("<a href='/index.php?page=sales_details&sale_id=$saleId' class='btn btn-sm btn-primary'><i class='fa fa-eye'></i></a>");
                    }
                    else {
                        Utils::print_table_row("<a href='/index.php?page=sales_add&sale_id=$saleId' class='btn btn-sm btn-primary'><i class='fa fa-edit'></i></a>");
                    }
                    echo "</tr>";
                }
            ?>
        </tbody>
        </table>
        </div>