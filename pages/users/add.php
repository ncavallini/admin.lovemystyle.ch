<script src="/inc/zxcvbn.js"></script>

<h1>Aggiungi Utente</h1>
<form action="actions/users/add.php" method="POST" id="add-user-form">
    <div class="row">
        <div class="col-6">
            <label for="first_name">Nome</label>
            <input id="first_name-input" type="text" name="first_name" class="form-control" required>
        </div>
        <div class="col-6">
            <label for="last_name">Cognome</label>
            <input id="last_name-input" type="text" name="last_name" class="form-control" required>
        </div>
    </div>
    <br>
    <label for="username">Nome utente</label>
    <input id="username-input" type="text" name="username" required class="form-control tt">
    <br>
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

    <div class="row">
        <div class="col-6">
            <label for="tel">Telefono</label>
            <input type="tel" name="tel" class="form-control" required pattern="<?php echo Utils::get_phone_regex() ?>">
        </div>
        <div class="col-6">
            <label for="email">E-mail</label>
            <input type="email" name="email" class="form-control" required>
        </div>
    </div>

    <br>

    <div class="row">
        <div class="col-3">
            <label for="street">Via</label>
            <input type="text" name="street" class="form-control" required>
        </div>
        <div class="col-3">
            <label for="postcode">CAP</label>
            <input type="text" name="postcode" class="form-control" required>
        </div>
        <div class="col-3">
            <label for="city">Città</label>
            <input type="text" name="city" class="form-control" required>
        </div>
        <div class="col-3">
            <label for="country">Paese</label>
            <select name="country" class="form-select" required>
                <option value="CH">Svizzera</option>
                <?php
                    Country::options();
                ?>
            </select>
        </div>
    </div>

    <br>

    <label for="iban">IBAN</label>
    <input type="text" name="iban" class="form-control" required>

    <br>
    <label for="role">Ruolo</label>
    <select name="role" class="form-select">
        <option value="STANDARD">Utente standard</option>
        <option value="OWNER">Proprietario</option>
        <?php if(Auth::is_admin()): ?>
        <option value="ADMIN">Amministratore IT</option>
        <?php endif; ?>
    </select>
    <br>

    <button type="submit" class="btn btn-primary">Aggiungi</button>
</form>

<script>
    function suggestUsername() {
        const first_name = document.getElementById('first_name-input').value;
        const last_name = document.getElementById('last_name-input').value;
        if (first_name === '' || last_name === '') {
            return;
        }
        let username = first_name[0] + '.' + last_name;
        username = username.toLowerCase();
        document.getElementById('username-input').value = username;
    }

    document.getElementById("username-input").addEventListener("focus", (e) => {
        suggestUsername();
    });

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


    const form = document.getElementById('add-user-form');

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