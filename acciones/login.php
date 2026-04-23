<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('../conexion.php');
include('../models/bitacora.php');

// Obtener los datos del formulario
$usuario = $_POST['usuario'];
$clavee = $_POST['clave'];
$clave = sha1($clavee);

// Consulta segura para obtener el usuario (Sensible a mayúsculas/minúsculas)
$sql = "SELECT * FROM usuario WHERE BINARY usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$count = $result->num_rows;

if ($count == 1) {
    $id_usuario = $row['id_usuario'];

    // Verificar si el usuario está bloqueado manualmente
    if (isset($row['bloqueado']) && $row['bloqueado'] == 1) {
        $_SESSION['estatus'] = 'error';
        $_SESSION['mensaje'] = 'Usuario bloqueado por el administrador.';
        header("Location: ../vistas/login.php");
        exit();
    }
    
    // Verificar si el usuario está aprobado
    if ($row['aprobado'] == 0) {
        $_SESSION['estatus'] = 'error';
        $_SESSION['mensaje'] = 'Usuario no aprobado.';
        header("Location: ../vistas/login.php");
        exit();
    }

    // Verificar si el usuario está bloqueado por intentos fallidos
    if ($row['intentos'] >= 3) {
        $_SESSION['estatus'] = 'error';
        $_SESSION['mensaje'] = 'Usuario bloqueado por múltiples intentos fallidos.';
        header("Location: ../vistas/login.php");
        exit();
    }

    // Verificar si la contraseña es correcta
    if ($row['clave'] !== $clave) {
        $sql_increment_intentos = "UPDATE usuario SET intentos = intentos + 1 WHERE usuario = ?";
        $stmt_inc = $conexion->prepare($sql_increment_intentos);
        $stmt_inc->bind_param("s", $usuario);
        $stmt_inc->execute();

        $_SESSION['estatus'] = 'error';
        $_SESSION['mensaje'] = 'Credenciales incorrectas.';
        header("Location: ../vistas/login.php");
        exit();
    }

    // Verificar sesión concurrente activa
    if (!empty($row['session_token']) && !empty($row['ultima_actividad'])) {
        $ultima_actividad_time = strtotime($row['ultima_actividad']);
        $tiempo_inactivo = time() - $ultima_actividad_time;
        // Si el usuario tuvo actividad hace menos de 30 segundos
        if ($tiempo_inactivo <= 30) {
            $_SESSION['estatus'] = 'error';
            $_SESSION['mensaje'] = 'Esta cuenta está actualmente en uso en otro dispositivo o ventana. Cierra dicha sesión o espera 30 segundos. Para tu seguridad, no permitimos accesos múltiples al mismo tiempo.';
            header("Location: ../vistas/login.php");
            exit();
        }
    }

    // Generar y guardar el token de sesión único
    $token = bin2hex(random_bytes(16));
    $_SESSION['session_token'] = $token;
    $sql_token = "UPDATE usuario SET session_token = ? WHERE id_usuario = ?";
    $stmt_token = $conexion->prepare($sql_token);
    $stmt_token->bind_param("si", $token, $id_usuario);
    $stmt_token->execute();

    // Restablecer los intentos fallidos
    $sql_reset_intentos = "UPDATE usuario SET intentos = 0 WHERE usuario = ?";
    $stmt_reset = $conexion->prepare($sql_reset_intentos);
    $stmt_reset->bind_param("s", $usuario);
    $stmt_reset->execute();

    // Establecer las variables de sesión
    $_SESSION["user"] = $usuario;
    $_SESSION['nombre'] = $row['nombre'];
    $_SESSION["correo"] = $row["correo"];
    $_SESSION["nacionalidad"] = $row['nacionalidad'];
    $_SESSION["cedula"] = $row['cedula'];
    $_SESSION["ip"] = $_SERVER["REMOTE_ADDR"];
    $_SESSION["tipo"] = $row["tipos"];  // <-- El rol se asigna directamente desde la base de datos
    $_SESSION['foto'] = $row['foto'];
    $_SESSION['id'] = $row['id_usuario'];
    $_SESSION['type'] = 'success';
    $_SESSION['alert'] = 'Bienvenido, ' . $row['nombre'] . '!';
    $_SESSION['ultima_conexion'] = $row['ultima_conexion'];

    // Actualizar la última conexión del usuario
    $sql_update = "UPDATE usuario SET ultima_conexion = NOW() WHERE id_usuario = ?";
    $stmt_update = $conexion->prepare($sql_update);
    $stmt_update->bind_param("i", $id_usuario);
    $stmt_update->execute();

    // Registrar la acción en la bitácora
    registrarAccion($conexion, 'Inicio de Sesión', $row['id_usuario']);

    header("Location: ../vistas/inicio.php");
    exit();
} else {
    // Incrementar los intentos fallidos si las credenciales son incorrectas
    $sql_increment_intentos = "UPDATE usuario SET intentos = intentos + 1 WHERE usuario = ?";
    $stmt_inc = $conexion->prepare($sql_increment_intentos);
    $stmt_inc->bind_param("s", $usuario);
    $stmt_inc->execute();

    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = 'Credenciales incorrectas.';
    header("Location: ../vistas/login.php");
    exit();
}
?>