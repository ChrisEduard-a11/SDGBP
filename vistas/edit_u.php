<?php
require_once("../models/header.php");
?>

<style>
    :root {
        --premium-violet: #8b5cf6;
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(139, 92, 246, 0.15);
    }
    [data-theme="dark"] {
        --glass-bg: rgba(30, 41, 59, 0.75);
        --glass-border: rgba(255, 255, 255, 0.08);
    }
    .form-section-card {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-radius: 1.25rem;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 8px 30px rgba(0,0,0,0.04);
    }
    .section-title {
        font-weight: 800;
        color: var(--premium-violet);
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding-bottom: 0.75rem;
        margin-bottom: 1.5rem;
        border-bottom: 2px solid var(--glass-border);
    }
    .form-control, .form-select {
        background: var(--glass-bg);
        border-color: var(--glass-border);
        color: var(--text-main);
        border-radius: 0.75rem;
    }
    .form-control:focus, .form-select:focus {
        background: var(--glass-bg);
        border-color: var(--premium-violet);
        color: var(--text-main);
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
    }
    .input-group-text {
        min-width: 42px;
        justify-content: center;
        background: rgba(139, 92, 246, 0.08);
        border-color: var(--glass-border);
        color: var(--premium-violet);
        border-radius: 0.75rem 0 0 0.75rem;
    }
    .input-group .form-control, .input-group .form-select { border-radius: 0 0.75rem 0.75rem 0; }
    .profile-preview-box {
        background: var(--glass-bg);
        border: 2px dashed var(--glass-border);
        border-radius: 1rem;
        padding: 1rem;
        text-align: center;
        transition: border-color 0.3s;
    }
    .profile-preview-box:hover { border-color: var(--premium-violet); }
    .profile-preview-box img { border-radius: 50%; object-fit: cover; }
    .btn-save-violet {
        background: linear-gradient(135deg, #8b5cf6, #6366f1);
        color: #fff; border: none; border-radius: 1rem;
        padding: 0.75rem 2.5rem; font-weight: 700; font-size: 1rem;
        transition: all 0.3s ease;
        box-shadow: 0 8px 20px rgba(139, 92, 246, 0.3);
    }
    .btn-save-violet:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(139, 92, 246, 0.45); color: #fff; }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">

        <header class="page-header-standard d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-user-edit me-2"></i>Editar Usuario</h1>
                <p class="text-muted">Modificación de perfiles, roles y permisos de acceso del personal administrativo</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('usuario.php')" class="text-decoration-none">Usuarios</a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </nav>
        </header>

        <?php
        include('../conexion.php');
        $id_usuario = $_REQUEST['id'];
        $sql = "SELECT * FROM usuario WHERE id_usuario = '$id_usuario'";
        $result = mysqli_query($conexion, $sql);
        $row = mysqli_fetch_assoc($result);
        ?>

        <form class="row g-0" action="../acciones/update_usu.php" method="POST" onsubmit="return validateFormEditU()" enctype="multipart/form-data">

            <input type="hidden" id="usuario_id" name="usuario_id" value="<?php echo $row['id_usuario']; ?>">
            <input type="hidden" name="id_usuario" value="<?php echo $row['id_usuario']; ?>">

            <div class="col-12">
                <div class="form-section-card">
                    <h5 class="section-title"><i class="fas fa-id-card"></i> Datos Personales</h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Usuario (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                <input class="form-control" type="text" id="inputUsuario" name="usuario" maxlength="255" value="<?php echo htmlspecialchars($row['usuario']); ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Nac. (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-flag"></i></span>
                                <select id="inputNacionalidad" name="nacionalidad" class="form-select" style="border-radius: 0 0.75rem 0.75rem 0;">
                                    <option value="V-" <?php if ($row['nacionalidad'] == 'V-') echo 'selected'; ?>>V</option>
                                    <option value="E-" <?php if ($row['nacionalidad'] == 'E-') echo 'selected'; ?>>E</option>
                                    <option value="G-" <?php if ($row['nacionalidad'] == 'G-') echo 'selected'; ?>>G</option>
                                    <option value="J-" <?php if ($row['nacionalidad'] == 'J-') echo 'selected'; ?>>J</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Cédula (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                <input class="form-control" type="number" id="inputCedula" name="cedula" value="<?php echo htmlspecialchars($row['cedula']); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nombre Completo (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input class="form-control" type="text" id="inputNombre" name="nombre" maxlength="255" value="<?php echo htmlspecialchars($row['nombre']); ?>">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Correo Electrónico (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input class="form-control" type="email" id="inputEmail" name="correo" maxlength="255" value="<?php echo htmlspecialchars($row['correo']); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="form-section-card">
                    <h5 class="section-title"><i class="fas fa-key"></i> Seguridad y Rol</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nueva Contraseña <span class="text-muted small">(vacío = no cambia)</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input class="form-control" id="inputPassword" type="password" name="clave" placeholder="••••••••">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Confirmar Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input class="form-control" id="inputPassword2" type="password" name="confirmar_clave" placeholder="••••••••">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword2"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tipo de Usuario (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                <select name="tipo" class="form-select" style="border-radius: 0 0.75rem 0.75rem 0;">
                                    <option value="<?php echo $row['tipos']; ?>" selected><?php echo $row['tipos']; ?></option>
                                    <?php if ($row['tipos'] != 'Super Usuario'): ?><option value="admin">Super Usuario</option><?php endif; ?>
                                    <?php if ($row['tipos'] != 'UPU'): ?><option value="upu">UPU</option><?php endif; ?>
                                    <?php if ($row['tipos'] != 'Administrador'): ?><option value="cont">Administrador</option><?php endif; ?>
                                    <?php if ($row['tipos'] != 'Chequeador'): ?><option value="inv">Chequeador</option><?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="form-section-card">
                    <h5 class="section-title"><i class="fas fa-camera"></i> Foto de Perfil</h5>
                    <div class="row g-4 align-items-center">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Subir Nueva Foto (Opcional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-cloud-upload-alt"></i></span>
                                <input class="form-control" type="file" name="imagen" id="imagen" accept="image/*" onchange="previewImage(event)">
                            </div>
                            <small class="text-muted">JPG, JPEG o PNG. Máx 10MB.</small>
                        </div>
                        <div class="col-md-3">
                            <div class="profile-preview-box">
                                <p class="small fw-bold text-muted mb-2">Foto Actual</p>
                                <img src="<?php echo htmlspecialchars($row['foto']); ?>" id="imagenActual" width="90" height="90" alt="Foto actual">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="profile-preview-box">
                                <p class="small fw-bold text-muted mb-2">Vista Previa</p>
                                <img src="" id="imagenPreview" width="90" height="90" alt="Vista previa">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 text-center py-3">
                <a href="javascript:void(0);" onclick="navigateTo('usuario.php')" class="btn btn-outline-secondary btn-lg rounded-4 px-4 me-3">
                    <i class="fa fa-arrow-left me-1"></i> Cancelar
                </a>
                <button class="btn-save-violet" type="submit" id="btnGuardar">
                    <i class="fa fa-save me-2"></i> Guardar Cambios
                </button>
            </div>

        </form>
    </div>
<?php
require_once("../models/footer.php");
?>
