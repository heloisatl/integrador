<?php

require_once __DIR__ . "/../../config/Config.php";

require_once ROOT_PATH . "/app/tools/mail/lib/PHPMailer/src/PHPMailer.php";
require_once ROOT_PATH . "/app/tools/mail/lib/PHPMailer/src/SMTP.php";
require_once ROOT_PATH . "/app/tools/mail/lib/PHPMailer/src/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {

    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->configurarSMTP();
    }

    private function configurarSMTP() {
        $this->mail->SMTPDebug = SMTP::DEBUG_OFF;
        $this->mail->isSMTP();
        $this->mail->Host = env('SMTP_HOST', 'smtp.gmail.com');
        $this->mail->SMTPAuth = true;
        $this->mail->Username = env('SMTP_USER', '');
        $this->mail->Password = env('SMTP_PASS', '');
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = (int) env('SMTP_PORT', 587);
        $this->mail->CharSet = 'UTF-8';
    }

    public function enviarNovaSenha($emailDestino, $novaSenha) {
        try {
            $remetenteEmail = env('MAIL_FROM_ADDRESS', env('SMTP_USER', ''));
            $remetenteNome  = env('MAIL_FROM_NAME', 'Equipe DevStudio');

            $this->mail->setFrom($remetenteEmail, $remetenteNome);
            $this->mail->addAddress($emailDestino);

            $this->mail->isHTML(true);
            $this->mail->Subject = 'Nova Senha - DevStudio';

            require_once __DIR__ . '/mailBody.php';
            $this->mail->Body = renderizarEmail($novaSenha, $emailDestino);
            $this->mail->AltBody = "DevStudio - Recuperação de Senha\n\nSua nova senha temporária é: " . $novaSenha . "\n\nAcesse a plataforma para fazer login e redefinir sua senha.";

            return $this->mail->send();
        } catch (Exception $e) {
            throw new Exception("Erro ao enviar email: " . $this->mail->ErrorInfo);
        }
    }
}
