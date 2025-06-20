<footer class="fixed-bottom">
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col"><b>&copy; <?php echo date("Y") ?> &mdash;
                        <a style="color: black;" href="https://www.pcngroup.ch" target="_blank">PCN Group</a>
                        &times; <span class="special uppercase">Love My Style</span>
                    </b></div>
                <div class="col"> 
                    <a href="/index.php?page=pos_view" style="color: black;">
                    <b>Sistema di cassa: </b>
                    <span id="pos-overall-icon"></span> &nbsp;
                    <span><a href="actions/pos/open_draw.php" class="btn btn-sm btn-secondary">Apri cassetto</a></span> &nbsp;
                    <span><a href="actions/pos/cut.php" class="btn btn-sm btn-secondary">Taglia carta</a></span> &nbsp;
    
                </a>
                    
                </div>
            </div>

        </div>
        <div class="card-body d-flex justify-content-between">
            <p class="card-text mb-0">
                Utente: <?php echo Auth::get_fullname() ?>
                <a href="actions/auth/logout.php">Cambia utente</a>
            </p>
            <p class="text-end mb-0" id="datetime"></p>
        </div>
    </div>
</footer>

<script>
    const datetime = document.getElementById('datetime');
    setInterval(() => {
        const date = new Date();
        datetime.innerHTML = date.toLocaleTimeString() + "<br>" + date.toLocaleDateString();
    }, 1000);



    const tds = Array.from(document.getElementsByTagName("td"));
    tds.forEach(td => {
    if (!isNaN(td.textContent.trim()) && td.textContent.trim() !== "" && td.children.length == 0) {
        td.classList.add("numeric");
    }
});


const cityInputs = document.querySelectorAll('input[name="city"]');
cityInputs.forEach(input => {
    input.addEventListener('change', function() {
        const city = this.value.trim();
        const postcodeInput = document.querySelector('input[name="postcode"]');
        const country = document.querySelector('select[name="country"]').value || "CH";
        if(city && postcodeInput) getPostCodeFromCity(postcodeInput, city, country);
    });
});

const postcodeInputs = document.querySelectorAll('input[name="postcode"]');
postcodeInputs.forEach(input => {
    input.addEventListener('change', function() {
        const postcode = this.value.trim();
        const cityInput = document.querySelector('input[name="city"]');
        const country = document.querySelector('select[name="country"]').value || "CH";
        if(postcode && cityInput) getCityFromPostCode(cityInput, postcode, country);
    });
});

const countrySelects = document.querySelectorAll('select[name="country"]');
countrySelects.forEach(select => {
    select.addEventListener('change', function() {
        const country = this.value;
        const postcodeInput = document.querySelector('input[name="postcode"]');
        const cityInput = document.querySelector('input[name="city"]');
        if(postcodeInput && cityInput) {
            if(postcodeInput.value.trim()) getCityFromPostCode(cityInput, postcodeInput.value.trim(), country);
            if(cityInput.value.trim()) getPostCodeFromCity(postcodeInput, cityInput.value.trim(), country);
        }
    });
});

</script>
