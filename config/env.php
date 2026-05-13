<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Cargar el archivo .env desde la raíz del proyecto
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

/**
 * Función auxiliar para obtener variables de entorno con un valor por defecto.
 */
if (!function_exists('env')) {
    function env($key, $default = null) {
        // Detectar si estamos en local (localhost o 127.0.0.1)
        $is_local = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', '::1']);
        
        // Si estamos en hosting (no local), intentamos buscar la variable con prefijo PROD_
        if (!$is_local) {
            $prod_key = 'PROD_' . $key;
            if (isset($_ENV[$prod_key]) && $_ENV[$prod_key] !== '') {
                return $_ENV[$prod_key];
            }
            if ($val = getenv($prod_key)) {
                return $val;
            }
        }
        
        // Comportamiento normal: buscar la clave directa o usar el default
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}

