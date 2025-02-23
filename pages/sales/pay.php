<?php
$dbconnection = DBConnection::get_db_connection();
$saleId = $_GET['sale_id'] ?? "";
$sql = "SELECT * FROM sales WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$sale = $stmt->fetch();
if (!$sale) {
    Utils::print_error("Vendita non trovata");
    goto end;
}

$total = 0;

$sql = "SELECT price, quantity FROM sales_items WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$items = $stmt->fetchAll();

foreach ($items as $item) {
    $total += $item["price"] * $item["quantity"];
}

if($sale["discount_type"] === "CHF") {
    $total -= ($sale['discount'] * 100);
}
else {
    $total = (1 - $sale['discount'] / 100) * $total;
}

?>

<h1>Pagamento</h1>
<p class="display-6 underline">Totale da pagare: <?php echo Utils::format_price($total) ?> CHF</p>

<form action="actions/sales/pay.php" method="POST" id="form-pay">
    <input type="hidden" name="sale_id" value="<?php echo $saleId ?>">
    <input type="hidden" name="total" value="<?php echo $total ?>">
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="fa-solid fa-credit-card"></i></span>
        <select id="payment_method-select" name="payment_method" class="form-select" required>
            <option value="CASH">Contanti</option>
            <option value="POS">Carta di credito / TWINT</option>
        </select>
    </div>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="fa-solid fa-money-bill"></i></span>
        <input type="number" id="given-input" step="0.01" min="0" name="amount" class="form-control" required placeholder="Importo pagato" id="amount">
        <span class="input-group-text">CHF</span>
    </div>
    <p hidden id="return-p" class="lead" style="font-size: 120%;">Resto: <span id="return-span"></span> CHF</p>
    <div class="alert alert-warning">
        <b>Attenzione!</b> La vendita diventa definitiva quando si clicca su "Conferma pagamento". L'azione è irreversibile. 
        Si consiglia di premere il pulsante solo dopo che il pagamento è andato a buon fine, onde evitare cambiamenti repentini del metodo di pagamento del Cliente (p.es. errore carta di credito, mancanza di contanti, etc). 
    </div>
    <button type="submit" class="btn btn-primary" id="btn-confirm">Conferma pagamento</button>
</form>
<p>&nbsp;</p>

<? end: ?>

<script>
    const givenInput = document.getElementById('given-input');
    const paymentMethodSelect = document.getElementById('payment_method-select');
    const returnSpan = document.getElementById('return-span');
    const returnP = document.getElementById('return-p');

    function updateReturn() {
        const total = <?php echo $total ?>;
        const given = parseFloat(givenInput.value) * 100;
        const paymentMethod = paymentMethodSelect.value;
        const returnAmount = given - total;

        if(paymentMethod === "CASH") {
            givenInput.removeAttribute("hidden");
            givenInput.setAttribute("required", "true");
        }

        else {
            givenInput.setAttribute("hidden", "true");
        }

        if(paymentMethod === "CASH" && returnAmount >= 0) {
            returnSpan.innerText = (returnAmount / 100).toFixed(2);
            returnP.removeAttribute('hidden');
        }

        else {
            returnP.setAttribute('hidden', 'true');
            givenInput.removeAttribute("required");
        }

        
    }

    givenInput.addEventListener('input', updateReturn);
    paymentMethodSelect.addEventListener('change', updateReturn);
    givenInput.addEventListener('change', () => {
        givenInput.value = Number(givenInput.value).toFixed(2);
    });

    document.getElementById("btn-confirm").addEventListener("click", (e) => {
       e.target.setAttribute("disabled", "true");
       document.getElementById("form-pay").submit();
    });

</script>