<?php
Auth::require_owner();
$connection = DBConnection::get_db_connection();
$q = $_GET['q'] ?? "";
$searchQuery = Pagination::build_search_query($q, ["last_name", "first_name", "username"]);
$sql = "SELECT * FROM users WHERE " . $searchQuery['text'] . " ORDER BY last_name ASC ";
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$pagination = new Pagination($stmt->rowCount());
$sql .= $pagination->get_sql();
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$users = $stmt->fetchAll();
?>


<h1>Utenti</h1>
<p></p>
<a href="/index.php?page=users_add" class="btn btn-primary"><i class="fa-solid fa-plus"></i></a>
<p>&nbsp;</p>
<form action="/index.php" method="GET" style="display: flex; align-items: center;">
    <div class="input-group mb-3">
            <input type="text" class="form-control" name="q" placeholder="Cerca utente" value="<?php echo $_GET['q'] ?? '' ?>" style="flex-grow: 1; margin-right: 8px;">
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
                <th>Nome utente</th>
                <th>Ruolo</th>
                <th>Telefono</th>
                <th>E-mail</th>
                <th>Ultimo accesso</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($users as $user) {
                echo "<tr>";
                Utils::print_table_row($user['last_name']);
                Utils::print_table_row($user['first_name']);
                Utils::print_table_row("<span class='tt'>" . $user['username'] . "</span>");
                Utils::print_table_row("<span class='tt'>" . $user['role'] . "</span>");
                Utils::print_table_row(Utils::format_phone_number($user['tel']));
                Utils::print_table_row($user['email']);
                Utils::print_table_row(Utils::format_datetime($user['last_login_at']));
                Utils::print_table_row("<span></span>");
                echo "</tr>";
            }
        
            ?>
        </tbody>
    </table>
</div>
<br>
<?php echo $pagination->get_page_links(); ?>

<script>
    function disableUser(username) {
        bootbox.confirm({
            title: "Disabilita Utente",
            message: "Sei sicuro di voler disabilitare l'utente?",
            
            callback: function(result) {
                if(result) {
                    window.location.href = "" + customer_id;
                }
            }
        })
    }
</script>