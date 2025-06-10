<h1>Riscatta Carta Regalo</h1>

<?php
$dbconnection = DBConnection::get_db_connection();
$saleId = $_GET['sale_id'];
$sql = "SELECT * FROM sales WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$sale = $stmt->fetch();

$sql = "SELECT * FROM sales_items WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$items = $stmt->fetchAll();

$sum = 0;

foreach($items as $item) {
$sum += $item['price'] * $item['quantity'];
}
$sum = $sum / 100;

if (!$sale) {
    Utils::print_error("Vendita non trovata");
    return;
}
?>

<form action="actions/giftcards/redeem.php" method="POST">
    <input type="hidden" name="sale_id" value="<?php echo $saleId ?>">
    <label for="card_id">Codice Carta Regalo *</label>
    <input type="text" name="card_id" id="card_id-input" class="form-control" required minlength="4" maxlength="4">
    <br>
    <label for="amount">Importo da scontare *</label>
    <input type="number" name="amount" id="amount-input" class="form-control" required min="0" max="0">
    <br>
    <button type="submit" class="btn btn-primary">Riscatta</button>
</form>

<script>
    document.getElementById("card_id-input").addEventListener("change", (e) => {
        if (e.target.value.length != 4) return;

        const formData = new FormData();
        formData.append("card_id", e.target.value);

        fetch("/actions/giftcards/get_balance.php", {
                method: "POST",
                body: formData,
                credentials: "include"
            })
            .then(res => {
                if (!res.ok) {
                    // Risposta HTTP 400, 404, ecc.
                    return res.json().then(err => {
                        console.error("Errore server:", err);
                        bootbox.alert(err.error || "Carta non trovata o scaduta.");
                    }).catch(() => {
                        bootbox.alert("Errore sconosciuto nel server.");
                    });
                }
                // Risposta OK, procedi con JSON
                return res.json();
            })
            .then(data => {
                // Protezione in caso il blocco sopra ha già gestito l’errore
                if (!data) return;

                if (data.is_expired) {
                    bootbox.alert("Carta regalo scaduta");
                    return;
                }
                if (data.balance === 0) {
                    bootbox.alert("Carta regalo vuota");
                    return;
                }
                if (typeof data.balance !== "number") {
                    console.error("Saldo non valido:", data.balance);
                    bootbox.alert("Errore nel saldo restituito dalla carta.");
                    return;
                }

                const amountInput = document.getElementById("amount-input");
                amountInput.value = Math.min(data.balance, <?php echo $sum ?>);
                amountInput.max = Math.min(data.balance, <?php echo $sum ?>);
            })
            .catch(e => {
                console.error("Errore di rete o parsing:", e);
                bootbox.alert("Errore di rete. Riprova.");
            });

    });
</script>