<?php
/**
 * Vendite — elenco completo (riscritto)
 * - Totale (CHF): applica lo sconto vendita SOLO agli articoli con is_discounted = 0
 * - Gli storni (status = 'negative') che contengono articoli scontati mostrano totale 0.00 CHF (robusto)
 * - Query ottimizzata: subtotali per vendita calcolati via subquery (niente N query per riga)
 */

$connection = DBConnection::get_db_connection();
$q = $_GET['q'] ?? "";

// Campi ricercabili: manteniamo gli alias s./c. dove necessario
$searchQuery = Pagination::build_search_query($q, [
    "s.sale_id", "s.username", "s.customer_id", "c.first_name", "c.last_name"
]);

// Elimina vendite senza righe (best-effort)
try {
    $sql = "DELETE FROM sales WHERE sale_id NOT IN (SELECT DISTINCT sale_id FROM sales_items)";
    $stmt = $connection->prepare($sql);
    $stmt->execute([]);
} catch (PDOException $e) {
    // opzionale: log
}

// Conteggio totale righe per la paginazione
$countSql = "SELECT COUNT(*) 
             FROM sales s 
             LEFT JOIN customers c ON s.customer_id = c.customer_id 
             WHERE " . $searchQuery['text'];
$countStmt = $connection->prepare($countSql);
$countStmt->execute($searchQuery['params']);
$totalRows = (int)$countStmt->fetchColumn();

// Paginazione
$pagination = new Pagination($totalRows, pageSize:50);

// Dati principali + subtotali con subquery (in centesimi)
$sql = "
SELECT
    s.*,
    c.first_name, 
    c.last_name,

    -- Subtotale scontabile: solo righe con is_discounted = 0
    COALESCE((
        SELECT SUM(si.quantity * si.price)
        FROM sales_items si
        WHERE si.sale_id = s.sale_id AND (si.is_discounted = 0 OR si.is_discounted IS NULL)
    ), 0) AS subtotal_discountable,

    -- Subtotale non scontabile: righe con is_discounted = 1
    COALESCE((
        SELECT SUM(si.quantity * si.price)
        FROM sales_items si
        WHERE si.sale_id = s.sale_id AND si.is_discounted = 1
    ), 0) AS subtotal_nondiscountable,

    -- Flag presenza articoli scontati
    EXISTS(
        SELECT 1 FROM sales_items si 
        WHERE si.sale_id = s.sale_id AND si.is_discounted = 1
    ) AS has_discounted

FROM sales s
LEFT JOIN customers c ON s.customer_id = c.customer_id
WHERE " . $searchQuery['text'] . "
ORDER BY s.created_at DESC, s.closed_at DESC
" . $pagination->get_sql();

$stmt = $connection->prepare($sql);
$stmt->execute($searchQuery['params']);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Vendite</h1>

<p></p>
<a href="/index.php?page=sales_add" class="btn btn-primary"><i class="fa-solid fa-plus"></i></a>

<p>&nbsp;</p>

<form action="/index.php" method="GET" style="display: flex; align-items: center;">
    <div class="input-group mb-3">
        <input type="text" class="form-control" name="q" placeholder="Cerca vendita"
               value="<?php echo htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES); ?>"
               style="flex-grow: 1; margin-right: 8px;">
        <br>
        <button type="submit" class="btn btn-secondary "><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>
    <input type="hidden" name="page" value="<?php echo htmlspecialchars($_GET['page'] ?? 'sales_list', ENT_QUOTES); ?>">
