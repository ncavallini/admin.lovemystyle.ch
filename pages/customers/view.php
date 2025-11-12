<?php
$connection = DBConnection::get_db_connection();
$q = $_GET['q'] ?? "";
$searchQuery = Pagination::build_search_query($q, ["last_name", "first_name", "customer_number", "email", "tel"]);

// First, get the total count for pagination
$countSql = "SELECT COUNT(*) FROM customers WHERE " . $searchQuery['text'];
$countStmt = $connection->prepare($countSql);
$countStmt->execute($searchQuery['params']);
$totalRows = $countStmt->fetchColumn();

// Initialize pagination with the total count
$pagination = new Pagination($totalRows);

// Now get the actual data with pagination
$sql = "SELECT * FROM customers WHERE " . $searchQuery['text'] . " ORDER BY last_name ASC " . $pagination->get_sql();
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$customers = $stmt->fetchAll();
?>

<h1>Clienti</h1>
<p></p>
<a href="/index.php?page=customers_add" class="btn btn-primary"><i class="fa-solid fa-plus"></i></a>
<p>&nbsp;</p>
<form action="/index.php" method="GET" style="display: flex; align-items: center;">
    <div class="input-group mb-3">
            <input type="text" class="form-control" name="q" placeholder="Cerca cliente" value="<?php echo $_GET['q'] ?? '' ?>" style="flex-grow: 1; margin-right: 8px;">
            <button type="submit" class="btn btn-secondary "><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>
    <input type="hidden" name="page" value="<?php echo $_GET['page'] ?>">
</form>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Cognome</th>
                <th>Nome</th>
                <th>N. Cliente</th>
                <th>Data di nascita</th>
                <th>Indirizzo</th>
                <th>Telefono</th>
                <th>E-mail</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($customers as $customer) {
                $birthDate = "-";
                if($customer['birth_date'] != "0000-00-00") {
                    $birthDate = Utils::format_date($customer['birth_date']);
                }
                echo "<tr>";    
                Utils::print_table_row(data: $customer['last_name']);
                Utils::print_table_row(data: $customer['first_name']);
                Utils::print_table_row(data: "<span class='tt'>" . $customer['customer_number'] . "</span>");
                Utils::print_table_row(data: $birthDate);
                Utils::print_table_row(data: Utils::format_address($customer['street'], $customer['postcode'], $customer['city'], $customer['country']));
                Utils::print_table_row(data: Utils::format_phone_number($customer['tel']));
                Utils::print_table_row(data: $customer['email']);
                Utils::print_table_row(data: <<<EOD
                <a href="index.php?page=customers_edit&customer_id={$customer['customer_id']}"  class="btn btn-outline-primary btn-sm" title='Modifica'><i class="fa-solid fa-pen"></i></a>
                <a href="index.php?page=customers_card&customer_id={$customer['customer_id']}" class="btn btn-outline-primary btn-sm" title='Gestione carta fedeltà'><i class="fa-solid fa-address-card"></i></a>
                <a href="index.php?page=sales_customer-view&customer_id={$customer['customer_id']}" class="btn btn-outline-primary btn-sm" title='Vendite cliente'><i class="fa-solid fa-bag-shopping"></i></a>
                <a onclick="deleteCustomer('{$customer['customer_id']}')" class="btn btn-outline-danger btn-sm" title='Elimina'><i class="fa-solid fa-trash"></i></a>
EOD
                );

                echo "</tr>";
            }
        
            ?>
        </tbody>
    </table>
</div>
<small class="text-body-secondary"><?php echo $pagination->get_total_rows() ?> risultati.</small>
<br>
<?php echo $pagination->get_page_links(); ?>

<script>
    function deleteCustomer(customer_id) {
        bootbox.confirm({
            title: "Elimina Cliente",
            message: "Sei sicuro di voler eliminare il cliente?",
            
            callback: function(result) {
                if(result) {
                    window.location.href = "/actions/customers/delete.php?customer_id=" + customer_id;
                }
            }
        })
    }
</script>