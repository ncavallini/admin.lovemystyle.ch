<?php if ($tablet = isset($_GET['tablet'])): ?>

    <style>
        body {
            background-color: #c3d5ed;
        }
    </style>


<?php endif ?>

<?php $lang = isset($_GET['lang']) ? $_GET['lang'] : 'it'; ?>
<?php if($lang === "it"): ?>


<h1>Aggiungi Cliente</h1>
<p>I campi contrassegnati con * sono obbligatori.</p>
<form action="actions/customers/add.php" method="POST">
    <div class="row">
        <div class="col-6">
            <label for="first_name">Nome *</label>
            <input type="text" name="first_name" class="form-control" placeholder="Nome" required>
        </div>
        <div class="col-6">
            <label for="last_name">Cognome *</label>
            <input type="text" name="last_name" class="form-control" placeholder="Cognome" required>
        </div>
    </div>

    <br>


    <label for="birth_date">Data di nascita</label>
    <input type="date" name="birth_date" class="form-control" max="<?php echo date("Y-m-d") ?>">
    <br>
    <label for="street">Via</label>
    <input type="text" name="street" class="form-control" placeholder="Via">
    <br>

    <div class="row">
        <div class="col-4">
            <label for="postcode">CAP</label>
            <input type="text" name="postcode" id="postcode-input" class="form-control" placeholder="CAP">
        </div>
        <div class="col-8">
            <label for="city">Città</label>
            <input type="text" name="city" id="city-input" class="form-control" placeholder="Città">
        </div>
    </div>
    <br>

    <label for="country">Paese</label>
    <select name="country" class="form-select" id="country-select">
        <option value="" selected disabled>Seleziona Paese</option>
        <option value="CH">Svizzera</option>
        <?php
        Country::options();
        ?>
    </select>
    <br>

    <div class="row">
        <div class="col-4">
            <label for="tel">Telefono</label>
            <input type="text" name="tel" id="tel-input" class="form-control" placeholder="Telefono" pattern="<?php echo Utils::get_phone_regex() ?>">
        </div>
        <div class="col-8">
            <label for="email">E-mail *</label>
            <input type="email" name="email" class="form-control" placeholder="E-mail" required>
        </div>
    </div>
    <br>
    <div class="form-check">
        <input type="checkbox" checked name="is_newsletter_allowed" class="form-check-input">
        <label for="is_newsletter_allowed" class="form-check-label">Desidera ricevere la newsletter?</label>
    </div>


    <br>
    <input type="hidden" name="tablet" value="<?php echo $tablet ? "1" : "0" ?>">

    <button type="submit" class="btn btn-primary"><?php echo $tablet ? "Registrati" : "Aggiungi Cliente" ?></button>
</form>

<?php elseif($lang === "de"): ?>
    <h1>Kunden hinzufügen</h1>
