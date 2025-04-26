function getStatusIcon(status) {
    if(status === "OK" || status === 0) {
        return '<i class="fas fa-check-circle text-success"></i>';
    } else if(status === "WARN" || status === 1) {
        return '<i class="fas fa-triangle-exclamation text-warning"></i>';
    }
    else if(status === "ERROR" || status === 2) {
        return '<i class="fas fa-times-circle text-danger"></i>';
    }
}

let beepInterval;
let beepInterrupted = false;
const audioCtx = new AudioContext();


function startBeeping(interval = 1000) {
    if(beepInterval) return;
   
    function playBeep() {
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();
        

        oscillator.frequency.setValueAtTime(1800, audioCtx.currentTime);
        oscillator.type = "sine";

        gainNode.gain.setValueAtTime(1, audioCtx.currentTime);
        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);

        oscillator.start();
        setTimeout(() => {
            oscillator.stop();
        }, 500); // Beep duration (shorter than interval)
    }

    // Ensure only one interval is running
    

    beepInterval = setInterval(playBeep, interval);
}

function stopBeeping() {
    if (beepInterval) {
        clearInterval(beepInterval);
        beepInterval = null;
    }
    beepInterrupted = true
}


function setToast(toast) {
    localStorage.setItem("toast", JSON.stringify(toast))
}

function displayToast() {
    if(localStorage.getItem("toast") === null) return
    const toast = localStorage.getItem("toast")
    Toast.create(JSON.parse(toast))
    localStorage.removeItem("toast")

}

document.addEventListener("DOMContentLoaded", () => {
    displayToast()
})