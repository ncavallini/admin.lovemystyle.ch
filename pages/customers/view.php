<?php
$connection = DBConnection::get_db_connection();
$q = $_GET['q'] ?? "";
$searchQuery = Pagination::build_search_query($q, ["last_name", "first_name", "customer_number", "email", "phone"]);
$sql = "SELECT * FROM customers WHERE " . $searchQuery['text'] . " ORDER BY last_name ASC ";
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$pagination = new Pagination($stmt->rowCount());
$sql .=  $pagination->get_sql();
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$customers = $stmt->fetchAll();
?>


<h1>Clienti</h1>

<div class="table-responsive">
    <table class="table">
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
         Utils::print_table_row(data: $customer['last_name']);
         Utils::print_table_row(data: $customer['first_name']);
         Utils::print_table_row(data: "<span class='tt'>" . $customer['customer_number'] . "</span>");
         Utils::print_table_row(data: $customer['last_name']);
         Utils::print_table_row(data: $customer['last_name']);
         Utils::print_table_row(data: $customer['last_name']);
         Utils::print_table_row(data: $customer['last_name']);
         Utils::print_table_row(data: $customer['last_name']);

    }
            ?>
        </tbody>
    </table>
</div>