<h1>Modifica Utente</h1>

<?php
$dbconnection = DBConnection::get_db_connection();
$username = $_GET['username'] ?? null;
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$username]);
$user = $stmt->fetch();
if (!$user) {
    Utils::print_error("Utente non trovato");
    goto end;
}
?>

<ul>
    <li><b>Nome:</b> <?php echo $user['first_name'] ?></li>
    <li><b>Cognome:</b> <?php echo $user['last_name'] ?></li>
    <li><b>Nome utente:</b> <span class="tt"><?php echo $user['username'] ?></span></li>

</ul>


<form action="actions/users/edit.php" method="POST">
    <label for="tel">Telefono</label>
    <input type="text" name="tel" required class="form-control" value="<?php echo $user['tel'] ?>" pattern="<?php echo Utils::get_phone_regex() ?>">
    <br>
    <label for="email">E-mail</label>
    <input type="email" name="email" required class="form-control" value="<?php echo $user['email'] ?>">
    <br>
    <label for="role">Ruolo</label>
    <select name="role" class="form-select">
        <option value="STANDARD" <?php echo $user['role'] === "STANDARD" ? "selected" : "" ?>>Utente standard</option>
        <option value="OWNER" <?php echo $user['role'] === "OWNER" ? "selected" : "" ?>>Proprietario</option>
        <?php if(Auth::is_admin()): ?>
        <option value="ADMIN" <?php echo $user['role'] === "ADMIN" ? "selected" : "" ?>>Amministratore IT</option>
        <?php endif; ?>
    </select>
    <br>

    <input type="hidden" name="username" value="<?php echo $user['username'] ?>">
    <button type="submit" class="btn btn-primary">Salva</button>
</form>

<?php end: ?>