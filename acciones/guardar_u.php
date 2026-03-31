<?php
session_start();
include('../conexion.php');
include('../models/notificaciones.php');
include('../models/bitacora.php');

// Obtener los datos del formulario
$usuario = $_POST['usuario'];
$nombre = $_POST['nombre'];
$cedula = $_POST['cedula'];
$nacionalidad = $_POST['nacionalidad'];
$correo = $_POST['correo'];
$clave = $_POST['clave'];
$confirmar_clave = $_POST['confirmar_clave'];
$tipo = $_POST['tipo'];
$pregunta = $_POST['pregunta'];
$respuesta = $_POST['respuesta'];
$respuesta_encrip = sha1($respuesta);
$pregunta2 = $_POST['pregunta2'];
$respuesta2 = $_POST['respuesta2'];
$respuesta_encrip2 = sha1($respuesta2);

// Verificar que las contraseñas coincidan
if ($clave !== $confirmar_clave) {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "Las contraseñas no coinciden";
    header("Location: ../vistas/registro_u.php");
    exit();
}

if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,16}$/', $clave)) {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "La contraseña no cumple con los requisitos de seguridad.";
    header("Location: ../vistas/registro_u.php");
    exit();
}

// Encriptar la contraseña
$clave_encrip = sha1($clave);

// Verificar duplicado de Usuario o Cédula
$sql_check = "SELECT id_usuario FROM usuario WHERE cedula = '$cedula' OR usuario = '$usuario'";
$res_check = mysqli_query($conexion, $sql_check);
if (mysqli_num_rows($res_check) > 0) {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "El Nombre de Usuario o la Cédula ya se encuentran registrados.";
    header("Location: ../vistas/registro_u.php");
    exit();
}

// Ruta por defecto de la foto si no se sube ninguna
$rutaDestino = '../img/fotos/default_profile.png';

// Si se subió imagen, procesarla
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
    $imagen = $_FILES['imagen'];
    $nombreImagen = time() . '_' . basename($imagen['name']);
    $rutaUpload = '../img/fotos/' . $nombreImagen;
    $tipoArchivo = strtolower(pathinfo($nombreImagen, PATHINFO_EXTENSION));
    $tamañoArchivo = $imagen['size'];

    // Validar el tamaño del archivo (10 MB)
    if ($tamañoArchivo > 10 * 1024 * 1024) {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "El tamaño del archivo no debe exceder los 10 MB.";
        header("Location: ../vistas/registro_u.php");
        exit();
    }

    // Validar el tipo de archivo
    $tiposPermitidos = ['jpg', 'jpeg', 'png'];
    if (!in_array($tipoArchivo, $tiposPermitidos)) {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Solo se permiten archivos JPG, JPEG y PNG.";
        header("Location: ../vistas/registro_u.php");
        exit();
    }

    // Mover archivo
    if (move_uploaded_file($imagen['tmp_name'], $rutaUpload)) {
        $rutaDestino = $rutaUpload;
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Error crítico al guardar la imagen en el servidor.";
        header("Location: ../vistas/registro_u.php");
        exit();
    }
}

// Insertar los datos en la base de datos
$sql = "INSERT INTO usuario (nombre, nacionalidad, cedula, usuario, correo, clave, pregunta, respuesta, pregunta2, respuesta2, foto, tipos) 
        VALUES ('$nombre', '$nacionalidad', '$cedula', '$usuario', '$correo', '$clave_encrip', '$pregunta', '$respuesta_encrip', '$pregunta2', '$respuesta_encrip2', '$rutaDestino', '$tipo')";
$result = mysqli_query($conexion, $sql);

if ($result) {
    $id_usuario = $_SESSION['id'];
    $_SESSION["estatus"] = "success";
    $_SESSION["mensaje"] = "Enhorabuena...! Registro Exitoso!";

    registrarAccion($conexion, 'Registro de Usuario', $id_usuario);
    header("Location: ../vistas/usuario.php");
} else {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "Registro Fallido...! " . mysqli_error($conexion);
    header("Location: ../vistas/registro_u.php");
}
exit();
?>