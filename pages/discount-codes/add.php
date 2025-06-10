<h1>Aggiungi Codice Sconto</h1>
<form action="actions/discount-codes/add.php" method="POST">
    <label for="code">Codice (max. 8 caratteri)</label>
    <input type="text" autocomplete="off" id="code" name="code" class="form-control" required maxlength="8" pattern="[A-Za-z0-9]{8}" placeholder="Inserisci codice sconto (8 caratteri)" title="Il codice deve essere di 8 caratteri alfanumerici.">
    <br>
    <div class="row">
        <div class="col-6">
            <label for="from_date">Dal</label>
            <input type="date" id="from_date" name="from_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>" title="Data di inizio validità del codice sconto.">
        </div>
        <div class="col-6">
            <label for="to_date">Al</label>
            <input type="date" id="to_date" name="to_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>" title="Data di fine validità del codice sconto.">
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-6">
            <label for="discount">Sconto</label>
            <input type="number" id="discount" name="discount" class="form-control" required min="0" step="0.01" placeholder="Inserisci sconto (es. 10.00)" title="Inserisci l'importo dello sconto.">
        </div>
        <div class="col-6">
            <label for="discount_type">Tipo di Sconto</label>
            <select id="discount_type" name="discount_type" class="form-select" required>
                <option value="%">%</option>
                <option value="CHF">CHF</option>
            </select>
        </div>
    </div>
    <br>
    <button type="submit" class="btn btn-primary">Aggiungi</button>
</form>