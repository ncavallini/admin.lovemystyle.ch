<h1>Statistiche</h1>
<a href="index.php?page=stats_view"><i class="fas fa-arrow-left"></i>Torna al menù statistiche</a>
<p>&nbsp;</p>
<h2>Classifica dei prodotti più venduti</h2>
<?php 
    $nItems = isset($_GET['nItems']) && is_numeric($_GET['nItems']) ? $_GET['nItems'] : 20; // Numero di prodotti da mostrare
?>
<p>Questa pagina mostra i <?php echo $nItems; ?> prodotti più venduti, in assoluto o in uno specifico lasso di tempo.</p>

<form action="#" method="GET">
    <input type="hidden" name="page" value="stats-views_most-sold-product">
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

        <!-- Questo blocco verrà mostrato/nascosto -->
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
        <label for="nItems">Numero di prodotti da mostrare:</label>
        <div class="col-4">
               <select name="nItems" class="form-select mt-2">
        <option value="10" <?php echo ($nItems == 10) ? 'selected' : ''; ?>>10</option>
        <option value="20" <?php echo ($nItems == 20) ? 'selected' : ''; ?>>20</option>
        <option value="50" <?php echo ($nItems == 50) ? 'selected' : ''; ?>>50</option>
        <option value="100" <?php echo ($nItems == 100) ? 'selected' : ''; ?>>100</option>
    </select>
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
  si.product_id,
  p.name AS product_name,
  p.product_id as product_id,
 p.brand_id AS brand_id,
    b.name AS brand_name,
      SUM(si.quantity) AS total_quantity_sold
FROM
  sales_items si
JOIN
  products p ON si.product_id = p.product_id
  JOIN
    brands b ON p.brand_id = b.brand_id
GROUP BY
  si.product_id, p.name
ORDER BY
  total_quantity_sold DESC
LIMIT $nItems;
EOD;

    $stmt = $dbconnection->prepare($sql);
    $stmt->execute();
} else {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $sql = <<<EOD
       SELECT
    si.product_id,
    p.name AS product_name,
    p.brand_id AS brand_id,
    b.name AS brand_name,
    SUM(si.quantity) AS total_quantity_sold
FROM
    sales_items si
JOIN
    products p ON si.product_id = p.product_id
JOIN
    sales s ON si.sale_id = s.sale_id
JOIN
    brands b ON p.brand_id = b.brand_id
WHERE
    s.closed_at >= :start_date
    AND s.closed_at <= :end_date
    AND s.status = 'completed'
GROUP BY
    si.product_id, p.name
ORDER BY
    total_quantity_sold DESC
LIMIT $nItems;
EOD;

    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([
        ':start_date' => $start_date,
        ':end_date' => $end_date
    ]);
}


$top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<p>&nbsp;</p>
<div class="table-responsive">
    <table id="top_products" class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>Pos.</th>
                <th>ID Prodotto</th>
                <th>Nome</th>
                <th>Brand</th>
                <th>Quantità totale venduta</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $position = 1;
            foreach ($top_products as $row): ?>
                <tr>
                    <?php
                    echo Utils::print_table_row($position++);
                    echo Utils::print_table_row("<span class='tt'>" . $row['product_id'] . "</span>");
                    echo Utils::print_table_row($row['product_name']);
                    echo Utils::print_table_row($row['brand_name']);
                    echo Utils::print_table_row($row['total_quantity_sold']);
                    ?>
                </tr>
            <?php endforeach; ?>

        </tbody>
    </table>
</div>

<script>
    function toggleTimeframeInputs() {
        const isTimeframe = document.getElementById("timeframe_type-timeframe").checked;
        const inputs = document.querySelectorAll(".timeframe-inputs");
        inputs.forEach(input => {
            input.style.display = isTimeframe ? "block" : "none";
        });
    }

    // Al caricamento iniziale
    document.addEventListener("DOMContentLoaded", () => {
        toggleTimeframeInputs();

        // Aggiunge l'evento al cambio radio
        document.querySelectorAll("input[name='timeframe_type']").forEach(radio => {
            radio.addEventListener("change", toggleTimeframeInputs);
        });
    });
</script>

<?php
$title = "Top 10 dei prodotti più venduti";
$title .= (isset($_GET['timeframe_type']) && $_GET['timeframe_type'] === "timeframe") ? " dal " . Utils::format_date($_GET['start_date']) . " al " . Utils::format_date($_GET['end_date']) : "";

?>

<script>
    let dt = new DataTable("#top_products", {
        language: {
            url: '//cdn.datatables.net/plug-ins/2.3.0/i18n/it-IT.json',
        },

        pageLength: 30, // quanti elementi per pagina
        lengthMenu: [10, 25, 30, 50, 100], // opzioni selezionabili nel menu a tendina
        sortable: true,
        searchable: true,
        columnDefs: [{
                targets: 0,
                type: 'date-eu'
            } // la colonna "Data"
        ],
        dom: 'Brtip', // B = Buttons, f = filter, r = processing, t = table, i = info, p = pagination
        buttons: [{
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i>',
                title: '<?php echo $title ?>', // Excel file name (optional)
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i>',
                title: '<?php echo $title ?>', // Excel file name (optional)
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