<h1>Statistiche</h1>
<a href="index.php?page=stats_view"><i class="fas fa-arrow-left"></i>Torna al menù statistiche</a>
<p>&nbsp;</p>
<h2>Vendite per fascia oraria</h2>
<p>Questa pagina mostra le vendite per fascia oraria.</p>


<p>&nbsp;</p>
<form action="#" method="GET">
    <input type="hidden" name="page" value="stats-views_time-intervals">
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

<p>&nbsp;</p>



<?php if (isset($_GET['timeframe_type'])): ?>

    <h3>Risultati</h3>
    <?php
    $timeframe_type = $_GET['timeframe_type'] ?? 'absolute';
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $dbconnection = DBConnection::get_db_connection();
    if ($timeframe_type == 'absolute') {
        $sql = <<<EOD
            SELECT 
  CONCAT(
    LPAD(HOUR(closed_at), 2, '0'), ':',
    LPAD(FLOOR(MINUTE(closed_at) / 30) * 30, 2, '0')
  ) AS interval_start,
  CONCAT(
    LPAD(HOUR(closed_at + INTERVAL 30 MINUTE), 2, '0'), ':',
    LPAD(FLOOR(MINUTE(closed_at + INTERVAL 30 MINUTE) / 30) * 30, 2, '0')
  ) AS interval_end,
  COUNT(*) AS sale_count
FROM sales
WHERE status = 'completed'
  AND closed_at IS NOT NULL
GROUP BY interval_start, interval_end
ORDER BY interval_start;
EOD;
        $stmt = $dbconnection->prepare($sql);
        $stmt->execute();
    } else {

        $sql = <<<EOD
     SELECT 
  TIME_FORMAT(DATE_FORMAT(closed_at, '%H:%i'), '%H:%i') AS sale_time,
  CONCAT(
    LPAD(HOUR(closed_at), 2, '0'), ':',
    LPAD(FLOOR(MINUTE(closed_at) / 30) * 30, 2, '0')
  ) AS interval_start,
  CONCAT(
    LPAD(HOUR(closed_at + INTERVAL 30 MINUTE), 2, '0'), ':',
    LPAD(FLOOR(MINUTE(closed_at + INTERVAL 30 MINUTE) / 30) * 30, 2, '0')
  ) AS interval_end,
  COUNT(*) AS sale_count
FROM sales
WHERE status = 'completed'
  AND closed_at BETWEEN :start_date AND :end_date
GROUP BY interval_start, interval_end
ORDER BY interval_start;

EOD;
        $stmt = $dbconnection->prepare($sql);
        $stmt->execute([
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ]);
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="table-responsive">
        <table id="time_table" class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>Fascia oraria</th>
                    <th>Vendite</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?php echo $row["interval_start"] . " - " . $row['interval_end'] ?></td>
                        <td><?php echo $row['sale_count']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <br>
    <canvas id="time_plot"></canvas>


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

    <script>
        let day_by_day_dt = new DataTable("#time_table", {
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
                    title: 'Vendite per fascia oraria <?php if(isset($start_date) && isset($end_date)) echo date("d_m_Y", strtotime($start_date)) ?> al <?php echo date("d_m_Y", strtotime($end_date)) ?>', // Excel file name (optional)
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i>',
                    title: 'Vendite per fascia oraria <?php if(isset($start_date) && isset($end_date)) echo date("d_m_Y", strtotime($start_date)) ?> al <?php echo date("d_m_Y", strtotime($end_date)) ?>', // Excel file name (optional)
                    customize: function(win) {
                        $(win.document.body).css('font-size', '10pt');
                        $(win.document.body).find('table')
                            .addClass('compact')
                            .css('font-size', 'inherit');
                    }
                }
            ]
        });

        $(document).ready(function() {
            const ctx = document.getElementById('time_plot').getContext('2d');

            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_map(fn($row) => $row['interval_start'] . "-" . $row['interval_end'], $data)) ?>,
                    datasets: [{
                        label: 'Vendite per fascia oraria',
                        data: <?php echo json_encode(array_map(fn($row) => $row['sale_count'], $data)) ?>,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
<?php endif; ?>