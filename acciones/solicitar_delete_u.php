<?php
session_start();
include('../conexion.php');

$loggedUserId = $_SESSION['id'] ?? 0;
$loggedUserType = $_SESSION['tipo'] ?? '';
$superAdminId = 8; 

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = 'ID de usuario no proporcionado.';
    header("Location: ../vistas/usuario.php");
    exit();
}

$idObjetivo = intval($_GET['id']);

// Si el usuario autenticado no es tipo admin
if ($loggedUserType !== 'admin') {
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = 'No tienes permisos para realizar esta acción.';
    header("Location: ../vistas/usuario.php");
    exit();
}

// Prevenir auto-solicitud de eliminación
if ($loggedUserId == $idObjetivo) {
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = 'No puedes solicitar eliminar tu propia cuenta.';
    header("Location: ../vistas/usuario.php");
    exit();
}

// Prevenir eliminar al ID 8
if ($idObjetivo == $superAdminId) {
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = 'No se puede eliminar la cuenta principal del sistema.';
    header("Location: ../vistas/usuario.php");
    exit();
}

// Si quien hace click es el ID 8, debería ir directamente a eliminar,
// pero por si acaso, lo redirigimos (no debería procesarse aquí sino directo).
if ($loggedUserId == $superAdminId) {
    header("Location: delete_u.php?id=" . $idObjetivo);
    exit();
}

// Comprobar si ya existe una solicitud pendiente
$checkSql = "SELECT id_solicitud FROM solicitudes_eliminacion_u WHERE id_objetivo = ? AND estado = 'pendiente'";
$checkStmt = $conexion->prepare($checkSql);
$checkStmt->bind_param("i", $idObjetivo);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    $_SESSION['estatus'] = 'info';
    $_SESSION['mensaje'] = 'Ya existe una solicitud de eliminación pendiente para este usuario.';
    header("Location: ../vistas/usuario.php");
    exit();
}
$checkStmt->close();

// Crear solicitud en la base de datos
$sql = "INSERT INTO solicitudes_eliminacion_u (id_solicitante, id_objetivo, estado) VALUES (?, ?, 'pendiente')";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $loggedUserId, $idObjetivo);

if ($stmt->execute()) {
    $_SESSION['estatus'] = 'success';
    $_SESSION['mensaje'] = 'Solicitud de eliminación enviada al Super Administrador Principal para su revisión.';
} else {
    $_SESSION['estatus'] = 'error';
    $_SESSION['mensaje'] = 'Error al enviar la solicitud: ' . $conexion->error;
}

$stmt->close();
header("Location: ../vistas/usuario.php");
exit();
?>
