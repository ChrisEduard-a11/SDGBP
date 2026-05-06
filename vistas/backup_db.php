<?php
require_once("../models/header.php");

require_once __DIR__ . "/../PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/../PHPMailer/src/Exception.php";
require_once __DIR__ . "/../PHPMailer/src/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function extractTableFromQuery($query) {
    // Mejor patrón: buscar FROM tabla, ignorando subqueries
    if (preg_match('/\bFROM\s+`?(\w+)`?/i', $query, $matches)) {
        return $matches[1];
    }
    return null;
}

$allowedBackupUserId = 8;
if (!isset($_SESSION['id']) || intval($_SESSION['id']) !== $allowedBackupUserId) {
    header("Location: ../vistas/inicio.php");
    exit;
}

$backupVerified = $_SESSION['backup_db_2fa_verified'] ?? false;
$backupPasswordVerified = $_SESSION['backup_db_password_verified'] ?? false;
$backupPasswordError = '';
$backupPasswordSuccess = '';
$backup2faError = '';
$backup2faSuccess = '';
$backup2faSent = !empty($_SESSION['backup_db_2fa_sent']);
$backup2faEmail = $_SESSION['correo'] ?? '';
$backupReady = $backupVerified && $backupPasswordVerified;

$sqlExecution = [
    'success' => false,
    'error' => null,
    'results' => [],
    'query' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'send_backup_2fa') {
            if (empty($backup2faEmail)) {
                $backup2faError = 'No hay un correo registrado en la sesión para enviar el código 2FA.';
            } else {
                $codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $_SESSION['backup_db_2fa_code'] = $codigo;
                $_SESSION['backup_db_2fa_expires'] = time() + 900;
                $_SESSION['backup_db_2fa_sent'] = true;
                $backup2faSent = true;

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'soporte.sdgbp2024@gmail.com';
                    $mail->Password = 'ktwf cyvz rmyh lqfy';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port = 465;
                    $mail->setFrom('soporte.sdgbp2024@gmail.com', 'SDGBP Security');
                    $mail->addAddress($backup2faEmail);
                    $mail->isHTML(true);
                    $mail->CharSet = 'UTF-8';
                    $mail->Subject = 'Código 2FA de acceso a Backup DB';
                    $mail->Body = "<p>Hola,</p><p>Se ha solicitado acceso a la vista de respaldo de base de datos. Utiliza el siguiente código de 6 dígitos para continuar:</p><h2>{$codigo}</h2><p>Este código expira en 15 minutos.</p>";
                    $mail->send();
                    $backup2faSuccess = 'Código 2FA enviado a tu correo. Revisa tu bandeja y escribe el código a continuación.';
                } catch (Exception $e) {
                    $backup2faError = 'No se pudo enviar el código 2FA: ' . $mail->ErrorInfo;
                }
            }
        } elseif ($_POST['action'] === 'confirm_backup_password') {
            $backupPasswordValue = trim($_POST['backup_password'] ?? '');
            $backupUserId = $_SESSION['id'] ?? $_SESSION['id_usuario'] ?? null;

            if (empty($backupPasswordValue)) {
                $backupPasswordError = 'Ingresa tu contraseña actual para continuar.';
            } elseif (!$backupUserId) {
                $backupPasswordError = 'No se pudo verificar tu sesión. Vuelve a iniciar sesión.';
            } else {
                $passwordHash = sha1($backupPasswordValue);
                $stmt = $conexion->prepare("SELECT clave, intentos FROM usuario WHERE id_usuario = ? LIMIT 1");
                $stmt->bind_param('i', $backupUserId);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $stmt->close();

                if (!$row) {
                    $backupPasswordError = 'No se encontró el usuario en la base de datos.';
                } elseif ($row['intentos'] >= 3) {
                    session_unset();
                    session_destroy();
                    header('Location: ../vistas/login.php?msg=Usuario bloqueado');
                    exit;
                } elseif ($passwordHash !== $row['clave']) {
                    $sql_increment = "UPDATE usuario SET intentos = intentos + 1 WHERE id_usuario = ?";
                    $stmt_inc = $conexion->prepare($sql_increment);
                    $stmt_inc->bind_param('i', $backupUserId);
                    $stmt_inc->execute();
                    $stmt_inc->close();

                    $intentos_restantes = max(0, 3 - ($row['intentos'] + 1));
                    if ($intentos_restantes <= 0) {
                        session_unset();
                        session_destroy();
                        header('Location: ../vistas/login.php?msg=Usuario bloqueado');
                        exit;
                    }
                    $backupPasswordError = 'Contraseña incorrecta. Intentos restantes: ' . $intentos_restantes;
                } else {
                    $sql_reset = "UPDATE usuario SET intentos = 0 WHERE id_usuario = ?";
                    $stmt_reset = $conexion->prepare($sql_reset);
                    $stmt_reset->bind_param('i', $backupUserId);
                    $stmt_reset->execute();
                    $stmt_reset->close();

                    $_SESSION['backup_db_password_verified'] = true;
                    $backupPasswordVerified = true;
                    $backupPasswordSuccess = 'Contraseña verificada. Ahora falta el código 2FA si aún no lo has confirmado.';
                }
            }
        } elseif ($_POST['action'] === 'confirm_backup_2fa') {
            $codigoIngresado = trim($_POST['backup_2fa_code'] ?? '');
            $codigoGuardado = $_SESSION['backup_db_2fa_code'] ?? '';
            $expires = $_SESSION['backup_db_2fa_expires'] ?? 0;

            if (empty($codigoIngresado)) {
                $backup2faError = 'Ingresa el código 2FA enviado por correo.';
            } elseif (time() > $expires) {
                $backup2faError = 'El código ha expirado. Vuelve a solicitar uno nuevo.';
                unset($_SESSION['backup_db_2fa_code'], $_SESSION['backup_db_2fa_expires'], $_SESSION['backup_db_2fa_sent']);
                $backup2faSent = false;
            } elseif ($codigoIngresado !== $codigoGuardado) {
                $backup2faError = 'Código incorrecto. Verifica el correo y vuelve a intentarlo.';
            } else {
                $_SESSION['backup_db_2fa_verified'] = true;
                unset($_SESSION['backup_db_2fa_code'], $_SESSION['backup_db_2fa_expires'], $_SESSION['backup_db_2fa_sent']);
                $backupVerified = true;
                $backup2faSuccess = 'Verificación 2FA completada. Ahora falta tu contraseña si aún no la has ingresado.';
            }
        } elseif ($_POST['action'] === 'update_row') {
            $table = $_POST['edit_table'];
            $keyColumn = $_POST['edit_key_column'];
            $keyValue = $_POST['edit_key_value'];
            $sets = [];
            foreach ($_POST as $k => $v) {
                if (strpos($k, 'edit_') === 0 && $k !== 'edit_table' && $k !== 'edit_key_column' && $k !== 'edit_key_value') {
                    $column = substr($k, 5);
                    $sets[] = "`$column` = '" . mysqli_real_escape_string($conexion, $v) . "'";
                }
            }
            $query = "UPDATE `$table` SET " . implode(', ', $sets) . " WHERE `$keyColumn` = '" . mysqli_real_escape_string($conexion, $keyValue) . "'";
            try {
                if (!$backupReady) {
                    $sqlExecution['error'] = 'Necesitas verificar contraseña y 2FA antes de modificar filas.';
                } else {
                    mysqli_query($conexion, $query);
                    if (mysqli_errno($conexion) === 0) {
                        $sqlExecution['success'] = true;
                        $sqlExecution['results'][] = ['type' => 'info', 'message' => 'Fila actualizada correctamente.'];
                    } else {
                        $sqlExecution['error'] = mysqli_error($conexion);
                    }
                }
            } catch (Exception $e) {
                $sqlExecution['error'] = $e->getMessage();
            }
        } elseif ($_POST['action'] === 'delete_row') {
            $table = $_POST['delete_table'];
            $keyColumn = $_POST['delete_key_column'];
            $keyValue = $_POST['delete_key_value'];
            $query = "DELETE FROM `$table` WHERE `$keyColumn` = '" . mysqli_real_escape_string($conexion, $keyValue) . "'";
            try {
                if (!$backupReady) {
                    $sqlExecution['error'] = 'Necesitas verificar contraseña y 2FA antes de eliminar filas.';
                } else {
                    mysqli_query($conexion, $query);
                    if (mysqli_errno($conexion) === 0) {
                        $sqlExecution['success'] = true;
                        $sqlExecution['results'][] = ['type' => 'info', 'message' => 'Fila eliminada correctamente.'];
                    } else {
                        $sqlExecution['error'] = mysqli_error($conexion);
                    }
                }
            } catch (Exception $e) {
                $sqlExecution['error'] = $e->getMessage();
            }
        }
    }
    elseif (isset($_POST['sql_console_query'])) {
        if (!$backupReady) {
            $sqlExecution['error'] = 'Necesitas verificar contraseña y 2FA antes de usar la consola SQL.';
        } else {
            $sqlExecution['query'] = trim($_POST['sql_console_query']);
            if ($sqlExecution['query'] === '') {
                $sqlExecution['error'] = 'Debes ingresar una consulta SQL.';
            } else {
                // Dividir consultas por ; y ejecutar cada una individualmente para evitar cuelgues
                $queries = array_filter(array_map('trim', explode(';', $sqlExecution['query'])));
                $sqlExecution['results'] = [];
                $hasError = false;
                foreach ($queries as $index => $query) {
                    if (empty($query)) continue;
                    try {
                        $result = mysqli_query($conexion, $query);
                        if ($result === false) {
                            $sqlExecution['results'][] = [
                                'type' => 'error',
                                'message' => 'Consulta ' . ($index + 1) . ' falló: ' . mysqli_error($conexion),
                            ];
                            $hasError = true;
                            break; // Detener en el primer error para evitar más problemas
                        } else {
                            if (is_object($result)) {
                                // Es un SELECT o similar con resultados
                                $columns = [];
                                while ($field = mysqli_fetch_field($result)) {
                                    $columns[] = $field->name;
                                }
                                $rows = [];
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $rows[] = $row;
                                }
                                $table = extractTableFromQuery($query);
                                $sqlExecution['results'][] = [
                                    'type' => 'select',
                                    'columns' => $columns,
                                    'rows' => $rows,
                                    'table' => $table,
                                ];
                                mysqli_free_result($result);
                            } else {
                                // Comando como INSERT, UPDATE, DELETE
                                $affected = mysqli_affected_rows($conexion);
                                $sqlExecution['results'][] = [
                                    'type' => 'info',
                                    'message' => 'Consulta ' . ($index + 1) . ' ejecutada: ' . $affected . ' filas afectadas.',
                                ];
                            }
                        }
                    } catch (Exception $e) {
                        $sqlExecution['results'][] = [
                            'type' => 'error',
                            'message' => 'Consulta ' . ($index + 1) . ' falló: ' . $e->getMessage(),
                        ];
                        $hasError = true;
                        break;
                    }
                }
                if (!$hasError) {
                    $sqlExecution['success'] = true;
                }
            }
        }
    }
}

