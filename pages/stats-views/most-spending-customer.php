<h1>Statistiche</h1>
<a href="index.php?page=stats_view"><i class="fas fa-arrow-left"></i> Torna al menù statistiche</a>
<p>&nbsp;</p>

<h2>Clienti più spendenti</h2>
<?php 
    // Numero di clienti da mostrare (default 10/20/50/100 come nell’altra pagina)
    $nItems = isset($_GET['nItems']) && is_numeric($_GET['nItems']) ? (int)$_GET['nItems'] : 10; 

    // Metrica: 'spend' = per spesa totale, 'receipts' = per numero scontrini
    $metric = isset($_GET['metric']) && in_array($_GET['metric'], ['spend','receipts']) ? $_GET['metric'] : 'spend';

    // Tipo intervallo
    $timeframeType = isset($_GET['timeframe_type']) && $_GET['timeframe_type'] === 'timeframe' ? 'timeframe' : 'absolute';
?>

<p>Questa pagina mostra i <?php echo $nItems; ?> clienti che hanno speso di più o che hanno effettuato più scontrini, in assoluto o in un intervallo di tempo.</p>

<form action="#" method="GET">
    <input type="hidden" name="page" value="stats-views_most-spending-customer">

    <div class="row">
        <div class="col">
            <label class="form-label d-block">Metrica</label>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" name="metric" id="metric-spend" value="spend" <?php echo $metric==='spend' ? 'checked' : '';?>>
                <label for="metric-spend" class="form-check-label">Per spesa totale</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" name="metric" id="metric-receipts" value="receipts" <?php echo $metric==='receipts' ? 'checked' : '';?>>
                <label for="metric-receipts" class="form-check-label">Per numero scontrini</label>
            </div>
        </div>
    </div>

    <p>&nbsp;</p>

    <div class="row">
        <div class="col">
            <input type="radio" class="form-check-input" name="timeframe_type" id="timeframe_type-absolute" value="absolute"
                <?php echo ($timeframeType==='absolute') ? 'checked' : ''; ?>>
            <label for="timeframe_type-absolute" class="form-check-label">Assoluto</label>
        </div>
        <div class="col">
            <input type="radio" class="form-check-input" name="timeframe_type" id="timeframe_type-timeframe" value="timeframe"
                <?php echo ($timeframeType==='timeframe') ? 'checked' : ''; ?>>
            <label for="timeframe_type-timeframe" class="form-check-label">Intervallo di tempo</label>
        </div>

        <div class="col timeframe-inputs">
            <label for="start_date">Data inizio:</label>
            <input type="date" name="start_date" id="start_date-input" class="form-control"
                value="<?php echo $_GET['start_date'] ?? '' ?>" max="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="col timeframe-inputs">
            <label for="end_date">Data fine:</label>
            <input type="date" name="end_date" id="end_date-input" class="form-control"
                value="<?php echo $_GET['end_date'] ?? '' ?>" max="<?php echo date('Y-m-d'); ?>">
        </div>
    </div>

    <p>&nbsp;</p>

    <div class="row">
        <label for="nItems">Numero di clienti da mostrare:</label>
        <div class="col-4">
            <select name="nItems" class="form-select mt-2">
                <option value="10"  <?php echo ($nItems == 10)  ? 'selected' : ''; ?>>10</option>
                <option value="20"  <?php echo ($nItems == 20)  ? 'selected' : ''; ?>>20</option>
                <option value="50"  <?php echo ($nItems == 50)  ? 'selected' : ''; ?>>50</option>
                <option value="100" <?php echo ($nItems == 100) ? 'selected' : ''; ?>>100</option>
            </select>
        </div>
    </div>

    <br>
    <button type="submit" class="btn btn-outline-primary">Visualizza</button>
</form>

<?php
$db = DBConnection::get_db_connection();

/**
 * Nota su schema tabelle (assunzioni):
 * - sales (sale_id, customer_number, status, closed_at, ...)
 * - sales_items (sale_id, product_id, quantity, price, total_price?, ...)
 * - customers (customer_number, first_name, last_name, email, ...)
 *
 * Calcolo importo riga:
 *   preferiamo usare si.total_price se disponibile, altrimenti si.price * si.quantity
 */
