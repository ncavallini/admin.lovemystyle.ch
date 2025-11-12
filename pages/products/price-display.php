<h1>Stampa display prezzi</h1>
<?php
$encodedProducts = $_POST['products'];
$product_ids = json_decode(($encodedProducts), true);

// Validate and ensure you have an array of IDs
if (!is_array($product_ids) || empty($product_ids)) {
    Utils::redirect("/index.php?page=products_view");
    die;
}

$placeholders = implode(',', array_fill(0, count($product_ids), '?'));

$sql = "SELECT p.*, b.name AS brand_name
        FROM products p
        JOIN brands b ON p.brand_id = b.brand_id
        WHERE p.product_id IN ($placeholders)";

$stmt = $dbconnection->prepare($sql);
$stmt->execute($product_ids);
$products = $stmt->fetchAll();


?>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>ID Prodotto</th>
                <th>Nome interno</th>
                <th>Brand</th>
                <th>Nome visualizzato (IT)</th>
                <th>Nome visualizzato (DE)</th>
                <th>Nome visualizzato (EN)</th>
                <th>Prezzo (CHF)</th>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach($products as $product) {
                    $id = "displayname_" . $product['product_id'] . "_";
                    echo "<tr data-x-product-id='{$product['product_id']}'>";
                    Utils::print_table_row($product['product_id']);
                    Utils::print_table_row($product['name']);
                    Utils::print_table_row($product['brand_name']);
                    Utils::print_table_row("<input type='text' value='{$product['name']}' id='{$id}it'  class='form-control' required>");
                    Utils::print_table_row("<input type='text' value='' id='{$id}de'  class='form-control' required>");
                    Utils::print_table_row("<input type='text' value='' id='{$id}en'  class='form-control' required>");
                    Utils::print_table_row(data: ($product['is_discounted'] ? "(<span class='strikethrough'>" . Utils::format_price($product['full_price']) . "</span>)&nbsp;&nbsp;" : "") .  Utils::format_price($product['price']));
                    echo "</tr>";
                }
            ?>
        </tbody>
    </table>
</div>

<p>&nbsp;</p>
<button onclick="printDisplay()" class="btn btn-primary">Stampa</button>
<p>&nbsp;</p>
<div class="alert alert-warning">Stampare con l'opzione <b>Dimensioni effettive</b> per evitare il bordo bianco, e ricordarsi di caricare carta <b>A5</b>! </div>
 
<script>
    function printDisplay() {
        let result = [];
        let rows = document.querySelectorAll('tr[data-x-product-id]');
        rows.forEach(row => {
            let id = row.getAttribute('data-x-product-id');
            let brandName = row.querySelector('td:nth-child(3)').innerText;
            let it = document.getElementById(`displayname_${id}_it`).value;
            let de = document.getElementById(`displayname_${id}_de`).value;
            let en = document.getElementById(`displayname_${id}_en`).value;
            let price = row.querySelector('td:last-child').innerHTML;
            result.push({
                id: id,
                brandName: brandName,
                it: it,
                de: de,
                en: en,
                price: price
            });
        });

        const form = document.createElement('form');
        form.method = 'POST';
	form.target = "_blank";
        form.action = '/actions/products/print_price_display.php';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'products';
        input.value = JSON.stringify(result);
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
        
    }
</script>