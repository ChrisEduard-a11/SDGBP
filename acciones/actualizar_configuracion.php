<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once("../conexion.php"); // Asegúrate de tener una conexión a la base de datos
include('../models/bitacora.php');

$pregunta1 = $_POST['pregunta1']; // Pregunta de seguridad 1
$pregunta2 = $_POST['pregunta2']; // Pregunta de seguridad 2
$respuesta1 = $_POST['respuesta1']; // Respuesta a la pregunta de seguridad 1
$respuesta2 = $_POST['respuesta2']; // Respuesta a la pregunta de seguridad 2
$password_actual = $_POST['password_actual']; // Contraseña actual ingresada por el usuario
$password = $_POST['password'];
$password1 = $_POST['password1'];
$telefono_form = $_POST['telefono'] ?? '';
$telegram_id_form = $_POST['telegram_id'] ?? '';

// Obtener datos actuales para verificar bloqueos
$sql_check = "SELECT telefono, telegram_id FROM usuario WHERE id_usuario = " . $_SESSION['id'];
$res_check = $conexion->query($sql_check);
$current_data = $res_check->fetch_assoc();

// Verificar que la contraseña actual sea correcta
$sql = "SELECT clave FROM usuario WHERE id_usuario = " . $_SESSION['id'];
$result = $conexion->query($sql);

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (sha1($password_actual) !== $user['clave']) {
        $_SESSION['estatus'] = "error";
        $_SESSION['mensaje'] = "La contraseña actual es incorrecta.";
        header("Location: ../vistas/configuracion_usuario.php");
        exit();
    }
} else {
    $_SESSION['estatus'] = "error";
    $_SESSION['mensaje'] = "Error al verificar la contraseña actual.";
    header("Location: ../vistas/configuracion_usuario.php");
    exit();
}

// Verificar que las contraseñas nuevas coincidan
if (!empty($password) && $password !== $password1) {
    $_SESSION['estatus'] = "error";
    $_SESSION['mensaje'] = "Error: las contraseñas nuevas no coinciden.";
    header("Location: ../vistas/configuracion_usuario.php");
    exit();
}

if (!empty($password) && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,16}$/', $password)) {
    $_SESSION["estatus"] = "error";
    $_SESSION["mensaje"] = "La nueva contraseña no cumple con los requisitos de seguridad.";
    header("Location: ../vistas/configuracion_usuario.php");
    exit();
}

// Procesar la imagen o borrado
if (isset($_POST['eliminar_foto']) && $_POST['eliminar_foto'] == '1') {
    // Obtener la foto actual
    $sql_foto = "SELECT foto FROM usuario WHERE id_usuario = " . $_SESSION['id'];
    $res_foto = mysqli_query($conexion, $sql_foto);
    $user_foto = mysqli_fetch_assoc($res_foto);
    $rutaFotoAnterior = $user_foto['foto'];
    
    // Eliminar la imagen anterior si no es la default
    if (!empty($rutaFotoAnterior) && file_exists($rutaFotoAnterior) && strpos($rutaFotoAnterior, 'default_profile.png') === false) {
        unlink($rutaFotoAnterior);
    }
    
    $rutaDestino = '../img/default_profile.png';
    $sql_imagen = ", foto = ?";
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
        header("Location: ../vistas/configuracion_usuario.php");
        exit();
    }

    // Validar el tipo de archivo
    $tiposPermitidos = ['jpg', 'jpeg', 'png', 'svg'];
    if (!in_array($tipoArchivo, $tiposPermitidos)) {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Solo se permiten archivos JPG, JPEG, PNG y SVG.";
        header("Location: ../vistas/configuracion_usuario.php");
        exit();
    }

    // Mover la imagen a la carpeta de destino
    if (move_uploaded_file($imagen['tmp_name'], $rutaDestino)) {
        // Obtener la ruta de la imagen anterior
        $sql = "SELECT foto FROM usuario WHERE id_usuario = " . $_SESSION['id'];
        $result = mysqli_query($conexion, $sql);
        $usuario = mysqli_fetch_assoc($result);
        $rutaFotoAnterior = $usuario['foto'];

        // Eliminar la imagen anterior si existe y no es la predeterminada
        if (!empty($rutaFotoAnterior) && file_exists($rutaFotoAnterior) && strpos($rutaFotoAnterior, 'default_profile.png') === false) {
            unlink($rutaFotoAnterior);
        }

        // Actualizar la ruta completa de la imagen en la base de datos
        $sql_imagen = ", foto = ?";
    } else {
        $_SESSION["estatus"] = "error";
        $_SESSION["mensaje"] = "Error al mover el archivo a la carpeta de destino.";
        header("Location: ../vistas/configuracion_usuario.php");
        exit();
    }
} else {
    $sql_imagen = "";
}

