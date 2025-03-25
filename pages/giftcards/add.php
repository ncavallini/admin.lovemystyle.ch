<style>
    .giftcard {
        cursor: pointer;
    }

    .giftcard:focus {
        outline: 2px solid #0d6efd;
        outline-offset: 2px;
    }
</style>

<h1>Aggiungi Carta Regalo</h1>

<div class="container mt-4">
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4">
        <div class="col">
            <div class="giftcard card h-100" tabindex="0" role="button" onclick="selectGiftCard(this, 50)">
                <div class="card-body">
                    <h5 class="card-title">Carta Regalo 50 CHF</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="giftcard card h-100" tabindex="0" role="button" onclick="selectGiftCard(this, 100)">
                <div class="card-body">
                    <h5 class="card-title">Carta Regalo 100 CHF</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="giftcard card h-100" tabindex="0" role="button" onclick="selectGiftCard(this, 150)">
                <div class="card-body">
                    <h5 class="card-title">Carta Regalo 150 CHF</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="giftcard card h-100" tabindex="0" role="button" onclick="selectGiftCard(this, 200)">
                <div class="card-body">
                    <h5 class="card-title">Carta Regalo 200 CHF</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="giftcard card h-100" tabindex="0" role="button" onclick="selectGiftCard(this, 250)">
                <div class="card-body">
                    <h5 class="card-title">Carta Regalo 250 CHF</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="giftcard card h-100" tabindex="0" role="button" onclick="selectGiftCard(this, 300)">
                <div class="card-body">
                    <h5 class="card-title">Carta Regalo 300 CHF</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="giftcard card h-100" tabindex="0" role="button" onclick="selectGiftCard(this, 350)">
                <div class="card-body">
                    <h5 class="card-title">Carta Regalo 350 CHF</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="giftcard card h-100" tabindex="0" role="button" onclick="selectGiftCard(this, 400)">
                <div class="card-body">
                    <h5 class="card-title">Carta Regalo 400 CHF</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="giftcard card h-100" tabindex="0" role="button" onclick="selectGiftCard(this, 450)">
                <div class="card-body">
                    <h5 class="card-title">Carta Regalo 450 CHF</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="giftcard card h-100" tabindex="0" role="button" onclick="selectGiftCard(this, 500)">
                <div class="card-body">
                    <h5 class="card-title">Carta Regalo 500 CHF</h5>
                </div>
            </div>
        </div>
    </div>
</div>


<form action="actions/giftcards/add.php" method="POST" class="mt-4">
    <input id="value-input" type="hidden" name="value" value="" required>

    <div class="row">
        <div class="col-5">
            <label for="customer_id">Cerca Cliente registrato</label>
            <select name="customer_id" id="customer-select" class="form-control">
            </select>
        </div>
        <div class="col-1"><span class="text-center"><i>Oppure</i></span></div>
        <div class="col-3">
            <label for="first_name">Nome</label>
            <input id="first_name-input" type="text" name="first_name" class="form-control" required>
        </div>
        <div class="col-3">
            <label for="last_name">Cognome</label>
            <input id="last_name-input" type="text" name="last_name" class="form-control" required>
        </div>
    </div>

    <p>&nbsp;</p>
    <button disabled id="form-submit-btn" type="submit" class="btn btn-primary">Conferma</button>

</form>


<script>
    const cards = document.querySelectorAll('.giftcard');

    cards.forEach(card => {
        card.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                card.click();
            }
        });
    });


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
                            text: customer.first_name + " " + customer.last_name + " (" + new Date(customer.birth_date).toLocaleDateString() + ")"
                        }
                    })
                }
            },
        },
    });

    // 🔧 Move this OUTSIDE `.ready()` chain, and attach directly to #customer-select
    $("#customer-select").on("change", function (e) {
        if (e.target.value !== "") {
            $("#first_name-input").val("").prop("disabled", true).prop("required", false);
            $("#last_name-input").val("").prop("disabled", true).prop("required", false);
        } else {
            $("#first_name-input").prop("disabled", false).prop("required", true);
            $("#last_name-input").prop("disabled", false).prop("required", true);
        }
    });
});

document.getElementById("value-input").addEventListener("change", (e) => {
    if(e.target.value === "") {
        document.getElementById("form-submit-btn").setAttribute("disabled", "true");
    } else {
        document.getElementById("form-submit-btn").removeAttribute("disabled");
    }   
})


    function selectGiftCard(cardEl, value) {
        cards.forEach(card => {
            card.classList.remove("bg-primary-subtle");
        });
        cardEl.classList.add("bg-primary-subtle");
        document.getElementById('value-input').value = value;
        document.getElementById('value-input').dispatchEvent(new Event('change'));
    }
</script>