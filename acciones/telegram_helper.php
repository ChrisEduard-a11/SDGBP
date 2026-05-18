<?php
require_once __DIR__ . '/../config/env.php';

// Cargar TOKEN desde el entorno
if (!defined('TELEGRAM_BOT_TOKEN')) {

    define('TELEGRAM_BOT_TOKEN', env('TELEGRAM_BOT_TOKEN', 'TU_BOT_TOKEN_AQUI')); 
}


/**
 * Envía un mensaje a un chat ID específico vía Telegram
 * 
 * @param string $chat_id El ID del chat del usuario
 * @param string $mensaje El texto a enviar
 * @return bool Éxito o Fracaso
 */
function enviarMensajeTelegram($chat_id, $mensaje) {
    if (empty($chat_id) || TELEGRAM_BOT_TOKEN === 'TU_BOT_TOKEN_AQUI') {
        return false;
    }

    
    $api_base_url = "https://proxy-telegram.cristianarcaya2003.workers.dev"; 
    
    $url = $api_base_url . "/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    $params = [
        'chat_id' => $chat_id,
        'text' => $mensaje,
        'parse_mode' => 'HTML'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Útil para entornos locales sin certificados actualizados
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return false;
    }

    $result = json_decode($response, true);
    return isset($result['ok']) && $result['ok'] === true;
}

/**
 * Envía un código OTP de recuperación vía Telegram
 */
function enviarOTPTelegram($chat_id, $codigo, $usuario = null) {
    $mensaje = "<b>🚀 Alerta de Seguridad SDGBP</b>\n";
    $mensaje .= "━━━━━━━━━━━━━━━━━━━\n";
    if ($usuario) {
        $mensaje .= "<b>Hola, @" . $usuario . "!</b>\n";
    }
    $mensaje .= "Hemos recibido una solicitud para acceder o recuperar tu cuenta.\n\n";
    $mensaje .= "🔐 <b>TU CÓDIGO DE RECUPERACIÓN:</b>\n";
    $mensaje .= "<blockquote><code>" . $codigo . "</code></blockquote>\n\n";
    $mensaje .= "<i>⏳ Este código es temporal y confidencial.</i>\n";
    $mensaje .= "<i>⚠️ Si no solicitaste esto, ignora este mensaje y contacta soporte.</i>\n";
    $mensaje .= "━━━━━━━━━━━━━━━━━━━\n";
    $mensaje .= "🏭 <i>Sistema de Gestión de Bienes y Pagos</i>";
    
    return enviarMensajeTelegram($chat_id, $mensaje);
}
?>
