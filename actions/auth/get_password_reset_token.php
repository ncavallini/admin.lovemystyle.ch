<?php
require_once __DIR__ . "/../../inc/inc.php";
$username = $_POST['username'];
$dbconnection = DBConnection::get_db_connection();
$sql = "DELETE FROM password_reset_tokens WHERE username = ?";
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$username]);
$sql = "INSERT INTO password_reset_tokens (username, token, expires_at) VALUES (?, ?, ?)";
$stmt = $dbconnection->prepare($sql);
$token = bin2hex(string: random_bytes(length: 16));
$stmt->execute([$username, $token, (new DateTime())->modify("+15 minutes")->format("Y-m-d H:i:s")]);

$sql = "SELECT email FROM users WHERE username = ?";    
$stmt = $dbconnection->prepare($sql);
$stmt->execute([$username]);
$to = $stmt->fetchColumn();

$link = "https://admin.lovemystyle.ch/index.php?page=forgot-password&token=$token";

$subject = "Reimpostazione password Gestionale Love My Style";
$body = <<<EOT
    <h1>Reimpostazione password Gestionale Love My Style</h1>
    <p>Per reimpostare la tua password, clicca sul seguente link:</p>
    <p style='text-align: center'><a href="$link">REIMPOSTA PASSWORD</a></p>
    <p>Questo link scadrà dopo 15 minuti.</p>
    <p>Se non hai richiesto questa email, puoi ignorarla.</p>
    <p>Grazie,</p>
    <p style="text-align: right">Amministratore di Sistema</p>

EOT;
$email = Email::send($to, $subject, $body);
if ($email) {
    echo <<<EOT
        <!DOCTYPE html>
        <html lang="it">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Reimpostazione Password</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        </head>
        <body>
        <div class='container'>
        <div class="alert alert-success">
        <p>Controlla la tua e-mail. Ti è stato inviato un link per reimpostare la password.</p>
        <p>Se non ricevi l'email, controlla la cartella spam o contatta l'amministratore.</p>
        <p><a class='alert-link' href="/index.php">Al login</a></p>
        </div>

        </div>
        </body>
        </html>

    EOT;
} else {

}