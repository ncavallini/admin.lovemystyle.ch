<?php
$dbconnection = DBConnection::get_db_connection();
if (!isset($_GET['sale_id']) && !isset($saleId)) {
    $sql = "INSERT INTO sales (sale_id, created_at, username) VALUES (UUID(), NOW(), ?)";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([Auth::get_username()]);

    $sql = "SELECT sale_id FROM sales WHERE username = ? AND NOT is_finalized ORDER BY created_at DESC LIMIT 1";
    $stmt = $dbconnection->prepare($sql);
    $stmt->execute([Auth::get_username()]);
    $saleId = $stmt->fetchColumn(0);
} else {
    $saleId = $_GET['sale_id'];
}

$sql = "SELECT i.*, p.name FROM sales_items i JOIN products p USING(product_id) WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM sales WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$sale = $stmt->fetch();

?>

<h1>Aggiungi / Modifica vendita</h1>
<p><b>Operatore:</b> <?php echo Auth::get_username() ?></p>
<form id="form-new-item" action="actions/sales/add_item.php" method="POST">
    <input type="hidden" name="sale_id" value="<?php echo $saleId ?>">
    <label for="sku">Codice Articolo *</label>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="fa-solid fa-barcode"></i></span>
        <input type="text" minlength="13" maxlength="13" name="sku" class="form-control tt" style="font-size: 150%;" required id="sku-input"></input>
        <span class="input-group-text"><button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i></button></span>
    </div>
    </form>
    <br>
    <h2>Voci di vendita</h2>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Codice Articolo</th>
                    <th>Nome</th>
                    <th>Variante</th>
                    <th>Quantità</th>
                    <th>Prezzo unit. (CHF)</th>
                    <th>Prezzo totale (CHF)</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $subtotal = 0;
                foreach ($items as $item) {
                    $sku = InternalNumbers::get_sku($item["product_id"], $item["variant_id"]);
                    $sql = "SELECT color, size FROM product_variants WHERE product_id = ? AND variant_id = ?";
                    $stmt = $dbconnection->prepare($sql);
                    $stmt->execute([$item['product_id'], $item['variant_id']]);
                    $variantData = $stmt->fetch();
                    $subtotal += $item['price'] * $item['quantity'];
                    echo "<tr>";
                    Utils::print_table_row(InternalNumbers::get_sku($item['product_id'], $item['variant_id']));
                    Utils::print_table_row($item['name']);
                    Utils::print_table_row($variantData['color'] . " / " . $variantData['size']);
                    Utils::print_table_row($item['quantity']);
                    Utils::print_table_row(Utils::format_price($item['price']));
                    Utils::print_table_row(Utils::format_price($item['total_price']));
                    Utils::print_table_row(
                        <<<EOD
                        <a href="actions/sales/decrease_item_quantity.php?sale_id=$saleId&sku=$sku" class='btn btn-sm btn-warning'><i class='fa fa-minus'></i></a>
                        <a onclick="deleteItem('$sku')" class='btn btn-sm btn-danger'><i class='fa fa-trash'></i></a>
                        EOD

                    );
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <hr>
    <p class="display-6 underline">Subtotale: <?php echo Utils::format_price($subtotal) ?> CHF</p>
    <p>&nbsp;</p>
    <h2>Sconto</h2>
    È possibile applicare uno sconto alla vendita. Inserire l'importo dello sconto in CHF o la percentuale sul subtotale.
    <div class="row d-flex align-items-center">
    <div class="col-10">
    <label for="discount" class="form-label">Sconto</label>

        <div class="input-group mb-3">
            <input type="number" class="form-control" value="<?php echo $sale["discount"] ?? 0 ?>" min="0" step="0.01" name="discount" id="discount-input" required oninput="computeGrandTotal()">
            <span class="input-group-text" id="discount-input-group-text">CHF</span>

        </div>
        
    </div>
    <div class="col-2 d-flex align-items-center gap-3">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="discount_type" onclick="setInputGroupText(0)" value="CHF" <?php if($sale["discount_type"] === "CHF") echo "checked" ?> >
            <label class="form-check-label" for="radio1">CHF</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="discount_type"  onclick="setInputGroupText(1)" value="%" <?php if($sale["discount_type"] === "%") echo "checked" ?>>
            <label class="form-check-label" for="radio2">%</label>
        </div>
    </div>
</div>
<p>&nbsp;</p>
<hr>
<p class="display-6 underline b" id="grand-total"></p>
<p>&nbsp;</p> 
<a class="btn btn-primary" href="index.php?page=sales_pay&sale_id=<?php echo $saleId ?>">Vai al pagamento</a>
</div>


<script>
    document.getElementById("sku-input").focus();
    const form = document.getElementById("form-new-item");
    const newItemInput = document.getElementById("sku-input");
    newItemInput.addEventListener("input", () => {
        if (newItemInput.value.length == 13) {
            form.submit();
        }
    })

    function deleteItem(sku) {
        bootbox.confirm({
            title: "Elimina Prodotto",
            message: "Sei sicuro di voler eliminare il prodotto dal carrello?",
            callback: function(result) {
                if (result) {
                    window.location.href = "/actions/sales/delete_item.php?sale_id=<?php echo $saleId ?>&sku=" + sku;
                }
            }
        })
    }

    let discountType = "<?php echo $sale["discount_type"] ?>"

    function setInputGroupText(type) {
        const inputGroupText = document.getElementById("discount-input-group-text");
        const discountInput = document.getElementById("discount-input");
        if (type == 0) {
            inputGroupText.innerText = "CHF";
            discountInput.removeAttribute("max");
            discountType = "CHF";
        } else {
            inputGroupText.innerText = "%";
            discountInput.setAttribute("max", 100);
            discountType = "%";
        }
        computeGrandTotal();
    }

    function computeGrandTotal() {
        const subtotal = <?php echo $subtotal ?>;
        const discount = document.getElementById("discount-input").value;
        let grandTotal;
        if(discountType === "CHF") {
            grandTotal = subtotal - (discount * 100);
        } else {
            grandTotal = subtotal - (subtotal * discount / 100);
        }
        grandTotal /= 100;
        grandTotal = Math.max(grandTotal, 0);
        document.getElementById("grand-total").innerText = "Totale: " + grandTotal.toFixed(2) + " CHF";

        const formData = new FormData();    
        formData.append("sale_id", "<?php echo $saleId ?>");
        formData.append("discount", discount);
        formData.append("discount_type", discountType);

        fetch("/actions/sales/update_discount.php", {
            method: "POST",
            body: formData,
            credentials:"include"
        }).then(res => console.log(res));
    }

    computeGrandTotal();
</script>