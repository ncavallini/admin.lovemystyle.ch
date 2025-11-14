<?php
require_once __DIR__ . "/../actions_init.php";

// CSRF Protection
CSRF::requireValidToken();

$dbconnection = DBConnection::get_db_connection();
$sql = "INSERT INTO elisa_sanna_nov_25 (first_name, last_name, email, phone, date, time) VALUES (:first_name, :last_name, :email, :phone, :date, :time)";
$stmt = $dbconnection->prepare($sql);
$res = $stmt->execute([
    ':first_name' => $_POST['first-name'] ?? '',
    ':last_name' => $_POST['last-name'] ?? '',
    ':email' => $_POST['email'] ?? '',
    ':phone' => $_POST['phone'] ?? '',
    ':date' => $_POST['date'] ?? '',
    ':time' => $_POST['time'] ?? ''
]);
if(!$res) {
    $errorStrings = [
        "it" => "Si è verificato un errore durante la registrazione. È possibile che la sua mail sia già stata utilizzata.",
        "de" => "Bei der Anmeldung ist ein Fehler aufgetreten. Möglicherweise wurde Ihre E-Mail-Adresse bereits verwendet.",
        "en" => "An error occurred during registration. It is possible that your email has already been used."
    ];
    Utils::print_error($errorStrings[$_POST['lang'] ?? 'it'] ?? $errorStrings['it'], true);
}
$lang = $_POST['lang'] ?? 'it';
header("Location: /index.php?page=public-forms_elisa-sanna-nov-25&lang={$lang}&tablet=1&success=1");
?>
