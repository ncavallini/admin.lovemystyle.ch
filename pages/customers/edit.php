
<h1>Modifica Cliente</h1>
<?php
    $dbconnection = DBConnection::get_db_connection();
    $stmt = $dbconnection->prepare("SELECT * FROM customers WHERE customer_id = ?");
    $stmt->execute([$_GET['customer_id'] ?? ""]);
    $customer = $stmt->fetch();
    if(!$customer) {
        Utils::print_error("Cliente non trovato.");
        goto end;
    }
?>


<p>I campi contrassegnati con * sono obbligatori.</p>
<form action="actions/customers/edit.php" method="POST">
    <input type="hidden" name="customer_id" value="<?php echo $customer['customer_id'] ?>">
    <div class="row">
        <div class="col-6">
        <label for="first_name">Nome *</label>
        <input type="text" name="first_name" class="form-control" placeholder="Nome" value="<?php echo $customer['first_name'] ?>" required>
        </div>
        <div class="col-6">
        <label for="last_name">Cognome *</label>
        <input type="text" name="last_name" class="form-control" placeholder="Cognome" value="<?php echo $customer['last_name'] ?>" required>
        </div>
    </div>
    
    <br>
    
    <label for="birth_date">Data di nascita</label>
    <input type="date" name="birth_date" class="form-control" max="<?php echo date("Y-m-d") ?>" value="<?php echo $customer['birth_date'] ?>">
    <br>
    <label for="street">Via</label>
    <input type="text" name="street" class="form-control" placeholder="Via" value="<?php echo $customer['street'] ?? "" ?>">
    <br>

    <div class="row">
        <div class="col-4">
            <label for="postcode">CAP</label>
            <input type="text" name="postcode" class="form-control" placeholder="CAP" value="<?php echo $customer['postcode'] ?? "" ?>">
        </div>
        <div class="col-8">
            <label for="city">Città</label>
            <input type="text" name="city" class="form-control" placeholder="Città" value="<?php echo $customer['city'] ?? "" ?>">
        </div>
    </div>
    <br>
    
    <label for="country">Paese</label>
    <select name="country" class="form-select">
        <option value="CH">Svizzera</option>
        <?php 
        Country::options($customer['country'] ?? "");
        ?>
    </select>
    <br>

    <div class="row">
        <div class="col-4">
            <label for="tel">Telefono</label>
            <input type="text" name="tel" id="tel-input" class="form-control" placeholder="Telefono" value="<?php echo $customer['tel'] ?? "" ?>" pattern="<?php echo Utils::get_phone_regex() ?>">
        </div>
        <div class="col-8">
            <label for="email">E-mail *</label>
            <input type="email" name="email" class="form-control" placeholder="E-mail" value="<?php echo $customer['email'] ?>" required>
        </div>
    </div>
    <br>
    <div class="form-check">
    <input type="checkbox" name="is_newsletter_allowed" class="form-check-input" <?php if(Brevo::is_newsletter_subscribed($customer['email'])) echo "checked" ?>>
    <label for="is_newsletter_allowed" class="form-check-label">Desidera ricevere la newsletter?</label>
    </div>


            <br>

    <button type="submit" class="btn btn-primary">Salva Cliente</button>
</form>

<script>
      const telInput = document.getElementById('tel-input');
  telInput.addEventListener("change", function() {
    if(telInput.value.startsWith("00")) {
        telInput.value = telInput.value.replace(/^00/, "+");
    }
    else if(telInput.value.startsWith("0")) {
        telInput.value = telInput.value.replace(/^0/, "+41");
    }
  });
</script>
<?php
end:
?>