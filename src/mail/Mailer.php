<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once ROOT_DIR . '/../vendor/autoload.php';

class Mailer {
    public static function send(
        string $to,
        string $toName,
        string $subject,
        string $htmlBody,
        string $textBody = ''
    ): bool {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = SMTP_ENCRYPTION === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
            $mail->addAddress($to, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $textBody ?: strip_tags($htmlBody);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Mailer error: ' . $mail->ErrorInfo);
            return false;
        }
    }

    public static function emailTemplate(string $title, string $body): string {
        $appName = APP_NAME;
        $accent  = '#00ffbf';
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; background: #111; color: #eee; margin: 0; padding: 0; }
    .wrap { max-width: 600px; margin: 40px auto; background: #1e1e1e; border-radius: 12px; overflow: hidden; }
    .header { background: {$accent}; padding: 30px; text-align: center; }
    .header h1 { margin: 0; color: #111; font-size: 24px; }
    .content { padding: 30px; }
    .content h2 { color: {$accent}; }
    .btn { display: inline-block; background: {$accent}; color: #111; padding: 12px 28px;
           border-radius: 8px; text-decoration: none; font-weight: bold; margin: 20px 0; }
    .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; border-top: 1px solid #333; }
    .code { font-size: 32px; font-weight: bold; letter-spacing: 8px; color: {$accent};
            background: #111; padding: 15px 30px; border-radius: 8px; display: inline-block; margin: 20px 0; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="header"><h1>⚙️ {$appName}</h1></div>
    <div class="content">
      <h2>{$title}</h2>
      {$body}
    </div>
    <div class="footer">© rayaz.org · Built by TechZeeLand · <a href="mailto:mail@rayaz.org" style="color:#666;">mail@rayaz.org</a></div>
  </div>
</body>
</html>
HTML;
    }

    public static function sendOTP(string $to, string $name, string $code): bool {
        $body = "<p>Hi {$name},</p><p>Your verification code is:</p><div class='code'>{$code}</div><p>This code expires in 10 minutes. Do not share it with anyone.</p>";
        return self::send($to, $name, APP_NAME . ' – Verification Code', self::emailTemplate('Your Login Code', $body));
    }

    public static function sendContactConfirmation(string $to, string $name, string $subject): bool {
        $body = "<p>Hi {$name},</p><p>We received your message about: <strong>{$subject}</strong></p><p>We'll get back to you as soon as possible.</p>";
        return self::send($to, $name, 'We received your message – ' . APP_NAME, self::emailTemplate('Message Received', $body));
    }

    public static function sendContactNotification(array $msg): bool {
        $body = "<p><strong>From:</strong> {$msg['name']} ({$msg['email']})</p><p><strong>Subject:</strong> {$msg['subject']}</p><hr><p>" . nl2br(htmlspecialchars($msg['message'])) . "</p>";
        return self::send(SMTP_FROM, SMTP_FROM_NAME, 'New Contact: ' . $msg['subject'], self::emailTemplate('New Contact Message', $body));
    }
}
