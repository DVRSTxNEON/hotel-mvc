<?php
// ── Requiere PHPMailer (instalado en libs/phpmailer/) ─────────────────────────
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../libs/phpmailer/src/Exception.php';
require_once __DIR__ . '/../libs/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../libs/phpmailer/src/SMTP.php';

// ── Configuración SMTP ────────────────────────────────────────────────────────
define('MAIL_HOST',     'smtp.gmail.com');   // servidor SMTP
define('MAIL_PORT',     587);                // 587 = TLS | 465 = SSL
define('MAIL_USER',     'pierremorales1607@gmail.com');
define('MAIL_PASS',     'ggee lret wcib gotv'); // contraseña de aplicación de Google
define('MAIL_FROM',     'pierremorales1607@gmail.com');
define('MAIL_FROM_NAME','Hotel Luxury');

/**
 * Crea y configura una instancia base de PHPMailer.
 * Lanza PHPMailer\PHPMailer\Exception si algo falla.
 */
function crearMailer(): PHPMailer {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USER;
    $mail->Password   = MAIL_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = MAIL_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);

    return $mail;
}

// ─────────────────────────────────────────────────────────────────────────────
//  1. CORREO DE INICIO DE SESIÓN
// ─────────────────────────────────────────────────────────────────────────────
function enviarCorreoLogin(array $usuario): void {
    try {
        $mail = crearMailer();
        $mail->addAddress($usuario['email'], $usuario['nombre']);
        $mail->Subject = 'Inicio de sesión — Hotel Luxury';
        $mail->isHTML(true);

        $fecha = date('d/m/Y H:i');
        $nombre = htmlspecialchars($usuario['nombre']);

        $mail->Body = "
        <div style='font-family:Arial,sans-serif;max-width:520px;margin:auto;border:1px solid #ddd;border-radius:10px;overflow:hidden'>
            <div style='background:#1e3a5f;padding:20px;text-align:center'>
                <h2 style='color:#fff;margin:0'>Hotel Luxury</h2>
            </div>
            <div style='padding:24px'>
                <p style='font-size:15px'>Hola, <strong>{$nombre}</strong>.</p>
                <p style='font-size:15px'>Se ha iniciado sesión en tu cuenta el <strong>{$fecha}</strong>.</p>
                <p style='font-size:13px;color:#888'>Si no fuiste tú, cambia tu contraseña de inmediato.</p>
            </div>
            <div style='background:#f5f5f5;padding:12px;text-align:center;font-size:11px;color:#aaa'>
                © " . date('Y') . " Hotel Luxury — Notificación automática
            </div>
        </div>";

        $mail->AltBody = "Hola {$nombre}, se inició sesión en tu cuenta el {$fecha}.";

        $mail->send();
    } catch (Exception $e) {
        // El error de correo no debe interrumpir el flujo del login
        error_log("Error al enviar correo de login: " . $e->getMessage());
    }
}

// ─────────────────────────────────────────────────────────────────────────────
//  2. CORREO DE RESERVA + PDF ADJUNTO
// ─────────────────────────────────────────────────────────────────────────────
function enviarCorreoReserva(array $usuario, array $reserva, string $pdfBytes): void {
    try {
        $mail = crearMailer();
        $mail->addAddress($usuario['email'], $usuario['nombre']);
        $mail->Subject = 'Confirmación de reserva #' . str_pad($reserva['id'], 5, '0', STR_PAD_LEFT) . ' — Hotel Luxury';
        $mail->isHTML(true);

        $nombre   = htmlspecialchars($usuario['nombre']);
        $cedula   = htmlspecialchars($usuario['cedula']);
        $hab      = htmlspecialchars($reserva['habitacion']);
        $ini      = $reserva['fecha_inicio'];
        $fin      = $reserva['fecha_fin'];
        $noches   = (new DateTime($ini))->diff(new DateTime($fin))->days;
        $numRes   = '#' . str_pad($reserva['id'], 5, '0', STR_PAD_LEFT);
        $fecha    = date('d/m/Y H:i');

        $mail->Body = "
        <div style='font-family:Arial,sans-serif;max-width:520px;margin:auto;border:1px solid #ddd;border-radius:10px;overflow:hidden'>
            <div style='background:#1e3a5f;padding:20px;text-align:center'>
                <h2 style='color:#fff;margin:0'>Hotel Luxury</h2>
                <p style='color:#cce;margin:4px 0 0'>Comprobante de Reserva</p>
            </div>
            <div style='padding:24px'>
                <p style='font-size:15px'>Hola, <strong>{$nombre}</strong>. Tu reserva ha sido confirmada.</p>
                <table style='width:100%;border-collapse:collapse;font-size:14px;margin-top:12px'>
                    <tr style='background:#f0f4fa'><td style='padding:8px;font-weight:bold'>N° de Reserva</td><td style='padding:8px'>{$numRes}</td></tr>
                    <tr>                            <td style='padding:8px;font-weight:bold'>Habitación</td>   <td style='padding:8px'>{$hab}</td></tr>
                    <tr style='background:#f0f4fa'><td style='padding:8px;font-weight:bold'>Fecha Inicio</td> <td style='padding:8px'>{$ini}</td></tr>
                    <tr>                            <td style='padding:8px;font-weight:bold'>Fecha Fin</td>    <td style='padding:8px'>{$fin}</td></tr>
                    <tr style='background:#f0f4fa'><td style='padding:8px;font-weight:bold'>Noches</td>       <td style='padding:8px'>{$noches} noche(s)</td></tr>
                    <tr>                            <td style='padding:8px;font-weight:bold'>Huésped</td>      <td style='padding:8px'>{$nombre}</td></tr>
                    <tr style='background:#f0f4fa'><td style='padding:8px;font-weight:bold'>Cédula</td>       <td style='padding:8px'>{$cedula}</td></tr>
                </table>
                <p style='font-size:13px;color:#555;margin-top:16px'>El comprobante PDF está adjunto a este correo.</p>
            </div>
            <div style='background:#f5f5f5;padding:12px;text-align:center;font-size:11px;color:#aaa'>
                Generado el {$fecha} — © " . date('Y') . " Hotel Luxury
            </div>
        </div>";

        $mail->AltBody = "Reserva {$numRes} confirmada. Habitación {$hab} del {$ini} al {$fin} ({$noches} noches).";

        // Adjuntar el PDF directamente desde memoria (sin guardar archivo)
        $filename = 'Reserva_' . str_pad($reserva['id'], 5, '0', STR_PAD_LEFT) . '.pdf';
        $mail->addStringAttachment($pdfBytes, $filename, 'base64', 'application/pdf');

        $mail->send();
    } catch (Exception $e) {
        error_log("Error al enviar correo de reserva: " . $e->getMessage());
    }
}