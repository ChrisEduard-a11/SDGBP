<?php
require_once("../models/header.php");
require_once("../conexion.php"); // Conexión a la base de datos

// --- Lógica de PHP (sin cambios) ---

// Obtener la foto de perfil del usuario desde la base de datos
$id_usuario = $_SESSION['id']; // ID del usuario actual
// Usamos prepared statement para mayor seguridad (aunque solo sea un SELECT)
$sqlFoto = "SELECT foto FROM usuario WHERE id_usuario = ?";
$stmtFoto = $conexion->prepare($sqlFoto);
$stmtFoto->bind_param("i", $id_usuario);
$stmtFoto->execute();
$resultFoto = $stmtFoto->get_result();

if ($resultFoto && $resultFoto->num_rows > 0) {
    $user = $resultFoto->fetch_assoc();
    // Verifica si la foto existe en la DB y usa la URL correcta, si no, usa el default
    $foto_perfil = !empty($user['foto']) ? $user['foto'] : '../img/default_profile.png';
} else {
    $foto_perfil = '../img/default_profile.png'; // Foto predeterminada si no se encuentra el usuario
}
$stmtFoto->close();

$usuario_id = $_SESSION['id']; // Obtén el ID del usuario actual

// Obtener datos del usuario incluyendo preferencias y contacto
$sql = "SELECT pregunta, pregunta2, telefono, telegram_id FROM usuario WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$pregunta1 = $row['pregunta'];
$pregunta2 = $row['pregunta2'];
$telefono = $row['telefono'] ?? '';
$telegram_id = $row['telegram_id'] ?? '';
$stmt->close();

// Lista completa de opciones de preguntas de seguridad
$opciones_preguntas = [
    "¿Comida favorita?",
    "¿Color Preferido?",
    "¿Nombre de mi mascota?",
    "¿Deporte Favorito?",
    "¿Lugar de nacimiento?",
    "¿Nombre de mi mejor amigo de la infancia?",
    "¿Película favorita?",
    "¿Nombre de mi primer maestro?",
    "¿Marca de mi primer automóvil?",
    "¿Nombre de mi primer jefe?",
];

// ------------------------------------
?>

