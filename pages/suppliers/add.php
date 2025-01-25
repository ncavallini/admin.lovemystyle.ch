<h1>Aggiungi Fornitore</h1>

<p>I campi contrassegnati con * sono obbligatori.</p>
<form action="actions/suppliers/add.php" method="POST">
    <label for="name">Nome / Ragione Sociale *</label>
    <input type="text" class="form-control" name="name" placeholder="Nome / Ragione Sociale" required>
    <br>
    <label for="street">Via *</label>
    <input type="text" class="form-control" name="street" placeholder="Via" required>
    <br>
    <div class="row">
        <div class="col-4">
        <label for="postcode">CAP *</label>
        <input type="text" class="form-control" name="postcode" placeholder="CAP" required>
        </div>
        <div class="col-8">
        <label for="city">Città *</label>
        <input type="text" class="form-control" name="city" placeholder="Città" required>
        </div>
    </div>
    <br>
    <label for="country">Paese *</label>
    <select name="country" class="form-select">
        <option value="CH">Svizzera</option>
        <?php
            Country::options();
        ?>
    </select>
    <br>
    <label for="tel">Telefono *</label>
    <input type="text" class="form-control" name="tel" placeholder="Telefono">
    <br>
    <label for="email">E-mail *</label>
    <input type="email" class="form-control" name="email" placeholder="E-mail" required>
    <br>
    <div class="row">
        <div class="col-10">
        <label for="vat_number">Partita IVA</label>
        <input type="text" class="form-control" name="vat_number" placeholder="Partita IVA">
        </div>
            <div class="col-2">
                <a href="https://www.zefix.ch/it/search/entity/welcome" target="_blank">Verifica ZEFIX (CH)</a> | 
                <a href="https://ec.europa.eu/taxation_customs/vies/#/vat-validation" target="_blank">Verifica VIES (UE)</a>
            </div>
    </div>

    <br>

    <button type="submit" class="btn btn-primary">Aggiungi Fornitore</button>

<?php end: ?>