</form>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Data e ora apertura</th>
            <th>Data e ora chiusura</th>
            <th>Cliente</th>
            <th>Totale (CHF)</th>
            <th>Metodo di pagamento</th>
            <th>Operatore</th>
            <th>Azioni</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($sales as $sale): ?>
            <?php
            $saleId   = $sale['sale_id'];
            $sign     = ($sale['status'] === "negative") ? -1 : 1;

            $absSubtotalDiscountable    = (int)$sale['subtotal_discountable'];    // centesimi
            $absSubtotalNonDiscountable = (int)$sale['subtotal_nondiscountable']; // centesimi
            $hasDiscounted              = ((int)$sale['has_discounted'] === 1);

            // Se è storno e (erroneamente) ci sono articoli scontati, mostra 0
            if ($sale['status'] === 'negative' && $hasDiscounted) {
                $totalSigned = 0;
            } else {
                // Applica sconto vendita SOLO sulla quota scontabile
                $discountVal  = (float)($sale['discount'] ?? 0);
                $discountType = (string)($sale['discount_type'] ?? 'CHF');

                $discountCentsApplied = 0;
                if ($discountVal > 0 && $absSubtotalDiscountable > 0) {
                    if ($discountType === 'CHF') {
                        $discountCentsApplied = min((int)round($discountVal * 100), $absSubtotalDiscountable);
                    } else { // "%"
                        $discountCentsApplied = (int) floor($absSubtotalDiscountable * ($discountVal / 100));
                    }
                }

                $absGrandTotal = ($absSubtotalDiscountable - $discountCentsApplied) + $absSubtotalNonDiscountable;
                $totalSigned   = $sign * $absGrandTotal; // centesimi con segno
            }

            ?>
            <tr class="sale-<?php echo htmlspecialchars($sale['status']); ?>">
                <?php
                Utils::print_table_row(Utils::format_datetime($sale["created_at"]));
                Utils::print_table_row(empty($sale['closed_at']) ? "-" : "<b>" . Utils::format_datetime($sale['closed_at'])  . "</b>");
                Utils::print_table_row(($sale['first_name'] !== null) ? htmlspecialchars($sale['first_name'] . " " . $sale['last_name']) : "<i>Esterno</i>");
                Utils::print_table_row(Utils::format_price($totalSigned)); // <-- Totale adattato
                Utils::print_table_row(Utils::format_payment_method($sale['payment_method'] ?? "-") ?: "-");
                Utils::print_table_row(Auth::get_fullname_by_username($sale['username']));

                switch ($sale['status']) {
                    case "completed":
                        Utils::print_table_row(
                            class: "text-start",
                            data: "<a href='/index.php?page=sales_details&sale_id={$saleId}' class='btn btn-sm btn-outline-primary' title='Visualizza'>
                                    <i class='fa fa-eye'></i>
                                  </a> 
                                  &nbsp; 
                                  <a href='javascript:void(0)' onclick='askCancelType(\"{$saleId}\")' class='btn btn-sm btn-outline-danger' title='Storna'>
                                    <i class='fa fa-trash'></i>
                                  </a>"
                        );
                        break;
                    case "open":
                        Utils::print_table_row(
                            class: "text-start",
                            data: "<a href='/index.php?page=sales_add&sale_id={$saleId}' class='btn btn-sm btn-outline-primary' title='Modifica'>
                                    <i class='fa fa-edit'></i>
                                  </a>"
                        );
                        break;
                    default:
                        Utils::print_table_row(
                            class: "text-start",
                            data: "<a href='/index.php?page=sales_details&sale_id={$saleId}' class='btn btn-sm btn-outline-primary' title='Visualizza'>
                                    <i class='fa fa-eye'></i>
                                  </a>"
                        );
                        break;
                }
                ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<small class="text-body-secondary"><?php echo (int)$pagination->get_total_rows(); ?> risultati.</small>
<?php echo $pagination->get_page_links(); ?>

<script>
function askCancelType(sale_id) {
    window.location.href = "/actions/sales/cancel_partial.php?sale_id=" + encodeURIComponent(sale_id);

   /* bootbox.prompt({

        title: "Selezionare il tipo di storno",
        message: "<div class='alert alert-primary'><i class='fa-solid fa-circle-info'></i> Lo storno parziale cancella la vendita in questione e ne crea una nuova copia modificabile.</div>",
        size: "lg",
        inputType: 'radio',
        inputOptions: [
            { text: 'Totale', value: 'total' },
            { text: 'Parziale', value: 'partial' }
        ],
        callback: function (result) {
            if (!result) return;
            if (result === "total") {
                window.location.href = "/actions/sales/cancel_total.php?sale_id=" + encodeURIComponent(sale_id);
            } else {
                */
            /* }
        }
    });
    */
}

</script>
