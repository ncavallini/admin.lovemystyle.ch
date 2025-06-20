<?php
require_once __DIR__ . "/../actions_init.php";
$dbconnection = DBConnection::get_db_connection();
$sql = "SELECT * FROM `customers` where is_newsletter_allowed = TRUE";
$stmt = $dbconnection->prepare($sql);
$stmt->execute();
$emails = $stmt->fetchAll();
$mailer = Email::get_php_mailer("info@lovemystyle.ch");
$mailer->Subject = "Codice Sconto";
$mailer->Body = file_get_contents(__DIR__ . "/../../templates/emails/welcome_code.html");
$mailer->isHTML(true);
$mailer->addAddress("info@lovemystyle.ch");
foreach ($emails as $email) {
    $mailer->addBCC($email['email']);
}
if ($mailer->send()) {
    echo("Email inviata con successo");
} else {
    Utils::print_error("Errore nell'invio dell'email: " . $mailer->ErrorInfo, true);
}
?>