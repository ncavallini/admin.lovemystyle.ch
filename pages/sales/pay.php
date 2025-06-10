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
$negative = $_GET['negative'] || 0;

$sql = "SELECT price, quantity FROM sales_items WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$items = $stmt->fetchAll();

foreach ($items as $item) {
    $total += $item["price"] * $item["quantity"];
}

$total = Utils::compute_discounted_price($total, $sale['discount'], $sale['discount_type']);

$sql = "SELECT rate FROM exchange_rates WHERE currency = 'EUR'";
$stmt = $dbconnection->prepare($sql);
$stmt->execute();
$rate = $stmt->fetchColumn();


$factor = $negative ? -1 : 1;
$total_eur = $factor * $total * $rate;

?>

<h1>Pagamento</h1>
<p class="display-6 underline">Totale da pagare: <?php echo Utils::format_price($total * $factor) ?> CHF <small>(<?php echo Utils::format_price($total_eur) ?> €)</small> </p>

<form action="actions/sales/pay.php" method="POST" id="form-pay">
    <input type="hidden" name="sale_id" value="<?php echo $saleId ?>">
    <input type="hidden" name="total" value="<?php echo $total ?>">
    <input type="hidden" name="negative" value="<?php echo $negative ?>">
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="fa-solid fa-credit-card"></i></span>
        <select id="payment_method-select" name="payment_method" class="form-select" required>
            <option value="CASH">Contanti</option>
            <option value="POS">Carta di credito / TWINT</option>
        </select>
    </div>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="fa-solid fa-money-bill"></i></span>
        <input type="number" id="given-input" step="0.01" min="0" name="amount"  class="form-control" placeholder="Importo pagato" id="amount">
        <span class="input-group-text">
            <select name="currency" id="currency-select" class="form-select">
                <option value="CHF" selected>CHF</option>
                <option value="EUR">€</option>
            </select>
        </span>
    </div>
    <p hidden id="return-p" class="lead" style="font-size: 120%;">Resto: <span id="return-span"></span> </p>
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
        const currency = document.getElementById('currency-select').value;
        const currencyRate = currency === "CHF" ? 1 : <?php echo $rate ?>;
        let returnAmount = 0;

        if(currency === "CHF") {
            returnAmount = given - total;
        }
        else {
            returnAmount = given - (total* currencyRate);
            returnAmount = returnAmount * currencyRate;

        }

        if(paymentMethod === "CASH") {
            givenInput.removeAttribute("hidden");
        }

        else {
            givenInput.setAttribute("hidden", "true");
        }

        if(paymentMethod === "CASH" && returnAmount >= 0) {
            returnSpan.innerText = (returnAmount / 100).toFixed(2) + " CHF"; 
            returnP.removeAttribute('hidden');
        }

        else {
            returnP.setAttribute('hidden', 'true');
        }

        
    }

    givenInput.addEventListener('input', updateReturn);
    paymentMethodSelect.addEventListener('change', updateReturn);
    givenInput.addEventListener('change', () => {
        givenInput.value = Number(givenInput.value).toFixed(2);
    });

    document.getElementById("btn-confirm").addEventListener("click", (e) => {
e.preventDefault();
let given = parseFloat(givenInput.value);
if(isNaN(given)) given = 0.0;
given *= 100;
        const paymentMethod = paymentMethodSelect.value;
const total = <?php echo $total ?>;
 
if (paymentMethod === "CASH" && given < total && !<?php echo $negative ? 'true' : 'false'; ?>) {
    alert("Importo insufficiente");
 	return; 
	
}      
       e.target.setAttribute("disabled", "true");
       document.getElementById("form-pay").submit();
    });

</script>