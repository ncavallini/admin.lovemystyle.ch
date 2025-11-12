<h1>Statistiche</h1>
<a href="index.php?page=stats_view"><i class="fas fa-arrow-left"></i> Torna al menù statistiche</a>
<p>&nbsp;</p>

<h2>Vendite per paese di residenza</h2>
<?php 
    // Tipo intervallo
    $timeframeType = isset($_GET['timeframe_type']) && $_GET['timeframe_type'] === 'timeframe' ? 'timeframe' : 'absolute';
    
    // Metrica di ordinamento
    $orderBy = isset($_GET['order_by']) && in_array($_GET['order_by'], ['customers', 'receipts', 'spend']) ? $_GET['order_by'] : 'spend';
?>

<p>Questa pagina mostra le vendite aggregate per paese di residenza dei clienti, con possibilità di suddivisione per cantone per la Svizzera.</p>

<form action="#" method="GET">
    <input type="hidden" name="page" value="stats-views_sales-by-country">

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
$db = DBConnection::get_db_connection();

/**
 * Calcolo importo riga
 */
$amountExpr = "COALESCE(si.total_price, si.price * si.quantity)";

/**
 * Query principale per aggregare per paese
 */
$selectByCountry = "
    SELECT
        COALESCE(c.country, 'Sconosciuto') AS country,
        COUNT(DISTINCT c.customer_id) AS customers_count,
        COUNT(DISTINCT s.sale_id) AS receipts_count,
        SUM($amountExpr) AS total_spent
    FROM sales s
    JOIN sales_items si ON si.sale_id = s.sale_id
    LEFT JOIN customers c ON c.customer_id = s.customer_id
    WHERE s.status = 'completed'
";

/** Timeframe */
$params = [];
if ($timeframeType === 'timeframe') {
    $start_date = $_GET['start_date'] ?? '';
    $end_date   = $_GET['end_date'] ?? '';
    if (!empty($start_date) && !empty($end_date)) {
        $selectByCountry .= " AND s.closed_at >= :start_date AND s.closed_at < DATE_ADD(:end_date, INTERVAL 1 DAY) ";
        $params[':start_date'] = $start_date . " 00:00:00";
        $params[':end_date']   = $end_date   . " 23:59:59";
    }
}

$selectByCountry .= " GROUP BY country ";

/** Order By dinamico */
switch ($orderBy) {
    case 'customers':
        $selectByCountry .= " ORDER BY customers_count DESC, total_spent DESC";
        break;
    case 'receipts':
        $selectByCountry .= " ORDER BY receipts_count DESC, total_spent DESC";
        break;
    default: // 'spend'
        $selectByCountry .= " ORDER BY total_spent DESC, receipts_count DESC";
        break;
}

$stmt = $db->prepare($selectByCountry);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->execute();
$countryRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

/** Titolo export */
$title = "Vendite per paese";
if ($timeframeType === 'timeframe' && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $title .= " dal " . Utils::format_date($_GET['start_date']) . " al " . Utils::format_date($_GET['end_date']);
}

/** Array per grafici */
$chartLabels = [];
$chartCustomers = [];
$chartReceipts = [];
$chartSpent = [];
$hasSwissData = false;

foreach ($countryRows as $row) {
    $chartLabels[] = $row['country'];
    $chartCustomers[] = (int)$row['customers_count'];
    $chartReceipts[] = (int)$row['receipts_count'];
    $chartSpent[] = (float)$row['total_spent'];
    
    if ($row['country'] === 'CH') {
        $hasSwissData = true;
    }
}
?>

<!-- Grafici -->
<p>&nbsp;</p>
<h3>Visualizzazione grafica</h3>
<div class="row">
    <div class="col-md-6">
        <canvas id="chartByCountry"></canvas>
    </div>
    <div class="col-md-6">
        <canvas id="chartSpendByCountry"></canvas>
    </div>
</div>

