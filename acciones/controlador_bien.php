<?php
session_start();
include('../conexion.php');
include_once('../models/bitacora.php'); // Asegúrate de incluir el archivo donde está registrarAccion

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_bien = $_POST['nombre'];
    $categoria = $_POST['categoria'];
    $serial = $_POST['serial'];
    $codigo_alternativo = $_POST['codigo_alternativo'];
    $fecha_adquisicion = $_POST['fecha_adquisicion'];

    // Si el usuario seleccionó "Otro (especificar)"
    if ($id_bien === "nuevo") {
        $nombre = trim($_POST['nuevo_nombre']);
        $descripcion = trim($_POST['nueva_descripcion']);
    } else {
        // Consultar nombre y descripción reales del bien seleccionado
        $sql_bien = "SELECT nombre, descripcion FROM bienes WHERE id = ?";
        $stmt_bien = $conexion->prepare($sql_bien);
        $stmt_bien->bind_param("i", $id_bien);
        $stmt_bien->execute();
        $result_bien = $stmt_bien->get_result();

        if ($row_bien = $result_bien->fetch_assoc()) {
            $nombre = $row_bien['nombre'];
            $descripcion = $row_bien['descripcion'];
        } else {
            $mensaje = "Error: No se encontró el bien seleccionado.";
            $_SESSION['estatus'] = 'error';
            $_SESSION['mensaje'] = $mensaje;
            header("Location: ../vistas/registro_bien.php");
            exit();
        }
        $stmt_bien->close();
    }

    // Validar los datos
    if (empty($nombre) || empty($descripcion) || empty($categoria) || empty($serial) || empty($codigo_alternativo) || empty($fecha_adquisicion)) {
        $mensaje = "Todos los campos son obligatorios.";
        $_SESSION['estatus'] = 'error';
        $_SESSION['mensaje'] = $mensaje;
        header("Location: ../vistas/registro_bien.php");
        exit();
    } else {
        // Insertar los datos en la base de datos
        $sql = "INSERT INTO bienes (nombre, descripcion, serial, categoria_id, codigo, fecha_adquisicion) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssss", $nombre, $descripcion, $serial, $categoria, $codigo_alternativo, $fecha_adquisicion);

        if ($stmt->execute()) {
            $mensaje = "Bien nacional registrado exitosamente.";
            // Registrar en bitácora
            if (isset($_SESSION['id'])) {
                $accion_bitacora = 'Registró Bien Nacional - Nombre: ' . $nombre;
                registrarAccion($conexion, $accion_bitacora, $_SESSION['id']);
            }
            $_SESSION['estatus'] = 'success';
            $_SESSION['mensaje'] = $mensaje;
            header("Location: ../vistas/lista_bienes.php");
            exit();
        } else {
            // Mostrar el error de la consulta SQL
            $mensaje = "Error al registrar el bien: " . $stmt->error;
            $_SESSION['estatus'] = 'error';
            $_SESSION['mensaje'] = $mensaje;
            header("Location: ../vistas/registro_bien.php");
            exit();
        }
        $stmt->close();
    }
}
?>