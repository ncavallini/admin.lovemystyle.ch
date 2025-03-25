<h1>Chiusura di cassa</h1>

<?php
$connection = DBConnection::get_db_connection();
$q = $_GET['q'] ?? "";
$searchQuery = Pagination::build_search_query($q, ["date"]);
$sql = "SELECT * FROM closings WHERE " . $searchQuery['text'] . " ORDER BY date DESC ";
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$pagination = new Pagination($stmt->rowCount());
$sql .= $pagination->get_sql();
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$closings = $stmt->fetchAll();
?>

<p>&nbsp;</p>
<form action="/index.php" method="GET" style="display: flex; align-items: center;">
    <div class="input-group mb-3">
            <input type="date" class="form-control" name="q" value="<?php echo $_GET['q'] ?? '' ?>" max="<?php echo date("Y-m-d", strtotime("yesterday")) ?>" style="flex-grow: 1; margin-right: 8px;">
            <button type="submit" class="btn btn-secondary "><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>
    <input type="hidden" name="page" value="<?php echo $_GET['page'] ?>">
</form>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Data</th>
                <th>Contenuto (CHF)</th>
                <th>Incasso (CHF)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($closings as $closing) {
                echo "<tr>";    
                Utils::print_table_row(Utils::format_date($closing['date']));
                Utils::print_table_row(Utils::format_price($closing['content']));
                Utils::print_table_row(Utils::format_price($closing['income']));
                echo "</tr>";
            }
        
            ?>
        </tbody>
    </table>
</div>
<br>
<?php echo $pagination->get_page_links(); ?>
