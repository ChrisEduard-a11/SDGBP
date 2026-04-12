<?php
session_start();
include('../conexion.php');
include('../models/bitacora.php');

// Obtener los datos del formulario
$id_usuario = $_POST['id_usuario'];
$nuevo_usuario = $_POST['usuario'];
$nacionalidad = $_POST['nacionalidad'];
$cedula = $_POST['cedula'];
$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$clave = $_POST['clave'];
$confirmar_clave = $_POST['confirmar_clave'];
$tipo = $_POST['tipo'];

// Obtener el valor actual del campo `usuario` desde la base de datos
$sql = "SELECT usuario FROM usuario WHERE id_usuario = '$id_usuario'";
$result = mysqli_query($conexion, $sql);
$row = mysqli_fetch_assoc($result);
$usuario_actual = $row['usuario'];

// Verificar si el campo `usuario` ha cambiado
if ($nuevo_usuario !== $usuario_actual) {
    $sql_usuario = ", usuario='$nuevo_usuario'";
} else {
    $sql_usuario = ""; // No actualizar el campo `usuario`
}

// Verificar duplicado de Usuario o Cédula (excluyendo al usuario actual)
$sql_check = "SELECT id_usuario FROM usuario WHERE (cedula = '$cedula' OR usuario = '$nuevo_usuario') AND id_usuario != '$id_usuario'";
$res_check = mysqli_query($conexion, $sql_check);
if (mysqli_num_rows($res_check) > 0) {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "El Nombre de Usuario o la Cédula ya se encuentran vinculados a otra cuenta.";
    header("Location: ../vistas/edit_u.php?id=$id_usuario");
    exit();
}

// Verificar que las contraseñas coincidan
if (!empty($clave) && $clave !== $confirmar_clave) {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "Las contraseñas no coinciden";
    header("Location: ../vistas/edit_u.php?id=$id_usuario");
    exit();
}

if (!empty($clave) && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,16}$/', $clave)) {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "La nueva contraseña no cumple con los requisitos de seguridad.";
    header("Location: ../vistas/edit_u.php?id=$id_usuario");
    exit();
}

// Encriptar la contraseña si se ha proporcionado una nueva
if (!empty($clave)) {
    $clave_encrip = sha1($clave);
    $sql_clave = ", clave='$clave_encrip', fecha_cambio_clave=CURRENT_DATE";
} else {
    $sql_clave = "";
}

// Procesar la imagen
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
    $imagen = $_FILES['imagen'];
    $nombreImagen = time() . '_' . $imagen['name'];
    $rutaDestino = '../img/fotos_perfil/' . $nombreImagen;
    $tipoArchivo = strtolower(pathinfo($nombreImagen, PATHINFO_EXTENSION));
    $tamañoArchivo = $imagen['size'];

    // Validar el tamaño del archivo (10 MB = 10 * 1024 * 1024 bytes)
    if ($tamañoArchivo > 10 * 1024 * 1024) {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "El tamaño del archivo no debe exceder los 10 MB.";
        header("Location: ../vistas/edit_u.php?id=$id_usuario");
        exit();
    }

    // Validar el tipo de archivo
    $tiposPermitidos = ['jpg', 'jpeg', 'png', 'svg'];
    if (!in_array($tipoArchivo, $tiposPermitidos)) {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Solo se permiten archivos JPG, JPEG, PNG y SVG.";
        header("Location: ../vistas/edit_u.php?id=$id_usuario");
        exit();
    }

    // Mover la imagen a la carpeta de destino
    if (move_uploaded_file($imagen['tmp_name'], $rutaDestino)) {
        // Obtener la ruta de la imagen anterior
        $sql = "SELECT foto FROM usuario WHERE id_usuario = '$id_usuario'";
        $result = mysqli_query($conexion, $sql);
        $usuario = mysqli_fetch_assoc($result);
        $rutaFotoAnterior = $usuario['foto'];

        // Eliminar la imagen anterior si existe y no es la por defecto
        if (file_exists($rutaFotoAnterior) && strpos($rutaFotoAnterior, 'default_profile.png') === false) {
            unlink($rutaFotoAnterior);
        }

        // Actualizar la ruta de la imagen en la base de datos
        $sql_imagen = ", foto='$rutaDestino'";
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Error al subir la imagen";
        header("Location: ../vistas/edit_u.php?id=$id_usuario");
        exit();
    }
} else {
    $sql_imagen = "";
}

// Actualizar los datos del usuario en la base de datos
$sql = "UPDATE usuario SET nacionalidad='$nacionalidad', cedula='$cedula', nombre='$nombre', correo='$correo', tipos='$tipo' $sql_usuario $sql_clave $sql_imagen WHERE id_usuario='$id_usuario'";
$result = mysqli_query($conexion, $sql);

if ($result) {
    // Actualizar las variables de sesión con los nuevos valores si el usuario editado es el usuario actual
    if ($_SESSION['id'] == $id_usuario) {
        $_SESSION['nombre'] = $nombre; // Actualizar el nombre en la sesión
        $_SESSION['correo'] = $correo; // Actualizar el correo en la sesión
        if (!empty($sql_imagen)) {
            $_SESSION['foto'] = $rutaDestino; // Actualizar la foto en la sesión si se cambió
        }
    }

    $_SESSION["estatus"] = "success";
    $_SESSION["mensaje"] = "Usuario actualizado exitosamente";

    $accion_bitacora = 'Actualizó Usuario - Usuario: ' . $nuevo_usuario . ' | Nombre: ' . $nombre . ' | Rol: ' . $tipo;
    registrarAccion($conexion, $accion_bitacora, $id_usuario);
    header("Location: ../vistas/usuario.php");
} else {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "Error al actualizar el usuario: " . mysqli_error($conexion);
    header("Location: ../vistas/edit_u.php?id=$id_usuario");
}
exit();