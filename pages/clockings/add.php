<?php
    Auth::require_owner();
?>

<h1>Aggiungi <i>manualmente</i> timbratura</h1>

<form action="actions/clockings/add_manual.php" method="post">
    <p><b>Utente:</b> <?php echo Auth::get_fullname_by_username($_GET['username'] ?? "") ?></p>
    <div class="row">
        <div class="col-6">
            <label for="datetime">Data e ora</label>
            <input type="datetime-local" name="datetime" class="form-control" required max="<?php echo date("Y-m-d\TH:i:s") ?>">
        </div>
        <div class="col-6">
            <label for="clocking_type">Tipo</label>
            <select name="clocking_type" class="form-select">
                <option value="in">Entrata</option>
                <option value="out">Uscita</option>
            </select>
        </div>
    </div>
    <input type="hidden" name="username" value="<?php echo htmlspecialchars($_GET['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <br>
    <button type="submit" class="btn btn-primary">Aggiungi</button>