// --- MODIFICACIÓN CLAVE INICIA AQUÍ ---
$types = "";
$params = [];
$sql = "UPDATE usuario SET id_usuario = id_usuario";

if (!empty($sql_imagen)) {
    $sql .= $sql_imagen; // either empty or ', foto=?'
    $types .= "s";
    $params[] = $rutaDestino;
}

if (!empty($password)) {
    $hashed_password = sha1($password);
    $sql .= ", clave = ?, fecha_cambio_clave = CURRENT_DATE";
    $types .= "s";
    $params[] = $hashed_password;
}

// Solo si se envían respuestas nuevas
if (!empty($respuesta1) && !empty($respuesta2)) {
    $hashed_respuesta1 = sha1($respuesta1);
    $hashed_respuesta2 = sha1($respuesta2);

    $sql .= ", pregunta = ?, respuesta = ?, pregunta2 = ?, respuesta2 = ?";
    $types .= "ssss";
    array_push($params, $pregunta1, $hashed_respuesta1, $pregunta2, $hashed_respuesta2);
}

// Actualizar Teléfono y Telegram ID SOLO si están vacíos en la DB
if (!empty($telefono_form) && empty($current_data['telefono'])) {
    if (strlen($telefono_form) >= 10 && strlen($telefono_form) <= 11) {
        // Verificar duplicado
        $stmt_dup = $conexion->prepare("SELECT id_usuario FROM usuario WHERE telefono = ? AND id_usuario != ?");
        $stmt_dup->bind_param("si", $telefono_form, $_SESSION['id']);
        $stmt_dup->execute();
        if ($stmt_dup->get_result()->num_rows > 0) {
            $_SESSION['estatus'] = "error";
            $_SESSION['mensaje'] = "Este número de teléfono ya está vinculado a otra cuenta.";
            header("Location: ../vistas/configuracion_usuario.php");
            exit();
        }
        $sql .= ", telefono = ?";
        $types .= "s";
        $params[] = $telefono_form;
    } else {
        $_SESSION['estatus'] = "error";
        $_SESSION['mensaje'] = "El formato del teléfono es inválido (10-11 dígitos).";
        header("Location: ../vistas/configuracion_usuario.php");
        exit();
    }
}

if (!empty($telegram_id_form) && empty($current_data['telegram_id'])) {
    // Verificar duplicado
    $stmt_dup_tg = $conexion->prepare("SELECT id_usuario FROM usuario WHERE telegram_id = ? AND id_usuario != ?");
    $stmt_dup_tg->bind_param("si", $telegram_id_form, $_SESSION['id']);
    $stmt_dup_tg->execute();
    if ($stmt_dup_tg->get_result()->num_rows > 0) {
        $_SESSION['estatus'] = "error";
        $_SESSION['mensaje'] = "Este Telegram ID ya está vinculado a otra cuenta.";
        header("Location: ../vistas/configuracion_usuario.php");
        exit();
    }
    $sql .= ", telegram_id = ?";
    $types .= "s";
    $params[] = $telegram_id_form;
}

$sql .= " WHERE id_usuario = ?";
$types .= "i";
$params[] = $_SESSION['id'];
// --- MODIFICACIÓN CLAVE TERMINA AQUÍ ---


// Ejecutar la consulta
$stmt_update = $conexion->prepare($sql);
if ($stmt_update && !empty($types)) {
    $stmt_update->bind_param($types, ...$params);
    $result_update = $stmt_update->execute();
} else {
    $result_update = false;
}

if ($result_update) {
    // Ya no se actualiza $_SESSION['nombre'] porque el nombre no se actualiza en la BD.
    
    if (!empty($sql_imagen) && isset($rutaDestino)) {
        $_SESSION['foto'] = $rutaDestino; // Actualizar la foto en la sesión si se cambió
    }

    // Registrar la acción en la bitácora
    registrarAccion($conexion, 'Actualización de Configuración', $_SESSION['id']);

    $_SESSION['estatus'] = "success";
    $_SESSION['mensaje'] = "Configuración actualizada con éxito.";
} else {
    $_SESSION['estatus'] = "error";
    $_SESSION['mensaje'] = "Error al actualizar la configuración: " . $conexion->error;
}

header("Location: ../vistas/configuracion_usuario.php");
exit();