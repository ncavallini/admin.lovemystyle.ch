<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . "/../../vendor/autoload.php";
class Email
{

    public static function send
    (
        string $to,
        string $subject,
        string $message,
        array $file_attachments = [],  // [[ 'path' => 'path/to/file', 'name' => 'filename', 'type' => 'application/pdf']]
        array $string_attachments = [] // [[ 'content' => 'string', 'name' => 'filename', 'type' => 'application/pdf']]
    ): bool {
        

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

            $mail->setFrom($GLOBALS['CONFIG']['SMTP_USERNAME'], "Love My Style");
            $mail->addAddress($to);
            if(Auth::is_logged()) {
                $mail->addReplyTo($_SESSION['user']['email'], $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']);
            }
             

            $mail->isHTML($GLOBALS['CONFIG']['SMTP_USE_HTML']);
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'quoted-printable';

            $mail->Subject = $subject;
            $mail->Body = $message;

            foreach ($file_attachments as $file) {
                $mail->addAttachment($file['path'], $file['name'], PHPMailer::ENCODING_BASE64, $file['type']);
            }

            foreach ($string_attachments as $attachment) {
                $mail->addStringAttachment($attachment['content'], $attachment['name'], PHPMailer::ENCODING_BASE64, $attachment['type']);
            }

            if (!$mail->send()) {
                Utils::print_error("Errore invio email: " . $mail->ErrorInfo);
                return false;
            } else {
                return true;
            }
        } catch (Exception $e) {
            Utils::print_error("Errore invio email: " . $e->getMessage());
            return false;
        }
    }
}