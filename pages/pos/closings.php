<h1>Chiusura di cassa</h1>

<?php
$connection = DBConnection::get_db_connection();
$q = $_GET['q'] ?? "";
$searchQuery = Pagination::build_search_query($q, ["date"]);

// First, get the total count for pagination
$countSql = "SELECT COUNT(*) FROM closings WHERE " . $searchQuery['text'];
$countStmt = $connection->prepare($countSql);
$countStmt->execute($searchQuery['params']);
$totalRows = $countStmt->fetchColumn();

// Initialize pagination with the total count
$pagination = new Pagination($totalRows);

// Now get the actual data with pagination
$sql = "SELECT * FROM closings WHERE " . $searchQuery['text'] . " ORDER BY date DESC " . $pagination->get_sql();
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
                <th>Stampa scontrino</th>
            </tr>
        </thead>
        <tbody>

            <?php
            // Contenuto odierno in cassa (in centesimi)
            $sql = "SELECT content FROM cash_content WHERE id = 1";
            $stmt = $connection->prepare($sql);
            $stmt->execute();
            $todayContent = $stmt->fetchColumn();

            // Vendite di oggi (completate o storni)
            $sql = "SELECT sale_id, status, discount, discount_type 
                    FROM sales 
                    WHERE DATE(closed_at) = CURDATE() 
                      AND (status = 'completed' OR status = 'negative')";
            $stmt = $connection->prepare($sql);
            $stmt->execute();
            $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $todayIncome = 0;

            foreach ($sales as $sale) {
                $sign = ($sale['status'] === "negative") ? -1 : 1;

                // Carica gli articoli della vendita (prezzi in centesimi)
                $sql = "SELECT product_id, variant_id, quantity, price, total_price, is_discounted
                        FROM sales_items
                        WHERE sale_id = ?";
                $stmt = $connection->prepare($sql);
                $stmt->execute([$sale['sale_id']]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Suddividi il subtotale tra quota scontabile e non scontabile
                $absSubtotalDiscountable = 0;      // is_discounted = 0
                $absSubtotalNonDiscountable = 0;   // is_discounted = 1
                $containsDiscounted = false;

                foreach ($items as $it) {
                    $line = (int)$it['price'] * (int)$it['quantity'];
                    if (!empty($it['is_discounted'])) {
                        $containsDiscounted = true;
                        $absSubtotalNonDiscountable += $line;
                    } else {
                        $absSubtotalDiscountable += $line;
                    }
                }

                // Regola aziendale: gli articoli scontati non possono essere stornati.
                // Per robustezza: se mai dovesse comparire uno storno con articoli scontati, ignoriamo il suo impatto.
                if ($sale['status'] === "negative" && $containsDiscounted) {
                    continue;
                }

                // Applica lo sconto di vendita SOLO alla quota scontabile
                $discountVal  = (float)($sale['discount'] ?? 0);
                $discountType = (string)($sale['discount_type'] ?? 'CHF');

                $discountCentsApplied = 0;
                if ($discountVal > 0 && $absSubtotalDiscountable > 0) {
                    if ($discountType === 'CHF') {
                        $discountCentsApplied = min((int)round($discountVal * 100), $absSubtotalDiscountable);
                    } else { // "%"
                        $discountCentsApplied = (int) floor($absSubtotalDiscountable * ($discountVal / 100));
                    }
                }

                $absGrandTotal = ($absSubtotalDiscountable - $discountCentsApplied) + $absSubtotalNonDiscountable;
                $todayIncome += $sign * $absGrandTotal;
            }
            ?>

            <?php
            echo "<tr style='font-style: italic;'>";
            Utils::print_table_row(date("d/m/Y") . " (oggi - provvisorio)");
            Utils::print_table_row(Utils::format_price($todayContent));
            Utils::print_table_row(Utils::format_price($todayIncome), "b");
            Utils::print_table_row("");
            echo "</tr>";
            ?>

            <?php
            foreach ($closings as $closing) {
                echo "<tr>";
                Utils::print_table_row(Utils::format_date($closing['date']));
                Utils::print_table_row(Utils::format_price($closing['content']));
                Utils::print_table_row(Utils::format_price($closing['income']), "b");
                Utils::print_table_row(data: <<<EOD
                <button class="btn btn-outline-secondary btn-sm" onclick="printReceipt('{$closing['date']}')" ><i class='fa-solid fa-receipt'></i></button>
EOD
                );
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<br>
<?php echo $pagination->get_page_links(); ?>

<script>
    function printReceipt(date) {
        fetch(`/cron/close_cash_desk.php?key=<?php echo $CONFIG['CRON_KEY'] ?>&date=${date}&no_update=1`)
            .then(res => res.text())
            .catch(err => {
                console.error("Error printing receipt:", err);
                alert("Errore durante la stampa dello scontrino. Controlla la console per maggiori dettagli.");
            });
    }
</script>
