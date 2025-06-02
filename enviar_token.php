<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

function enviarCodigo($correoDestino, $codigo) {
    $mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'neutroks2022@gmail.com';
    $mail->Password = 'ffvl zkqr ldkh bjyg';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->CharSet = 'UTF-8'; // 👈 ESTO CORRIGE LOS CARACTERES RAROS

    $mail->setFrom('neutroks2022@gmail.com', 'Sistema de Citas Médicas');
    $mail->addAddress($correoDestino);
    $mail->Subject = '🔐 Código de verificación - Sistema de Citas Médicas';
    $mail->isHTML(true);

    $mail->Body = "
        <div style='font-family: Arial, sans-serif; padding: 20px;'>
            <h2 style='color: #007BFF;'>🔐 Verificación en dos pasos</h2>
            <p>Hola,</p>
            <p>Recibimos una solicitud de inicio de sesión en tu cuenta del <strong>Sistema de Citas Médicas</strong>.</p>
            <p><strong>Tu código de verificación es:</strong></p>
            <h1 style='color: #28a745; font-size: 32px;'>$codigo</h1>
            <p>Este código es válido por pocos minutos. Si tú no solicitaste este acceso, puedes ignorar este mensaje o comunicarte con soporte.</p>
            <hr>
            <p style='font-size: 12px; color: gray;'>Mensaje automático enviado por el sistema de seguridad de Citas Médicas.</p>
        </div>
    ";

    $mail->send();
    return true;
} catch (Exception $e) {
    return false;
}
}
?>
