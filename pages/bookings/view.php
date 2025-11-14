<?php
$connection = DBConnection::get_db_connection();
$q = $_GET['q'] ?? "";
$searchQuery = Pagination::build_search_query($q, ["customer_id", "booking_id"]);
$countSql = "SELECT COUNT(*) FROM bookings WHERE " . $searchQuery['text'];
$countStmt = $connection->prepare($countSql);
$countStmt->execute($searchQuery['params']);
$totalRows = $countStmt->fetchColumn();

// Initialize pagination with the total count
$pagination = new Pagination($totalRows);

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
        ORDER BY b.from_datetime DESC" . $pagination->get_sql();
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
 <input type="text" class="form-control" name="q" placeholder="Cerca riservazione" value="<?php echo
  htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="flex-grow: 1; margin-right: 8px;">        <button type="submit" class="btn btn-secondary "><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>
    <input type="hidden" name="page" value="<?php echo htmlspecialchars($_GET['page'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
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
                Utils::print_table_row($booking['product_name'] . " @ " . $booking['size'] . " / " . $booking['color'] . " (<span class='tt'>" . InternalNumbers::get_sku($booking['product_id'], $booking['variant_id']) . "</span>)", "text-nowrap");
                
                Utils::print_table_row(data: <<<EOD
            <a href="actions/bookings/print.php?booking_id={$booking['booking_id']}" class="btn btn-outline-secondary btn-sm" title='Stampa'><i class="fa-solid fa-print"></i></a>
            <a onclick="deleteBooking('{$booking['booking_id']}')" class="btn btn-outline-danger btn-sm" title='Elimina'><i class="fa-solid fa-trash"></i></a>

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
    function deleteBooking(booking_id) {
        bootbox.confirm({
            title: "Elimina Prenotazione",
            message: "Sei sicuro di voler eliminare la prenotazione?",

            callback: function(result) {
                if (result) {
                    window.location.href = "/actions/bookings/delete.php?booking_id=" + booking_id;
                }
            }
        })
    }
</script>