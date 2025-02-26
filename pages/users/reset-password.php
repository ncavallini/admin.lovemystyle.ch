<script src="/inc/zxcvbn.js"></script>

<h1>Resetta Password</h1>

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

<form action="actions/users/reset_password.php" method="POST" id="reset-password-form">
    <input type="hidden" name="username" value="<?php echo $user['username'] ?>">
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
    <button type="submit" class="btn btn-primary">Resetta Password</button>
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
        } else {
            progress.classList.remove('bg-danger');
            progress.classList.add('bg-success');
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

<?php end: ?>