$backupVerified = $_SESSION['backup_db_2fa_verified'] ?? false;
$backupPasswordVerified = $_SESSION['backup_db_password_verified'] ?? false;
$backupReady = $backupVerified && $backupPasswordVerified;

$tables = [];
$relatedTables = [];
$resultTables = mysqli_query($conexion, "SHOW TABLES");
if ($resultTables) {
    while ($row = mysqli_fetch_row($resultTables)) {
        $tables[] = $row[0];
    }
}

$relationQuery = "SELECT TABLE_NAME, REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE CONSTRAINT_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL";
$relationResult = mysqli_query($conexion, $relationQuery);
if ($relationResult) {
    while ($rel = mysqli_fetch_assoc($relationResult)) {
        $table = $rel['TABLE_NAME'];
        $referenced = $rel['REFERENCED_TABLE_NAME'];
        if (!isset($relatedTables[$table])) {
            $relatedTables[$table] = [];
        }
        if (!in_array($referenced, $relatedTables[$table], true)) {
            $relatedTables[$table][] = $referenced;
        }
        if (!isset($relatedTables[$referenced])) {
            $relatedTables[$referenced] = [];
        }
        if (!in_array($table, $relatedTables[$referenced], true)) {
            $relatedTables[$referenced][] = $table;
        }
    }
}
?>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4">
        <h3 class="mt-4 text-center">Respaldo de Base de Datos</h3>
        <ol class="breadcrumb mb-4 bg-light p-3 rounded shadow-sm">
            <li class="breadcrumb-item"><a href="inicio.php" class="text-decoration-none">Inicio</a></li>
            <li class="breadcrumb-item"><a href="usuario.php" class="text-decoration-none">Usuario</a></li>
            <li class="breadcrumb-item active">Respaldo BD</li>
        </ol>

        <?php if (!$backupReady): ?>
        <style>
        .premium-auth-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .premium-auth-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            padding: 1.5rem;
            text-align: center;
        }
        .inst-input-wrapper {
            display: flex;
            align-items: center;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 0 1rem;
            margin-bottom: 1.25rem;
            transition: all 0.3s;
        }
        .inst-input-wrapper:focus-within {
            border-color: #f18000;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(241, 128, 0, 0.1);
        }
        .inst-icon {
            color: #94a3b8;
            font-size: 1.1rem;
            margin-right: 0.5rem;
            transition: color 0.3s;
        }
        .inst-input-wrapper:focus-within .inst-icon {
            color: #f18000;
        }
        .premium-input {
            width: 100%;
            background: transparent;
            border: none;
            padding: 1rem 0.5rem;
            color: #0f172a;
            outline: none;
            font-weight: 500;
        }
        .code-input-wrapper {
            height: 80px;
            justify-content: center;
            margin-bottom: 0;
        }
        .code-input {
            width: 100%;
            background: transparent;
            border: none;
            padding: 0.5rem 0;
            color: #0f172a;
            font-size: 2.5rem;
            outline: none;
            font-weight: 800;
            letter-spacing: 12px;
            text-align: center;
        }
        .code-input::placeholder {
            color: #cbd5e1;
            font-weight: 500;
            font-size: 2rem;
            letter-spacing: 5px;
        }
        .btn-premium {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-premium-primary {
            background: #0f172a;
            color: #fff;
        }
        .btn-premium-primary:hover {
            background: #f18000;
            transform: scale(1.02);
            box-shadow: 0 10px 15px -3px rgba(241, 128, 0, 0.3);
        }
        .btn-premium-success {
            background: #10b981;
            color: #fff;
        }
        .btn-premium-success:hover {
            background: #059669;
            transform: scale(1.02);
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
        }
        </style>
        
        <div class="row justify-content-center mt-5 mb-5">
            <div class="col-md-6 col-lg-5">
                <div class="premium-auth-container">
                    <div class="premium-auth-header">
                        <h4 class="font-weight-light my-2 m-0"><i class="fas fa-shield-alt text-warning me-2"></i> Autenticación Segura</h4>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <p class="text-muted text-center mb-4">Debes verificar tu identidad antes de acceder a la base de datos para mantener la integridad del sistema.</p>
                        
                        <?php if ($backupPasswordSuccess): ?>
                            <div class="alert alert-success border-0 shadow-sm rounded-3"><i class="fas fa-check-circle me-1"></i> <?php echo htmlspecialchars($backupPasswordSuccess); ?></div>
                        <?php endif; ?>
                        <?php if ($backupPasswordError): ?>
                            <div class="alert alert-danger border-0 shadow-sm rounded-3"><i class="fas fa-exclamation-triangle me-1"></i> <?php echo htmlspecialchars($backupPasswordError); ?></div>
                        <?php endif; ?>
                        <?php if ($backup2faSent): ?>
                            <div class="alert alert-info border-0 shadow-sm rounded-3"><i class="fas fa-info-circle me-1"></i> <?php echo htmlspecialchars($backup2faSuccess ?: 'Código enviado.'); ?></div>
                        <?php endif; ?>
                        <?php if ($backup2faError): ?>
                            <div class="alert alert-danger border-0 shadow-sm rounded-3"><i class="fas fa-times-circle me-1"></i> <?php echo htmlspecialchars($backup2faError); ?></div>
                        <?php endif; ?>

                        <?php if (!$backupPasswordVerified): ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="confirm_backup_password">
                                <div class="inst-input-wrapper">
                                    <div class="inst-icon"><i class="fas fa-lock"></i></div>
                                    <input type="password" name="backup_password" id="inputPassword" class="premium-input" placeholder="Contraseña actual" required autocomplete="off">
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="btn-premium btn-premium-primary">
                                        Verificar Contraseña <i class="fas fa-arrow-right"></i>
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <?php if (!$backupVerified): ?>
                                <form method="POST">
                                    <input type="hidden" name="action" value="confirm_backup_2fa">
                                    
                                    <div class="text-center mb-3">
                                        <div style="background: rgba(241, 128, 0, 0.1); width: 60px; height: 60px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; color: #f18000; font-size: 1.5rem; margin-bottom: 1rem;">
                                            <i class="fas fa-key"></i>
                                        </div>
                                    </div>

                                    <div class="inst-input-wrapper code-input-wrapper">
                                        <input type="text" name="backup_2fa_code" id="input2FA" class="code-input" placeholder="000000" maxlength="6" autocomplete="off" onkeypress="return soloNumeros(event)" required>
                                    </div>
                                    <div class="mt-4">
                                        <button type="submit" class="btn-premium btn-premium-success">
                                            Confirmar y Entrar <i class="fas fa-unlock-alt"></i>
                                        </button>
                                    </div>
                                </form>
                                <form method="POST" class="mt-3 text-center">
                                    <input type="hidden" name="action" value="send_backup_2fa">
                                    <button type="submit" class="btn btn-link text-decoration-none text-muted" style="transition: color 0.3s;" onmouseover="this.style.color='#f18000'" onmouseout="this.style.color='#6c757d'">
                                        <i class="fas fa-envelope me-1"></i> Reenviar código 2FA
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <script>
            function soloNumeros(e) {
                var key = window.Event ? e.which : e.keyCode
                return (key >= 48 && key <= 57)
            }
        </script>
        <?php else: ?>
        <style>
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .panel-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.1rem;
        }
        .panel-header.secondary {
            background: linear-gradient(135deg, #334155 0%, #475569 100%);
        }
        .action-card {
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 1.5rem;
            height: 100%;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }
        .action-card:hover {
            border-color: #cbd5e1;
            background: #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
            transform: translateY(-2px);
        }
        .action-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-left: auto;
            margin-right: auto;
        }
        .action-icon.green { color: #10b981; background: rgba(16, 185, 129, 0.1); }
        .action-icon.blue { color: #0284c7; background: rgba(2, 132, 199, 0.1); }
        .btn-modern {
            border-radius: 8px;
            padding: 0.8rem 1.2rem;
            font-weight: 600;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border: none;
            width: 100%;
        }
        .btn-modern-primary { background: #0f172a; color: #fff; }
        .btn-modern-primary:hover { background: #f18000; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 10px rgba(241, 128, 0, 0.3); }
        .btn-modern-success { background: #10b981; color: #fff; }
        .btn-modern-success:hover { background: #059669; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 6px rgba(16,185,129,0.2); }
        .btn-modern-outline { background: transparent; color: #0f172a; border: 1px solid #cbd5e1; }
        .btn-modern-outline:hover { background: #f1f5f9; border-color: #94a3b8; }
        .btn-modern-sm { padding: 0.5rem 1rem; width: auto; border-radius: 6px; }
        .form-check-modern {
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            background: #fff;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        .form-check-modern:hover { border-color: #cbd5e1; background: #f8fafc; }
        .form-check-modern input:checked + label { font-weight: 600; color: #0f172a; }
        .form-check-modern:has(input:checked) { border-color: #0f172a; background: #f8fafc; box-shadow: inset 0 0 0 1px #0f172a; }
        .form-check-modern .form-check-input {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            margin-top: 0;
            cursor: pointer;
        }
        .form-check-modern label { width: 100%; cursor: pointer; margin-bottom: 0; display: block; }
        .custom-file-upload {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 2.5rem 1rem;
            text-align: center;
            background: #fff;
            transition: all 0.3s;
            cursor: pointer;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .custom-file-upload:hover { border-color: #f18000; background: #fff8f1; }
        .custom-file-upload input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        .sql-console { background: #1e293b; color: #f8fafc; font-family: 'Consolas', monospace; border-radius: 8px; padding: 1.25rem; border: none; font-size: 0.95rem; line-height: 1.6; }
        .sql-console:focus { background: #0f172a; color: #fff; box-shadow: 0 0 0 4px rgba(241,128,0,0.2) !important; outline: none; }
        .sql-console::placeholder { color: #64748b; }
        .tag-pill { background: #e2e8f0; color: #334155; border-radius: 20px; padding: 0.3rem 0.8rem; font-size: 0.75rem; font-weight: 600; border: none; transition: all 0.2s; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;}
        .tag-pill:hover { background: #cbd5e1; color: #0f172a; transform: scale(1.05); }
        .tag-pill-action { background: #dbeafe; color: #1e40af; }
        .tag-pill-action:hover { background: #bfdbfe; }
        
        .result-table-wrapper {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }
        
        /* Optimización de tabla SQL - Estilo Hoja de Cálculo */
        .sql-datatable {
            width: 100% !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
        }
        .sql-datatable th {
            white-space: nowrap;
            background-color: #f1f5f9 !important;
            color: #475569 !important;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 15px !important;
            border-bottom: 2px solid #e2e8f0 !important;
        }
        .sql-datatable td {
            font-size: 0.85rem;
            color: #334155;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 10px 15px !important;
            white-space: nowrap; /* Evita que las filas se hagan muy altas */
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sql-datatable tr:hover td {
            background-color: #f8fafc;
        }
        </style>
        
        <div class="glass-panel mt-4">
            <div class="panel-header">
                <i class="fas fa-database text-warning"></i> <span>Gestión de Datos: Respaldo y Restauración</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <!-- Export Card -->
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="action-card">
                            <div class="text-center">
                                <div class="action-icon green"><i class="fas fa-cloud-download-alt"></i></div>
                                <h5 class="fw-bold mb-1 text-dark">Exportar Respaldo</h5>
                                <p class="text-muted small mb-4">Descarga una copia segura de la base de datos.</p>
                            </div>
                            <form action="../config/exportar_db.php" method="GET" class="d-flex flex-column h-100">
                                <div class="mb-4">
                                    <div class="form-check-modern">
                                        <input class="form-check-input" type="radio" name="export_type" id="export_both" value="both" checked>
                                        <label class="form-check-label" for="export_both">Estructura + Datos</label>
                                    </div>
                                    <div class="form-check-modern">
                                        <input class="form-check-input" type="radio" name="export_type" id="export_structure" value="structure">
                                        <label class="form-check-label" for="export_structure">Solo estructura</label>
                                    </div>
                                    <div class="form-check-modern">
                                        <input class="form-check-input" type="radio" name="export_type" id="export_data" value="data">
                                        <label class="form-check-label" for="export_data">Solo datos</label>
                                    </div>
                                </div>
                                <div class="mt-auto d-grid gap-2">
                                    <button type="submit" class="btn-modern btn-modern-success">
                                        <i class="fas fa-download"></i> Descargar SQL Completo
                                    </button>
                                    <button type="button" class="btn-modern btn-modern-outline" data-bs-toggle="modal" data-bs-target="#exportTableModal">
                                        <i class="fas fa-table"></i> Exportación Avanzada...
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Import Card -->
                    <div class="col-md-6">
                        <div class="action-card">
                            <div class="text-center">
                                <div class="action-icon blue"><i class="fas fa-cloud-upload-alt"></i></div>
                                <h5 class="fw-bold mb-1 text-dark">Importar Restauración</h5>
                                <p class="text-muted small mb-4">Sube un archivo previo para recuperar información.</p>
                            </div>
                            <form action="../config/importar_db.php" method="POST" enctype="multipart/form-data" onsubmit="return validateFormImportBD()" class="d-flex flex-column h-100">
                                <div class="mb-4">
                                    <div class="custom-file-upload">
                                        <input type="file" name="archivoBD" id="archivo_sql" accept=".sql" onchange="document.getElementById('fileNameLabel').innerText = this.files[0] ? this.files[0].name : 'Haz clic o arrastra un archivo .sql aquí'">
                                        <i class="fas fa-file-import mb-2 fs-2 text-secondary"></i>
                                        <div id="fileNameLabel" class="text-secondary fw-semibold">Haz clic o arrastra un archivo .sql aquí</div>
                                    </div>
                                </div>
                                <div class="mt-auto d-grid">
                                    <button type="submit" class="btn-modern btn-modern-primary">
                                        <i class="fas fa-upload"></i> Subir y Restaurar <span class="d-none d-sm-inline">Base de Datos</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-panel">
            <div class="panel-header secondary">
                <i class="fas fa-terminal text-info"></i> <span>Consola SQL Interactiva</span>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-success bg-opacity-10 border-success border-opacity-25 text-success d-flex align-items-center rounded-3 p-3 mb-4">
                    <i class="fas fa-shield-check fs-4 me-3"></i>
                    <div class="small">
                        <strong>Acceso Grantizado:</strong> Contraseña y factor 2FA están verificados. Puedes ejecutar sentencias de forma segura. Usa <code>;</code> para separar múltiples consultas.
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-xl-6 mb-3 mb-xl-0">
                        <h6 class="fw-bold text-secondary mb-3 small text-uppercase letter-spacing-1"><i class="fas fa-bolt text-warning me-1"></i> Sugerencias de Consultas</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="tag-pill tag-pill-action" onclick="insertSQLTemplate('SELECT * FROM ;')">SELECT *</button>
                            <button type="button" class="tag-pill tag-pill-action" onclick="insertSQLTemplate('SELECT COUNT(*) FROM ;')">COUNT</button>
                            <button type="button" class="tag-pill tag-pill-action" onclick="insertSQLTemplate('SELECT * FROM  WHERE id = ;')">SELECT WHERE</button>
                            <button type="button" class="tag-pill tag-pill-action" onclick="insertSQLTemplate('INSERT INTO  () VALUES ();')">INSERT</button>
                            <button type="button" class="tag-pill tag-pill-action" onclick="insertSQLTemplate('UPDATE  SET  WHERE id = ;')">UPDATE</button>
                            <button type="button" class="tag-pill tag-pill-action" onclick="insertSQLTemplate('DELETE FROM  WHERE id = ;')">DELETE</button>
                            <button type="button" class="tag-pill" onclick="insertSQLTemplate('ORDER BY id DESC LIMIT 10;')">+ ORDER DESC</button>
                            <button type="button" class="tag-pill" onclick="insertSQLTemplate('INNER JOIN  ON .id = .id;')">+ INNER JOIN</button>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <h6 class="fw-bold text-secondary mb-3 small text-uppercase letter-spacing-1"><i class="fas fa-table text-info me-1"></i> Explorador: Tablas Disponibles</h6>
                        <div class="d-flex flex-wrap gap-2" style="max-height: 100px; overflow-y: auto; padding-right: 5px;">
                            <?php foreach ($tables as $table): ?>
                                <button type="button" class="tag-pill" onclick="insertTableName('<?php echo htmlspecialchars($table, ENT_QUOTES); ?>')"><i class="fas fa-table text-muted me-1"></i> <?php echo htmlspecialchars($table); ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <?php if ($sqlExecution['error']): ?>
                    <div class="alert alert-danger rounded-3 border-0 shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($sqlExecution['error']); ?></div>
                <?php elseif ($sqlExecution['success']): ?>
                    <div class="alert alert-success rounded-3 border-0 shadow-sm"><i class="fas fa-check-circle me-2"></i>Consulta ejecutada correctamente.</div>
                <?php endif; ?>

                <form action="backup_db.php" method="POST">
                    <div class="mb-3 position-relative">
                        <label for="sql_console_query" class="visually-hidden">Consola SQL</label>
                        <i class="fas fa-chevron-right position-absolute" style="top: 20px; left: 18px; color: #94a3b8; font-size: 1.1rem;"></i>
                        <textarea id="sql_console_query" name="sql_console_query" class="form-control sql-console ps-5 w-100" rows="5" placeholder="SELECT * FROM usuario; -- Escribe tus sentencias aquí..." spellcheck="false"><?php echo htmlspecialchars($sqlExecution['query']); ?></textarea>
                    </div>
                    <div class="d-flex gap-3 justify-content-end">
                        <button type="button" class="btn-modern btn-modern-outline btn-modern-sm" onclick="document.getElementById('sql_console_query').value = '';">
                            <i class="fas fa-eraser"></i> Limpiar
                        </button>
                        <button type="submit" class="btn-modern btn-modern-primary btn-modern-sm">
                            <i class="fas fa-play"></i> Ejecutar SQL
                        </button>
                    </div>
                </form>

                <?php if (!empty($sqlExecution['results'])): ?>
                    <div class="mt-5">
                        <h6 class="fw-bold mb-3 text-uppercase text-secondary letter-spacing-1"><i class="fas fa-poll-h me-1"></i> Resultados de Ejecución</h6>
                        <?php foreach ($sqlExecution['results'] as $index => $result): ?>
                            <?php if ($result['type'] === 'select'): ?>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="m-0 fw-bold text-primary">Consulta <?php echo $index + 1; ?> (SELECT)</h6>
                                        <?php if ($result['table']): ?>
                                            <span class="badge bg-light text-dark border"><i class="fas fa-table text-muted me-1"></i> <?php echo htmlspecialchars($result['table']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="result-table-wrapper table-responsive">
                                        <table class="table table-sm table-hover mb-0 sql-datatable">
                                            <thead class="table-light text-secondary">
                                                <tr>
                                                    <?php foreach ($result['columns'] as $column): ?>
                                                        <th class="fw-semibold px-3 py-2 border-bottom"><?php echo htmlspecialchars($column); ?></th>
                                                    <?php endforeach; ?>
                                                    <th class="fw-semibold px-3 py-2 border-bottom text-end">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (count($result['rows']) === 0): ?>
                                                    <tr><td colspan="<?php echo count($result['columns']) + 1; ?>" class="text-center py-4 text-muted"><i class="fas fa-inbox fs-4 mb-2 d-block text-black-50"></i>No se encontraron filas coincidentes.</td></tr>
                                                <?php else: ?>
                                                    <?php foreach ($result['rows'] as $row): ?>
                                                        <tr>
                                                            <?php foreach ($result['columns'] as $column): ?>
                                                                <td class="px-3 align-middle" title="<?php echo htmlspecialchars((string)$row[$column]); ?>"><?php echo htmlspecialchars((string)$row[$column]); ?></td>
                                                            <?php endforeach; ?>
                                                            <?php if ($result['table'] || true): // Mostrar siempre ?>
                                                                <td class="px-3 align-middle text-end text-nowrap">
                                                                    <button type="button" class="btn btn-sm btn-outline-warning shadow-none" data-bs-toggle="modal" data-bs-target="#editModal" onclick="openEditModal('<?php echo htmlspecialchars($result['table'] ?: ''); ?>', <?php echo json_encode($result['columns']); ?>, <?php echo json_encode($row); ?>)"><i class="fas fa-edit"></i></button>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger shadow-none ms-1" onclick="confirmDelete('<?php echo htmlspecialchars($result['table'] ?: ''); ?>', '<?php echo htmlspecialchars($result['columns'][0]); ?>', '<?php echo htmlspecialchars((string)$row[$result['columns'][0]]); ?>')"><i class="fas fa-trash-alt"></i></button>
                                                                </td>
                                                            <?php endif; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php elseif ($result['type'] === 'error'): ?>
                                <div class="alert alert-danger rounded-3 border-0 shadow-sm py-2 px-3 mb-2">
                                    <i class="fas fa-times-circle me-1"></i> <strong>C-<?php echo $index + 1; ?>:</strong> <?php echo htmlspecialchars($result['message']); ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info rounded-3 border-0 shadow-sm py-2 px-3 mb-2">
                                    <i class="fas fa-check-double me-1"></i> <strong>C-<?php echo $index + 1; ?>:</strong> <?php echo htmlspecialchars($result['message']); ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; // fin if (!$backupReady) else ?>
    </div>

    <div class="modal fade" id="exportTableModal" tabindex="-1" aria-labelledby="exportTableModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="exportTableModalLabel"><i class="fas fa-table me-2"></i> Exportar tabla específica</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form action="../config/exportar_db.php" method="GET">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="table_name" class="form-label">Tabla</label>
                            <select id="table_name" name="table_name" class="form-select" required>
                                <option value="">Selecciona una tabla</option>
                                <?php foreach ($tables as $table): ?>
                                    <option value="<?php echo htmlspecialchars($table, ENT_QUOTES); ?>"><?php echo htmlspecialchars($table); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="tableRelationsAlert" class="alert alert-warning d-none">
                            <strong>Advertencia:</strong> esta tabla tiene relaciones con las siguientes tablas:
                            <span id="relatedTablesList"></span>.
                            Para evitar errores en la importación, exporta también estas tablas o usa la opción "Estructura + Datos" para ellas.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo de exportación</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_type" id="table_export_both" value="both" checked>
                                <label class="form-check-label" for="table_export_both">Estructura + Datos</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_type" id="table_export_structure" value="structure">
                                <label class="form-check-label" for="table_export_structure">Solo estructura</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_type" id="table_export_data" value="data">
                                <label class="form-check-label" for="table_export_data">Solo datos</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Exportar tabla</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Editar fila</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="editForm" action="backup_db.php" method="POST">
                    <div class="modal-body" id="editModalBody">
                        <!-- Campos se generan dinámicamente -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const relatedTables = <?php echo json_encode($relatedTables, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        const tableSelect = document.getElementById('table_name');
        const alertBox = document.getElementById('tableRelationsAlert');
        const relatedList = document.getElementById('relatedTablesList');

        if (tableSelect) {
            tableSelect.addEventListener('change', function() {
                const table = this.value;
                if (!table || !relatedTables[table] || relatedTables[table].length === 0) {
                    alertBox.classList.add('d-none');
                    relatedList.textContent = '';
                    return;
                }

                const related = relatedTables[table].map(function(item) {
                    return '<span class="badge bg-secondary me-1">' + item + '</span>';
                }).join('');
                relatedList.innerHTML = related;
                alertBox.classList.remove('d-none');
            });
        }

        function insertTableName(tableName) {
            const textarea = document.getElementById('sql_console_query');
            if (!textarea) {
                return;
            }
            const cursorPosition = textarea.selectionStart || 0;
            const currentValue = textarea.value;
            const before = currentValue.substring(0, cursorPosition);
            const after = currentValue.substring(cursorPosition);
            textarea.value = before + tableName + after;
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = cursorPosition + tableName.length;
        }

        function insertSQLTemplate(template) {
            const textarea = document.getElementById('sql_console_query');
            if (!textarea) {
                return;
            }
            const cursorPosition = textarea.selectionStart || 0;
            const currentValue = textarea.value;
            const before = currentValue.substring(0, cursorPosition);
            const after = currentValue.substring(cursorPosition);
            textarea.value = before + template + after;
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = cursorPosition + template.length;
        }

        function generateUpdate(table, columns, row) {
            let sets = [];
            for (let i = 0; i < columns.length; i++) {
                sets.push(columns[i] + "='" + row[columns[i]].replace(/'/g, "\\'") + "'");
            }
            const query = "UPDATE " + table + " SET " + sets.join(", ") + " WHERE " + columns[0] + "='" + row[columns[0]] + "';";
            insertSQLTemplate(query);
        }

        function generateDelete(table, keyColumn, keyValue) {
            const query = "DELETE FROM " + table + " WHERE " + keyColumn + "='" + keyValue + "';";
            insertSQLTemplate(query);
        }

        function openEditModal(table, columns, row) {
            if (!table) {
                table = prompt('Ingresa el nombre de la tabla para editar:');
                if (!table) return;
            }
            const body = document.getElementById('editModalBody');
            body.innerHTML = '';
            for (let i = 0; i < columns.length; i++) {
                const div = document.createElement('div');
                div.className = 'mb-3';
                div.innerHTML = `
                    <label class="form-label">${columns[i]}</label>
                    <input type="text" class="form-control" name="edit_${columns[i]}" value="${row[columns[i]] || ''}">
                `;
                body.appendChild(div);
            }
            // Agregar campos hidden para tabla, key, etc.
            const hiddenTable = document.createElement('input');
            hiddenTable.type = 'hidden';
            hiddenTable.name = 'edit_table';
            hiddenTable.value = table;
            body.appendChild(hiddenTable);

            const hiddenKey = document.createElement('input');
            hiddenKey.type = 'hidden';
            hiddenKey.name = 'edit_key_column';
            hiddenKey.value = columns[0];
            body.appendChild(hiddenKey);

            const hiddenKeyValue = document.createElement('input');
            hiddenKeyValue.type = 'hidden';
            hiddenKeyValue.name = 'edit_key_value';
            hiddenKeyValue.value = row[columns[0]];
            body.appendChild(hiddenKeyValue);

            const hiddenAction = document.createElement('input');
            hiddenAction.type = 'hidden';
            hiddenAction.name = 'action';
            hiddenAction.value = 'update_row';
            body.appendChild(hiddenAction);
        }

        function confirmDelete(table, keyColumn, keyValue) {
            if (!table) {
                table = prompt('Ingresa el nombre de la tabla para eliminar:');
                if (!table) return;
            }
            if (confirm('¿Estás seguro de eliminar esta fila?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'backup_db.php';
                form.style.display = 'none';

                const inputTable = document.createElement('input');
                inputTable.name = 'delete_table';
                inputTable.value = table;
                form.appendChild(inputTable);

                const inputKey = document.createElement('input');
                inputKey.name = 'delete_key_column';
                inputKey.value = keyColumn;
                form.appendChild(inputKey);

                const inputValue = document.createElement('input');
                inputValue.name = 'delete_key_value';
                inputValue.value = keyValue;
                form.appendChild(inputValue);

                const inputAction = document.createElement('input');
                inputAction.name = 'action';
                inputAction.value = 'delete_row';
                form.appendChild(inputAction);

                document.body.appendChild(form);
                form.submit();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof $ !== 'undefined' && $.fn.DataTable) {
                $('.sql-datatable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                    },
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
                    responsive: false, // Desactivar para permitir scroll horizontal nativo más fluido en escritorio
                    autoWidth: false,
                    dom: '<"row mb-2"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                });
            }
        });
    </script>

<?php require_once("../models/footer.php"); ?>
