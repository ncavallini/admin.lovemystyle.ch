<?php 
$product_ids = json_decode($_POST['products'], true);
$dbconnection = DBConnection::get_db_connection();
$sql = "SELECT * FROM products WHERE product_id IN (" . implode(',', array_map('intval', $product_ids)) . ")";
$stmt = $dbconnection->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll();


?>
<h1>Gestione Sconti</h1>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>ID Prodotto</th>
                <th>Nome</th>
                <th>Prezzo attuale (CHF)</th>
                <th>Sconto (%)</th>
                <th>Prezzo scontato (CHF)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo $product['product_id']; ?></td>
                    <td><?php echo $product['name']; ?></td>
                    <td><?php echo Utils::format_price($product['price']); ?></td>
                    <td>
                        <input type="number" data-product_id="<?php echo $product['product_id']; ?>" name="discount[<?php echo $product['product_id']; ?>]" value="0" min="0" max="100" class="form-control discount-input" />
                    </td>
                    <td class="discounted-price" data-product_id="<?php echo $product['product_id']; ?>">
                        <?php echo Utils::format_price($product['price']); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<button class="btn btn-primary" type="submit" onclick="submitDiscounts()">Salva Sconti</button>

<script>
    document.querySelectorAll('.discount-input').forEach(input => {
        input.addEventListener('input', function() {
            const row = this.closest('tr');
            const originalPrice = parseFloat(row.children[2].textContent.replace('CHF', '').trim());
            const discountPercent = parseFloat(this.value) || 0;
            const discountedPrice = originalPrice * (1 - discountPercent / 100);
            row.querySelector('.discounted-price').textContent = discountedPrice.toFixed(2);
        });
    });

    function submitDiscounts() {
        const discountedPrices = {};
        document.querySelectorAll('.discount-input').forEach(input => {
            const productId = input.getAttribute('data-product_id');
            const td = document.querySelector(`.discounted-price[data-product_id='${productId}']`);
            discountedPrices[productId] = parseFloat(td.textContent.replace('CHF', '').trim()) * 100;

        });
        // Send the discounts to the server via AJAX or form submission
        
        fetch("/actions/products/set_discounts.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            credentials: "include",
            body: JSON.stringify(discountedPrices)
        }).then(res => res.text());

        localStorage.setItem("selectedProducts", JSON.stringify([]))
        window.location.href = "/index.php?page=products_view"
    }

</script>