$amountExpr = "COALESCE(si.total_price, si.price * si.quantity)";

/**
 * Base query (comune ai due casi), filtrata per 'completed'
 * e con join a customers per nome.
 */
$selectCommon = "
    SELECT
        s.customer_id,
        CONCAT(COALESCE(c.first_name, ''), ' ', COALESCE(c.last_name, '')) AS customer_name,
        COUNT(DISTINCT s.sale_id) AS receipts_count,
        SUM($amountExpr) AS total_spent
    FROM sales s
    JOIN sales_items si ON si.sale_id = s.sale_id
    LEFT JOIN customers c ON c.customer_id = s.customer_id
    WHERE s.status = 'completed'
";
$groupOrderLimit = " GROUP BY s.customer_id ";

/** Costruiamo dinamicamente ORDER BY in base alla metrica scelta */
$orderBy = ($metric === 'receipts') ? " ORDER BY receipts_count DESC, total_spent DESC " 
                                    : " ORDER BY total_spent DESC, receipts_count DESC ";

/** Timeframe */
$params = [];
if ($timeframeType === 'timeframe') {
    $start_date = $_GET['start_date'] ?? '';
    $end_date   = $_GET['end_date'] ?? '';
    // Inclusivo su end_date fino a 23:59:59
    $selectCommon .= " AND s.closed_at >= :start_date AND s.closed_at < DATE_ADD(:end_date, INTERVAL 1 DAY) ";
    $params[':start_date'] = $start_date . " 00:00:00";
    $params[':end_date']   = $end_date   . " 23:59:59";
}

$sql = $selectCommon . $groupOrderLimit . $orderBy . " LIMIT :limitVal";
$stmt = $db->prepare($sql);

/** Bind parametri */
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limitVal', (int)$nItems, PDO::PARAM_INT);

$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

/** Titolo export */
$title = ($metric === 'receipts') ? "Top {$nItems} clienti per numero scontrini" : "Top {$nItems} clienti per spesa totale";
if ($timeframeType === 'timeframe' && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $title .= " dal " . Utils::format_date($_GET['start_date']) . " al " . Utils::format_date($_GET['end_date']);
}
?>

<p>&nbsp;</p>
<div class="table-responsive">
    <table id="top_customers" class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>Pos.</th>
                <th>Nome e cognome</th>
                <th>N. scontrini</th>
                <th>Spesa totale</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $pos = 1;
        foreach ($rows as $r):
            if(!$r['customer_id']) continue; // Skip clienti senza ID  
            $custName = trim($r['customer_name']);
            $receipts = (int)$r['receipts_count'];
            $spent    = (int)$r['total_spent'];
        ?>
            <tr>
                <?php
                    echo Utils::print_table_row($pos++);
                    echo Utils::print_table_row(htmlspecialchars($custName));
                    echo Utils::print_table_row($receipts);
                    echo Utils::print_table_row(Utils::format_price($spent));
                ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function toggleTimeframeInputs() {
    const isTimeframe = document.getElementById("timeframe_type-timeframe").checked;
    document.querySelectorAll(".timeframe-inputs").forEach(el => {
        el.style.display = isTimeframe ? "block" : "none";
    });
}

document.addEventListener("DOMContentLoaded", () => {
    toggleTimeframeInputs();
    document.querySelectorAll("input[name='timeframe_type']").forEach(r => {
        r.addEventListener("change", toggleTimeframeInputs);
    });
});
</script>

<script>
let dt = new DataTable("#top_customers", {
    language: { url: '//cdn.datatables.net/plug-ins/2.3.0/i18n/it-IT.json' },
    pageLength: 30,
    lengthMenu: [10, 25, 30, 50, 100],
    sortable: true,
    searchable: true,
    dom: 'Brtip',
    buttons: [
        {
            extend: 'excelHtml5',
            text: '<i class="fas fa-file-excel"></i>',
            title: '<?php echo $title; ?>',
        },
        {
            extend: 'print',
            text: '<i class="fas fa-print"></i>',
            title: '<?php echo $title; ?>',
            customize: function (win) {
                $(win.document.body).css('font-size', '10pt');
                $(win.document.body).find('table')
                    .addClass('compact')
                    .css('font-size', 'inherit');
            }
        }
    ]
});
</script>
