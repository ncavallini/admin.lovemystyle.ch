<?php
$connection = DBConnection::get_db_connection();
$q = $_GET['q'] ?? "";
$searchQuery = Pagination::build_search_query($q, ["code"]);
$sql = "SELECT * FROM discount_codes WHERE " . $searchQuery['text'] . " ORDER BY from_date DESC ";
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$pagination = new Pagination($stmt->rowCount());
$sql .= $pagination->get_sql();
$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$codes = $stmt->fetchAll();
?>


<h1>Codici Sconto</h1>
<p></p>
<a href="/index.php?page=discount-codes_add" class="btn btn-primary"><i class="fa-solid fa-plus"></i></a>
<p>&nbsp;</p>
<form action="/index.php" method="GET" style="display: flex; align-items: center;">
    <div class="input-group mb-3">
            <input type="text" class="form-control" name="q" placeholder="Cerca codice sconto" value="<?php echo $_GET['q'] ?? '' ?>" style="flex-grow: 1; margin-right: 8px;">
            <button type="submit" class="btn btn-secondary "><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>
    <input type="hidden" name="page" value="<?php echo $_GET['page'] ?>">
</form>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Codice</th>
                <th>Dal</th>
                <th>Al</th>
                <th>Sconto</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($codes as $code) {
                echo "<tr>";
                Utils::print_table_row("<span class='tt'>" . $code['code'] . "</span>");
                Utils::print_table_row(Utils::format_date($code['from_date']));
                Utils::print_table_row(Utils::format_date($code['to_date']));
                Utils::print_table_row($code['discount'] . " " . $code['discount_type']);
                Utils::print_table_row(<<<EOD
                <a onclick="deleteCode('{$code['code']}')" class="btn btn-outline-danger btn-sm" title='Elimina'><i class="fa-solid fa-trash"></i></a>
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
    function deleteCode(code) {
        bootbox.confirm({
            title: "Elimina Codice Sconto",
            message: "Sei sicuro di voler eliminare il codice sconto?",
            
            callback: function(result) {
                if(result) {
                    window.location.href = "/actions/discount-codes/delete.php?code=" + code;
                }
            }
        })
    }
</script>