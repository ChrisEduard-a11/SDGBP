<?php
session_start();
if (session_status() === PHP_SESSION_ACTIVE) {
    $_SESSION['LAST_ACTIVITY'] = time(); // Actualizar el tiempo de la última actividad
    
}
?>