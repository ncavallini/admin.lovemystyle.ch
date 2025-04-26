<?php
$connection = DBConnection::get_db_connection();
$q = $_GET['q'] ?? "";
$searchQuery = Pagination::build_search_query($q, ["product_id", "p.name", "p.price", "b.name"]);
$sql = "SELECT p.*, b.name AS brand_name FROM products p JOIN brands b USING(brand_id) WHERE " . $searchQuery['text'] . " ORDER BY last_edit_at DESC";;
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$pagination = new Pagination($stmt->rowCount());
$sql .= $pagination->get_sql();
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$products = $stmt->fetchAll();

$items = 0;
if(!empty($q)) {
    foreach ($products as $product) {
        $sql = "SELECT SUM(stock) FROM product_variants WHERE product_id = :product_id";
        $stmt = $connection->prepare($sql);
        $stmt->execute(['product_id' => $product['product_id']]);
        $stock = $stmt->fetchColumn();
        if($stock <= 1e6) $items += $stock;
    }
}

?>

<h1>Prodotti</h1>
<p></p>
<a href="/index.php?page=products_add" class="btn btn-primary" title="Aggiungi Prodotto"><i class="fa-solid fa-plus"></i></a>
<a href="/actions/products/print_inventory.php" class="btn btn-secondary" title="Stampa inventario"><i class="fa-solid fa-print"></i></a>
<a href="/actions/products/export_inventory.php" class="btn" style="background-color: #1D6F42; color: white;" title="Esporta inventario in Excel"><i class="fa-solid fa-file-excel"></i></a>
<a class="btn btn-secondary disabled" id="btn-apply-discount" href="javascript:void(0)" title="Applica sconto"><i class="fas fa-percent"></i></a>
<a onclick="printDisplay()" class="btn btn-secondary" title="Stampa cartello display prezzi"><i class="fa-solid fa-list"></i></a>

<p>&nbsp;</p>
<form action="/index.php" method="GET" style="display: flex; align-items: center;">
    <div class="input-group mb-3">
        <input type="text" class="form-control" name="q" placeholder="Cerca prodotto" value="<?php echo $_GET['q'] ?? '' ?>" style="flex-grow: 1; margin-right: 8px;">
        <button type="submit" class="btn btn-secondary "><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>
    <input type="hidden" name="page" value="<?php echo $_GET['page'] ?>">
</form>
<p>&nbsp;</p>
<a href="javascript:void(0)" id="check-select-all" onclick="toggleSelectAll(event)">Seleziona tutto</a>
<div class="table-responsive">
    <table id="table" class="table table-striped">
        <thead>
            <tr>
                <th style="width: 50px;">Seleziona</th>
                <th>ID Prodotto</th>
                <th>Nome</th>
                <th>Brand</th>
                <th>Prezzo (CHF)</th>
                <th>Varianti</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($products as $product) {
                echo "<tr data-product-id='{$product['product_id']}'>";
                Utils::print_table_row("<input type='checkbox' onclick='toggleProductSelection(event)' class='form-check-input product-checkbox'>");
                Utils::print_table_row("<span class='tt'>{$product['product_id']}</span>");
                Utils::print_table_row($product['name']);
                Utils::print_table_row($product['brand_name']);
                Utils::print_table_row(Utils::format_price($product['price']));
                Utils::print_table_row("<a href='index.php?page=variants_view&product_id={$product['product_id']}'  class='btn btn-outline-primary btn-sm' title='Varianti'><i class='fa-solid fa-shirt'></i></a>");
                Utils::print_table_row(
                    data: <<<EOD
                    <a href="index.php?page=products_edit&product_id={$product['product_id']}"  class="btn btn-outline-primary btn-sm" title='Modifica'><i class="fa-solid fa-pen"></i></a>
                    <a onclick="deleteProduct('{$product['product_id']}')" class="btn btn-outline-danger btn-sm" title='Elimina'><i class="fa-solid fa-trash"></i></a>
    EOD
                );
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<br>
<small class="text-body-secondary"><?php echo $pagination->get_total_rows() ?> risultati <?php if($q !== "") echo "(" . $items . " pezzi)" ?>.</small>

<?php echo $pagination->get_page_links(); ?>

<script>
    function deleteProduct(product_id) {
        bootbox.confirm({
            title: "Elimina Prodotto",
            message: "Sei sicuro di voler eliminare il prodotto e tutte le sue varianti?",

            callback: function(result) {
                if (result) {
                    window.location.href = "/actions/products/delete.php?product_id=" + product_id;
                }
            }
        })
    }


    let selectedProducts = new Set();

    function toggleSelectAll(event) {
        const checkboxes = document.getElementsByClassName("product-checkbox");
        for (let i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = true;
            toggleProductSelection({
                target: checkboxes[i]
            });
        }
    }

    function toggleProductSelection(event) {
        const selectAll = document.getElementById("check-select-all");
        selectAll.checked = false;
        const table = document.getElementById("table");
        const tr = event.target.closest("tr");
        const btn = document.getElementById("btn-apply-discount");
        const productId = tr.getAttribute("data-product-id");
        if (event.target.checked) {
            table.classList.remove("table-striped");
            selectedProducts.add(productId);
            tr.classList.add("table-primary");

        } else {
            table.classList.add("table-striped");
            selectedProducts.delete(productId);
            tr.classList.remove("table-primary");
        }

        if (selectedProducts.size > 0) {
            btn.onclick = applyMassDiscount;
            btn.classList.remove("disabled");
        } else {
            btn.onclick = null;
            btn.classList.add("disabled");
        }
    }

    function applyMassDiscount() {
        bootbox.prompt({
            title: "Applica sconto ai prodotti selezionati - Inserisci %",
            inputType: 'number',
            min: 0,
            max: 100,
            callback: function(result) {
                if (!result) return;
                const discount = parseInt(result);
                fetch("/actions/products/mass_discount.php", {
                    method: "POST",
                    credentials: "include",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        product_ids: [...selectedProducts],
                        discount: discount
                    })

                }).then(response => {
                    if (response.ok) {
                        window.location.reload();
                    } else {
                        bootbox.alert("<div class='alert alert-danger'>Errore durante l'applicazione dello sconto</div>");
                    }
                });
            }
        });
    }

    function printDisplay() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php?page=products_price-display';

        // Pass the same products again (or any data you want)
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'products';
            input.value = JSON.stringify([...selectedProducts]);
            form.appendChild(input);
        

        document.body.appendChild(form);
        form.submit(); // 🚀 This triggers a POST and navigates!
    }
</script>