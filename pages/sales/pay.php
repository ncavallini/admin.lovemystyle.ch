<?php
$dbconnection = DBConnection::get_db_connection();
$saleId = $_GET['sale_id'] ?? "";
$sql = "SELECT * FROM sales WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$sale = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$sale) {
    Utils::print_error("Vendita non trovata");
    goto end;
}

$negative = isset($_GET['negative']) ? (int)$_GET['negative'] : 0;
$isReturnEditable = ($negative === 1) && (($sale['discount_type'] ?? '') === 'CHF');

// =========================
// ITEMS: prezzi in centesimi + split scontabile/non scontabile
// =========================
$sql = "SELECT price, quantity, is_discounted FROM sales_items WHERE sale_id = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$saleId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$absSubtotalDiscountable = 0;      // is_discounted == 0
$absSubtotalNonDiscountable = 0;   // is_discounted == 1
foreach ($items as $it) {
    $line = (int)$it['price'] * (int)$it['quantity']; // CENTESIMI
    $isDisc = !empty($it['is_discounted']) && (int)$it['is_discounted'] === 1;
    if ($isDisc) $absSubtotalNonDiscountable += $line;
    else         $absSubtotalDiscountable   += $line;
}
$absSubtotalAll = $absSubtotalDiscountable + $absSubtotalNonDiscountable;

// =========================
// Sconto (server side, coerente con pagina carrello)
// =========================
$discountValue = (float)($sale['discount'] ?? 0.0);
$discountType  = (string)($sale['discount_type'] ?? 'CHF');

$discountCentsApplied = 0;
if ($discountValue > 0 && $absSubtotalDiscountable > 0) {
    if ($discountType === 'CHF') {
        $discountCentsApplied = min((int)round($discountValue * 100), $absSubtotalDiscountable);
    } else { // %
        $discountCentsApplied = (int) floor($absSubtotalDiscountable * ($discountValue / 100.0));
    }
}
$absGrandTotalCents = ($absSubtotalDiscountable - $discountCentsApplied) + $absSubtotalNonDiscountable;

// =========================
// Cambio
// - rate = EUR per 1 CHF
// - EUR display: CHF * eur_per_chf
// =========================
$sql = "SELECT rate FROM exchange_rates WHERE currency = 'EUR'";
$stmt = $dbconnection->prepare($sql);
$stmt->execute();
$eur_per_chf = (float)$stmt->fetchColumn();
if ($eur_per_chf <= 0) { $eur_per_chf = 1.0; }
$chf_per_eur = 1.0 / $eur_per_chf;

$factor = $negative ? -1 : 1;

// Valori display iniziali (si aggiorneranno via JS se l’utente cambia lo sconto in storno)
$total_chf_display = Utils::format_price($factor * $absGrandTotalCents);
$total_eur_display = Utils::format_price($factor * $absGrandTotalCents * $eur_per_chf);
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<title>Pagamento</title>
</head>
<body>

<h1><?php echo $negative ? 'Rimborso' : 'Pagamento'; ?></h1>

<?php if ($isReturnEditable): ?>
  <!-- In storno + CHF: sconto editabile -->
   <div style="visibility: none;" hidden class="hidden">
  <div class="mb-3">
    <h2>Sconto (storno, modificabile perché in CHF)</h2>
    <div class="row d-flex align-items-center">
      <div class="col-10">
        <label for="discount-input" class="form-label">Sconto</label>
        <div class="input-group mb-2">
          <input type="number"
                 class="form-control"
                 value="<?php echo htmlspecialchars($sale['discount'] ?? 0, ENT_QUOTES); ?>"
                 min="0"
                 step="0.01"
                 id="discount-input"
                 required>
          <span class="input-group-text">CHF</span>
        </div>
        <div class="text-muted small">
          Base sconto: <span id="discount-base"><?php echo Utils::format_price($absSubtotalDiscountable); ?> CHF</span><br>
          Sconto applicato: <span id="discount-applied"><?php echo Utils::format_price($discountCentsApplied); ?> CHF</span>
        </div>
      </div>
      <div class="col-2 d-flex align-items-center gap-3">
        <div class="form-check">
          <input class="form-check-input" type="radio" checked disabled>
          <label class="form-check-label">CHF</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" disabled>
          <label class="form-check-label">%</label>
        </div>
      </div>
    </div>
  </div>
