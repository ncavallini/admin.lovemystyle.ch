<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../utils/functions.php";
class Email
{

    public static function send
    (
        string $to,
        string $subject,
        string $message,
        array $file_attachments = [],  // [[ 'path' => 'path/to/file', 'name' => 'filename', 'type' => 'application/pdf']]
        array $string_attachments = [] // [[ 'content' => 'string', 'name' => 'filename', 'type' => 'application/pdf']]
    ): bool
    {
        if(!isset($_SESSION['user'])) {
            Utils::print_error( "Errore invio email: utente non loggato");
            return false;
        }

        $mail = new PHPMailer;

        try {
            $mail->isSMTP();
            $mail->Host = $GLOBALS['CONFIG']['SMTP_HOSTNAME'];
            $mail->SMTPAuth = $GLOBALS['CONFIG']['SMTP_AUTH'];
            $mail->Username = $GLOBALS['CONFIG']['SMTP_USERNAME'];
            $mail->Password = $GLOBALS['CONFIG']['SMTP_PASSWORD'];
            $mail->SMTPSecure = $GLOBALS['CONFIG']['SMTP_ENCRYPTION'];
            $mail->Port = $GLOBALS['CONFIG']['SMTP_PORT'];
            $mail->SMTPDebug = $GLOBALS['CONFIG']['SMTP_DEBUG'];

            $mail->setFrom($GLOBALS['CONFIG']['SMTP_USERNAME'],  html_entity_decode($_SESSION['user']['first_name']) . ' ' . html_entity_decode($_SESSION['user']['last_name']));
            $mail->addAddress($to);
            $mail->addReplyTo($_SESSION['user']['email'], $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']);

            $mail->isHTML($GLOBALS['CONFIG']['SMTP_USE_HTML']);
            $mail->CharSet = 'UTF-8';

            $mail->Subject = "[PCN Group] - " . $subject;
            $mail->Body = $message;

            foreach ($file_attachments as $file) {
                $mail->addAttachment($file['path'], $file['name'], PHPMailer::ENCODING_BASE64, $file['type']);
            }

            foreach ($string_attachments as $attachment) {
                $mail->addStringAttachment($attachment['content'], $attachment['name'], PHPMailer::ENCODING_BASE64, $attachment['type']);
            }

            if (!$mail->send()) {
                Utils::print_error( "Errore invio email: " . $mail->ErrorInfo);
                return false;
            } else {
                return true;
            }
        } catch (Exception $e) {
            Utils::print_error( "Errore invio email: " . $e->getMessage());
            return false;
        }
    }

   /* public static function get_file_as_string(string $path): string {
        $http = HttpClient::get_client();

        $response = $http->get($path, [
            'headers' => [
                'Cookie' => "PHPSESSID=" . session_id()
            ]
        ]);
        return $response->getBody()->getContents();
    }
*/
}