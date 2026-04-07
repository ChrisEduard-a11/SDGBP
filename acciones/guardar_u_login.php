<?php
session_start();
include('../conexion.php');
include('../models/notificaciones.php');

// Obtener los datos del formulario
$usuario = $_POST['usuario'];
$nombre = $_POST['nombre'];
$nacionalidad = $_POST['nacionalidad'];
$cedula = $_POST['cedula'];
$correo = $_POST['correo'];
$clave = $_POST['clave'];
$confirmar_clave = $_POST['confirmar_clave'];

$pregunta = $_POST['pregunta'];
$respuesta = $_POST['respuesta'];
$respuesta_encrip = sha1($respuesta);
$pregunta2 = $_POST['pregunta2'];
$respuesta2 = $_POST['respuesta2'];
$respuesta_encrip2 = sha1($respuesta2);

// Encriptar la contraseña
$clave_encrip = sha1($clave);

// Verificar que las contraseñas coincidan
if ($clave !== $confirmar_clave) {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "Las contraseñas no coinciden.";
    header("Location: ../vistas/register.php");
    exit();
}

if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,16}$/', $clave)) {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "La contraseña no cumple con los requisitos de seguridad.";
    header("Location: ../vistas/register.php");
    exit();
}

// Manejar la subida de la foto
$foto_predeterminada = "../img/default_profile.png"; // Ruta de la foto predeterminada
$foto = $foto_predeterminada;

if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
    $directorio_destino = "../img/fotos/"; // Directorio donde se guardará la foto
    $nombre_archivo = basename($_FILES['foto']['name']);
    $ruta_archivo = $directorio_destino . $nombre_archivo;

    // Crear el directorio si no existe
    if (!is_dir($directorio_destino)) {
        mkdir($directorio_destino, 0777, true);
    }

    // Mover el archivo subido al directorio de destino
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_archivo)) {
        $foto = $ruta_archivo; // Usar la foto subida
    }
}

// Insertar el nuevo usuario
$sql = "INSERT INTO usuario (nombre, nacionalidad, cedula, usuario, correo, clave, pregunta, respuesta, pregunta2, respuesta2, tipos, aprobado, foto) 
        VALUES ('$nombre', '$nacionalidad', '$cedula', '$usuario', '$correo', '$clave_encrip', '$pregunta', '$respuesta_encrip', '$pregunta2', '$respuesta_encrip2', 'upu', 0, '$foto')";
$result = mysqli_query($conexion, $sql);

if ($result) {
    // Notificar a los administradores sobre el nuevo usuario pendiente
    $titulo_noti = "Nuevo Usuario Pendiente";
    $mensaje_noti = "El usuario @$usuario ($nombre) se ha registrado y requiere aprobación.";
    crearNotificacion($conexion, null, $titulo_noti, $mensaje_noti, 'warning', 'fas fa-user-clock', null, 'admin');

    $_SESSION["estatus"] = "success";
    $_SESSION["mensaje"] = "Registro Exitoso! El usuario debe ser aprobado por un administrador.";
    header("Location: ../vistas/login.php");
} else {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "Registro Fallido: " . mysqli_error($conexion);
    header("Location: ../vistas/register.php");
}
exit();
?>