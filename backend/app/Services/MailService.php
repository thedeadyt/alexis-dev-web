<?php
namespace App\Services;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MailService {
    public function sendContactNotification(array $contact): void {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = config('mail.mailers.smtp.host');
        $mail->SMTPAuth   = true;
        $mail->Username   = config('mail.mailers.smtp.username');
        $mail->Password   = config('mail.mailers.smtp.password');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = config('mail.mailers.smtp.port');
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(config('mail.from.address'), 'alexis dev web');
        $mail->addAddress(config('mail.from.address'));
        $firstName = htmlspecialchars($contact['first_name'] ?? '', ENT_QUOTES, 'UTF-8');
        $lastName  = htmlspecialchars($contact['last_name']  ?? '', ENT_QUOTES, 'UTF-8');
        $email     = htmlspecialchars($contact['email']      ?? '', ENT_QUOTES, 'UTF-8');
        $phone     = htmlspecialchars($contact['phone']      ?? '', ENT_QUOTES, 'UTF-8');
        $type      = htmlspecialchars($contact['type']       ?? '', ENT_QUOTES, 'UTF-8');
        $budget    = htmlspecialchars($contact['budget']     ?? '', ENT_QUOTES, 'UTF-8');

        $mail->Subject = "Nouvelle demande de {$firstName} {$lastName}";
        $mail->isHTML(true);
        $mail->Body = "
            <h2>Nouvelle demande de contact</h2>
            <p><strong>Nom :</strong> {$firstName} {$lastName}</p>
            <p><strong>Email :</strong> {$email}</p>
            <p><strong>Téléphone :</strong> " . ($phone !== '' ? $phone : '—') . "</p>
            <p><strong>Type :</strong> {$type}</p>
            <p><strong>Budget :</strong> {$budget}</p>
            <p><strong>Message :</strong><br>" . nl2br(htmlspecialchars($contact['message'] ?? '')) . "</p>
        ";
        $mail->send();
    }
}
