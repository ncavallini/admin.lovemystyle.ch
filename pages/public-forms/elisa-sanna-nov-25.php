<style>
    form {
        margin-top: 20px;
    }

    label {
        font-weight: bold;
        margin-bottom: 5px;
        display: block;
    }

    .form-control,
    .form-select {
        width: 100%;
        padding: 8px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    body {
        background-color: #c2d5ed;
    }
</style>


<?php
$email = $_GET['email'] ?? '';
$lang = $_GET['lang'] ?? 'it';
$success = isset($_GET['success']);

$dbconnection = DBConnection::get_db_connection();
$sql = "SELECT first_name, last_name, tel FROM users WHERE email = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$email]);
$user = $stmt->fetch();
if ($user) {
    $firstName = $user['first_name'];
    $lastName = $user['last_name'];
    $tel = $user['tel'];
} else {
    $firstName = '';
    $lastName = '';
    $tel = '';
}
?>

<?php if ($lang === "it" && !$success): ?>

    <h1>Iscriviti all'evento Elisa Sanna</h1>
    <p>Compila il modulo qui sotto per iscriverti all'evento.</p>
    <p>Puoi partecipare anche senza iscriverti, ma la prenotazione ti garantisce un'esperienza migliore.</p>


    <form action="/actions/public-forms/elisa-sanna-nov-25.php" method="POST">
        <div class="row">
            <div class="col-6">
                <label for="first-name">Nome *</label>
                <input class="form-control" type="text" id="first-name" name="first-name" value="<?php echo htmlspecialchars($firstName); ?>" required>
            </div>
            <div class="col-6">
                <label for="last-name">Cognome *</label>
                <input class="form-control" type="text" id="last-name" name="last-name" value="<?php echo htmlspecialchars($lastName); ?>" required>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <label for="email">Email *</label>
                <input class="form-control" type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="col-6">
                <label for="phone">Numero di telefono</label>
                <input class="form-control" type="tel" id="phone" name="phone">
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <label for="date">Data desiderata</label>
                <select class="form-select" name="date" id="date-select">
                    <option value="" disabled selected>Seleziona una data</option>
                    <option value="2025-11-28">Venerdì 28 novembre</option>
                    <option value="2025-11-29">Sabato 29 novembre</option>
                </select>
            </div>
            <div class="col-6">
                <label for="time">Orario desiderato</label>
                <select class="form-select" name="time" id="time-select">
                    <option value="" disabled selected>Seleziona un orario</option>
                </select>
            </div>
        </div>
        <p>&nbsp;</p>
        <input type="hidden" name="lang" value="it">
        <input type="hidden" name="tablet" value="1">
        <button type="submit" class="btn btn-primary">Iscriviti</button>
    </form>

<?php elseif ($lang === "it" && $success): ?>
    <h2>Grazie per esserti iscritto!</h2>
    <p>Ti aspettiamo all'evento.</p>
    <p><i>Barbara Alberti e il suo Team</i></p>
<?php elseif ($lang === "de" && !$success): ?>

    <h1>Elisa Sanna Event Anmeldung</h1>
    <p>Bitte füllen Sie das untenstehende Formular aus, um sich für das Event anzumelden.</p>
    <p>Sie können auch ohne Anmeldung teilnehmen, aber eine Reservierung garantiert Ihnen ein noch besseres Erlebnis.</p>

    <form action="/actions/public-forms/elisa-sanna-nov-25.php" method="POST">
        <div class="row">
            <div class="col-6">
                <label for="first-name">Vorname *</label>
                <input class="form-control" type="text" id="first-name" name="first-name" value="<?php echo htmlspecialchars($firstName); ?>" required>
            </div>
            <div class="col-6">
                <label for="last-name">Nachname *</label>
                <input class="form-control" type="text" id="last-name" name="last-name" value="<?php echo htmlspecialchars($lastName); ?>" required>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <label for="email">E-Mail *</label>
                <input class="form-control" type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="col-6">
                <label for="phone">Telefonnummer</label>
                <input class="form-control" type="tel" id="phone" name="phone">
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <label for="date">Gewünschtes Datum</label>
                <select class="form-select" name="date" id="date-select">
                    <option value="" disabled selected>Datum auswählen</option>
                    <option value="2025-11-28">Freitag, 28. November</option>
                    <option value="2025-11-29">Samstag, 29. November</option>
                </select>
            </div>
            <div class="col-6">
                <label for="time">Gewünschte Uhrzeit</label>
                <select class="form-select" name="time" id="time-select">
                    <option value="" disabled selected>Uhrzeit auswählen</option>
                </select>
            </div>
        </div>
        <input type="hidden" name="lang" value="de">
                <input type="hidden" name="tablet" value="1">
        <p>&nbsp;</p>
        <button type="submit" class="btn btn-primary">Anmelden</button>
    </form>

<?php elseif ($lang === "de" && $success): ?>
    <h2>Vielen Dank für Ihre Anmeldung!</h2>
    <p>Wir freuen uns, Sie bei der Veranstaltung begrüßen zu dürfen.</p>
    <p><i>Barbara Alberti und ihr Team</i></p>

<?php elseif ($lang === "en" && !$success): ?>

    <h1>Elisa Sanna Event Registration</h1>
    <p>Please fill in the form below to register for the event.</p>
    <p>You may attend even without registering, but your reservation will ensure a better experience.</p>

    <form action="/actions/public-forms/elisa-sanna-nov-25.php" method="POST">
        <div class="row">
            <div class="col-6">
                <label for="first-name">First Name *</label>
                <input class="form-control" type="text" id="first-name" name="first-name" value="<?php echo htmlspecialchars($firstName); ?>" required>
            </div>
            <div class="col-6">
                <label for="last-name">Last Name *</label>
                <input class="form-control" type="text" id="last-name" name="last-name" value="<?php echo htmlspecialchars($lastName); ?>" required>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <label for="email">Email *</label>
                <input class="form-control" type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="col-6">
                <label for="phone">Phone Number</label>
                <input class="form-control" type="tel" id="phone" name="phone">
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <label for="date">Preferred Date</label>
                <select class="form-select" name="date" id="date-select">
                    <option value="" disabled selected>Select a date</option>
                    <option value="2025-11-28">Friday, 28 November</option>
                    <option value="2025-11-29">Saturday, 29 November</option>
                </select>
            </div>
            <div class="col-6">
                <label for="time">Preferred Time</label>
                <select class="form-select" name="time" id="time-select">
                    <option value="" disabled selected>Select a time</option>
                </select>
            </div>
        </div>
        <p>&nbsp;</p>
    
        <input type="hidden" name="lang" value="en">
                <input type="hidden" name="tablet" value="1">

        <button type="submit" class="btn btn-primary">Register</button>
    </form>
<?php elseif ($lang === "en" && $success): ?>
    <h2>Thank you for registering!</h2>
    <p>We look forward to seeing you at the event.</p>
    <p><i>Barbara Alberti and her Team</i></p>
<?php endif; ?>




<script>
    const dateSelect = document.getElementById('date-select');
    const timeSelect = document.getElementById('time-select');

    const timeOptions = {
        '2025-11-28': [
            "14:00",
            "14:30",
            "15:00",
            "15:30",
            "16:00",
            "16:30",
            "17:00",
            "17:30",
            "18:00",
        ],
        '2025-11-29': [
            "10:00",
            "10:30",
            "11:00",
            "11:30",
            "12:00",
            "12:30",
            "13:00",
            "13:30",
            "14:00",
            "14:30",
            "15:00",
            "15:30",
            "16:00",
            "16:30"
        ]
    };

    dateSelect.addEventListener('change', function() {
        const selectedDate = this.value;
        timeSelect.innerHTML = '<option value="" disabled selected>Seleziona un orario</option>';

        if (timeOptions[selectedDate]) {
            timeOptions[selectedDate].forEach(function(time) {
                const option = document.createElement('option');
                option.value = time;
                option.textContent = time;
                timeSelect.appendChild(option);
            });
        }
    });
</script>