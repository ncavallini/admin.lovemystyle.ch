const POS_MIDDLEWARE_URL = document.body.getAttribute("data-pos-url");
const SSE_URL = `${POS_MIDDLEWARE_URL}/status/sse`;
const MAX_RETRIES = 5
let retryCount = 0;

let eventSource = new EventSource(SSE_URL);

function connectToSSE() {
    eventSource = new EventSource(SSE_URL);

    eventSource.onopen = () => {
        console.log("Connected to the SSE server.");
        retryCount = 0;
    };

    eventSource.onmessage = (event) => {
        let posStatus;
        try {
             posStatus = JSON.parse(event.data);

            if(posStatus.errorLevel === 2 && sessionStorage.getItem('posAlertShown') !== 'true') {
                sessionStorage.setItem('posAlertShown', 'true');
                bootbox.dialog({
                    "title": "Errore nel sistema di cassa",
                    "message": "<div class='alert alert-danger'><p>Il sistema di cassa ha riscontrato un errore critico. È possibile che la connessione con uno o più dispositivi, o con il server sia fallita.</p><p>Cliccare <a href='index.php?page=pos_view' class='alert-link'>qui</a> per verificare lo stato del sistema.</p><p>Controllare i dispositivi e la connessione di rete. Se il problema persiste, <b>contattare l'Amministratore di Sistema</b>.</p><p>Finché il problema non sarà risolto, non sarà possibile effettuare o stornare vendite e stampare etichette.</div>",
                })
            }

            if(posStatus.timeSinceCashdrawOpened >= 30) {
                startBeeping();
            }
            
            if(!posStatus.receiptPrinter.isCashdrawOpen) stopBeeping();
          
        

            if (window.location.pathname === '/index.php' && new URLSearchParams(window.location.search).get('page') === 'pos_view') {
                updateLabelPrinterUI(posStatus);
                updateReceiptPrinterUI(posStatus);

                const pollingDatetime = document.getElementById('polling-datetime');
                if (pollingDatetime) {
                    pollingDatetime.innerText = new Date(posStatus.pollingEpoch).toLocaleString();
                }
            }

            const posOverallIcon = document.getElementById('pos-overall-icon');
            posOverallIcon.innerHTML = getStatusIcon(posStatus.errorLevel)

        } catch (error) {
            console.error("Error parsing SSE data:", error);
            posStatus.errorLevel = 2;
            
        }
    };

    eventSource.onerror = () => {
        console.warn(`SSE connection error. Retry ${retryCount + 1} of ${MAX_RETRIES}...`);

        eventSource.close();

        if (retryCount < MAX_RETRIES) {
            retryCount++;
            setTimeout(connectToSSE, 3000 * retryCount);
        } else {
            bootbox.alert(
                "<div class='alert alert-danger'>Errore di connessione al sistema POS. Controlla la rete e ricarica la pagina.</div>",
                () => {
                    location.reload();
                }
            );
        }
    };
}

// Start the connection
connectToSSE();
