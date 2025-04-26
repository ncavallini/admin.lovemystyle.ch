<h1>Reimpostazione Password</h1>

<?php
    $token = $_GET['token'] ?? null;
?>

<?php if ($token === null): ?>

    <p>Inserisci il tuo nome utente per iniziare il ripristino della password.</p>
    <form action="actions/auth/get_password_reset_token" method="POST">
        <label for="username">Nome utente</label>
        <input type="text" id="username" name="username" class="form-control" required>
        <br>
        <button type="submit" class="btn btn-primary">Invia</button>
    </form>

<?php else: ?>

<?php
$dbconnection = DBConnection::get_db_connection();
$sql = "SELECT * FROM password_reset_tokens WHERE token = ?;";    
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$token]);
$token_data = $stmt->fetch();
if (!$token_data) {
    Utils::print_error(message: "Token non valido o scaduto.");
    echo "<p><a href='index.php?page=forgot-password'>Torna alla pagina di reimpostazione password</a></p>";
    return;
}

if($token_data['expires_at'] < date("Y-m-d H:i:s")) {
    Utils::print_error(message: "Token non valido o scaduto.");
    echo "<p><a href='index.php?page=forgot-password'>Torna alla pagina di reimpostazione password</a></p>";
    return;
}
    
?>

    <script src="/inc/zxcvbn.js"></script>


<p>Inserisci per due volte la nuova password.</p>
<form action="actions/auth/reset_password.php" id="reset-password-form" method="POST">
    <input type="hidden" name="token" value="<?php echo $token ?>">
    <div class="row">
        <div class="col-4">
            <label for="password">Password</label>
            <input id="password-input" type="password" name="password" class="form-control" required>
        </div>
        <div class="col-4">
            <label for="confirm_password">Conferma Password</label>
            <input id="confirm_password-input" type="password" name="confirm_password" class="form-control" required>
        </div>

        <div class="col-4">
            <label for="strength">Sicurezza Password</label>
            <div class="progress" role="progressbar">
                <div id="strength-progress" class="progress-bar"></div>
            </div>
        </div>
    </div>

    <p>&nbsp;</p>
    <div class="row">
        <div class="alert alert-primary">La barra "Sicurezza Password" si deve colorare di verde!</div>
    </div>
    <br>
    <button type="submit" id="btn-submit" class="btn btn-primary" disabled>Reimposta password</button>
</form>

<script>
    function checkPasswords() {
        const password = document.getElementById('password-input').value;
        const confirm_password = document.getElementById('confirm_password-input').value;
        if (password !== confirm_password) {
            bootbox.alert({
                title: "Errore",
                message: "<div class='alert alert-danger'>Le password non corrispondono.</div>"
            });
            document.getElementById('password-input').value = '';
            document.getElementById('confirm_password-input').value = '';
        }
    }

    function checkPasswordStrength() {
        const progress = document.getElementById('strength-progress');
        const password = document.getElementById('password-input').value;
        const result = zxcvbn(password);
        progress.style.width = result.score * 25 + '%';
        if (result.score < 3) {
            progress.classList.remove('bg-success');
            progress.classList.add('bg-danger');
            document.getElementById('btn-submit').disabled = true;
        } else {
            progress.classList.remove('bg-danger');
            progress.classList.add('bg-success');
            document.getElementById('btn-submit').disabled = false;

        }

        return result.score;
    }

    document.getElementById("confirm_password-input").addEventListener("focusout", (e) => {
        checkPasswords();
    });

    document.getElementById("password-input").addEventListener("input", (e) => {
        checkPasswordStrength();
    });


    const form = document.getElementById('reset-password-form');

    form.addEventListener("submit", (e) => {
        const strength = checkPasswordStrength();
        if (strength < 3) {
            e.preventDefault();
            bootbox.alert({
                title: "Errore",
                message: "<div class='alert alert-danger'>La password non è abbastanza sicura.</div>"
            });
        }
    })
</script>


<?php endif; ?>