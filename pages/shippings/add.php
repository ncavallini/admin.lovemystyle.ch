<h1>Aggiungi Spedizione</h1>
<p>Dove è diretta la spedizione?</p>
<form>
    <label for="country">Paese di destinazione</label>
    <select name="country" id="country-select" class="form-select">
        <option value="" disabled selected>Seleziona un Paese</option>
        <option value="CH">Svizzera</option>
        <option value="EXT">Estero</option>
    </select>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const countrySelect = document.getElementById('country-select');
        countrySelect.addEventListener('change', function() {
            if (this.value === 'CH') {
                window.location.href = '/index.php?page=shippings_add_ch';
            } else {
                bootbox.confirm({
                    title: "Spedizione Estero",
                    message: "",
                    buttons: {
                        cancel: {
                            label: '<i class="fa fa-times"></i> Cancel'
                        },
                        confirm: {
                            label: '<i class="fa fa-check"></i> Confirm'
                        }
                    },
                    callback: function(result) {
                        if(result) {
                            window.location.href = '/index.php?page=shippings_add_ext';
                        }
                        else {
                            window.location.reload();
                        }
                    }
                });
            }
        });
    });
</script>