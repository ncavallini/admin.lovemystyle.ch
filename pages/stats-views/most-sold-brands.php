<h1>Statistiche</h1>
<a href="index.php?page=stats_view"><i class="fas fa-arrow-left"></i> Torna al menù statistiche</a>
<p>&nbsp;</p>
<h2>Vendite per Brand</h2>
<p>Questa pagina mostra il numero di vendite e il valore totale per ciascun brand, in assoluto o in un intervallo di tempo specifico.</p>

<form action="#" method="GET">
    <input type="hidden" name="page" value="stats-views_most-sold-brands">
    <div class="row">
        <div class="col">
            <input type="radio" class="form-check-input" name="timeframe_type" id="timeframe_type-absolute" value="absolute"
                <?php echo (!isset($_GET['timeframe_type']) || $_GET['timeframe_type'] == 'absolute') ? 'checked' : ''; ?>>
            <label for="timeframe_type-absolute" class="form-check-label">Assoluto</label>
        </div>
        <div class="col">
            <input type="radio" class="form-check-input" name="timeframe_type" id="timeframe_type-timeframe" value="timeframe"
                <?php echo (isset($_GET['timeframe_type']) && $_GET['timeframe_type'] == 'timeframe') ? 'checked' : ''; ?>>
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
    <br>
    <button type="submit" class="btn btn-outline-primary">Visualizza</button>
</form>

<?php
$dbconnection = DBConnection::get_db_connection();

if (!isset($_GET['timeframe_type']) || $_GET['timeframe_type'] == 'absolute') {
    $sql = <<<EOD
        SELECT
            b.name AS brand_name,
            COUNT(DISTINCT s.sale_id) AS total_sales,
            SUM(si.total_price) AS total_revenue
        FROM
            sales_items si
        JOIN
            sales s ON si.sale_id = s.sale_id
        JOIN
            products p ON si.product_id = p.product_id
        JOIN
            brands b ON p.brand_id = b.brand_id
        WHERE
            s.status = 'completed'
        GROUP BY
            b.brand_id
        ORDER BY
            total_revenue DESC
       
    EOD;
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute();
} else {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $sql = <<<EOD
        SELECT
            b.name AS brand_name,
            COUNT(DISTINCT s.sale_id) AS total_sales,
            SUM(si.total_price) AS total_revenue
        FROM
            sales_items si
        JOIN
            sales s ON si.sale_id = s.sale_id
        JOIN
            products p ON si.product_id = p.product_id
        JOIN
            brands b ON p.brand_id = b.brand_id
        WHERE
            s.status = 'completed'
            AND s.closed_at BETWEEN :start_date AND :end_date
        GROUP BY
            b.brand_id
        ORDER BY
            total_revenue DESC
        LIMIT 10;
    EOD;
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([
        ':start_date' => $start_date,
        ':end_date' => $end_date
    ]);
}

$top_brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<p>&nbsp;</p>
<div class="table-responsive">
    <table id="top_brands" class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>Pos.</th>
                <th>Brand</th>
                <th>Numero vendite</th>
                <th>Valore totale (CHF)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $position = 1;
            $chart_labels = [];
            $chart_values = [];
            foreach ($top_brands as $row):
                $chart_labels[] = $row['brand_name'];
                $chart_values[] = $row['total_revenue'] / 100; // Assuming cents
                ?>
                <tr>
                    <?php
                    echo Utils::print_table_row($position++);
                    echo Utils::print_table_row($row['brand_name']);
                    echo Utils::print_table_row($row['total_sales']);
                    echo Utils::print_table_row(number_format($row['total_revenue'] / 100, 2, ',', '.'));
                    ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<canvas id="pieChart" width="400" height="400"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const pieChart = new Chart(document.getElementById('pieChart'), {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'Valore vendite (CHF)',
                data: <?php echo json_encode($chart_values); ?>,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Distribuzione valore vendite per Brand'
                }
            }
        }
    });
</script>

<script>
    function toggleTimeframeInputs() {
        const isTimeframe = document.getElementById("timeframe_type-timeframe").checked;
        const inputs = document.querySelectorAll(".timeframe-inputs");
        inputs.forEach(input => {
            input.style.display = isTimeframe ? "block" : "none";
        });
    }

    document.addEventListener("DOMContentLoaded", () => {
        toggleTimeframeInputs();
        document.querySelectorAll("input[name='timeframe_type']").forEach(radio => {
            radio.addEventListener("change", toggleTimeframeInputs);
        });
    });
</script>

<?php
$title = "Vendite per brand";
$title .= (isset($_GET['timeframe_type']) && $_GET['timeframe_type'] === "timeframe") ? " dal " . Utils::format_date($_GET['start_date']) . " al " . Utils::format_date($_GET['end_date']) : "";
?>

<script>
    let dt = new DataTable("#top_brands", {
        language: {
            url: '//cdn.datatables.net/plug-ins/2.3.0/i18n/it-IT.json',
        },
        pageLength: 30,
        lengthMenu: [10, 25, 30, 50, 100],
        sortable: true,
        searchable: true,
        columnDefs: [{
            targets: 0,
            type: 'date-eu'
        }],
        dom: 'Brtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i>',
                title: '<?php echo $title ?>',
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i>',
                title: '<?php echo $title ?>',
                customize: function(win) {
                    $(win.document.body).css('font-size', '10pt');
                    $(win.document.body).find('table')
                        .addClass('compact')
                        .css('font-size', 'inherit');
                }
            }
        ]
    });
</script>
