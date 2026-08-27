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
        $this->mail->Host = "smtp.gmail.com";
        $this->mail->SMTPAuth = true;
        $this->mail->Username = "creatormvc@gmail.com";
        $this->mail->Password = "antcbjvvcciyarpz";
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;
        $this->mail->CharSet = 'UTF-8';
    }

    public function enviarNovaSenha($emailDestino, $novaSenha) {
        try {
            $this->mail->setFrom('creatormvc@gmail.com', 'Equipe DevStudio');
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
