<?php
Auth::require_owner();
?>
<h1>Gestione timbrature</h1>

<?php
$username = $_GET['username'] ?? "";
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');
$dbconnection = DBConnection::get_db_connection();

$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$username]);
$user = $stmt->fetch();
if (!$user) {
    Utils::print_error("Utnete non trovato");
    return;
}
?>

<p><b>Utente:</b> <?php echo Auth::get_fullname_by_username($username) ?> </p>

<form action="#" method="get">
    <div class="row d-flex align-items-end">
        <div class="col-4">
            <label for="month">Mese</label>
            <select name="month" class="form-select">
                <?php
                for ($m = 1; $m <= 12; $m++) {
                    $selected = ($m == intval($month)) ? "selected" : "";
                    echo "<option value='$m' $selected>" . str_pad($m, 2, '0', STR_PAD_LEFT) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-4">
            <label for="year">Anno</label>
            <select name="year" class="form-select">
                <?php
                for ($y = 2025; $y <= intval(date("Y")); $y++) {
                    $selected = ($y == intval($year)) ? "selected" : "";
                    echo "<option value='$y' $selected>" . $y . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-4 d-flex">
            <button type="submit" class="btn btn-primary w-100">Aggiorna</button>
        </div>
    </div>
    <input type="hidden" name="page" value="<?php echo $_GET['page'] ?>">
    <input type="hidden" name="username" value="<?php echo $username ?>">
</form>


<?php
$sql = "SELECT * FROM clockings WHERE username = :username AND YEAR(datetime) = :year AND MONTH(datetime) = :month ORDER BY datetime ASC";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([
    ":username" => $username,
    ":year" => $year,
    ":month" => $month
]);

$clockings = $stmt->fetchAll();
?>
<p>&nbsp;</p>
<a href="/index.php?page=clockings_add&username=<?php echo $username ?>" class="btn btn-primary"><i class="fa-solid fa-plus"></i></a>
<br>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Data</th>
                <th>Ora</th>
                <th>Entrata/Uscita</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clockings as $clocking): ?>
                <?php $trClass = $clocking['type'] === "in" ? "table-success" : "table-danger"; ?>
                <tr class="<?= $trClass ?>">
                    <?php
                    Utils::print_table_row(Utils::format_date($clocking['datetime']));
                    Utils::print_table_row((new DateTimeImmutable($clocking['datetime']))->format('H:i:s'));
                    Utils::print_table_row($clocking['type'] === "in" ? "Entrata" : "Uscita");
                    Utils::print_table_row(
                        <<<EOD
                <a href="index.php?page=clockings_edit&clocking_id={$clocking['clocking_id']}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-pencil"></i>
                </a>
                <button onclick="confirmDelete('{$clocking['clocking_id']}')" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-trash"></i>
                </button>
                EOD
                    );
                    ?>
                </tr>
            <?php endforeach; ?>

        </tbody>
    </table>
</div>

<br>

<?php
$sum = 0;

$workedToday = 0;
for ($i = 0; $i < count($clockings); $i += 2) {
    $in = new DateTimeImmutable($clockings[$i]["datetime"]);
    if ($i + 1 >= count($clockings)) {
        break;
    }
    $out = new DateTimeImmutable($clockings[$i + 1]["datetime"]);
    $sum += $out->getTimestamp() - $in->getTimestamp();
}
$isWorking = count($clockings) % 2 == 1;
?>


<p class="lead">Totale mese: <?php echo gmdate("H:i:s", $sum); ?> <?php if ($isWorking): ?>
        <span class="badge bg-success">In corso</span>
    <?php endif; ?>
</p>

<br>

<button <?php echo $isWorking ? "disabled" : "" ?> onclick="printReport()" class="btn btn-secondary">Stampa foglio ore</button>
<p>&nbsp;</p>
<?php 
if ($isWorking) {
    echo "<div class='alert alert-warning'>Attenzione: impossibile produrre il foglio ore mentre l'utente è in servizio.</div>";
}
?>

<script>
    function confirmDelete(clocking_id) {
        bootbox.confirm("<div class='alert alert-danger'>Eliminare questa timbratura?</div>", function(res) {
            if (res) {
                window.location.href = `/actions/clockings/delete.php?clocking_id=${clocking_id}`;
            }
        });
    }

    function printReport() {
        bootbox.prompt({
            title: "Stampa foglio ore",
            message: '<p>Scegli il tipo di foglio ore</p>',
            inputType: 'radio',
            inputOptions: [{
                    text: 'Rendiconto semplice',
                    value: 'simple'
                },
                {
                    text: 'Rendiconto dettagliato',
                    value: 'details'
                }
            ],
            callback: function(result) {
                if (!result) {
                    return;
                }
                window.location.href = `/actions/clockings/print_report.php?username=<?php echo $username ?>&year=<?php echo $year ?>&month=<?php echo $month ?>&type=${result}`;
            }
        });
    }
</script>