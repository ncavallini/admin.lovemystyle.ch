<h1>Contenuto della cassa</h1>

<?php 
    $dbconnection = DBConnection::get_db_connection();
    $sql = "SELECT * FROM cash_content";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch();
?>

<p class="display-6">Contenuto attuale: <?php echo Utils::format_price($result['content']) ?> CHF</p>

<ul>
    <li><b>Ultimo aggiornamento:</b> <?php echo Utils::format_datetime($result["last_updated_at"]) ?> da <?php echo Auth::get_fullname_by_username($result['last_updated_by']) ?></li>
</ul>

<form action="actions/cash/edit.php" method="POST">
    <label for="content">Nuovo importo (CHF)</label>
    <input type="number" name="content" min="0" step="0.05" class="form-control" value="<?php echo Utils::format_price($result['content']) ?>" required>
    <br>
    <button type="submit" class="btn btn-primary">Aggiorna</button>
</form>
