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
                <th>Abilitato?</th>
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
                Utils::print_table_row("<input type='checkbox' onclick='return false;' class='form-check-input' " . ($user['is_enabled'] ? "checked" : "") . ">");
                Utils::print_table_row(Utils::format_phone_number($user['tel']));
                Utils::print_table_row($user['email']);
                Utils::print_table_row(Utils::format_datetime($user['last_login_at']));
                echo "<td class='text-nowrap'>";
                $href = $user['role'] === "ADMIN" ? "#" : "/actions/users/toggle_status.php?username={$user['username']}";
                if ($user['is_enabled']) {
                    echo "<a href='$href' title='Disabilita' class='btn btn-sm btn-outline-warning'><i class='fa-solid fa-user-slash'></i></a>";
                } else {
                    echo "<a href='$href' title='Abilita' class='btn btn-sm btn-outline-success'><i class='fa-solid fa-user-check'></i></a>";
                }
                echo "&nbsp;";
                echo "<a href='/index.php?page=users_edit&username={$user['username']}' title='Modifica' class='btn btn-sm btn-outline-primary'><i class='fa-solid fa-pen'></i></a>";
                echo "&nbsp;";
                echo "<a href='/index.php?page=users_reset-password&username={$user['username']}' title='Resetta Password' class='btn btn-sm btn-outline-primary'><i class='fa-solid fa-asterisk'></i><i class='fa-solid fa-asterisk'></i><i class='fa-solid fa-asterisk'></i></a>";
                echo "&nbsp;";
                echo "<a href='javascript:void(0);' onclick=\"confirmDelete('{$user['username']}', '{$user['first_name']}', '{$user['last_name']}')\" title='Elimina' class='btn btn-sm btn-outline-danger'><i class='fa-solid fa-trash'></i></a>";
                echo "&nbsp;";
                echo "<a href='/index.php?page=clockings_view&username={$user['username']}' title='Timbrature' class='btn btn-sm btn-outline-secondary'><i class='fa-solid fa-clock'></i></a>";
                echo "</td>";

                echo "</tr>";
            }

            ?>
        </tbody>
    </table>
</div>
<br>
<?php echo $pagination->get_page_links(); ?>

<script>
    function confirmDelete(username, firstName, lastName) {
        bootbox.confirm(`<div class='alert alert-danger'>Sei sicuro di voler eliminare l'utente ${firstName} ${lastName}?</div>`, (res) => {
            if (res) window.location.href = `/actions/users/delete.php?username=${username}`;
        })
    }
</script>