<?php
session_start();
include('../conexion.php');
include('../models/bitacora.php');

$superAdminId = 8; // Cambia por el ID real de tu super admin

// Si la petición es POST (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $claveSuperAdmin = $_POST['clave_superadmin'] ?? '';

    // No permitir borrar sin ID válido
    if (!$id_usuario) {
        echo json_encode(['success' => false, 'error' => 'ID de usuario no proporcionado']);
        exit;
    }

    // No permitir que el super admin se borre a sí mismo
    if ($id_usuario == $superAdminId) {
        echo json_encode(['success' => false, 'error' => 'No puedes borrar al super admin']);
        exit;
    }

    // Si el usuario logueado es el super admin, puede borrar directamente
    if ($_SESSION['id'] == $superAdminId) {
        $puedeBorrar = true;
    } else {
        // Si no es el super admin, validar la clave del super admin
        $sql = "SELECT clave FROM usuario WHERE id_usuario = $superAdminId";
        $res = $conexion->query($sql);
        if ($row = $res->fetch_assoc()) {
            // Suponiendo que usas SHA-1 para las contraseñas
            if (sha1($claveSuperAdmin) === $row['clave']) {
                $puedeBorrar = true;
            } else {
                echo json_encode(['success' => false, 'error' => 'Contraseña del super admin incorrecta']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Super admin no encontrado']);
            exit;
        }
    }

    if ($puedeBorrar) {
        // Obtener la información del usuario, incluyendo la foto
        $sql = "SELECT foto FROM usuario WHERE id_usuario = $id_usuario";
        $result = mysqli_query($conexion, $sql);

        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            $foto = $row['foto'];

            // Eliminar la foto del servidor
            $rutaFoto = '../img/fotos/' . $foto;
            if ($foto && file_exists($rutaFoto)) {
                unlink($rutaFoto);
            }

            // Eliminar el usuario de la base de datos
            $sql_delete = "DELETE FROM usuario WHERE id_usuario = $id_usuario";
            $result_delete = mysqli_query($conexion, $sql_delete);

            // Registrar en bitácora
            if (isset($_SESSION['id'])) {
                registrarAccion($conexion, 'Eliminó un Usuario', $_SESSION['id']);
            }

            echo json_encode(['success' => true]);
            exit;
        } else {
            echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
            exit;
        }
    }
} else {
    // Si la petición es GET, puedes mantener la lógica anterior o mostrar error
    header("Location: ../vistas/usuario.php");
    exit;
}