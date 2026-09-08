<?php

$__grove_autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($__grove_autoload)) {
    require_once $__grove_autoload;
}

/**
 * Sends an email via the configured SMTP transport.
 * Returns true on success, false on failure (errors are captured, not thrown,
 * including the case where the PHPMailer library itself failed to load —
 * e.g. the vendor/ folder wasn't deployed).
 */
function send_mail(string $toAddress, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool
{
    if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
        error_log('Mail send skipped: PHPMailer is not available (vendor/ missing or composer install not run).');
        return false;
    }

    $config = require __DIR__ . '/../config/config.php';
    $mail = $config['mail'];

    $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mailer->isSMTP();
        $mailer->Host = $mail['host'];
        $mailer->Port = $mail['port'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $mail['username'];
        $mailer->Password = $mail['password'];
        $mailer->SMTPSecure = $mail['encryption'];

        if ($mail['host'] === 'localhost' || $mail['host'] === '127.0.0.1') {
            // Connecting to the mail server via its internal/loopback address
            // means the TLS certificate (issued for the public hostname) won't
            // match — skip peer verification for this internal connection only.
            $mailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];
        }

        $mailer->setFrom($mail['from_address'], $mail['from_name']);
        $mailer->addAddress($toAddress, $toName);

        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body = $htmlBody;
        $mailer->AltBody = $textBody !== '' ? $textBody : strip_tags($htmlBody);

        $mailer->send();
        return true;
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        error_log('Mail send failed: ' . $mailer->ErrorInfo);
        return false;
    } catch (\Throwable $e) {
        error_log('Mail send failed: ' . $e->getMessage());
        return false;
    }
}
