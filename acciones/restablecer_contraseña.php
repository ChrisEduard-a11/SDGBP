<?php
session_start();
include('../conexion.php');

$token = $_POST['token'];
$nueva_contrasena = $_POST['nueva_contraseña'];
$confirmar_contrasena = $_POST['confirmar_contraseña'];

// Verificar si las contraseñas coinciden
if ($nueva_contrasena !== $confirmar_contrasena) {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "Las contraseñas no coinciden.";
    header("Location: ../vistas/recu_correo.php");
    exit();
}

if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,16}$/', $nueva_contrasena)) {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "La contraseña no cumple con los requisitos de seguridad.";
    header("Location: ../vistas/recu_correo.php");
    exit();
}

// Verificar el token
$sql = "SELECT * FROM recuperacion WHERE token = '$token' AND expira > NOW()";
$result = mysqli_query($conexion, $sql);

// Manejar errores en la consulta SQL
if (!$result) {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "Error en la consulta: " . mysqli_error($conexion);
    header("Location: ../vistas/recu_correo.php");
    exit();
}

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $correo = $row['correo'];

    // Actualizar la contraseña
    $nueva_contrasena_encrip = sha1($nueva_contrasena);
    $sql = "UPDATE usuario SET clave = '$nueva_contrasena_encrip', fecha_cambio_clave = CURRENT_DATE WHERE correo = '$correo'";
    if (!mysqli_query($conexion, $sql)) {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Error al actualizar la contraseña: " . mysqli_error($conexion);
        header("Location: ../vistas/recu_correo.php");
        exit();
    }

    // Eliminar el token
    $sql = "DELETE FROM recuperacion WHERE token = '$token'";
    if (!mysqli_query($conexion, $sql)) {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Error al eliminar el token: " . mysqli_error($conexion);
        header("Location: ../vistas/recu_correo.php");
        exit();
    }

    $_SESSION["estatus"] = "success";
    $_SESSION["mensaje"] = "Tu contraseña ha sido restablecida exitosamente.";
    header("Location: ../vistas/login.php");
} else {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "El enlace de recuperación ha expirado o es inválido.";
    header("Location: ../vistas/login.php");
}
?>