<p>Felder mit * sind Pflichtfelder.</p>
<form action="actions/customers/add.php" method="POST">
    <div class="row">
        <div class="col-6">
            <label for="first_name">Vorname *</label>
            <input type="text" name="first_name" class="form-control" placeholder="Vorname" required>
        </div>
        <div class="col-6">
            <label for="last_name">Nachname *</label>
            <input type="text" name="last_name" class="form-control" placeholder="Nachname" required>
        </div>
    </div>

    <br>

    <label for="birth_date">Geburtsdatum</label>
    <input type="date" name="birth_date" class="form-control" max="<?php echo date('Y-m-d') ?>">
    <br>

    <label for="street">Straße</label>
    <input type="text" name="street" class="form-control" placeholder="Straße">
    <br>

    <div class="row">
        <div class="col-4">
            <label for="postcode">PLZ</label>
            <input type="text" name="postcode" id="postcode-input" class="form-control" placeholder="PLZ">
        </div>
        <div class="col-8">
            <label for="city">Stadt</label>
            <input type="text" name="city" id="city-input" class="form-control" placeholder="Stadt">
        </div>
    </div>
    <br>

    <label for="country">Land</label>
    <select name="country" class="form-select" id="country-select">
        <option value="" selected disabled>Land auswählen</option>
        <option value="CH">Schweiz</option>
        <?php
        Country::options(lang: 'de');
        ?>
    </select>
    <br>

    <div class="row">
        <div class="col-4">
            <label for="tel">Telefon</label>
            <input type="text" name="tel" id="tel-input" class="form-control" placeholder="Telefon" pattern="<?php echo Utils::get_phone_regex() ?>">
        </div>
        <div class="col-8">
            <label for="email">E-Mail *</label>
            <input type="email" name="email" class="form-control" placeholder="E-Mail" required>
        </div>
    </div>
    <br>

    <div class="form-check">
        <input type="checkbox" checked name="is_newsletter_allowed" class="form-check-input">
        <label for="is_newsletter_allowed" class="form-check-label">Möchten Sie den Newsletter erhalten?</label>
    </div>

    <br>

    <input type="hidden" name="tablet" value="<?php echo $tablet ? '1' : '0' ?>">

    <button type="submit" class="btn btn-primary"><?php echo $tablet ? 'Registrieren' : 'Kunden hinzufügen' ?></button>
</form>

<?php elseif($lang === "en"): ?>
<h1>Add Customer</h1>
<p>Fields marked with * are required.</p>
<form action="actions/customers/add.php" method="POST">
    <div class="row">
        <div class="col-6">
            <label for="first_name">First Name *</label>
            <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
        </div>
        <div class="col-6">
            <label for="last_name">Last Name *</label>
            <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
        </div>
    </div>

    <br>

    <label for="birth_date">Date of Birth</label>
    <input type="date" name="birth_date" class="form-control" max="<?php echo date('Y-m-d') ?>">
    <br>

    <label for="street">Street</label>
    <input type="text" name="street" class="form-control" placeholder="Street">
    <br>

    <div class="row">
        <div class="col-4">
            <label for="postcode">ZIP Code</label>
            <input type="text" name="postcode" id="postcode-input" class="form-control" placeholder="ZIP Code">
        </div>
        <div class="col-8">
            <label for="city">City</label>
            <input type="text" name="city" id="city-input" class="form-control" placeholder="City">
        </div>
    </div>
    <br>

    <label for="country">Country</label>
    <select name="country" class="form-select" id="country-select">
        <option value="" selected disabled>Select Country</option>
        <option value="CH">Switzerland</option>
        <?php
        Country::options("en");
        ?>
    </select>
    <br>

    <div class="row">
        <div class="col-4">
            <label for="tel">Phone</label>
            <input type="text" name="tel" id="tel-input" class="form-control" placeholder="Phone" pattern="<?php echo Utils::get_phone_regex() ?>">
        </div>
        <div class="col-8">
            <label for="email">Email *</label>
            <input type="email" name="email" class="form-control" placeholder="Email" required>
        </div>
    </div>
    <br>

    <div class="form-check">
        <input type="checkbox" checked name="is_newsletter_allowed" class="form-check-input">
        <label for="is_newsletter_allowed" class="form-check-label">Would you like to receive the newsletter?</label>
    </div>

    <br>

    <input type="hidden" name="tablet" value="<?php echo $tablet ? '1' : '0' ?>">

    <button type="submit" class="btn btn-primary"><?php echo $tablet ? 'Sign up' : 'Add Customer' ?></button>
</form>
<?php endif ?>


<script src="inc/postcode.js"></script>
<script>
    const telInput = document.getElementById('tel-input');
    telInput.addEventListener("change", function() {
        if (telInput.value.startsWith("00")) {
            telInput.value = telInput.value.replace(/^00/, "+");
        } else if (telInput.value.startsWith("0")) {
            telInput.value = telInput.value.replace(/^0/, "+41");
        }
    });
</script>

