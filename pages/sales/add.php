<?php

/**
 * Sales Management Page (UI aligned with the previous "Dettagli vendita" rewrite)
 * - Applies sale-level discount ONLY to items with is_discounted = false
 * - Discounted items CANNOT be returned (negative sales) -> hard block + end
 * - Joins product, brand, variant in one query (no per-row DB hits)
 * - Shows "Già scontato?" badge column + 3-card subtotals block
 * - JS recomputes grand total client-side using the same discountable-only logic
 */


$dbConnection = DBConnection::get_db_connection();
$isNegative = isset($_GET['negative']) && $_GET['negative'] === "1";

// Initialize or get sale ID
if (!isset($_GET['sale_id']) && !isset($saleId)) {
    $sql = "INSERT INTO sales (sale_id, created_at, username) VALUES (UUID(), NOW(), ?)";
    $stmt = $dbConnection->prepare($sql);
    $stmt->execute([Auth::get_username()]);

    $sql = "SELECT sale_id FROM sales WHERE username = ? AND status = 'open' ORDER BY created_at DESC LIMIT 1";
    $stmt = $dbConnection->prepare($sql);
    $stmt->execute([Auth::get_username()]);
    $saleId = $stmt->fetchColumn(0);
} else {
    $saleId = $_GET['sale_id'];
}

// Get sale data
$sql = "SELECT * FROM sales WHERE sale_id = ?";
$stmt = $dbConnection->prepare($sql);
$stmt->execute([$saleId]);
$sale = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

// Items with product/brand/full_price + variant joined
$sql = "
    SELECT i.*,
           p.name AS product_name,
           p.brand_id,
           p.full_price,          -- in cents
           b.name AS brand_name,
           pv.color, pv.size
    FROM sales_items i
    JOIN products p USING(product_id)
    JOIN brands  b USING(brand_id)
    LEFT JOIN product_variants pv
      ON pv.product_id = i.product_id AND pv.variant_id = i.variant_id
    WHERE i.sale_id = ?
