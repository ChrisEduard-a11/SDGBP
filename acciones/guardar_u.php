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

// Encriptar la contraseña
$clave_encrip = sha1($clave);

// Procesar la imagen
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
    $imagen = $_FILES['imagen'];
    $nombreImagen = time() . '_' . $imagen['name'];
    $rutaDestino = '../img/fotos/' . $nombreImagen;
    $tipoArchivo = strtolower(pathinfo($nombreImagen, PATHINFO_EXTENSION));
    $tamañoArchivo = $imagen['size'];

    // Validar el tamaño del archivo (10 MB = 10 * 1024 * 1024 bytes)
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

    // Mover la imagen a la carpeta de destino
    if (move_uploaded_file($imagen['tmp_name'], $rutaDestino)) {
        // Insertar los datos en la base de datos
        $sql = "INSERT INTO usuario (nombre, nacionalidad, cedula, usuario, correo, clave, pregunta, respuesta, pregunta2, respuesta2, foto, tipos) 
                VALUES ('$nombre', '$nacionalidad', '$cedula', '$usuario', '$correo', '$clave_encrip', '$pregunta', '$respuesta_encrip', '$pregunta2', '$respuesta_encrip2','$rutaDestino', '$tipo')";
        $result = mysqli_query($conexion, $sql);

        if ($result) {
            $id_usuario = $_SESSION['id'];
            $_SESSION["estatus"] = "success";
            $_SESSION["mensaje"] = "Enhorabuena...! Registro Exitoso!";


            registrarAccion($conexion, 'Registro de Usuario', $id_usuario);
            header("Location: ../vistas/usuario.php");
        } else {
            $_SESSION["estatus"] = "error";
            $_SESSION["mensaje"] = "Registro Fallido...!";
            header("Location: ../vistas/registro_u.php");
        }
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Error al subir la imagen";
        header("Location: ../vistas/registro_u.php");
    }
} else {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "No se ha seleccionado ninguna imagen o ha ocurrido un error. Error: " . $_FILES['imagen']['error'];
    header("Location: ../vistas/registro_u.php");
}
exit();
?>