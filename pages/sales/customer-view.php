<?php
$connection = DBConnection::get_db_connection();
$customer_id = $_GET['customer_id'] ?? null;
if (!$customer_id) {
    Utils::print_error("Nessun cliente selezionato.");
    die;
}
$sql = "SELECT * FROM customers WHERE customer_id = :customer_id";
$stmt = $connection->prepare($sql);
$searchQuery = Pagination::build_search_query("", []);
$stmt->execute([':customer_id' => $customer_id]);
$customer = $stmt->fetch();
if (!$customer) {
    Utils::print_error("Cliente non trovato.");
    die;
}

$sql = "SELECT COUNT(*) FROM sales WHERE customer_id = :customer_id";
$stmt = $connection->prepare($sql);
$stmt->execute([':customer_id' => $customer_id]);
$totalRows = $stmt->fetchColumn();
$pagination = new Pagination($stmt->rowCount(), pageSize:100);

$sql = "SELECT * FROM sales  WHERE customer_id = :customer_id ORDER BY closed_at DESC, created_at DESC";
$stmt = $connection->prepare($sql);
$sql .= $pagination->get_sql();
$stmt = $connection->prepare($sql);
$stmt->execute([':customer_id' => $customer_id]);
$sales = $stmt->fetchAll();


?>
<h1>Vendite a <?php echo $customer['first_name'] . " " . $customer['last_name'] ?> </h1>
<p></p>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Data e ora apertura</th>
                <th>Data e ora chiusura</th>
                <th>Totale (CHF)</th>
                <th>Metodo di pagamento</th>
                <th>Operatore</th>
                <th>Visualizza</th>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ($sales as $sale) {
                    $saleId = $sale['sale_id'];
                    $total = 0;
                    $sign = $sale['status'] === "negative" ? -1 : 1;
                    $sql = "SELECT SUM(quantity * price) FROM sales_items WHERE sale_id = ?";
                    $stmt = $connection->prepare($sql);
                    $stmt->execute([$saleId]);
                    $total = $stmt->fetchColumn(0);
                    $total = Utils::compute_discounted_price($total, $sale['discount'] , $sale['discount_type']);
                  
                    echo "<tr class='sale-{$sale['status']}'>";

                    Utils::print_table_row(Utils::format_datetime($sale["created_at"]));
                    Utils::print_table_row(empty($sale['closed_at']) ? "-" : "<b>" . Utils::format_datetime($sale['closed_at'])  . "</b>");
                    Utils::print_table_row(Utils::format_price($total * $sign));
                    Utils::print_table_row(strtoupper($sale['payment_method'] ?? "-") ?? "-");
                    Utils::print_table_row(Auth::get_fullname_by_username($sale['username']));
                    Utils::print_table_row("<a href='index.php?page=sales_details&sale_id={$saleId}' class='btn btn-sm btn-outline-primary'><i class='fa fa-eye'></i></a>");
                }
            ?>
        </tbody>
        </table>
        </div>
        <small class="text-body-secondary"><?php echo $pagination->get_total_rows() ?> risultati.</small>

        <?php echo $pagination->get_page_links(); ?>