<!-- Tabella principale -->
<p>&nbsp;</p>
<h3>Dettaglio per paese</h3>
<div class="table-responsive">
    <table id="sales_by_country" class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>Paese</th>
                <th>N. clienti</th>
                <th>N. scontrini</th>
                <th>Spesa totale</th>
                <th>Spesa media/cliente</th>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach ($countryRows as $r):
            $country = htmlspecialchars($r['country']);
            $customers = (int)$r['customers_count'];
            $receipts = (int)$r['receipts_count'];
            $spent = (float)$r['total_spent'];
            $avgPerCustomer = $customers > 0 ? $spent / $customers : 0;
        ?>
            <tr>
                <?php
                    echo Utils::print_table_row(($country != "Sconosciuto" ? Country::iso2name($country) : "") . " ($country)");
                    echo Utils::print_table_row($customers);
                    echo Utils::print_table_row($receipts);
                    echo Utils::print_table_row(Utils::format_price($spent));
                    echo Utils::print_table_row(Utils::format_price($avgPerCustomer));
                ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
/**
 * SEZIONE SVIZZERA - Suddivisione per cantone
 */
if ($hasSwissData) {
    // Query per ottenere i dati svizzeri con città/CAP per determinare il cantone
    $selectSwiss = "
        SELECT
            s.customer_id,
            c.postcode,
            COUNT(DISTINCT s.sale_id) AS receipts_count,
            SUM($amountExpr) AS total_spent
        FROM sales s
        JOIN sales_items si ON si.sale_id = s.sale_id
        LEFT JOIN customers c ON c.customer_id = s.customer_id
        WHERE s.status = 'completed'
            AND c.country = 'CH'
    ";
    
    if ($timeframeType === 'timeframe' && !empty($params)) {
        $selectSwiss .= " AND s.closed_at >= :start_date AND s.closed_at < DATE_ADD(:end_date, INTERVAL 1 DAY) ";
    }
    
    $selectSwiss .= " GROUP BY s.customer_id ";
    
    $stmtSwiss = $db->prepare($selectSwiss);
    foreach ($params as $k => $v) {
        $stmtSwiss->bindValue($k, $v);
    }
    $stmtSwiss->execute();
    $swissCustomers = $stmtSwiss->fetchAll(PDO::FETCH_ASSOC);
    
    
    // Chiamata API per comune -> cantone
    function getCantonFromAPI($postcode) {
        // API openplzapi.org per determinare il cantone
        $url = "https://openplzapi.org/ch/Localities?postalCode=" . urlencode($postcode);
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            curl_close($ch);
            
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data[0]["canton"]["shortName"])) {
                    // Estrarre il cantone dalla risposta
                    return $data[0]["canton"]["shortName"];
                }
                //else var_dump($postcode);
            }
        } catch (Exception $e) {
            // In caso di errore, ritorna "Cantone sconosciuto"
            //var_dump($postcode);
        }
        
        return 'Cantone sconosciuto';
    }
    
    // Aggregazione per cantone
    $cantonData = [];
    foreach ($swissCustomers as $customer) {
        $canton = getCantonFromAPI($customer['postcode']);
        
        if (!isset($cantonData[$canton])) {
            $cantonData[$canton] = [
                'customers' => 0,
                'receipts' => 0,
                'spent' => 0
            ];
        }
        
        $cantonData[$canton]['customers']++;
        $cantonData[$canton]['receipts'] += (int)$customer['receipts_count'];
        $cantonData[$canton]['spent'] += (float)$customer['total_spent'];
    }
    
    // Ordinamento
    switch ($orderBy) {
        case 'customers':
            uasort($cantonData, function($a, $b) { return $b['customers'] - $a['customers']; });
            break;
        case 'receipts':
            uasort($cantonData, function($a, $b) { return $b['receipts'] - $a['receipts']; });
            break;
        default:
            uasort($cantonData, function($a, $b) { return $b['spent'] - $a['spent']; });
            break;
    }
    
    // Prepara dati per grafico cantoni
    $cantonLabels = array_keys($cantonData);
    $cantonCustomers = [];
    $cantonReceipts = [];
    $cantonSpent = [];
    
    foreach ($cantonData as $data) {
        $cantonCustomers[] = $data['customers'];
        $cantonReceipts[] = $data['receipts'];
        $cantonSpent[] = $data['spent'];
    }
    ?>
    
    <!-- Sezione Svizzera -->
    <p>&nbsp;</p>
    <hr>
    <h2>Dettaglio Svizzera - Suddivisione per cantone</h2>
    <p class="text-muted"><small><i class="fas fa-info-circle"></i> I cantoni sono determinati tramite openplzapi.org basata sul codice postale</small></p>
    
    <!-- Grafici cantoni -->
    <div class="row mt-4">
        <div class="col-md-6">
            <canvas id="chartByCanton"></canvas>
        </div>
        <div class="col-md-6">
            <canvas id="chartSpendByCanton"></canvas>
        </div>
    </div>
    
    <!-- Tabella cantoni -->
    <p>&nbsp;</p>
    <div class="table-responsive">
        <table id="sales_by_canton" class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>Cantone</th>
                    <th>N. clienti</th>
                    <th>N. scontrini</th>
                    <th>Spesa totale</th>
                    <th>Spesa media/cliente</th>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach ($cantonData as $canton => $data):
                $customers = $data['customers'];
                $receipts = $data['receipts'];
                $spent = $data['spent'];
                $avgPerCustomer = $customers > 0 ? $spent / $customers : 0;
            ?>
                <tr>
                    <?php
                        echo Utils::print_table_row(htmlspecialchars($canton));
                        echo Utils::print_table_row($customers);
                        echo Utils::print_table_row($receipts);
                        echo Utils::print_table_row(Utils::format_price($spent));
                        echo Utils::print_table_row(Utils::format_price($avgPerCustomer));
                    ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php
}
?>

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
let dtCountry = new DataTable("#sales_by_country", {
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

<?php if ($hasSwissData): ?>
let dtCanton = new DataTable("#sales_by_canton", {
    language: { url: '//cdn.datatables.net/plug-ins/2.3.0/i18n/it-IT.json' },
    pageLength: 30,
    lengthMenu: [10, 25, 30, 50],
    order: [[3, 'desc']],
    dom: 'Bfrtip',
    buttons: [
        {
            extend: 'excelHtml5',
            text: '<i class="fas fa-file-excel"></i>',
            title: 'Vendite Svizzera per cantone<?php echo ($timeframeType === "timeframe" && !empty($_GET["start_date"]) ? " dal " . Utils::format_date($_GET["start_date"]) . " al " . Utils::format_date($_GET["end_date"]) : ""); ?>',
        },
        {
            extend: 'print',
            text: '<i class="fas fa-print"></i>',
            title: 'Vendite Svizzera per cantone',
            customize: function (win) {
                $(win.document.body).css('font-size', '10pt');
                $(win.document.body).find('table')
                    .addClass('compact')
                    .css('font-size', 'inherit');
            }
        }
    ]
});
<?php endif; ?>
</script>

<!-- Chart.js per i grafici -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Grafico numero clienti/scontrini per paese
const ctxCountry = document.getElementById('chartByCountry');
new Chart(ctxCountry, {
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
                text: 'Clienti e scontrini per paese'
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

// Grafico spesa totale per paese
const ctxSpendCountry = document.getElementById('chartSpendByCountry');
new Chart(ctxSpendCountry, {
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
                text: 'Spesa totale per paese'
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

<?php if ($hasSwissData): ?>
// Grafico numero clienti/scontrini per cantone
const ctxCanton = document.getElementById('chartByCanton');
new Chart(ctxCanton, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($cantonLabels); ?>,
        datasets: [
            {
                label: 'N. clienti',
                data: <?php echo json_encode($cantonCustomers); ?>,
                backgroundColor: 'rgba(153, 102, 255, 0.8)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            },
            {
                label: 'N. scontrini',
                data: <?php echo json_encode($cantonReceipts); ?>,
                backgroundColor: 'rgba(255, 206, 86, 0.8)',
                borderColor: 'rgba(255, 206, 86, 1)',
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
                text: 'Clienti e scontrini per cantone (CH)'
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

// Grafico spesa totale per cantone
const ctxSpendCanton = document.getElementById('chartSpendByCanton');
new Chart(ctxSpendCanton, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($cantonLabels); ?>,
        datasets: [{
            label: 'Spesa totale (CHF)',
            data: <?php echo json_encode($cantonSpent); ?>,
            backgroundColor: 'rgba(255, 99, 132, 0.8)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            title: {
                display: true,
                text: 'Spesa totale per cantone (CH)'
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
<?php endif; ?>
</script>