</div>
<?php else: ?>
  <!-- Altri casi: promemoria sconto -->
  <p class="text-muted">
    Sconto registrato: <b><?php echo htmlspecialchars($sale['discount'] ?? 0); ?> <?php echo htmlspecialchars($discountType); ?></b>
  </p>
<?php endif; ?>

<p class="display-6 underline" id="total-display">
  Totale da <?php echo $negative ? 'rimborsare' : 'pagare'; ?>:
  <span id="total-chf"><?php echo $total_chf_display; ?></span> CHF
  <small>(<span id="total-eur"><?php echo $total_eur_display; ?></span> €)</small>
</p>

<form action="actions/sales/pay.php" method="POST" id="form-pay">
  <input type="hidden" name="sale_id" value="<?php echo htmlspecialchars($saleId, ENT_QUOTES) ?>">
  <input type="hidden" name="total" id="total-decimal" value="<?php echo number_format($absGrandTotalCents / 100.0, 2, '.', ''); ?>">
  <input type="hidden" name="negative" value="<?php echo (int)$negative ?>">

  <!-- Hidden per backend (importi effettivi in CHF decimali) -->
  <input type="hidden" name="cash_effective_paid_chf" id="cash_effective_paid_chf" value="0">
  <input type="hidden" name="pos_paid_chf" id="pos_paid_chf" value="0">

  <div class="card mb-3">
    <div class="card-header fw-bold"><i class="fa-solid fa-money-bill"></i> <?php echo $negative ? 'Contanti da restituire' : 'Contanti'; ?></div>
    <div class="card-body">
      <div class="row g-2 align-items-center">
        <div class="col-md-6">
          <label for="cash_given" class="form-label"><?php echo $negative ? 'Contanti da restituire' : 'Importo consegnato'; ?></label>
          <div class="input-group">
            <input type="number" step="0.01" min="0" id="cash_given" name="cash_tendered" class="form-control" placeholder="0.00">
            <span class="input-group-text">
              <select name="cash_currency" id="cash_currency" class="form-select">
                <option value="CHF" selected>CHF</option>
                <option value="EUR">€</option>
              </select>
            </span>
          </div>
          <div class="form-text">
            <?php if ($negative): ?>
              Inserisci quanto rimborsi in contanti (CHF oppure EUR).
            <?php else: ?>
              Inserisci quanto il cliente ti porge in contanti (in CHF oppure EUR). Se non usa contanti, lascia 0.
            <?php endif; ?>
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Resto</label>
          <p id="cash_change_p" class="lead m-0" hidden>
            Resto: <span id="cash_change_span"></span>
          </p>
          <p id="cash_needed_p" class="text-muted m-0" hidden>
            Contanti necessari (senza resto): <span id="cash_needed_span"></span>
          </p>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header fw-bold"><i class="fa-solid fa-credit-card"></i> <?php echo $negative ? 'Rimborso su POS (TWINT/Carta)' : 'Carta di credito / TWINT (POS)'; ?></div>
    <div class="card-body">
      <div class="row g-2 align-items-center">
        <div class="col-md-6">
          <label for="pos_amount" class="form-label"><?php echo $negative ? 'Importo di rimborso su POS (CHF)' : 'Importo su POS (CHF)'; ?></label>
          <div class="input-group">
            <span class="input-group-text">CHF</span>
            <input type="number" step="0.01" min="0" id="pos_amount" class="form-control" placeholder="0.00">
          </div>
          <div class="form-text">
            Si auto-compila per far quadrare il totale. Puoi anche modificarlo: l’importo in contanti si adegua di conseguenza.
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Riepilogo split</label>
          <p class="m-0">
            Contanti (effettivi): <b><span id="cash_effective_span">-</span> CHF</b><br>
            POS: <b><span id="pos_effective_span">-</span> CHF</b><br>
            Somma: <b><span id="sum_effective_span">-</span> CHF</b>
          </p>
        </div>
      </div>
    </div>
  </div>

  <div class="alert alert-warning">
    <b>Attenzione!</b> La vendita diventa definitiva quando si clicca su "Conferma pagamento". L'azione è irreversibile.
    Premi il pulsante solo dopo che il pagamento è andato a buon fine.
  </div>

  <button type="submit" class="btn btn-primary" id="btn-confirm">Conferma <?php echo $negative ? 'rimborso' : 'pagamento'; ?></button>
