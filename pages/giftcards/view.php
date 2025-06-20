<?php
$connection = DBConnection::get_db_connection();
$q = $_GET['q'] ?? "";
$searchQuery = Pagination::build_search_query($q, ["last_name", "first_name", "customer_id", "card_id"]);
$sql = "SELECT gc.*, COALESCE(c.first_name, gc.first_name) AS resolved_first_name, COALESCE(c.last_name, gc.last_name) AS resolved_last_name FROM gift_cards gc LEFT JOIN customers c USING(customer_id)  WHERE " . $searchQuery['text'] . " ORDER BY gc.created_at DESC ";
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$pagination = new Pagination($stmt->rowCount());
$sql .= $pagination->get_sql();
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$gift_cards = $stmt->fetchAll();
?>


<h1>Carte Regalo</h1>
<p></p>
<a href="/index.php?page=giftcards_add" class="btn btn-primary"><i class="fa-solid fa-plus"></i></a>
<p>&nbsp;</p>
<form action="/index.php" method="GET" style="display: flex; align-items: center;">
    <div class="input-group mb-3">
            <input type="text" class="form-control" name="q" placeholder="Cerca carta regalo" value="<?php echo $_GET['q'] ?? '' ?>" style="flex-grow: 1; margin-right: 8px;">
            <button type="submit" class="btn btn-secondary "><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>
    <input type="hidden" name="page" value="<?php echo $_GET['page'] ?>">
</form>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>N. Carta</th>
                <th>Cliente</th>
                <th>Importo (CHF)</th>
                <th>Bilancio (CHF)</th>
                <th>Data di emissione</th>
                <th>Data di inizio validità</th>
                <th>Data di scadenza</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($gift_cards as $gift_card) {
                echo "<tr>";    
                Utils::print_table_row(data: "<span class='tt'>" . $gift_card['card_id'] . "</span>");
                Utils::print_table_row(data: $gift_card['resolved_first_name'] . " " . $gift_card['resolved_last_name']);
                Utils::print_table_row(Utils::format_price($gift_card['amount']));
                Utils::print_table_row(Utils::format_price($gift_card['balance']));
                Utils::print_table_row(Utils::format_date($gift_card['created_at']));
                Utils::print_table_row(Utils::format_date($gift_card['starts_at']));
                Utils::print_table_row(Utils::format_date($gift_card['expires_at']));
                Utils::print_table_row(data: <<<EOD
                <a href="index.php?page=sales_details&sale_id={$gift_card['sale_id']}" class="btn btn-outline-primary btn-sm" title='Vedi vendita'><i class="fa-solid fa-eye"></i></a>
EOD
                );

                echo "</tr>";
            }
        
            ?>
        </tbody>
    </table>
</div>
<br>
<small class="text-body-secondary"><?php echo $pagination->get_total_rows() ?> risultati.</small>

<?php echo $pagination->get_page_links(); ?>

<script>
    function deleteCustomer(customer_id) {
        bootbox.confirm({
            title: "Elimina Cliente",
            message: "Sei sicuro di voler eliminare il cliente?",
            
            callback: function(result) {
                if(result) {
                    window.location.href = "/actions/customers/delete.php?customer_id=" + customer_id;
                }
            }
        })
    }
</script>