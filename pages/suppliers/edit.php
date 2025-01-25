<h1>Modifica Fornitore</h1>

<?php 
$connection = DBConnection::get_db_connection();
$sql = "SELECT * FROM suppliers WHERE supplier_id = ?";
$stmt = $connection->prepare($sql);
$res = $stmt->execute([$_GET['supplier_id']]);
$supplier = $stmt->fetch();

if(!$res) {
    Utils::print_error("Fornitore non trovato.");
    goto end;
}
?>

<p>I campi contrassegnati con * sono obbligatori.</p>
<form action="actions/suppliers/edit.php" method="POST">
    <input type="hidden" name="supplier_id" value="<?php echo $supplier['supplier_id'] ?>">
<label for="name">Nome / Ragione Sociale *</label>
    <input type="text" class="form-control" name="name" placeholder="Nome / Ragione Sociale" value="<?php echo $supplier['name'] ?>" required>
    <br>
    <label for="street">Via *</label>
    <input type="text" class="form-control" name="street" placeholder="Via" value="<?php echo $supplier['street'] ?>" required>
    <br>
    <div class="row">
        <div class="col-4">
        <label for="postcode">CAP *</label>
        <input type="text" class="form-control" name="postcode" placeholder="CAP" value="<?php echo $supplier['postcode'] ?>" required>
        </div>
        <div class="col-8">
        <label for="city">Città *</label>
        <input type="text" class="form-control" name="city" placeholder="Città" value="<?php echo $supplier['city'] ?>" required>
        </div>
    </div>
    <br>
    <label for="country">Paese *</label>
    <select name="country" class="form-select">
        <option value="CH">Svizzera</option>
        <?php
            Country::options($supplier['country']);
        ?>
    </select>
    <br>
    <label for="tel">Telefono *</label>
    <input type="text" class="form-control" name="tel" placeholder="Telefono" value="<?php echo $supplier['tel'] ?>">
    <br>
    <label for="email">E-mail *</label>
    <input type="email" class="form-control" name="email" placeholder="E-mail" value="<?php echo $supplier['email'] ?>" required>
    <br>
    <div class="row">
        <div class="col-10">
        <label for="vat_number">Partita IVA</label>
        <input type="text" class="form-control" name="vat_number" placeholder="Partita IVA" value="<?php echo $supplier['vat_number'] ?>">
        </div>
            <div class="col-2">
                <a href="https://www.zefix.ch/it/search/entity/welcome" target="_blank">Verifica ZEFIX (CH)</a> | 
                <a href="https://ec.europa.eu/taxation_customs/vies/#/vat-validation" target="_blank">Verifica VIES (UE)</a>
            </div>
    </div>

    <br>

    <button type="submit" class="btn btn-primary">Salva Fornitore</button>

<?php end: ?>