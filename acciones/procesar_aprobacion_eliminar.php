<?php
session_start();
include('../conexion.php');

$superAdminId = 8;
$loggedUserId = $_SESSION['id'] ?? 0;

$action = $_REQUEST['action'] ?? '';
$idSolicitud = intval($_REQUEST['id_solicitud'] ?? 0);

if ($loggedUserId != $superAdminId) {
    if (in_array($action, ['aprobar'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'No autorizado.']);
    } else {
        die("No autorizado.");
    }
    exit();
}

if ($action == 'rechazar') {
    $sql = "UPDATE solicitudes_eliminacion_u SET estado = 'rechazado' WHERE id_solicitud = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idSolicitud);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['estatus'] = 'success';
    $_SESSION['mensaje'] = 'La solicitud de eliminación ha sido rechazada exitosamente.';
    header("Location: ../vistas/usuario.php");
    exit();
}

if ($action == 'aprobar') {
    header('Content-Type: application/json');
    $codigoIngresado = $_POST['codigo_2fa'] ?? '';
    
    if (empty($codigoIngresado)) {
        echo json_encode(['status' => 'error', 'message' => 'Código no proporcionado.']);
        exit();
    }
    
    // Validar código
    $sql = "SELECT id_objetivo, codigo_2fa, expiracion_2fa FROM solicitudes_eliminacion_u WHERE id_solicitud = ? AND estado = 'pendiente'";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idSolicitud);
    $stmt->execute();
    $solicitud = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$solicitud) {
        echo json_encode(['status' => 'error', 'message' => 'Solicitud no encontrada o ya procesada.']);
        exit();
    }
    
    if ($solicitud['codigo_2fa'] !== $codigoIngresado) {
        echo json_encode(['status' => 'error', 'message' => 'El código ingresado es incorrecto. Intenta nuevamente.']);
        exit();
    }
    
    if (strtotime($solicitud['expiracion_2fa']) < time()) {
        echo json_encode(['status' => 'error', 'message' => 'El código ha expirado. Rechaza o solicita enviar uno nuevo.']);
        exit();
    }
    
    // Todo válido, ejecutar eliminación real
    $idObjetivo = $solicitud['id_objetivo'];
    
    // Primero, obtener la imagen para borrarla si existe (como en delete_u.php)
    $stmt_img = mysqli_prepare($conexion, "SELECT foto FROM usuario WHERE id_usuario = ?");
    mysqli_stmt_bind_param($stmt_img, "i", $idObjetivo);
    mysqli_stmt_execute($stmt_img);
    mysqli_stmt_bind_result($stmt_img, $foto_path);
    mysqli_stmt_fetch($stmt_img);
    mysqli_stmt_close($stmt_img);

    // Borramos el usuario
    $sqlDelete = "DELETE FROM usuario WHERE id_usuario = ?";
    $stmtDel = $conexion->prepare($sqlDelete);
    $stmtDel->bind_param("i", $idObjetivo);
    
    if ($stmtDel->execute()) {
        // Eliminar foto del servidor si no es la por defecto
        if ($foto_path && $foto_path !== '../assets/img/default.jpg' && file_exists($foto_path)) {
            unlink($foto_path);
        }
    
        // Marcar solicitud como aprobada
        $sqlUpd = "UPDATE solicitudes_eliminacion_u SET estado = 'aprobado' WHERE id_solicitud = ?";
        $stmtUpd = $conexion->prepare($sqlUpd);
        $stmtUpd->bind_param("i", $idSolicitud);
        $stmtUpd->execute();
        $stmtUpd->close();
        
        echo json_encode(['status' => 'success', 'message' => 'Identidad confirmada mediante 2FA y usuario eliminado correctamente del sistema.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos al tratar de eliminar al usuario.']);
    }
    $stmtDel->close();
}
?>
