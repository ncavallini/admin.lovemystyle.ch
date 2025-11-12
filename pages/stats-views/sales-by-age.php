<h1>Statistiche</h1>
<a href="index.php?page=stats_view"><i class="fas fa-arrow-left"></i> Torna al menù statistiche</a>
<p>&nbsp;</p>

<h2>Vendite per fascia d'età</h2>
<?php 
    // Tipo intervallo
    $timeframeType = isset($_GET['timeframe_type']) && $_GET['timeframe_type'] === 'timeframe' ? 'timeframe' : 'absolute';
    
    // Metrica di ordinamento
    $orderBy = isset($_GET['order_by']) && in_array($_GET['order_by'], ['customers', 'receipts', 'spend']) ? $_GET['order_by'] : 'spend';
?>

<p>Questa pagina mostra le vendite aggregate per fascia d'età dei clienti.</p>

<form action="#" method="GET">
    <input type="hidden" name="page" value="stats-views_sales-by-age">

    <div class="row">
        <div class="col">
            <label class="form-label d-block">Ordina per</label>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" name="order_by" id="order-spend" value="spend" <?php echo $orderBy==='spend' ? 'checked' : '';?>>
                <label for="order-spend" class="form-check-label">Spesa totale</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" name="order_by" id="order-receipts" value="receipts" <?php echo $orderBy==='receipts' ? 'checked' : '';?>>
                <label for="order-receipts" class="form-check-label">N. scontrini</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio" class="form-check-input" name="order_by" id="order-customers" value="customers" <?php echo $orderBy==='customers' ? 'checked' : '';?>>
                <label for="order-customers" class="form-check-label">N. clienti</label>
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

    <br>
    <button type="submit" class="btn btn-outline-primary">Visualizza</button>
</form>

<?php
/**
 * Funzione per calcolare l'età
 */
