<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../../vendor/autoload.php";

class Email
{
    /**
     * Build a configured PHPMailer instance for SMTP.
     */
    public static function get_php_mailer(?string $from): PHPMailer
    {
        $mail = new PHPMailer(true);

        // Transport
        $mail->isSMTP();
        $mail->Host       = $GLOBALS['CONFIG']['SMTP_HOSTNAME'];
        $mail->SMTPAuth   = (bool)$GLOBALS['CONFIG']['SMTP_AUTH'];
        $mail->Username   = $GLOBALS['CONFIG']['SMTP_USERNAME'];
        $mail->Password   = $GLOBALS['CONFIG']['SMTP_PASSWORD'];
        $mail->SMTPSecure = $GLOBALS['CONFIG']['SMTP_ENCRYPTION']; // 'ssl' | 'tls' | ''
        $mail->Port       = (int)$GLOBALS['CONFIG']['SMTP_PORT'];
        $mail->SMTPDebug  = (int)$GLOBALS['CONFIG']['SMTP_DEBUG'];

        // Envelope sender (Return-Path) — keep aligned with authenticated mailbox
        $mail->Sender = $GLOBALS['CONFIG']['SMTP_USERNAME'];

        // From name (brand)
        $brandName = "Love My Style";

        // For DMARC alignment, set From to the authenticated mailbox.
        $mail->setFrom($GLOBALS['CONFIG']['SMTP_USERNAME'], $brandName);

        // Reply-To strategy:
        // 1) If user is logged in, prefer replying to the staff member (nice UX).
        // 2) Else, if a custom $from was provided and is different from the auth mailbox, use it as Reply-To.
        if (class_exists('Auth') && method_exists('Auth', 'is_logged') && Auth::is_logged()) {
            $sessionEmail = $_SESSION['user']['email'] ?? null;
            $sessionName  = trim(($sfn = $_SESSION['user']['first_name'] ?? '') . ' ' . ($sln = $_SESSION['user']['last_name'] ?? ''));
            if ($sessionEmail && filter_var($sessionEmail, FILTER_VALIDATE_EMAIL)) {
                $mail->addReplyTo($sessionEmail, $sessionName ?: $brandName);
            }
        } elseif ($from && filter_var($from, FILTER_VALIDATE_EMAIL)
            && strcasecmp($from, $GLOBALS['CONFIG']['SMTP_USERNAME']) !== 0) {
            $mail->addReplyTo($from, $brandName);
        }

        // Content
        $mail->isHTML((bool)$GLOBALS['CONFIG']['SMTP_USE_HTML']);
        $mail->CharSet  = 'UTF-8';
        $mail->Encoding = 'base64'; // single consistent transfer encoding

        return $mail;
    }

    /**
     * Send an email with retries and optional attachments.
     *
     * @param string      $to
     * @param string      $subject
     * @param string      $message
     * @param array<int,array{path:string,name?:string,type?:string}> $file_attachments
     * @param array<int,array{content:string,name:string,type?:string}> $string_attachments
     * @param string|null $from Optional reply-to email (see get_php_mailer)
     */
    public static function send(
        string $to,
        string $subject,
        string $message,
        array $file_attachments = [],
        array $string_attachments = [],
        ?string $from = null
    ): bool
    {
        $maxRetries = max(1, (int)($GLOBALS['CONFIG']['SMTP_MAX_RETRIES'] ?? 1));
        $lastError  = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $mail = self::get_php_mailer($from);

            try {
                // Recipient
                $mail->addAddress($to);

                // Subject/Body
                $mail->Subject = $subject;
                $mail->Body    = $message;

                // AltBody for HTML mail (improves deliverability/UX in plain-text clients)
                if ($mail->ContentType === 'text/html') {
                    $alt = trim(html_entity_decode(strip_tags($message)));
                    $mail->AltBody = $alt !== '' ? $alt : $subject;
                }

                // File attachments
                foreach ($file_attachments as $file) {
                    if (!isset($file['path'])) {
                        continue;
                    }
                    $path = $file['path'];
                    if (!is_readable($path)) {
                        continue;
                    }
                    $name = $file['name'] ?? basename($path);
                    $type = $file['type'] ?? PHPMailer::CONTENT_TYPE_OCTETSTREAM;
                    $mail->addAttachment($path, $name, PHPMailer::ENCODING_BASE64, $type);
                }

                // String attachments
                foreach ($string_attachments as $att) {
                    if (!isset($att['content'], $att['name'])) {
                        continue;
                    }
                    $type = $att['type'] ?? PHPMailer::CONTENT_TYPE_OCTETSTREAM;
                    $mail->addStringAttachment($att['content'], $att['name'], PHPMailer::ENCODING_BASE64, $type);
                }

                if ($mail->send()) {
                    return true;
                }

                $lastError = $mail->ErrorInfo ?: 'Unknown error';
            } catch (Exception $e) {
                $lastError = $e->getMessage();
            } catch (\Throwable $t) {
                $lastError = $t->getMessage();
            }
        }

        if ($lastError) {
            if (class_exists('Utils') && method_exists('Utils', 'print_error')) {
                Utils::print_error("Errore invio email: " . $lastError);
            }
        }

        return false;
    }
}
