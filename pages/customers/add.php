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
    <label for="gender">Genere *</label>
    <select id="gender" name="gender" class="form-select" required>
        <option selected value="" disabled>Scegli</option>
        <option value="M">M</option>
        <option value="F">F</option>
    </select>
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
            <input type="text" name="postcode" class="form-control" placeholder="CAP">
        </div>
        <div class="col-8">
            <label for="city">Città</label>
            <input type="text" name="city" class="form-control" placeholder="Città">
        </div>
    </div>
    <br>

    <select name="country" class="form-select">
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
            <input type="text" name="tel" class="form-control" placeholder="Telefono" pattern="<?php echo Utils::get_phone_regex() ?>">
        </div>
        <div class="col-8">
            <label for="email">E-mail *</label>
            <input type="email" name="email" class="form-control" placeholder="E-mail" required>
        </div>
    </div>
    <br>
    <div class="form-check">
    <input type="checkbox" name="is_newsletter_allowed" class="form-check-input">
    <label for="is_newsletter_allowed" class="form-check-label">Desidera ricevere la newsletter?</label>
    </div>


            <br>

    <button type="submit" class="btn btn-primary">Aggiungi Cliente</button>
</form>
