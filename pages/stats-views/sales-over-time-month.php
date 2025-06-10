<h1>Statistiche</h1>
<a href="index.php?page=stats_view"><i class="fas fa-arrow-left"></i>Torna al menù statistiche</a>
<p>&nbsp;</p>
<h2>Vendite mese per mese</h2>
<p>Questa pagina mostra le vendite nel tempo. Puoi filtrare i dati per data e operatore.</p>

<p>&nbsp;</p>
<form action="#" method="GET">
    <input type="hidden" name="page" value="stats-views_sales-over-time-month">
    <div class="row">
        <div class="col">
            <label for="start_month">Dal mese</label>
            <select name="start_month" required class="form-select">
                <?php
                for ($i = 1; $i <= 12; $i++) {
                    $selected = (isset($_GET['start_month']) && $_GET['start_month'] == $i) ? 'selected' : '';
                    echo "<option value='$i' $selected>" . str_pad(strval($i), 2, "0", STR_PAD_LEFT) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="col">
            <label for="start_year">Anno:</label>
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
            <label for="end_date">Al mese:</label>
            <select name="end_month" required class="form-select">
                <?php
                for ($i = 1; $i <= 12; $i++) {
                    $selected = (isset($_GET['end_month']) && $_GET['end_month'] == $i) ? 'selected' : '';
                    echo "<option value='$i' $selected>" . str_pad(strval($i), 2, "0", STR_PAD_LEFT) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="col">
            <label for="end_year">Anno:</label>
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
WITH gross_totals AS (
  SELECT
    si.sale_id,
    SUM(si.price * si.quantity) AS gross_total
  FROM sales_items si
  GROUP BY si.sale_id
),
net_sales AS (
  SELECT
    s.sale_id,
    DATE_FORMAT(s.closed_at, '%Y-%m-01') AS month_start,
    gt.gross_total,
    CASE
      WHEN s.discount_type = 'CHF' THEN
        GREATEST(gt.gross_total - s.discount*100, 0)
      WHEN s.discount_type = '%' THEN
        GREATEST(gt.gross_total - (gt.gross_total * s.discount / 100), 0)
      ELSE
        gt.gross_total
    END AS net_total
  FROM sales s
  JOIN gross_totals gt ON s.sale_id = gt.sale_id
  WHERE s.status = 'completed'
    AND s.created_at >= DATE_FORMAT(:start_ym, '%Y-%m-01')
    AND s.created_at <  DATE_FORMAT(:end_ym, '%Y-%m-01') + INTERVAL 1 MONTH
    %op
)
SELECT
  month_start,
  COUNT(*) AS sales_count,
  SUM(net_total) AS total_value
FROM net_sales
GROUP BY month_start
ORDER BY month_start;


EOD;

    $start_date = $_GET['start_year'] . '-' . str_pad($_GET['start_month'], 2, '0', STR_PAD_LEFT) . '-01';
    $end_date = $_GET['end_year'] . '-' . str_pad($_GET['end_month'], 2, '0', STR_PAD_LEFT) . '-01';

    $sql = str_replace('%op', $operator ? 'AND operator = :username' : '', $sql);
    $sql .= <<<EOD
EOD;

    $dbconnection = DBConnection::get_db_connection();
    $stmt = $dbconnection->prepare($sql);
    $params = [
        'start_ym' => $start_date,
        'end_ym' => $end_date,
    ];

    if ($operator) {
        $params['username'] = $operator;
    }
    $stmt->execute($params);
    $month_by_month = $stmt->fetchAll();

    ?>

    <div class="table-responsive">
        <table id="month_by_month" class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>Mese/anno</th>
                    <th>Vendite (#)</th>
                    <th>Incasso (CHF)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($month_by_month as $row): ?>
                    <?php if(!$row['month_start']) continue; ?>
                    <tr>
                        <td><?php echo date("m/Y", strtotime($row['month_start'])) ?></td>
                        <td><?php echo $row['sales_count']; ?></td>
                        <td><?php echo Utils::format_price($row['total_value']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            </tr>
            </thead>
        </table>
    </div>
    <br>
    <canvas id="month_by_month_plot"></canvas>
    <script>
        let month_by_month_plot = new DataTable("#month_by_month", {
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
                    title: 'Vendite mese per mese <?php echo date("m_Y", strtotime($start_date)) ?> - <?php echo date("m_Y", timestamp: strtotime($end_date)) ?>',
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i>',
                    title: 'Vendite mese per mese <?php echo date("m/Y", strtotime($start_date)) ?> - <?php echo date("m/Y", timestamp: strtotime($end_date)) ?>',
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
            const ctx = document.getElementById('month_by_month_plot').getContext('2d');

            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_map(fn($row) => date("m/Y", strtotime($row['month_start'])), $month_by_month)) ?>,
                    datasets: [{
                        label: 'Incasso mensile (CHF)',
                        data: <?php echo json_encode(array_map(fn($row) => $row['total_value'] / 100, $month_by_month)) ?>,
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