";
$stmt = $dbConnection->prepare($sql);
$stmt->execute([$saleId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Compute absolute subtotals (cents, positive). Apply sign at the end for display.
$containsDiscountedItems = false;
$absSubtotalDiscountable = 0;      // items with is_discounted == false
$absSubtotalNonDiscountable = 0;   // items with is_discounted == true
$pieces = 0;

foreach ($items as $item) {
    $qty = (int)$item['quantity'];
    $lineCents = (int)$item['price'] * $qty;
    $isDisc = !empty($item['is_discounted']) && (int)$item['is_discounted'] === 1;
    if ($isDisc) {
        $absSubtotalNonDiscountable += $lineCents;
    } else {
        $absSubtotalDiscountable += $lineCents;
    }
    $containsDiscountedItems = $containsDiscountedItems || $isDisc;
    $pieces += $qty;
}

$absSubtotalAll = $absSubtotalDiscountable + $absSubtotalNonDiscountable;

// BUSINESS RULE: discounted items cannot be returned
if ($isNegative && $containsDiscountedItems) {
    Utils::print_error("Operazione non consentita: gli articoli scontati non possono essere restituiti.");
    goto end;
}

?>



<?php

// Discount values from DB (when selling you can edit, when returning just show)
$discountValue = (float)($sale["discount"] ?? 0);              // numeric
$discountType  = (string)($sale["discount_type"] ?? "CHF");    // "CHF" or "%"

// Compute absolute discounted total (cents). Apply discount only to discountable bucket.
$discountCentsApplied = 0;
if ($discountValue > 0 && $absSubtotalDiscountable > 0) {
    if ($discountType === "CHF") {
        $discountCentsApplied = min((int)round($discountValue * 100), $absSubtotalDiscountable);
    } else { // "%"
        $discountCentsApplied = (int) floor($absSubtotalDiscountable * ($discountValue / 100));
    }
}
$absGrandTotal = ($absSubtotalDiscountable - $discountCentsApplied) + $absSubtotalNonDiscountable;

// Signed amounts for display
$factor = $isNegative ? -1 : 1;
$subtotalSigned = $factor * $absSubtotalAll;
$grandTotalSigned = $factor * $absGrandTotal;

// Precompute helper for links
$negParam = $isNegative ? '1' : '0';
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isNegative ? 'Storna vendita' : 'Aggiungi / Modifica vendita'; ?></title>
</head>

<body>
    <h1><?php echo $isNegative ? 'Storna vendita' : 'Aggiungi / Modifica vendita'; ?></h1>

    <p><b>Operatore:</b> <?php echo htmlspecialchars(Auth::get_fullname()); ?></p>

    <!-- Product Form -->
    <form id="form-new-item" action="actions/sales/add_item.php" method="POST">
        <input type="hidden" name="sale_id" value="<?php echo htmlspecialchars($saleId); ?>">
        <input type="hidden" name="negative" value="<?php echo $negParam; ?>">
        <input type="hidden" name="old_sale_id" value="<?php echo isset($_GET['old_sale_id']) ? ($_GET['old_sale_id']) : ''; ?>">

        <label for="sku">Codice Articolo *</label>
        <div class="input-group mb-3">
            <span class="input-group-text">
                <i class="fa-solid fa-barcode"></i>
            </span>
            <input type="text"
                minlength="13"
                maxlength="13"
                name="sku"
                class="form-control tt"
                style="font-size: 150%;"
                required
                id="sku-input"
                autocomplete="off">
        </div>
    </form>

    <br>

    <!-- Sales Items Table -->
    <h2>Voci di vendita</h2>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Codice Articolo</th>
                    <th>Nome</th>
                    <th>Brand</th>
                    <th>Variante</th>
                    <th class="text-end">Quantità</th>
                    <th class="text-end">Prezzo unit. (CHF)</th>
                    <th class="text-end">Prezzo totale (CHF)</th>
                    <th class="text-center">Già scontato?</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <?php
                    $sku = InternalNumbers::get_sku($item["product_id"], $item["variant_id"]);
                    $variant = trim(($item['color'] ?? '') . " / " . ($item['size'] ?? ''));
                    $qty = (int)$item['quantity'];
                    $unitCents  = (int)$item['price'];
                    $totalCents = (int)$item['total_price'];
                    $fullUnitCents  = (int)$item['full_price'];
                    $fullTotalCents = $fullUnitCents * $qty;
                    $isDisc = !empty($item['is_discounted']) && (int)$item['is_discounted'] === 1;

                    $unitSigned  = $unitCents  * $factor;
                    $totalSigned = $totalCents * $factor;
                    $fullUnitSigned  = $fullUnitCents  * $factor;
                    $fullTotalSigned = $fullTotalCents * $factor;
                    ?>
                    <tr>
                        <?php Utils::print_table_row($sku); ?>
                        <?php Utils::print_table_row($item['product_name']); ?>
                        <?php Utils::print_table_row($item['brand_name']); ?>
                        <?php Utils::print_table_row($variant); ?>
                        <td class="text-end"><?php echo $qty; ?></td>
                        <td class="text-end">
                            <?php if ($isDisc): ?>
                                (<span class="strikethrough"><?php echo Utils::format_price($fullUnitSigned); ?></span>)&nbsp;&nbsp;<?php echo Utils::format_price($unitSigned); ?>
                            <?php else: ?>
                                <?php echo Utils::format_price($unitSigned); ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ($isDisc): ?>
                                (<span class="strikethrough"><?php echo Utils::format_price($fullTotalSigned); ?></span>)&nbsp;&nbsp;<?php echo Utils::format_price($totalSigned); ?>
                            <?php else: ?>
                                <?php echo Utils::format_price($totalSigned); ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($isDisc): ?>
                                <span class="badge bg-secondary">Sì</span>
                            <?php else: ?>
                                <span class="badge bg-success">No</span>
                            <?php endif; ?>
                        </td>
                        <?php
                        $actions = <<<EOD
                        <a href="actions/sales/decrease_item_quantity.php?sale_id={$saleId}&sku={$sku}&negative={$negParam}" class="btn btn-sm btn-warning">
                            <i class="fa fa-minus"></i>
                        </a>
                        <a onclick="deleteItem('{$sku}')" class="btn btn-sm btn-danger">
                            <i class="fa fa-trash"></i>
                        </a>
EOD;
                        Utils::print_table_row($actions);
                        ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p><?php echo (int)$pieces; ?> pezzi.</p>

    <hr>

    <!-- Subtotals block (same UI pattern as the detail page) -->
    <div class="row g-3">
        <div class="col-md-4">
            <div class="p-3 border rounded">
                <div class="b">Subtotale (tutti gli articoli)</div>
                <div class="display-6"><?php echo Utils::format_price($subtotalSigned); ?> CHF</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 border rounded">
                <div class="b">Quota scontabile</div>
                <div class="h3 m-0"><?php echo Utils::format_price($factor * $absSubtotalDiscountable); ?> CHF</div>
                <small class="text-muted">Gli articoli con <i>Già scontato? = No</i></small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 border rounded">
                <div class="b">Quota non scontabile</div>
                <div class="h3 m-0"><?php echo Utils::format_price($factor * $absSubtotalNonDiscountable); ?> CHF</div>
                <small class="text-muted">Gli articoli già scontati non ricevono lo sconto vendita</small>
            </div>
        </div>
    </div>

    <p>&nbsp;</p>

    <?php if (!$isNegative): ?>
        <!-- Customer Section -->
        <h2>Cliente</h2>
        <p>Scannerizzare la carta cliente se disponibile oppure cercare un cliente per nome e cognome</p>

        <label for="customer_number">N. Cliente</label>
        <select name="customer_number" id="customer-select" class="form-control"></select>

        <p>&nbsp;</p>

        <!-- Discount Section -->
        <h2>Sconto</h2>
        <p>Lo sconto si applica <b>solo</b> agli articoli non già scontati.</p>

        <div class="row d-flex align-items-center">
            <div class="col-10">
                <label for="discount" class="form-label">Sconto</label>
                <div class="input-group mb-3">
                    <input type="number"
                        class="form-control"
                        value="<?php echo htmlspecialchars($sale["discount"] ?? 0); ?>"
                        min="0"
                        step="0.01"
                        name="discount"
                        id="discount-input"
                        required
                        oninput="computeGrandTotal()">
                    <span class="input-group-text" id="discount-input-group-text"><?php echo ($sale["discount_type"] ?? 'CHF') === '%' ? '%' : 'CHF'; ?></span>
                </div>

                <!-- >>> AGGIUNTO: Base Sconto + Sconto applicato (server + JS) -->
                <div id="discount-info" class="text-muted small">
                    Base sconto: <span id="discount-base"><?php echo Utils::format_price($absSubtotalDiscountable); ?> CHF</span><br>
                    Sconto applicato: <span id="discount-applied"><?php echo Utils::format_price($discountCentsApplied); ?> CHF</span>
                </div>
                <!-- <<< FINE AGGIUNTA -->
            </div>
            <div class="col-2 d-flex align-items-center gap-3">
                <div class="form-check">
                    <input class="form-check-input"
                        type="radio"
                        name="discount_type"
                        onclick="setInputGroupText(0)"
                        value="CHF"
                        <?php echo ($sale["discount_type"] ?? '') === "CHF" ? "checked" : ""; ?>>
                    <label class="form-check-label">CHF</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input"
                        type="radio"
                        name="discount_type"
                        onclick="setInputGroupText(1)"
                        value="%"
                        <?php echo ($sale["discount_type"] ?? '') === "%" ? "checked" : ""; ?>>
                    <label class="form-check-label">%</label>
                </div>
            </div>
        </div>

        <a href="#"
            onclick="window.open('index.php?page=giftcards_redeem&sale_id=<?php echo urlencode($saleId); ?>', 'GiftcardPopup', 'width=600,height=600,scrollbars=yes,resizable=yes'); return false;"
            class="btn btn-sm btn-secondary">
            Usa Carta Regalo
        </a>
        <a href="#"
            onclick="window.open('index.php?page=discount-codes_redeem&sale_id=<?php echo urlencode($saleId); ?>', 'Discount-CodePopup', 'width=600,height=600,scrollbars=yes,resizable=yes'); return false;"
            class="btn btn-sm btn-secondary">
            Usa Codice Sconto
        </a>
    <?php else: ?>
        <p><b>Nota:</b> Storno di vendita. Gli articoli scontati non sono ammessi nello storno.</p>

        <?php if (($sale["discount_type"] ?? '') === "CHF"): ?>
            <!-- Storno con sconto assoluto (CHF): consentiamo la modifica manuale -->
            <h2>Sconto (storno, modificabile perché in CHF)</h2>
            <div class="row d-flex align-items-center">
                <div class="col-10">
                    <label for="discount" class="form-label">Sconto</label>
                    <div class="input-group mb-3">
                        <input type="number"
                            class="form-control"
                            value="<?php echo htmlspecialchars($sale["discount"] ?? 0); ?>"
                            min="0"
                            step="0.01"
                            name="discount"
                            id="discount-input"
                            required
                            oninput="computeGrandTotal()">
                        <span class="input-group-text" id="discount-input-group-text">CHF</span>
                    </div>

                    <div id="discount-info" class="text-muted small">
                        Base sconto: <span id="discount-base"><?php echo Utils::format_price($absSubtotalDiscountable); ?> CHF</span><br>
                        Sconto applicato: <span id="discount-applied"><?php echo Utils::format_price($discountCentsApplied); ?> CHF</span>
                    </div>
                </div>
                <div class="col-2 d-flex align-items-center gap-3">
                    <!-- In storno blocchiamo il tipo sconto a CHF -->
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="discount_type" value="CHF" checked disabled>
                        <label class="form-check-label">CHF</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="discount_type" value="%" disabled>
                        <label class="form-check-label">%</label>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Storno con sconto %: resta sola lettura -->
            <p>Sconto registrato: <?php echo htmlspecialchars($sale["discount"] ?? 0); ?> <?php echo htmlspecialchars($sale["discount_type"] ?? ''); ?></p>
        <?php endif; ?>
    <?php endif; ?>


    <hr>

    <p class="display-6 underline b" id="grand-total">
        Totale: <?php echo Utils::format_price($grandTotalSigned); ?> CHF
    </p>

    <p>&nbsp;</p>

    <a class="btn btn-primary"
        href="index.php?page=sales_pay&sale_id=<?php echo urlencode($saleId); ?>&negative=<?php echo $negParam; ?>">
        Vai al pagamento
    </a>

    <script>
        // Vars for dynamic recompute (mirror server logic)
        const SALE_ID = '<?php echo htmlspecialchars($saleId, ENT_QUOTES); ?>';
        const IS_NEGATIVE = <?php echo $isNegative ? 'true' : 'false'; ?>;
        let IS_RETURN_EDITABLE = IS_NEGATIVE && (discountType === 'CHF');



        // Absolute subtotals in cents (positive)
        const ABS_SUBTOTAL_DISCOUNTABLE = <?php echo (int)$absSubtotalDiscountable; ?>;
        const ABS_SUBTOTAL_NON_DISCOUNTABLE = <?php echo (int)$absSubtotalNonDiscountable; ?>;

        // DB discount used as initial value
        const DB_DISCOUNT = <?php echo (float)($sale["discount"] ?? 0); ?>;
        let discountType = '<?php echo htmlspecialchars($sale["discount_type"] ?? "CHF", ENT_QUOTES); ?>';

        document.addEventListener('DOMContentLoaded', function() {
            initializeForm();
            initializeCustomerSelect();
            computeGrandTotal();
        });

        // Debounce per evitare troppe chiamate ravvicinate
let _updDiscTimer = null;

/**
 * Aggiorna lo sconto della vendita lato server.
 * - In storno (%): non fa nulla (bloccato).
 * - In storno (CHF): permette la modifica (forza discount_type="CHF").
 * - In vendita: aggiorna sempre (CHF o %).
 * @param {number|string} rawDiscount - valore sconto inserito (es. "12,50" o 12.5)
 * @param {boolean} immediate - se true salta il debounce
 */
function updateDiscount(rawDiscount, immediate = false) {
  // Normalizza numero (supporta "12,34")
  const d = (() => {
    if (rawDiscount === null || rawDiscount === undefined) return 0;
    const n = parseFloat(String(rawDiscount).replace(",", "."));
    if (!Number.isFinite(n) || n < 0) return 0;
    return Math.round(n * 100) / 100; // 2 decimali
  })();

  // Regole storno
  if (IS_NEGATIVE && discountType !== 'CHF') {
    // Storno con %: non consentito aggiornare
    return;
  }

  const doSend = () => {
    const fd = new FormData();
    fd.append("sale_id", SALE_ID);
    fd.append("discount", d.toString());
    fd.append("discount_type", (IS_NEGATIVE ? "CHF" : (discountType === "%" ? "%" : "CHF")));
    fd.append("negative", IS_NEGATIVE ? "1" : "0");

    fetch("/actions/sales/update_discount.php", {
      method: "POST",
      credentials: "include",
      body: fd
    }).catch(() => { /* silenzio: UI resta responsive anche offline */ });
  };

  if (immediate) {
    doSend();
  } else {
    clearTimeout(_updDiscTimer);
    _updDiscTimer = setTimeout(doSend, 200);
  }
}

        function initializeForm() {
            const skuInput = document.getElementById("sku-input");
            const form = document.getElementById("form-new-item");
            if (skuInput) {
                skuInput.focus();
                skuInput.addEventListener("input", function() {
                    if (skuInput.value.length === 13) {
                        form.submit();
                    }
                });
            }
        }

        function initializeCustomerSelect() {
            if (typeof $ !== 'undefined' && $("#customer-select").length) {
                $("#customer-select").select2({
                    language: "it",
                    theme: "bootstrap-5",
                    allowClear: true,
                    placeholder: "",
                    ajax: {
                        url: '/actions/customers/list.php',
                        dataType: 'json',
                        processResults: function(data) {
                            return {
                                results: data.results.map(function(customer) {
                                    return {
                                        id: customer.customer_number,
                                        text: customer.first_name + " " + customer.last_name + " (" + new Date(customer.birth_date).toLocaleDateString() + ")"
                                    };
                                })
                            };
                        }
                    }
                }).on("change", function(e) {
                    updateCustomer(e.target.value);
                });
            }
            if (localStorage.getItem("last_sale_customer_number") !== null) {
                const lastCustomerNumber = localStorage.getItem("last_sale_customer_number");
                const customerSelect = document.getElementById("customer-select");
                if (customerSelect) {
                    fetch("/actions/customers/list.php?term=" + lastCustomerNumber, {
                        method: "GET",
                        credentials: "include"
                    }).then(response => response.json()).then(data => {
                        const customer = data.results[0];
                        if (customer === undefined) return;
                        const option = new Option(
                            customer.first_name + " " + customer.last_name + " (" + new Date(customer.birth_date).toLocaleDateString() + ")",
                            lastCustomerNumber,
                            true,
                            true
                        );
                        customerSelect.appendChild(option).trigger('change');
                    });
                }
            }
        }

        function updateCustomer(customerNumber) {
            const formData = new FormData();
            formData.append("sale_id", SALE_ID);
            formData.append("customer_number", customerNumber);
            localStorage.setItem("last_sale_customer_number", customerNumber);

            fetch("/actions/sales/update_customer.php", {
                method: "POST",
                body: formData,
                credentials: "include"
            }).catch(function(error) {
                console.error('Error updating customer:', error);
            });
        }

        function setInputGroupText(type) {
            // In storno non permettiamo di cambiare il tipo sconto
            if (IS_NEGATIVE) return;

            const inputGroupText = document.getElementById("discount-input-group-text");
            const discountInput = document.getElementById("discount-input");

            if (type === 0) {
                inputGroupText.innerText = "CHF";
                discountInput?.removeAttribute("max");
                discountType = "CHF";
            } else {
                inputGroupText.innerText = "%";
                discountInput?.setAttribute("max", 100);
                discountType = "%";
            }
            IS_RETURN_EDITABLE = IS_NEGATIVE && (discountType === 'CHF');
            computeGrandTotal();
        }

        function computeGrandTotal() {
            const discountInput = document.getElementById("discount-input");

            // Durante storno, leggiamo l'input solo se tipo sconto è CHF (editabile)
            const canEditNow = (!IS_NEGATIVE) || (IS_NEGATIVE && discountType === 'CHF');
            const discount = (discountInput && canEditNow) ?
                (parseFloat(discountInput.value) || 0) :
                DB_DISCOUNT;

            // Applica lo sconto solo alla quota scontabile
            let discountCentsApplied = 0;
            if (discount > 0 && ABS_SUBTOTAL_DISCOUNTABLE > 0) {
                if (discountType === "CHF") {
                    discountCentsApplied = Math.min(Math.round(discount * 100), ABS_SUBTOTAL_DISCOUNTABLE);
                } else {
                    discountCentsApplied = Math.floor(ABS_SUBTOTAL_DISCOUNTABLE * (discount / 100));
                }
            }

            const absGrand = (ABS_SUBTOTAL_DISCOUNTABLE - discountCentsApplied) + ABS_SUBTOTAL_NON_DISCOUNTABLE;
            const signedGrand = IS_NEGATIVE ? -absGrand : absGrand;

            const el = document.getElementById("grand-total");
            if (el) el.innerText = "Totale: " + (signedGrand / 100).toFixed(2) + " CHF";

            // Aggiorna info sconto
            const baseEl = document.getElementById("discount-base");
            const appliedEl = document.getElementById("discount-applied");
            if (baseEl) baseEl.innerText = (ABS_SUBTOTAL_DISCOUNTABLE / 100).toFixed(2) + " CHF";
            if (appliedEl) appliedEl.innerText = (discountCentsApplied / 100).toFixed(2) + " CHF";

            // Persistenza:
            // - vendita: sempre
            // - storno: solo se tipo sconto è CHF (editabile)
            if (!IS_NEGATIVE || (IS_NEGATIVE && discountType === 'CHF')) {
                updateDiscount(discount);
            }
        }


        function deleteItem(sku) {
            const message = "Sei sicuro di voler eliminare il prodotto dal carrello?";
            const confirmDelete = function() {
                const url = "/actions/sales/delete_item.php?sale_id=" + encodeURIComponent(SALE_ID) +
                    "&sku=" + encodeURIComponent(sku) +
                    "&negative=" + (IS_NEGATIVE ? '1' : '0');
                window.location.href = url;
            };

            if (typeof bootbox !== 'undefined') {
                bootbox.confirm({
                    title: "Elimina Prodotto",
                    message: message,
                    callback: function(result) {
                        if (result) confirmDelete();
                    }
                });
            } else {
                if (confirm(message)) confirmDelete();
            }
        }
    </script>

    <?php end: ?>
</body>

</html>