<?php
$connection = DBConnection::get_db_connection();
$q = $_GET['q'] ?? "";
$searchQuery = Pagination::build_search_query($q, ["sale_id", "username", "s.customer_id", "c.first_name", "c.last_name"]);
$sql = "DELETE FROM sales WHERE sale_id NOT IN (SELECT DISTINCT sale_id FROM sales_items)";
$stmt = $connection->prepare($sql);
$stmt->execute([]);

$sql = "SELECT s.*, c.first_name, c.last_name FROM sales s  LEFT JOIN customers c ON s.customer_id = c.customer_id WHERE " . $searchQuery['text'] . " ORDER BY created_at DESC, closed_at DESC, created_at DESC";
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
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ($sales as $sale) {
                    $saleId = $sale['sale_id'];
                    $total = 0;
                    $sql = "SELECT SUM(quantity * price) FROM sales_items WHERE sale_id = ?";
                    $stmt = $connection->prepare($sql);
                    $stmt->execute([$saleId]);
                    $total = $stmt->fetchColumn(0);
                    $total = Utils::compute_discounted_price($total, $sale['discount'], $sale['discount_type']);
                  
                    echo "<tr class='sale-{$sale['status']}'>";

                    Utils::print_table_row(Utils::format_datetime($sale["created_at"]));
                    Utils::print_table_row(empty($sale['closed_at']) ? "-" : "<b>" . Utils::format_datetime($sale['closed_at'])  . "</b>");
                    Utils::print_table_row(($sale['first_name'] != null) ? $sale['first_name'] . " " . $sale['last_name'] : "<i>Esterno</i>");
                    Utils::print_table_row(Utils::format_price($total));
                    Utils::print_table_row(strtoupper($sale['payment_method'] ?? "-") ?? "-");
                    Utils::print_table_row(Auth::get_fullname_by_username($sale['username']));

                switch ($sale['status']) {
                    case "completed":
                        Utils::print_table_row(
                            class: "text-start",
                            data: "<a href='/index.php?page=sales_details&sale_id=$saleId' class='btn btn-sm btn-outline-primary' title='Visualizza'>
                                <i class='fa fa-eye'></i>
                            </a> 
                            &nbsp; 
                            <a href='javascript:void(0)' onclick='askCancelType(\"{$saleId}\")' class='btn btn-sm btn-outline-danger' title='Storna'>
                                <i class='fa fa-trash'></i>
                            </a>"
                        );
                        break;
                    case "open":
                        Utils::print_table_row(class: "text-start", data: "<a href='/index.php?page=sales_add&sale_id=$saleId' class='btn btn-sm btn-outline-primary' title='Modifica'><i class='fa fa-edit'></i></a>");
                        break;
                    default:
                        Utils::print_table_row(
                            class: "text-start",
                            data: "<a href='/index.php?page=sales_details&sale_id=$saleId' class='btn btn-sm btn-outline-primary' title='Visualizza'>
                                <i class='fa fa-eye'></i>
                            </a> "
                        );
                        break;
                }
                    echo "</tr>";
                }
            ?>
        </tbody>
        </table>
        </div>
        <small class="text-body-secondary"><?php echo $pagination->get_total_rows() ?> risultati.</small>

        <?php echo $pagination->get_page_links(); ?>

<script>
    function askCancelType(sale_id) {
        bootbox.prompt({
            title: "Selezionare il tipo di storno",
            message: "<div class='alert alert-primary'><i class='fa-solid fa-circle-info'></i> Lo storno parziale cancella la vendita in questione e ne crea una nuova copia modificabile.</div>",
            size: "lg",
            inputType: 'radio',
            inputOptions: [
                {
                    text: 'Totale',
                    value: 'total',
                },
                {
                    text: 'Parziale',
                    value: 'partial',
                }
            ],
            callback: function (result) {
                if (result) {
                    if (result == "total") {
                        window.location.href = "/actions/sales/cancel_total.php?sale_id=" + sale_id;
                    }
                    else {
                        window.location.href = "/actions/sales/cancel_partial.php?sale_id=" + sale_id;
                    }
                }
            }
        })
    }
</script>