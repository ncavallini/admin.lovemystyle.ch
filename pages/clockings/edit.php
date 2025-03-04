<?php
Auth::require_owner();
?>
<h1>Modifica timbratura</h1>
<?php
$dbconnection = DBConnection::get_db_connection();
$clocking_id = $_GET['clocking_id'] ?? "";
$sql = "SELECT * FROM clockings WHERE clocking_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$clocking_id]);
$clocking = $stmt->fetch();

if (!$clocking) {
    Utils::print_error("Timbratura non trovata");
    return;
}


?>

<form action="actions/clockings/edit.php" method="post">
    <input type="hidden" name="clocking_id" value="<?php echo $clocking_id; ?>">
    <div class="row">
        <div class="col-6">
            <label for="datetime">Data e ora</label>
            <input type="datetime-local" name="datetime" class="form-control" required value="<?php echo $clocking["datetime"] ?>" max="<?php echo date("Y-m-d\TH:i:s") ?>">
        </div>
        <div class="col-6">
            <label for="clocking_type">Tipo</label>
            <select name="clocking_type" class="form-select">
                <option value="in" <?php echo $clocking['type'] == 'in' ? 'selected' : ''; ?>>Entrata</option>
                <option value="out" <?php echo $clocking['type'] == 'out' ? 'selected' : ''; ?>>Uscita</option>
            </select>
        </div>
    </div>
    <br>
    <button type="submit" class="btn btn-primary">Salva</button>