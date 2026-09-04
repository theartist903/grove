<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Sends an email via the configured SMTP transport.
 * Returns true on success, false on failure (errors are captured, not thrown).
 */
function send_mail(string $toAddress, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool
{
    $config = require __DIR__ . '/../config/config.php';
    $mail = $config['mail'];

    $mailer = new PHPMailer(true);

    try {
        $mailer->isSMTP();
        $mailer->Host = $mail['host'];
        $mailer->Port = $mail['port'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $mail['username'];
        $mailer->Password = $mail['password'];
        $mailer->SMTPSecure = $mail['encryption'];

        $mailer->setFrom($mail['from_address'], $mail['from_name']);
        $mailer->addAddress($toAddress, $toName);

        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body = $htmlBody;
        $mailer->AltBody = $textBody !== '' ? $textBody : strip_tags($htmlBody);

        $mailer->send();
        return true;
    } catch (PHPMailerException $e) {
        error_log('Mail send failed: ' . $mailer->ErrorInfo);
        return false;
    }
}
