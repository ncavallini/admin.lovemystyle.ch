<footer class="fixed-bottom">
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col"><b>&copy; <?php echo date("Y") ?> &mdash;
                        <a style="color: black;" href="https://www.pcngroup.ch" target="_blank">PCN Group</a>
                        &times; Love My Style
                    </b></div>
                <div class="col"> 
                    <a href="/index.php?page=pos_view" style="color: black;">
                    <b>Sistema di cassa: </b>
                    <span id="pos-overall-icon"></span> &nbsp;
                    <span><a href="actions/pos/open_draw.php" class="btn btn-sm btn-secondary">Apri cassetto</a></span> &nbsp;
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

</script>