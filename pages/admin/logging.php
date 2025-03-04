<?php


Auth::require_admin();

$date = $_GET['date'] ?? date('Y-m-d');
?>

<h1>Log di Sistema</h1>
<form action="#" method="GET">
    <input type="hidden" name="page" value="admin_logging">
    <label for="date">Data</label>
    <input class="form-control" type="date" name="date" value="<?= $date ?>" max="<?= date('Y-m-d') ?>">
    <br>
    <button class="btn btn-primary" type="submit">Cerca</button>
</form>
<hr>

<?php
$logs = Logging::get_logger()->get_logs($date);
$logs = array_reverse($logs);
?>
<?php if ($logs === []): ?>
    <div class="alert alert-success">Nessun log per la data selezionata.</div>
<?php else:
echo "<div class='accordion' id='accordion'>";
$i = 0;
    foreach ($logs as $log) {
        $badge = get_badge($log['level_name']);
        $short_message = substr($log['message'], 0, 50) . (strlen($log['message']) > 50 ? '...' : '');
        $message = $log['message'];
        $datetime = (new DateTime($log['datetime']))->format('d/m/Y, H:i:s');
        $context = $log['context'] ?? []; // Ensure context exists
        $values = array_values($context) + ['', '', '', '']; // Fallback default values
        list($correlationId, $file, $line, $trace) = $values;
        echo <<<EOD
            <div class="accordion-item" id="accordion-item-$i">
                <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-$i" href="#accordion-item-$i">
                    {$badge} &nbsp; &nbsp; <b>{$correlationId }</b> &nbsp; &mdash; {$short_message}
                </button>
                </h2>
                <div id="collapse-$i" class="accordion-collapse collapse" data-bs-parent="#accordion">
                <div class="accordion-body">
                    $message
                    <ul>
                    <li>File: $file</li>
                    <li>Linea: $line</li>
                    <li>Data e Ora: $datetime</li>
                    </ul>
                    <br>
                </div>
                </div>
            </div>
        EOD;
        $i++;
    }
?>

<?php endif ?>

<?php
    function get_badge(string $level) {
        switch($level) {
            case 'ERROR':
                return '<span class="badge rounded-pill text-bg-danger">ERROR</span>';
        
            case 'DEBUG':
                return '<span class="badge rounded-pill text-bg-warning">DEBUG</span>';

            case 'INFO':
                return '<span class="badge rounded-pill text-bg-primary">INFO</span>';

            default:
                return '<span class="badge rounded-pill text-bg-secondary">MISC</span>';
    }
}
?>