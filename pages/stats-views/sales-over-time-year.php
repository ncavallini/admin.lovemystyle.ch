<h1>Statistiche</h1>
<a href="index.php?page=stats_view"><i class="fas fa-arrow-left"></i>Torna al menù statistiche</a>
<p>&nbsp;</p>
<h2>Vendite anno per anno</h2>
<p>Questa pagina mostra le vendite nel tempo. Puoi filtrare i dati per data e operatore.</p>

<p>&nbsp;</p>
<form action="#" method="GET">
    <input type="hidden" name="page" value="stats-views_sales-over-time-year">
    <div class="row">
        <div class="col">
            <label for="start_year">Dall'anno:</label>
            <select name="start_year" class="form-select" required>
                <?php
                $current_year = date('Y');
                for ($i = $current_year; $i >= 2025; $i--) {
                    $selected = (isset($_GET['start_year']) && $_GET['start_year'] == $i) ? 'selected' : '';
                    echo "<option value='$i' $selected>$i</option>";
                }
                ?>
            </select>
        </div>
        <div class="col">
            <label for="end_year">All'anno:</label>
            <select name="end_year" class="form-select" required>
                <?php
                $current_year = date('Y');
                for ($i = $current_year; $i >= 2025; $i--) {
                    $selected = (isset($_GET['end_year']) && $_GET['end_year'] == $i) ? 'selected' : '';
                    echo "<option value='$i' $selected>$i</option>";
                }
                ?>
            </select>
        </div>
        <div class="col">
            <label for="operator">Operatore:</label>
            <select id="operator" name="operator" class="form-select">
                <option value="">Tutti</option>
                <?php
                $sql = "SELECT username, first_name, last_name FROM users ORDER BY last_name ASC";
                $dbconnection = DBConnection::get_db_connection();
                $stmt = $dbconnection->prepare($sql);
                $stmt->execute();
                $operators = $stmt->fetchAll();
                foreach ($operators as $operator) {
                    $selected = (isset($_GET['operator']) && $_GET['operator'] == $operator['username']) ? 'selected' : '';
                    $full_name = $operator['first_name'] . ' ' . $operator['last_name'];
                    echo "<option $selected value='{$operator['username']}'>{$full_name} ({$operator['username']})</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <br>
    <button type="submit" class="btn btn-outline-primary">Visualizza</button>
</form>

<p>&nbsp;</p>


<?php if (isset($_GET['start_year']) && isset($_GET['end_year'])): ?>

    <h3>Risultati</h3>
    <?php
    $operator = $_GET['operator'] ?? null;
    $sql = <<<EOD
SELECT
  DATE_FORMAT(closed_at, '%Y') AS year,
  COUNT(*) AS cnt
FROM sales
WHERE status = 'completed'
  AND created_at >= DATE_FORMAT(:start_year, '%Y-01-01')
  AND created_at <  DATE_FORMAT(:end_year, '%Y-01-01') + INTERVAL 1 YEAR
  %op
GROUP BY year
ORDER BY year;
EOD;


    $sql = str_replace('%op', $operator ? 'AND operator = :username' : '', $sql);
    $start_year = $_GET['start_year'];
    $end_year = $_GET['end_year'];

    $dbconnection = DBConnection::get_db_connection();
    $stmt = $dbconnection->prepare($sql);
    $params = [
        'start_year' => $_GET['start_year'] . "-01-01",
        'end_year' => $_GET['end_year'] . "-01-01",
    ];

    if ($operator) {
        $params['username'] = $operator;
    }
    $stmt->execute($params);
    $year_by_year = $stmt->fetchAll();

    ?>

    <div class="table-responsive">
        <table id="year_by_year" class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>Anno</th>
                    <th>Vendite</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($year_by_year as $row): ?>
                    <tr>
                        <?php if(!$row['year']) continue; ?>
                        <td><?php echo date("Y", strtotime($row['year'])) ?></td>
                        <td><?php echo $row['cnt']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            </tr>
            </thead>
        </table>
    </div>
    <br>
    <canvas id="year_by_year_plot"></canvas>
    <script>
        let year_by_year = new DataTable("#year_by_year", {
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
                    title: 'Vendite <?php echo $start_year ?> - <?php echo $end_year ?>', // Excel file name (optional)
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i>',
                    title: 'Vendite <?php echo $start_year ?> - <?php echo $end_year ?>', // Excel file name (optional)
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
            const ctx = document.getElementById('year_by_year_plot').getContext('2d');

            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_map(fn($row) => ($row['year']) ? date("Y", strtotime($row['year'])) : "", $year_by_year)) ?>,
                    datasets: [{
                        label: 'Totale anniuale',
                        data: <?php echo json_encode(array_map(fn($row) => $row['cnt'], $year_by_year)) ?>,
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