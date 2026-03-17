<?php
session_start();
require_once("../conexion.php");

$data = json_decode(file_get_contents("php://input"), true);
$clave = $data['password'];
$password = sha1($clave);

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(["message" => "Sesión no iniciada"]);
    exit();
}

// Obtener la información del usuario desde la base de datos
$usuario_id = $_SESSION['id_usuario'];
$sql = "SELECT clave, intentos FROM usuario WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

// Verificar si el usuario está bloqueado
if ($row['intentos'] >= 3) {
    session_destroy(); // Destruir la sesión
    http_response_code(403);
    echo json_encode(["message" => "Usuario bloqueado. Redirigiendo al login...", "redirect" => "../vistas/login.php"]);
    exit();
}

// Verificar la contraseña usando SHA-1
if ($password === $row['clave']) {
    // Restablecer el contador de intentos fallidos
    $sql_reset = "UPDATE usuario SET intentos = 0 WHERE id_usuario = ?";
    $stmt_reset = $conexion->prepare($sql_reset);
    $stmt_reset->bind_param("i", $usuario_id);
    $stmt_reset->execute();
    $stmt_reset->close();

    http_response_code(200);
    echo json_encode(["message" => "Contraseña verificada"]);
} else {
    // Incrementar el contador de intentos fallidos
    $sql_increment = "UPDATE usuario SET intentos = intentos + 1 WHERE id_usuario = ?";
    $stmt_increment = $conexion->prepare($sql_increment);
    $stmt_increment->bind_param("i", $usuario_id);
    $stmt_increment->execute();
    $stmt_increment->close();

    // Verificar si el usuario debe ser bloqueado
    $intentos_restantes = 3 - ($row['intentos'] + 1);
    if ($intentos_restantes <= 0) {
        session_destroy(); // Destruir la sesión
        http_response_code(403);
        echo json_encode(["message" => "Usuario bloqueado. Redirigiendo al login...", "redirect" => "../vistas/login.php"]);
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Contraseña incorrecta. Intentos restantes: $intentos_restantes"]);
    }
}
?>