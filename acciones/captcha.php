<?php
session_start();

// Configuración del captcha
$captcha_length = 6; // Longitud del captcha
$captcha_text = strtoupper(substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789"), 0, $captcha_length)); // Letras en mayúsculas
$_SESSION['captcha'] = $captcha_text;

// Crear la imagen
$width = 200;
$height = 70;
$image = imagecreate($width, $height);

// Colores
$background_color = imagecolorallocate($image, 240, 240, 240); // Fondo claro
$text_color = imagecolorallocate($image, 50, 50, 50); // Texto oscuro
$line_color = imagecolorallocate($image, 200, 200, 200); // Líneas de fondo

// Dibujar líneas de fondo para hacer el captcha más difícil de leer
for ($i = 0; $i < 10; $i++) {
    imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
}

// Agregar el texto del captcha
$font_size = 20; // Tamaño de la fuente
$font_path = __DIR__ . '/fonts/arial.ttf'; // Ruta a la fuente TTF
$x = 20; // Posición X inicial
$y = 50; // Posición Y inicial
imagettftext($image, $font_size, rand(-10, 10), $x, $y, $text_color, $font_path, $captcha_text);

// Enviar la imagen al navegador
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);
?>