</form>
<p>&nbsp;</p>

<?php end: ?>

<script>
(function() {
  // ===== Server vars =====
  const IS_NEGATIVE = <?php echo $negative ? 'true' : 'false' ?>;
  const EUR_PER_CHF = <?php echo json_encode($eur_per_chf, JSON_UNESCAPED_UNICODE) ?>;
  const CHF_PER_EUR = <?php echo json_encode($chf_per_eur, JSON_UNESCAPED_UNICODE) ?>;

  // Quote in centesimi (positive)
  const ABS_SUBTOTAL_DISCOUNTABLE = <?php echo (int)$absSubtotalDiscountable; ?>;
  const ABS_SUBTOTAL_NON_DISCOUNTABLE = <?php echo (int)$absSubtotalNonDiscountable; ?>;
  const DB_DISCOUNT = <?php echo (float)$discountValue; ?>;
  const DISCOUNT_TYPE = <?php echo json_encode($discountType); ?>;
  const SALE_ID = <?php echo json_encode($saleId); ?>;
  const IS_RETURN_EDITABLE = <?php echo $isReturnEditable ? 'true' : 'false'; ?>;

  // ===== DOM =====
  const totalChfSpan = document.getElementById('total-chf');
  const totalEurSpan = document.getElementById('total-eur');
  const totalDecimalHidden = document.getElementById('total-decimal');

  const cashGivenInput   = document.getElementById('cash_given');
  const cashCurrencySel  = document.getElementById('cash_currency');
  const posAmountInput   = document.getElementById('pos_amount');

  const cashChangeP      = document.getElementById('cash_change_p');
  const cashChangeSpan   = document.getElementById('cash_change_span');
  const cashNeededP      = document.getElementById('cash_needed_p');
  const cashNeededSpan   = document.getElementById('cash_needed_span');

  const cashEffectiveHidden = document.getElementById('cash_effective_paid_chf');
  const posPaidHidden       = document.getElementById('pos_paid_chf');

  const cashEffectiveSpan = document.getElementById('cash_effective_span');
  const posEffectiveSpan  = document.getElementById('pos_effective_span');
  const sumEffectiveSpan  = document.getElementById('sum_effective_span');

  const btnConfirm = document.getElementById('btn-confirm');
  const form       = document.getElementById('form-pay');

  // Sconto editabile (solo storno+CHF)
  const discountInput = document.getElementById('discount-input');
  const discountBaseEl = document.getElementById('discount-base');
  const discountAppliedEl = document.getElementById('discount-applied');

  // ===== Helpers =====
  const toCentsFromDec = (vStr) => {
    const n = parseFloat((vStr || "").toString().replace(",", "."));
    return Number.isFinite(n) ? Math.round(n * 100) : 0;
  };
  const toDecFromCents = (cents) => (Number.isFinite(cents) ? (cents / 100).toFixed(2) : "");
  const centsToLabel = (c) => (Number.isFinite(c) ? (c / 100).toFixed(2) : "-");
  const fmtLabel = (c) => {
    const v = centsToLabel(c);
    return v === "-" ? "-" : `${v} CHF`;
  };
  const fmtPrice = (c) => (Number.isFinite(c) ? (c / 100).toFixed(2) : "0.00");

  // Target dinamico (totale in centesimi)
  let targetCents = <?php echo (int)$absGrandTotalCents; ?>;

  // Calcolo grand total in base allo sconto corrente
  function computeGrandTotalCents(currentDiscount, currentType) {
    let discountCentsApplied = 0;
    if (currentDiscount > 0 && ABS_SUBTOTAL_DISCOUNTABLE > 0) {
      if (currentType === 'CHF') {
        discountCentsApplied = Math.min(Math.round(currentDiscount * 100), ABS_SUBTOTAL_DISCOUNTABLE);
      } else {
        discountCentsApplied = Math.floor(ABS_SUBTOTAL_DISCOUNTABLE * (currentDiscount / 100.0));
      }
    }
    const absGrand = (ABS_SUBTOTAL_DISCOUNTABLE - discountCentsApplied) + ABS_SUBTOTAL_NON_DISCOUNTABLE;

    // UI info (se presente)
    if (discountBaseEl) discountBaseEl.innerText = (ABS_SUBTOTAL_DISCOUNTABLE / 100).toFixed(2) + " CHF";
    if (discountAppliedEl) discountAppliedEl.innerText = (discountCentsApplied / 100).toFixed(2) + " CHF";

    return absGrand;
  }

  // Aggiorna display totale e hidden
  function updateTotalsAndDisplay() {
    // Aggiorna label CHF/EUR (con segno)
    const signed = IS_NEGATIVE ? -targetCents : targetCents;
    totalChfSpan.textContent = (signed / 100).toFixed(2);
    totalEurSpan.textContent = ((signed / 100) * EUR_PER_CHF).toFixed(2);

    // Aggiorna hidden in decimali CHF (positivo, come importo assoluto)
    totalDecimalHidden.value = (targetCents / 100).toFixed(2);

    // Ricalcola split pagamenti
    computeSplit();
  }

  // ===== Split pagamenti =====
  let lastEdited = 'cash'; // 'cash' | 'pos'

  function computeSplit() {
    const target = targetCents;

    const posInputCents = Math.max(0, toCentsFromDec(posAmountInput.value));

    const cashCurrency = cashCurrencySel.value;
    const cashTenderedDec = parseFloat((cashGivenInput.value || "0").toString().replace(",", ".")) || 0;
    const cashTenderedCentsRaw = Math.max(0, Math.round(cashTenderedDec * 100));
    const cashTenderedChfCents = (cashCurrency === 'CHF')
      ? cashTenderedCentsRaw
      : Math.round(cashTenderedCentsRaw * CHF_PER_EUR); // EUR→CHF

    let cashEffectiveCents = 0;
    let posEffectiveCents  = 0;
    let changeCents        = 0;

    if (IS_NEGATIVE) {
      // Rimborso: split libero, niente resto
      cashNeededP.hidden = true;
      cashChangeP.hidden = true;
      changeCents = 0;

      if (lastEdited === 'pos') {
        posEffectiveCents = Math.min(posInputCents, target);
        cashEffectiveCents = target - posEffectiveCents;
      } else {
        cashEffectiveCents = Math.min(cashTenderedChfCents, target);
        posEffectiveCents  = target - cashEffectiveCents;
        posAmountInput.value = toDecFromCents(posEffectiveCents);
      }
    } else if (lastEdited === 'pos') {
      posEffectiveCents = Math.min(posInputCents, target);
      const cashNeededCents = target - posEffectiveCents;

      cashEffectiveCents = Math.min(cashTenderedChfCents, cashNeededCents);
      changeCents        = Math.max(cashTenderedChfCents - cashNeededCents, 0);

      cashNeededSpan.textContent = fmtLabel(cashNeededCents);
      cashNeededP.hidden = (cashNeededCents === 0);
    } else {
      cashEffectiveCents = Math.min(cashTenderedChfCents, target);
      posEffectiveCents  = target - cashEffectiveCents;

      posAmountInput.value = toDecFromCents(posEffectiveCents);
      changeCents = Math.max(cashTenderedChfCents - cashEffectiveCents, 0);

      cashNeededSpan.textContent = fmtLabel(cashEffectiveCents);
      cashNeededP.hidden = (cashEffectiveCents === 0 || cashEffectiveCents === target);
    }

    // Riepilogo
    cashEffectiveSpan.textContent = centsToLabel(cashEffectiveCents);
    posEffectiveSpan.textContent  = centsToLabel(posEffectiveCents);
    sumEffectiveSpan.textContent  = centsToLabel(cashEffectiveCents + posEffectiveCents);

    if (!IS_NEGATIVE && changeCents > 0) {
      cashChangeSpan.textContent = fmtLabel(changeCents);
      cashChangeP.hidden = false;
    } else {
      cashChangeP.hidden = true;
    }

    // Hidden per backend (decimali CHF)
    cashEffectiveHidden.value = toDecFromCents(cashEffectiveCents);
    posPaidHidden.value       = toDecFromCents(posEffectiveCents);

    // Validazioni
    let ok = true;
    const sumEquals = (cashEffectiveCents + posEffectiveCents) === target;

    if (IS_NEGATIVE) {
      ok = sumEquals;
    } else {
      const cashCover = cashTenderedChfCents >= cashEffectiveCents;
      ok = sumEquals && cashCover;
    }
    btnConfirm.disabled = !ok;
  }

  function normalizeMoneyInput(e) {
    if (e.target.value === '') return;
    const n = parseFloat(e.target.value.replace(",", "."));
    if (Number.isFinite(n)) e.target.value = n.toFixed(2);
  }

  // ===== Eventi split =====
  document.getElementById('cash_given').addEventListener('input',  () => { lastEdited = 'cash'; computeSplit(); });
  document.getElementById('cash_given').addEventListener('change', normalizeMoneyInput);
  document.getElementById('cash_currency').addEventListener('change', () => { lastEdited = 'cash'; computeSplit(); });

  document.getElementById('pos_amount').addEventListener('input',  () => { lastEdited = 'pos'; computeSplit(); });
  document.getElementById('pos_amount').addEventListener('change', normalizeMoneyInput);

  document.getElementById("btn-confirm").addEventListener("click", (e) => {
    e.preventDefault();
    computeSplit(); // sync finale

    if (!IS_NEGATIVE) {
      const target = targetCents;
      const cashEffCents = Math.round(parseFloat(document.getElementById('cash_effective_paid_chf').value) * 100) || 0;
      const posEffCents  = Math.round(parseFloat(document.getElementById('pos_paid_chf').value) * 100) || 0;

      // ricalcola contanti consegnati in CHF cent per il controllo
      const cashDec = parseFloat((document.getElementById('cash_given').value || "0").replace(",", ".")) || 0;
      let cashGivenCents = Math.max(0, Math.round(cashDec * 100));
      if (document.getElementById('cash_currency').value === 'EUR') {
        cashGivenCents = Math.round(cashGivenCents * CHF_PER_EUR);
      }

      if ((cashEffCents + posEffCents) !== target) {
        alert("La somma Contanti + POS deve essere uguale al totale.");
        return;
      }
      if (cashGivenCents < cashEffCents) {
        alert("L’importo in contanti consegnato non copre la quota in contanti.");
        return;
      }
    }

    btnConfirm.setAttribute("disabled", "true");
    form.submit();
  });

  // ===== Sconto editabile (solo storno+CHF) =====
  function persistDiscountCHF(newDiscount) {
    const fd = new FormData();
    fd.append("sale_id", SALE_ID);
    fd.append("discount", newDiscount.toString());
    fd.append("discount_type", "CHF");
    fd.append("negative", "1"); // IMPORTANT: storno

    fetch("/actions/sales/update_discount.php", {
      method: "POST",
      credentials: "include",
      body: fd
    }).catch(() => {});
  }

  function onDiscountChanged() {
    const d = parseFloat((discountInput.value || "0").toString().replace(",", ".")) || 0;
    // ricalcola grand total
    targetCents = computeGrandTotalCents(d, 'CHF');
    // aggiorna display e hidden e split
    updateTotalsAndDisplay();
    // persisti
    persistDiscountCHF(d);
  }

  if (IS_RETURN_EDITABLE && discountInput) {
    discountInput.addEventListener('input', onDiscountChanged);
    // prime valorizzazioni (in caso di repaint)
    targetCents = computeGrandTotalCents(parseFloat(discountInput.value || "0") || 0, 'CHF');
  } else {
    // Totale in base al DB (nessun editing locale)
    targetCents = computeGrandTotalCents(DB_DISCOUNT, DISCOUNT_TYPE);
  }

  // ===== Init UI =====
  // Etichette specifiche rimborso
  if (IS_NEGATIVE) {
    document.querySelector('h1').textContent = 'Rimborso';
    // nascondi elementi non rilevanti per resto
    cashNeededP.hidden = true;
    cashChangeP.hidden = true;
  }

  // reset input e prima computazione
  document.getElementById('cash_given').value = "";
  document.getElementById('pos_amount').value = "";
  btnConfirm.disabled = true;

  updateTotalsAndDisplay();

})();
</script>
