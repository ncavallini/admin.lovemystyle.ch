<h1>Statistiche</h1>
<a href="index.php?page=stats_view"><i class="fas fa-arrow-left"></i>Torna al menù statistiche</a>
<p>&nbsp;</p>
<h2>Vendite giorno per giorno</h2>
<p>Questa pagina mostra le vendite nel tempo. Puoi filtrare i dati per data e operatore.</p>

<div>
    <a href="javascript:void(0)" onclick="setPeriod('D')">Oggi</a> | <a href="javascript:void(0)" onclick="setPeriod('W')">Settimanale</a> | <a href="javascript:void(0)" onclick="setPeriod('M')">Mensile</a> | <a href="javascript:void(0)" onclick="setPeriod('Y')">Annuale</a>
</div>
<p>&nbsp;</p>
<form action="#" method="GET">
    <input type="hidden" name="page" value="stats-views_sales-over-time-day">
    <div class="row">
        <div class="col-4">
            <label for="start_date">Data inizio:</label>
            <input type="date" name="start_date" id="start_date-input" required class="form-control" value="<?php echo $_GET['start_date'] ?? '' ?>" max="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="col-4">
            <label for="end_date">Data fine:</label>
            <input type="date" name="end_date" id="end_date-input" required class="form-control" value="<?php echo $_GET['end_date'] ?? '' ?>" max="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="col-4">
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

<script>
    function setPeriod(period) {
        const startDateInput = document.getElementById('start_date-input');
        const endDateInput = document.getElementById('end_date-input');
        const today = new Date().toISOString().split('T')[0];

        if (period === "D") {
            startDateInput.value = today;
        } else if (period === "W") {
            const startOfWeek = new Date();
            startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay());
            startDateInput.value = startOfWeek.toISOString().split('T')[0];
        } else if (period === "M") {
            const firstDayOfMonth = new Date();
            firstDayOfMonth.setDate(1);
            startDateInput.value = firstDayOfMonth.toISOString().split('T')[0];
        } else if (period === "Y") {
            const firstDayOfYear = new Date();
            firstDayOfYear.setMonth(0, 1);
            startDateInput.value = firstDayOfYear.toISOString().split('T')[0];
        } else {
            console.error("Invalid period", period);
        }
        endDateInput.value = today;
    }
</script>

<?php if (isset($_GET['start_date']) && isset($_GET['end_date'])): ?>

    <h3>Risultati</h3>
    <?php
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $operator = $_GET['operator'] ?? null;
    $sql = <<<EOD
            WITH RECURSIVE
            params AS (
            SELECT
                :start_date AS start_date,
                :end_date AS end_date
            ),
            all_days AS (
            SELECT start_date AS day FROM params
            UNION ALL
            SELECT day + INTERVAL 1 DAY
            FROM all_days
            JOIN params ON day < end_date
            ),
            daily_counts AS (
            SELECT
                DATE(closed_at) AS day,
                COUNT(*) AS cnt
            FROM sales
            WHERE closed_at BETWEEN (SELECT start_date FROM params)
                                            AND (SELECT end_date   FROM params) + INTERVAL 1 DAY
                                
EOD;

    $sql .= $operator ? " AND username = :username" : '';

    $sql .= <<<EOD
            GROUP BY DATE(closed_at)
            )
            SELECT
            d.day,
            COALESCE(dc.cnt, 0) AS cnt
            FROM all_days d
            LEFT JOIN daily_counts dc ON dc.day = d.day
            ORDER BY d.day;
EOD;

    $dbconnection = DBConnection::get_db_connection();
    $stmt = $dbconnection->prepare($sql);
    if ($operator) {
        $stmt->execute([
            ':start_date' => $start_date,
            ':end_date' => $end_date,
            ':username' => $operator
        ]);
    } else {
        $stmt->execute([
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ]);
    }

    $day_by_day = $stmt->fetchAll();

    ?>

    <div class="table-responsive">
        <table id="day_by_day" class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Vendite</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($day_by_day as $row): ?>
                    <tr>
                        <td><?php echo Utils::format_date($row['day']) ?></td>
                        <td><?php echo $row['cnt']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            </tr>
            </thead>
        </table>
    </div>
    <br>
    <canvas id="day_by_day_plot"></canvas>
    <script>
        let day_by_day_dt = new DataTable("#day_by_day", {
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
                    title: 'Vendite dal <?php echo date("d_m_Y", strtotime($start_date)) ?> al <?php echo date("d_m_Y", strtotime($end_date)) ?>', // Excel file name (optional)
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i>',
                    title: 'Vendite dal <?php echo Utils::format_date($start_date) ?> al <?php echo Utils::format_date($end_date) ?>',
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
            const ctx = document.getElementById('day_by_day_plot').getContext('2d');

            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_map(fn($row) => $row['day'], $day_by_day)) ?>,
                    datasets: [{
                        label: 'Totale giornaliero',
                        data: <?php echo json_encode(array_map(fn($row) => $row['cnt'], $day_by_day)) ?>,
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