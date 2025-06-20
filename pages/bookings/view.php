<?php
$connection = DBConnection::get_db_connection();
$q = $_GET['q'] ?? "";
$searchQuery = Pagination::build_search_query($q, ["last_name", "first_name", "customer_id", "booking_id"]);
$sql = "SELECT 
  b.*, 
  c.first_name, c.last_name, c.customer_id, c.tel, c.email, 
  p.name AS product_name, 
  v.color, v.size
FROM bookings b
JOIN customers c ON b.customer_id = c.customer_id
JOIN products p ON b.product_id = p.product_id
JOIN product_variants v ON b.variant_id = v.variant_id AND b.product_id = v.product_id;
        WHERE " . $searchQuery['text'] . " 
        ORDER BY b.from_datetime DESC";
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$pagination = new Pagination($stmt->rowCount());
$sql .= $pagination->get_sql();
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$bookings = $stmt->fetchAll();
?>


<h1>Riservazioni</h1>
<p></p>
<a href="/index.php?page=bookings_add" class="btn btn-primary"><i class="fa-solid fa-plus"></i></a>
<p>&nbsp;</p>
<form action="/index.php" method="GET" style="display: flex; align-items: center;">
    <div class="input-group mb-3">
        <input type="text" class="form-control" name="q" placeholder="Cerca riservazione" value="<?php echo $_GET['q'] ?? '' ?>" style="flex-grow: 1; margin-right: 8px;">
        <button type="submit" class="btn btn-secondary "><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>
    <input type="hidden" name="page" value="<?php echo $_GET['page'] ?>">
</form>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Data/ora riservazione</th>
                <th>Data/ora scadenza</th>
                <th>Articolo</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($bookings as $booking) {
                echo "<tr>";
                Utils::print_table_row($booking['last_name'] . " " . $booking['first_name'], "text-nowrap");
                Utils::print_table_row(Utils::format_datetime($booking['from_datetime']), "text-nowrap");
                Utils::print_table_row(Utils::format_datetime($booking['to_datetime']), "text-nowrap");
                Utils::print_table_row($booking['product_name'] . " @ " . $booking['size'] . " / " . $booking['color'] . " (<span class='tt'>" . InternalNumbers::get_sku($booking['product_id'], $booking['variant_id']), "text-nowrap");
                /*
                Utils::print_table_row(data: <<<EOD
                <a href="index.php?page=sales_details&sale_id={$gift_card['sale_id']}" class="btn btn-outline-primary btn-sm" title='Vedi vendita'><i class="fa-solid fa-eye"></i></a>
EOD
                );
*/
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
                if (result) {
                    window.location.href = "/actions/customers/delete.php?customer_id=" + customer_id;
                }
            }
        })
    }
</script>