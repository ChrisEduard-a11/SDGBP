<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurar el tiempo de expiración de la sesión a 3 minutos
$session_timeout = 180; // 3 minutos en segundos

if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin') {
    $session_timeout = 3600; // 1 hora para Admins
}

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $session_timeout)) {
    // Si la última actividad fue hace más de $session_timeout segundos
    session_unset();     // Destruir todas las variables de sesión
    session_destroy();   // Destruir la sesión
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // Actualizar el tiempo de la última actividad

// Calcular el tiempo restante de la sesión en segundos
$time_remaining = $session_timeout - (time() - $_SESSION['LAST_ACTIVITY']);
?>