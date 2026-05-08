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
$telefono = $_POST['telefono'];
$telegram_id = $_POST['telegram_id'] ?? '';
$clave = $_POST['clave'];
$confirmar_clave = $_POST['confirmar_clave'];
$tipo = $_POST['tipo'];

// Obtener el valor actual del campo `usuario` desde la base de datos
$sql = "SELECT usuario FROM usuario WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$usuario_actual = $row['usuario'];

// Verificar si el campo `usuario` ha cambiado
if ($nuevo_usuario !== $usuario_actual) {
    $sql_usuario = ", usuario=?";
} else {
    $sql_usuario = ""; // No actualizar el campo `usuario`
}

// Verificar duplicado de Usuario, Cédula, Teléfono o Telegram ID (excluyendo al usuario actual)
$sql_check = "SELECT id_usuario, usuario, cedula, telefono, telegram_id FROM usuario WHERE (cedula = ? OR usuario = ? OR (telefono = ? AND telefono != '') OR (telegram_id = ? AND telegram_id != '')) AND id_usuario != ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("ssssi", $cedula, $nuevo_usuario, $telefono, $telegram_id, $id_usuario);
$stmt_check->execute();
$res_check = $stmt_check->get_result();
if ($res_check->num_rows > 0) {
    $dup = mysqli_fetch_assoc($res_check);
    $msg = "El Nombre de Usuario o la Cédula ya existen.";
    if ($dup['telefono'] === $telefono && !empty($telefono)) $msg = "El número de teléfono ya está vinculado a otra cuenta.";
    if ($dup['telegram_id'] === $telegram_id && !empty($telegram_id)) $msg = "El Telegram ID ya está vinculado a otra cuenta.";
    
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = $msg;
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
    $sql_clave = ", clave=?, fecha_cambio_clave=CURRENT_DATE";
} else {
    $sql_clave = "";
}

$sql_imagen = "";
$rutaDestino = "";

// Procesar la imagen o borrado
if (isset($_POST['eliminar_foto']) && $_POST['eliminar_foto'] == '1') {
    // Obtener la foto actual
    $sql_foto = "SELECT foto FROM usuario WHERE id_usuario = ?";
    $stmt_foto = $conexion->prepare($sql_foto);
    $stmt_foto->bind_param("i", $id_usuario);
    $stmt_foto->execute();
    $user_foto = $stmt_foto->get_result()->fetch_assoc();
    $rutaFotoAnterior = $user_foto['foto'];
    
    // Eliminar la imagen anterior si no es la default
    if (!empty($rutaFotoAnterior) && file_exists($rutaFotoAnterior) && strpos($rutaFotoAnterior, 'default_profile.png') === false) {
        unlink($rutaFotoAnterior);
    }
    
    $rutaDestino = '../img/default_profile.png';
    $sql_imagen = ", foto=?";
} else if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
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
        $sql = "SELECT foto FROM usuario WHERE id_usuario = ?";
        $stmt_del = $conexion->prepare($sql);
        $stmt_del->bind_param("i", $id_usuario);
        $stmt_del->execute();
        $user_foto_del = $stmt_del->get_result()->fetch_assoc();
        $rutaFotoAnterior = $user_foto_del['foto'];

        // Eliminar la imagen anterior si existe y no es la por defecto
        if (!empty($rutaFotoAnterior) && file_exists($rutaFotoAnterior) && strpos($rutaFotoAnterior, 'default_profile.png') === false) {
            unlink($rutaFotoAnterior);
        }

        // Actualizar la ruta de la imagen en la base de datos
        $sql_imagen = ", foto=?";
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Error al subir la imagen";
        header("Location: ../vistas/edit_u.php?id=$id_usuario");
        exit();
    }
} else {
    $sql_imagen = "";
}

// Construcción dinámica de parámetros para UPDATE
$types = "sssssss";
$params = [$nacionalidad, $cedula, $nombre, $correo, $telefono, $telegram_id, $tipo];

if (!empty($sql_usuario)) {
    $types .= "s";
    $params[] = $nuevo_usuario;
}
if (!empty($sql_clave)) {
    $types .= "s";
    $params[] = $clave_encrip;
}
if (!empty($sql_imagen)) {
    $types .= "s";
    $params[] = $rutaDestino;
}

$types .= "i";
$params[] = $id_usuario;

// Actualizar los datos del usuario en la base de datos
$sql = "UPDATE usuario SET nacionalidad=?, cedula=?, nombre=?, correo=?, telefono=?, telegram_id=?, tipos=? $sql_usuario $sql_clave $sql_imagen WHERE id_usuario=?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param($types, ...$params);
$result = $stmt->execute();

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