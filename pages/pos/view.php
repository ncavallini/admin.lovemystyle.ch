<h1>Sistema di cassa (POS)</h1>
<div class="card">
    <div class="h2 card-header">
        Stampante etichette <span id="labelPrinter-icon"></span>
    </div>
    <div class="card-body">
    <h3 class="h5 card-title" id="labelPrinter-printerNameAndModel">Attendere...</h3>

        <ul>
            <li><b>Etichette rimanenti: </b> <span class="badge rounded-pill " id="remainingLabels">Attendere...</spa></li>
            <li><b>Tipo etichette:</b> <span id="labelType"></span></li>
        </ul>
        <a href="actions/pos/test_label_printer.php" class="btn btn-primary">Test stampante</a>
    </div>
</div>

<p>&nbsp;</p>

<div class="card">
    <div class="h2 card-header">
        Stampante scontrini <span id="receiptPrinter-icon"></span>
    </div>
    <div class="card-body">
        <ul>
        <li><b>Indirizzo IP: </b> <span id="receiptPrinter-ip"></span> </li>
            <li><b>Porta:</b> <span id="receiptPrinter-port"></span></li>
            <li><b>Cassetto: </b> <span id="drawer" class="badge rounded-pill "></span></li>
        </ul>
        <a href="actions/pos/open_draw.php" class="btn btn-primary">Apri cassetto</a>
        <a href="actions/pos/test_receipt_printer.php" class="btn btn-primary">Test stampante</a>
    </div>
</div>

<script>
    console.log("<?php echo $GLOBALS['CONFIG']['POS_MIDDLEWARE_URL'] ?>/sse/status")
    const eventSource = new EventSource("<?php echo $GLOBALS['CONFIG']['POS_MIDDLEWARE_URL'] ?>/sse/status");

    eventSource.onopen = () => {
        console.log("Connected to the SSE server.");
    };

    eventSource.onmessage = (event) => {
        const data = JSON.parse(event.data);
        updateLabelPrinterUI(data);
        updateReceiptPrinterUI(data);

    }

    eventSource.onerror = (err) => {
        bootbox.alert("<div class='alert alert-danger'>Errore di connessione al sistema POS. Ricaricare la pagina.</div>", () => {
            location.reload();
        });
    };



    function updateLabelPrinterUI(data) {
        const labelPrinterIcon = document.getElementById('labelPrinter-icon');
        labelPrinterIcon.innerHTML = getStatusIcon(data.labelPrinter.isConnected ? "OK" : "ERROR");
        const printerNameAndModel = document.getElementById('labelPrinter-printerNameAndModel');
        printerNameAndModel.innerText = data.labelPrinter.printerName + " (" + data.labelPrinter.modelName + ")";


       
        const remainingLabels = document.getElementById('remainingLabels');
        remainingLabels.classList.remove('bg-success', 'bg-warning', 'bg-danger');
        if (data.labelPrinter.remainingLabels ><?php echo $GLOBALS['CONFIG']['POS_LABEL_LOW_THRESHOLD'] ?>) {
            remainingLabels.classList.add('bg-success');
        } else if (data.labelPrinter.remainingLabels <= <?php echo $GLOBALS['CONFIG']['POS_LABEL_LOW_THRESHOLD'] ?> && data.labelPrinter.remainingLabels > 0) {
            remainingLabels.classList.add('bg-warning');
        } else {
            remainingLabels.classList.add('bg-danger');
        }
        remainingLabels.innerText = data.labelPrinter.remainingLabels;

        const labelType = document.getElementById('labelType');
        labelType.innerText = data.labelPrinter.labelsName + " (SKU: " + data.labelPrinter.labelsSku + ")";
    }

    function updateReceiptPrinterUI(data) {
        const receiptPrinterIcon = document.getElementById('receiptPrinter-icon');
        receiptPrinterIcon.innerHTML = getStatusIcon(data.receiptPrinter.isReachable ? "OK" : "ERROR");

        const receiptPrinterIp =document.getElementById('receiptPrinter-ip');
        receiptPrinterIp.innerText = data.receiptPrinter.ip;

        const receiptPrinterPort = document.getElementById('receiptPrinter-port');
        receiptPrinterPort.innerText = data.receiptPrinter.port;

        const drawer = document.getElementById('drawer');
        drawer.classList.remove('bg-success', 'bg-warning');
        drawer.classList.add(!data.receiptPrinter.isCashdrawOpen ? 'bg-success' : 'bg-warning');
        drawer.innerText = data.receiptPrinter.isCashdrawOpen ? "Aperto" : "Chiuso";
    }
</script>