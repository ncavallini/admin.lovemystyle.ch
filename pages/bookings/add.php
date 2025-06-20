<h1>Aggiungi Riservazione</h1>
<p class="lead">Riservazione fino: <?php echo date("d/m/Y, H:i", strtotime(Utils::get_next_closing_datetime())) ?> </p>
<form action="actions/bookings/add.php" method="POST">
    <label for="customer_id">Cliente</label>
    <select required name="customer_id" id="customer-select" class="form-control">
    </select>
    <br>
    <label for="sku">Codice Articolo (scansionare)</label>
    <input type="text" minlength="13" maxlength="13" name="sku" id="sku-input" class="form-control" placeholder="Scansiona codice a barre o inserisci manualmente" required>
    <br>
    <button type="submit" class="btn btn-primary">Aggiungi Riservazione</button>
</form>

<script>
    $(document).ready(() => {
        $("#customer-select").select2({
            language: "it",
            theme: "bootstrap-5",
            allowClear: true,
            placeholder: "",
            ajax: {
                url: '/actions/customers/list.php',
                dataType: 'json',
                processResults: (data) => {
                    return {
                        results: data.results.map((customer) => {
                            return {
                                id: customer.customer_id,
                                text: customer.first_name + " " + customer.last_name + " (" + customer.tel + ", " + customer.email + ")"
                            }
                        })
                    }
                },
            },
        })})
</script>