<style>
    /* =========================================
       SISTEMA SDGBP - DISEÑO ULTRA PREMIUM 2026
       CONFIGURACIÓN DE USUARIO
       ========================================= */
    :root {
        --primary: #f18000;
        --primary-dark: #d67100;
        --primary-light: rgba(241, 128, 0, 0.1);
        --accent: #ff9800;
        --bg-body: #f8fafc;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        --radius-premium: 20px;
        --shadow-premium: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        --glass: rgba(255, 255, 255, 0.8);
        --glass-border: rgba(255, 255, 255, 0.3);
    }

    body {
        background-color: var(--bg-body);
        color: var(--text-main);
    }

    .breadcrumb-premium {
        background: var(--glass) !important;
        backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border) !important;
        border-radius: 12px !important;
        box-shadow: var(--shadow-premium);
    }

    .card-premium {
        background: #ffffff;
        border: none !important;
        border-radius: var(--radius-premium) !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .card-premium-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        padding: 1.5rem 2rem;
        border: none !important;
    }

    .card-premium-header h4 {
        color: white;
        margin: 0;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .section-title {
        color: var(--primary);
        font-weight: 800;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
    }

    .section-title i {
        background: var(--primary-light);
        color: var(--primary);
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        margin-right: 12px;
        font-size: 0.9rem;
    }

    /* --- INFO ITEMS --- */
    .info-item-box {
        background: #f1f5f9;
        padding: 1.25rem;
        border-radius: 15px;
        border-left: 4px solid var(--primary);
        transition: all 0.3s ease;
    }

    .info-item-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    .info-item-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .info-item-value {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-main);
    }

    /* --- PROFILE PIC --- */
    .profile-pic-wrapper {
        position: relative;
        width: 160px;
        height: 160px;
        margin: 0 auto;
        border-radius: 50%;
        padding: 5px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        box-shadow: 0 10px 20px rgba(241, 128, 0, 0.2);
    }

    .profile-pic-container {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        overflow: hidden;
        background: transparent;
        border: 4px solid white;
    }

    [data-theme="dark"] .profile-pic-container {
        background: transparent;
        border-color: #333;
    }

    .profile-pic {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .upload-btn-floating {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: white;
        color: var(--primary);
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        transition: all 0.2s ease;
        border: none;
    }

    .upload-btn-floating:hover {
        background: var(--primary);
        color: white;
        transform: scale(1.1);
    }

    /* --- FORMS --- */
    .form-control-premium, .form-select-premium {
        border: 1.5px solid var(--border-color) !important;
        border-radius: 12px !important;
        padding: 0.75rem 1rem !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
    }

    .form-control-premium:focus, .form-select-premium:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 4px var(--primary-light) !important;
    }

    .input-group-premium {
        border-radius: 12px;
        overflow: hidden;
    }

    .input-group-premium .input-group-text {
        background: #f1f5f9;
        border: 1.5px solid var(--border-color);
        border-right: none;
        color: var(--text-muted);
    }

    .input-group-premium .form-control {
        border-left: none !important;
    }

    .btn-save-premium {
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%) !important;
        border: none !important;
        padding: 1rem 2.5rem !important;
        border-radius: 15px !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: white !important;
        box-shadow: 0 8px 15px rgba(241, 128, 0, 0.25) !important;
        transition: all 0.3s ease !important;
    }

    .btn-save-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 20px rgba(241, 128, 0, 0.4) !important;
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        
        <header class="page-header-standard d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-user-cog me-2"></i>Configuración de Perfil</h1>
                <p class="text-muted">Gestión de información personal, preferencias de seguridad y credenciales de acceso</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="inicio.php" class="text-decoration-none text-muted fw-bold">Dashboard</a></li>
                    <li class="breadcrumb-item active">Configuración de Perfil</li>
                </ol>
            </nav>
        </header>

        <?php if (isset($mensaje)) { ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-3 fa-lg"></i>
                    <div><?php echo $mensaje; ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>

        <div class="card card-premium shadow">
            <div class="card-premium-header">
                <h4><i class="fas fa-user-circle me-2"></i> Perfil de Usuario</h4>
            </div>
            <div class="card-body p-4 p-md-5">

                <form action="../acciones/actualizar_configuracion.php" method="POST" enctype="multipart/form-data" onsubmit="return validarFormularioConfigU()">
                    
                    <!-- FOTO DE PERFIL CENTRADA -->
                    <div class="text-center mb-5">
                        <div class="profile-pic-wrapper">
                            <div class="profile-pic-container">
                                <img src="<?php echo $foto_perfil; ?>" alt="Perfil" id="profileImagePreview" class="profile-pic">
                            </div>
                            <div class="d-flex justify-content-center gap-2 mt-3 position-absolute" style="bottom: -20px; left: 50%; transform: translateX(-50%); width: 100%;">
                                <label for="inputFotoPerfil" class="upload-btn-floating position-relative d-inline-flex m-0" style="bottom: auto; right: auto; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <button type="button" class="upload-btn-floating position-relative d-inline-flex m-0 bg-danger text-white border-0" style="bottom: auto; right: auto; box-shadow: 0 4px 10px rgba(220,53,69,0.3);" onclick="eliminarFotoConfigU()" title="Eliminar Foto">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <input type="file" id="inputFotoPerfil" name="imagen" accept="image/*" class="d-none" onchange="previewProfileImageConfig(this)">
                            <input type="hidden" name="eliminar_foto" id="eliminar_foto_config" value="0">
                        </div>
                        <h5 class="mt-4 fw-bold mb-1 pt-3"><?php echo $_SESSION['nombre']; ?></h5>
                        <p class="text-muted small">Usa la cámara para subir foto o la papelera para quitarla</p>
                    </div>

                    <!-- INFORMACIÓN BÁSICA (BOXES) -->
                    <h5 class="section-title"><i class="fas fa-id-card"></i> Información Personal</h5>
                    <div class="row g-4 mb-5">
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="info-item-box">
                                <div class="info-item-label">ID Usuario</div>
                                <div class="info-item-value"><?php echo $_SESSION['user']; ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="info-item-box">
                                <div class="info-item-label">Tipo de Cuenta</div>
                                <div class="info-item-value text-uppercase"><?php echo $_SESSION['tipo']; ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="info-item-box">
                                <div class="info-item-label">Identificación</div>
                                <div class="info-item-value"><?php echo $_SESSION['nacionalidad'] . $_SESSION['cedula']; ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="info-item-box">
                                <div class="info-item-label">Correo</div>
                                <div class="info-item-value"><?php echo $_SESSION['correo']; ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- CONTACTO Y NOTIFICACIONES -->
                    <h5 class="section-title"><i class="fas fa-address-book"></i> Información de Contacto (Seguridad)</h5>
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Teléfono / WhatsApp <span class="text-danger">*</span></label>
                            <div class="input-group input-group-premium">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="text" class="form-control form-control-premium" name="telefono" value="<?php echo $telefono; ?>" 
                                    placeholder="Ej: 04121234567" 
                                    <?php echo !empty($telefono) ? 'readonly' : ''; ?>
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '');" maxlength="11">
                            </div>
                            <?php if (!empty($telefono)): ?>
                                <small class="text-muted mt-1 d-block"><i class="fas fa-lock me-1"></i> Campo bloqueado por seguridad.</small>
                            <?php else: ?>
                                <small class="text-primary mt-1 d-block"><i class="fas fa-info-circle me-1"></i> Una vez registrado, no podrá ser modificado.</small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-600 mb-0">Telegram Chat ID <span class="text-muted fw-normal">(Opcional)</span></label>
                                <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none fw-bold" data-bs-toggle="modal" data-bs-target="#modalAyudaTelegram">
                                    <i class="fas fa-question-circle me-1"></i>¿Cómo obtenerlo?
                                </button>
                            </div>
                            <div class="input-group input-group-premium">
                                <span class="input-group-text"><i class="fab fa-telegram-plane"></i></span>
                                <input type="text" class="form-control form-control-premium" name="telegram_id" value="<?php echo $telefono === '' ? '' : $telegram_id; ?>" 
                                    placeholder="Tu ID de Telegram"
                                    <?php echo !empty($telegram_id) ? 'readonly' : ''; ?>>
                            </div>
                            <?php if (!empty($telegram_id)): ?>
                                <small class="text-muted mt-1 d-block"><i class="fas fa-lock me-1"></i> Chat ID vinculado correctamente.</small>
                            <?php else: ?>
                                <small class="text-muted mt-1 d-block">Necesario para la recuperación vía Telegram Bot.</small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- SEGURIDAD -->
                    <h5 class="section-title"><i class="fas fa-shield-alt"></i> Seguridad y Recuperación</h5>
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Pregunta de Seguridad 1 <span class="text-muted fw-normal">(Opcional)</span></label>
                            <select class="form-select form-select-premium" id="pregunta1" name="pregunta1">
                                <option value="<?php echo $pregunta1; ?>" selected><?php echo htmlspecialchars($pregunta1); ?></option>
                                <?php foreach ($opciones_preguntas as $opcion) { if ($opcion !== $pregunta1) echo "<option>$opcion</option>"; } ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Respuesta Secreta 1</label>
                            <input type="text" class="form-control form-control-premium" id="respuesta1" name="respuesta1" placeholder="Solo si deseas cambiar tus preguntas">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Pregunta de Seguridad 2 <span class="text-muted fw-normal">(Opcional)</span></label>
                            <select class="form-select form-select-premium" id="pregunta2" name="pregunta2">
                                <option value="<?php echo $pregunta2; ?>" selected><?php echo htmlspecialchars($pregunta2); ?></option>
                                <?php foreach ($opciones_preguntas as $opcion) { if ($opcion !== $pregunta2) echo "<option>$opcion</option>"; } ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Respuesta Secreta 2</label>
                            <input type="text" class="form-control form-control-premium" id="respuesta2" name="respuesta2" placeholder="Solo si deseas cambiar tus preguntas">
                        </div>
                    </div>


                    <!-- CONTRASEÑA -->
                    <h5 class="section-title"><i class="fas fa-lock"></i> Gestión de Contraseñas</h5>
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-600 text-danger">Contraseña Actual *</label>
                            <div class="input-group input-group-premium">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                <input class="form-control" id="inputPasswordActual" type="password" name="password_actual" placeholder="Obligatoria para cambios">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibilityCU('inputPasswordActual', this)"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Nueva Contraseña</label>
                            <div class="input-group input-group-premium">
                                <span class="input-group-text"><i class="fas fa-plus-circle"></i></span>
                                <input class="form-control" id="inputPassword" type="password" name="password" placeholder="Opcional">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibilityCU('inputPassword', this)"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Confirmar Nueva</label>
                            <div class="input-group input-group-premium">
                                <span class="input-group-text"><i class="fas fa-check-double"></i></span>
                                <input class="form-control" id="inputPasswordConfirm" type="password" name="password1" placeholder="Repetir nueva">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibilityCU('inputPasswordConfirm', this)"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info border-0 rounded-3 mb-5" style="background: #eef2f7;">
                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i> La nueva contraseña solo se aplicará si rellenas ambos campos de "Nueva Contraseña".</small>
                    </div>

                    <div class="d-grid mt-5">
                        <button class="btn btn-save-premium" type="submit">
                            <i class="fas fa-save me-2"></i> Guardar Cambios del Perfil
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- MODAL AYUDA TELEGRAM -->
    <div class="modal fade" id="modalAyudaTelegram" tabindex="-1" aria-labelledby="modalAyudaTelegramLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold" id="modalAyudaTelegramLabel"><i class="fab fa-telegram-plane me-2"></i>Vinculación con Telegram</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- ADVERTENCIA ESTRÍCTA DE SEGURIDAD -->
                    <div class="p-3 mb-4 rounded-3 border-start border-4 border-danger" style="background: rgba(220, 53, 69, 0.05);">
                        <h6 class="text-danger fw-bold mb-1"><i class="fas fa-exclamation-triangle me-2"></i>AVISO DE SEGURIDAD ESTRICTA</h6>
                        <p class="small text-muted mb-0">La vinculación con Telegram es una operación <b>crítica</b>. Si introduces un ID que no es el tuyo, estarías permitiendo que un tercero <u>tome el control total de tu cuenta</u>. Asegúrate de seguir los pasos con extrema precisión.</p>
                    </div>

                    <p class="text-muted mb-4">Sigue estos pasos para vincular tu cuenta de forma segura:</p>
                    
                    <div class="d-flex align-items-start mb-4">
                        <div class="bg-primary-light text-primary rounded-circle p-3 me-3" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><b>1</b></div>
                        <div>
                            <h6 class="fw-bold mb-1">Obtener tu ID Personal</h6>
                            <p class="small text-muted mb-0">Busca en Telegram al bot <a href="https://t.me/userinfobot" target="_blank" class="text-primary fw-bold">@userinfobot</a> y presiona <b>/start</b>. Copia el número que dice <b>"Your ID"</b>. <i>(Verifica que sea tu propio perfil)</i>.</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-4">
                        <div class="bg-primary-light text-primary rounded-circle p-3 me-3" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><b>2</b></div>
                        <div>
                            <h6 class="fw-bold mb-1">Activar Recepción en SDGBP</h6>
                            <p class="small text-muted mb-0">Busca a <a href="https://t.me/SDGBP_SeguridadBot" target="_blank" class="text-primary fw-bold">@SDGBP_SeguridadBot</a> y presiona <b>/start</b>. Sin este paso, el sistema no podrá enviarte tus códigos.</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start">
                        <div class="bg-primary-light text-primary rounded-circle p-3 me-3" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><b>3</b></div>
                        <div>
                            <h6 class="fw-bold mb-1">Registrar y Bloquear</h6>
                            <p class="small text-muted mb-0">Pega el ID obtenido (Paso 1) en el campo "Telegram Chat ID" y guarda. <b>Una vez guardado, el campo se bloqueará por seguridad.</b></p>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4 border-0 rounded-3">
                        <small class="d-block"><i class="fas fa-shield-alt me-1"></i> Una vez vinculado, tu cuenta estará protegida por Verificación en Dos Pasos (2FA) vía Telegram.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3">
                    <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Entendido, tendré cuidado</button>
                </div>
            </div>
        </div>
    </div>


<script>
    function previewProfileImageConfig(input) {
        document.getElementById('eliminar_foto_config').value = '0';
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('profileImagePreview');
                if (preview) {
                    preview.src = e.target.result;
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function eliminarFotoConfigU() {
        document.getElementById('eliminar_foto_config').value = '1';
        document.getElementById('profileImagePreview').src = '../img/default_profile.png';
        document.getElementById('inputFotoPerfil').value = '';
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: 'Foto marcada para eliminar. Guarda los cambios para aplicar.',
            showConfirmButton: false,
            timer: 3000
        });
    }
</script>

<?php
require_once("../models/footer.php");
?>
