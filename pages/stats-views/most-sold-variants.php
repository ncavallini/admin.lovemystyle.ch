<h1>Statistiche</h1>
<a href="index.php?page=stats_view"><i class="fas fa-arrow-left"></i>Torna al menù statistiche</a>
<p>&nbsp;</p>
<h2>Classifica delle taglie/colori più venduti</h2>
<p>Questa pagina mostra le varianti più vendute, in assoluto o in uno specifico lasso di tempo, per uno specifico prodotto.</p>

<form action="#" method="GET">
    <input type="hidden" name="page" value="stats-views_most-sold-variants">
    <div class="row">
        <div class="col">
            <label for="product_id">Prodotto:</label>
            <select name="product_id" id="product_id-select" class="form-select"></select>
        </div>
    </div>
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
    <br>
    <button type="submit" class="btn btn-outline-primary">Visualizza</button>
</form>

<?php if(isset($_GET['product_id']) && !empty($_GET['product_id'])): ?>

<?php
$dbconnection = DBConnection::get_db_connection();

if(!isset($_GET['timeframe_type']) || $_GET['timeframe_type'] == 'absolute') {
    $sql = <<<EOD
       SELECT
  si.variant_id,
  pv.color,
  pv.size,
  SUM(si.quantity) AS total_sold
FROM sales_items si
JOIN sales s ON si.sale_id = s.sale_id
JOIN product_variants pv ON si.product_id = pv.product_id AND si.variant_id = pv.variant_id
WHERE si.product_id = :product_id
  AND s.status = 'completed'
GROUP BY si.variant_id, pv.color, pv.size
ORDER BY total_sold DESC;

EOD;

$stmt = $dbconnection->prepare($sql);
$stmt->execute([":product_id" => $_GET['product_id']]);
}

else {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'] ;
    $sql = <<<EOD
      SELECT
  si.variant_id,
  pv.color,
  pv.size,
  SUM(si.quantity) AS total_sold
FROM sales_items si
JOIN sales s ON si.sale_id = s.sale_id
JOIN product_variants pv ON si.product_id = pv.product_id AND si.variant_id = pv.variant_id
WHERE si.product_id = :product_id
  AND s.status = 'completed'
  AND s.closed_at BETWEEN :start_date AND :end_date
GROUP BY si.variant_id, pv.color, pv.size
ORDER BY total_sold DESC;
EOD;

    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([
        ':product_id' => $_GET['product_id'],
        ':start_date' => $start_date,
        ':end_date' => $end_date
    ]);
}


$variants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<p>&nbsp;</p>
<div class="table-responsive">
    <table id="top_products" class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>Pos.</th>
                <th>ID Variante</th>
                <th>Taglia</th>
                <th>Colore</th>
                <th>Quantità totale venduta</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $position = 1;
            foreach ($variants as $row): ?>
                <tr>
                    <?php
                    echo Utils::print_table_row($position++);
                    echo Utils::print_table_row("<span class='tt'>" . $row['variant_id'] . "</span>");
                    echo Utils::print_table_row($row['size']);
                    echo Utils::print_table_row($row['color']);
                    echo Utils::print_table_row($row['total_sold']);
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
$title = "Top 10 delle varianti più vendute per il prodotto " . $_GET['product_id'] ;
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
<?php endif;  ?>

<script>
    $(document).ready(() => {
        $("#product_id-select").select2({
            language: "it",
            theme: "bootstrap-5",
            allowClear: true,
            placeholder: "",
            ajax: {
                url: '/actions/products/list.php',
                dataType: 'json',
                processResults: (data) => {
                    return {
                        results: data.results.map((product) => {
                            console.log(product);
                            return {
                                id: product.product_id,
                                text: product.product_id + " - " +  product.name + " (" + product.brand_name + ")"
                            }
                        })
                    }
                },
            },
        })})
</script>