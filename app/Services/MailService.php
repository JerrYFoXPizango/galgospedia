<?php

declare(strict_types=1);

namespace Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;
use Config\Config;

class MailService
{
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host       = Config::get('MAIL_HOST', 'localhost');
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = Config::get('MAIL_USER', '');
        $this->mailer->Password   = Config::get('MAIL_PASS', '');
        $enc = Config::get('MAIL_ENCRYPTION', 'tls');
        $this->mailer->SMTPSecure = $enc === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port    = (int) Config::get('MAIL_PORT', 587);
        $this->mailer->CharSet = 'UTF-8';
        $this->mailer->setFrom(
            Config::get('MAIL_USER', 'noreply@galgospedia.com'),
            Config::get('MAIL_FROM_NAME', 'Galgospedia')
        );
    }

    public function send(string $to, string $toName, string $subject, string $html): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to, $toName);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $html;
            $this->mailer->AltBody = strip_tags(
                str_replace(['<br>', '<br/>', '<br />'], "\n", $html)
            );
            $this->mailer->send();
            return true;
        } catch (MailerException $e) {
            error_log('MailService: ' . $e->getMessage());
            return false;
        }
    }
}