function calculateAge($birthDate) {
    if (empty($birthDate)) {
        return null;
    }
    
    try {
        $birth = new DateTime($birthDate);
        $today = new DateTime('today');
        return $birth->diff($today)->y;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Funzione per determinare la fascia d'età
 */
function getAgeGroup($age) {
    if ($age === null) {
        return 'Età sconosciuta';
    }
    
    if ($age < 18) return '< 18 anni';
    if ($age < 25) return '18-24 anni';
    if ($age < 35) return '25-34 anni';
    if ($age < 45) return '35-44 anni';
    if ($age < 55) return '45-54 anni';
    if ($age < 65) return '55-64 anni';
    if ($age < 75) return '65-74 anni';
    if ($age < 85) return '75-84 anni';
    if ($age < 95) return '85-94 anni';
    if ($age <= 100) return '95-100 anni';
    return '100+ anni';
}

/**
 * Ordine delle fasce d'età per il display
 */
function getAgeGroupOrder($ageGroup) {
    $order = [
        '< 18 anni' => 1,
        '18-24 anni' => 2,
        '25-34 anni' => 3,
        '35-44 anni' => 4,
        '45-54 anni' => 5,
        '55-64 anni' => 6,
        '65-74 anni' => 7,
        '75-84 anni' => 8,
        '85-94 anni' => 9,
        '95-100 anni' => 10,
        '100+ anni' => 11,
        'Età sconosciuta' => 12
    ];
    
    return $order[$ageGroup] ?? 99;
}

$db = DBConnection::get_db_connection();

/**
 * Calcolo importo riga
 */
$amountExpr = "COALESCE(si.total_price, si.price * si.quantity)";

/**
 * Query per ottenere i dati dei clienti con le vendite
 */
$selectCustomers = "
    SELECT
        s.customer_id,
        c.birth_date,
        COUNT(DISTINCT s.sale_id) AS receipts_count,
        SUM($amountExpr) AS total_spent
    FROM sales s
    JOIN sales_items si ON si.sale_id = s.sale_id
    LEFT JOIN customers c ON c.customer_id = s.customer_id
    WHERE s.status = 'completed'
        AND s.customer_id IS NOT NULL
";

/** Timeframe */
$params = [];
if ($timeframeType === 'timeframe') {
    $start_date = $_GET['start_date'] ?? '';
    $end_date   = $_GET['end_date'] ?? '';
    if (!empty($start_date) && !empty($end_date)) {
        $selectCustomers .= " AND s.closed_at >= :start_date AND s.closed_at < DATE_ADD(:end_date, INTERVAL 1 DAY) ";
        $params[':start_date'] = $start_date . " 00:00:00";
        $params[':end_date']   = $end_date   . " 23:59:59";
    }
}

$selectCustomers .= " GROUP BY s.customer_id ";

$stmt = $db->prepare($selectCustomers);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->execute();
$customerRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

/**
 * Aggregazione per fascia d'età
 */
$ageGroupData = [];
$processedCustomers = []; // Per evitare duplicati

foreach ($customerRows as $customer) {
    // Skip se già processato
    if (isset($processedCustomers[$customer['customer_id']])) {
        continue;
    }
    $processedCustomers[$customer['customer_id']] = true;
    
    $age = calculateAge($customer['birth_date']);
    
    // Validazione età: escludi valori impossibili
    if ($age !== null && ($age < 0 || $age > 120)) {
        $age = null; // Tratta come età sconosciuta
    }
    
    $ageGroup = getAgeGroup($age);
    
    if (!isset($ageGroupData[$ageGroup])) {
        $ageGroupData[$ageGroup] = [
            'customers' => 0,
            'receipts' => 0,
            'spent' => 0
        ];
    }
    
    $ageGroupData[$ageGroup]['customers']++;
    $ageGroupData[$ageGroup]['receipts'] += (int)$customer['receipts_count'];
    $ageGroupData[$ageGroup]['spent'] += (float)$customer['total_spent'];
}

/**
 * Ordinamento per fascia d'età (naturale)
 */
uksort($ageGroupData, function($a, $b) {
    return getAgeGroupOrder($a) - getAgeGroupOrder($b);
});

/** Titolo export */
$title = "Vendite per fascia d'età";
if ($timeframeType === 'timeframe' && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $title .= " dal " . Utils::format_date($_GET['start_date']) . " al " . Utils::format_date($_GET['end_date']);
}

/** Array per grafici */
$chartLabels = [];
$chartCustomers = [];
$chartReceipts = [];
$chartSpent = [];

// Riordina per la metrica selezionata (per la tabella, non il grafico)
$displayData = $ageGroupData;
switch ($orderBy) {
    case 'customers':
        uasort($displayData, function($a, $b) { return $b['customers'] - $a['customers']; });
        break;
    case 'receipts':
        uasort($displayData, function($a, $b) { return $b['receipts'] - $a['receipts']; });
        break;
    case 'spend':
        uasort($displayData, function($a, $b) { return $b['spent'] - $a['spent']; });
        break;
}

// Dati per grafico (ordine naturale delle fasce)
foreach ($ageGroupData as $ageGroup => $data) {
    $chartLabels[] = $ageGroup;
    $chartCustomers[] = $data['customers'];
    $chartReceipts[] = $data['receipts'];
    $chartSpent[] = $data['spent'];
}
?>

<!-- Grafici -->
<p>&nbsp;</p>
<h3>Visualizzazione grafica</h3>
<div class="row">
    <div class="col-md-6">
        <canvas id="chartByAge"></canvas>
    </div>
    <div class="col-md-6">
        <canvas id="chartSpendByAge"></canvas>
    </div>
</div>

<!-- Tabella principale -->
<p>&nbsp;</p>
<h3>Dettaglio per fascia d'età</h3>
<div class="table-responsive">
    <table id="sales_by_age" class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>Fascia d'età</th>
                <th>N. clienti</th>
                <th>N. scontrini</th>
                <th>Spesa totale</th>
                <th>Spesa media/cliente</th>
                <th>Scontrini medi/cliente</th>
                <th>Spesa media/scontrino</th>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach ($displayData as $ageGroup => $data):
            $customers = $data['customers'];
            $receipts = $data['receipts'];
            $spent = $data['spent'];
            $avgPerCustomer = $customers > 0 ? $spent / $customers : 0;
            $avgReceiptsPerCustomer = $customers > 0 ? $receipts / $customers : 0;
            $avgPerReceipt = $receipts > 0 ? $spent / $receipts : 0;
        ?>
            <tr>
                <?php
                    echo Utils::print_table_row(htmlspecialchars($ageGroup));
                    echo Utils::print_table_row($customers);
                    echo Utils::print_table_row($receipts);
                    echo Utils::print_table_row(Utils::format_price($spent));
                    echo Utils::print_table_row(Utils::format_price($avgPerCustomer));
                    echo Utils::print_table_row(number_format($avgReceiptsPerCustomer, 1));
                    echo Utils::print_table_row(Utils::format_price($avgPerReceipt));
                ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Summary box -->
<p>&nbsp;</p>
<div class="row">
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h5 class="card-title">Totale clienti</h5>
                <p class="card-text display-6"><?php echo array_sum(array_column($ageGroupData, 'customers')); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h5 class="card-title">Totale scontrini</h5>
                <p class="card-text display-6"><?php echo array_sum(array_column($ageGroupData, 'receipts')); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h5 class="card-title">Spesa totale</h5>
                <p class="card-text display-6"><?php echo Utils::format_price(array_sum(array_column($ageGroupData, 'spent'))); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h5 class="card-title">Età media clienti</h5>
                <p class="card-text display-6">
                    <?php
                    // Calcolo età media - customerRows già contiene clienti unici dopo GROUP BY
                    $totalAge = 0;
                    $countWithAge = 0;
                    
                    foreach ($customerRows as $customer) {
                        $age = calculateAge($customer['birth_date']);
                        
                        // Validazione: solo età valide (0-120 anni)
                        if ($age !== null && $age >= 0 && $age <= 120) {
                            $totalAge += $age;
                            $countWithAge++;
                        }
                    }
                    
                    $avgAge = $countWithAge > 0 ? round($totalAge / $countWithAge) : 0;
                    echo $avgAge > 0 ? $avgAge . ' anni' : 'N/D';
                    ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Script per gestire il form -->
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

<!-- DataTables -->
<script>
let dtAge = new DataTable("#sales_by_age", {
    language: { url: '//cdn.datatables.net/plug-ins/2.3.0/i18n/it-IT.json' },
    pageLength: 30,
    lengthMenu: [10, 25, 30, 50, 100],
    order: [[3, 'desc']], // Ordina per spesa totale di default
    dom: 'Bfrtip',
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

<!-- Chart.js per i grafici -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Grafico numero clienti/scontrini per fascia d'età
const ctxAge = document.getElementById('chartByAge');
new Chart(ctxAge, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [
            {
                label: 'N. clienti',
                data: <?php echo json_encode($chartCustomers); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            },
            {
                label: 'N. scontrini',
                data: <?php echo json_encode($chartReceipts); ?>,
                backgroundColor: 'rgba(255, 159, 64, 0.8)',
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            title: {
                display: true,
                text: 'Clienti e scontrini per fascia d\'età'
            },
            legend: {
                display: true,
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Grafico spesa totale per fascia d'età
const ctxSpendAge = document.getElementById('chartSpendByAge');
new Chart(ctxSpendAge, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [{
            label: 'Spesa totale (CHF)',
            data: <?php echo json_encode($chartSpent); ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.8)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            title: {
                display: true,
                text: 'Spesa totale per fascia d\'età'
            },
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'CHF ' + value.toLocaleString('it-CH');
                    }
                }
            }
        }
    }
});
</script>