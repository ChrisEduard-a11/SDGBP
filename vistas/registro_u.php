<?php
require_once("../models/header.php")
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
    .btn-submit-violet {
        background: linear-gradient(135deg, #8b5cf6, #6366f1);
        color: #fff;
        border: none;
        border-radius: 1rem;
        padding: 0.75rem 2.5rem;
        font-weight: 700;
        font-size: 1rem;
        transition: all 0.3s ease;
        box-shadow: 0 8px 20px rgba(139, 92, 246, 0.3);
    }
    .btn-submit-violet:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(139, 92, 246, 0.45); color: #fff; }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">

        <header class="page-header-standard d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-user-plus me-2"></i>Registrar Usuario</h1>
                <p class="text-muted">Creación de nuevas cuentas de acceso y asignación de roles administrativos</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('inicio.php')" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigateTo('usuario.php')" class="text-decoration-none">Usuarios</a></li>
                    <li class="breadcrumb-item active">Nuevo Usuario</li>
                </ol>
            </nav>
        </header>

        <div class="alert alert-warning border-0 rounded-4 shadow-sm mb-4" role="alert">
            <h6 class="alert-heading mb-1"><i class="fas fa-exclamation-triangle me-2"></i> Formatos Permitidos</h6>
            <p class="mb-0 small">Solo se aceptan <strong>JPG, JPEG y PNG</strong>. Tamaño máximo: <strong>10MB</strong>.</p>
        </div>

        <form id="formRegistroU" class="row g-0" action="../acciones/guardar_u.php" method="POST" onsubmit="return validateFormRU()" enctype="multipart/form-data">

            <div class="col-12">
                <div class="form-section-card">
                    <h5 class="section-title"><i class="fas fa-id-card"></i> Datos Personales</h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="inputUsuario" class="form-label fw-semibold">Usuario (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                <input id="inputUsuario" class="form-control" type="text" name="usuario" maxlength="15" placeholder="ej. juan20">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="inputNacionalidad" class="form-label fw-semibold">Nac. (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-flag"></i></span>
                                <select id="inputNacionalidad" name="nacionalidad" class="form-select" style="border-radius: 0 0.75rem 0.75rem 0;">
                                    <option value="V-">V</option>
                                    <option value="E-">E</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="inputCedula" class="form-label fw-semibold">Cédula (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                <input id="inputCedula" class="form-control" type="number" name="cedula" placeholder="Número de cédula">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="inputNombre" class="form-label fw-semibold">Nombre Completo (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input id="inputNombre" class="form-control" type="text" name="nombre" maxlength="255" placeholder="Nombres y Apellidos">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label for="inputEmail" class="form-label fw-semibold">Correo Electrónico (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input class="form-control" id="inputEmail" type="email" name="correo" placeholder="ej. correo@gmail.com">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="form-section-card">
                    <h5 class="section-title"><i class="fas fa-key"></i> Credenciales de Acceso</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="inputPassword" class="form-label fw-semibold">Contraseña (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input class="form-control" id="inputPassword" type="password" name="clave" placeholder="••••••••">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword" onclick="togglePasswordVisibility('inputPassword', this)"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="inputPassword2" class="form-label fw-semibold">Confirmar Contraseña (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input class="form-control" id="inputPassword2" type="password" name="confirmar_clave" placeholder="••••••••">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword2" onclick="togglePasswordVisibility('inputPassword2', this)"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="inputTipo" class="form-label fw-semibold">Tipo de Usuario (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-users-cog"></i></span>
                                <select id="inputTipo" name="tipo" class="form-select" style="border-radius: 0 0.75rem 0.75rem 0;">
                                    <option value="admin">Super Usuario</option>
                                    <option value="upu">UPU</option>
                                    <option value="cont">Administrador</option>
                                    <option value="inv">Chequeador</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="form-section-card">
                    <h5 class="section-title"><i class="fas fa-shield-alt"></i> Preguntas de Seguridad</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="inputPregunta1" class="form-label fw-semibold">Pregunta 1 (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-question-circle"></i></span>
                                <select id="inputPregunta1" name="pregunta" class="form-select" style="border-radius: 0 0.75rem 0.75rem 0;">
                                    <option value="">Seleccionar pregunta...</option>
                                    <option value="¿Comida favorita?">¿Comida favorita?</option>
                                    <option value="¿Color Preferido?">¿Color Preferido?</option>
                                    <option value="¿Nombre de mi mascota?">¿Nombre de mi mascota?</option>
                                    <option value="¿Deporte Favorito?">¿Deporte Favorito?</option>
                                    <option value="¿Lugar de nacimiento?">¿Lugar de nacimiento?</option>
                                    <option value="¿Nombre de mi mejor amigo de la infancia?">¿Nombre de mi mejor amigo de la infancia?</option>
                                    <option value="¿Película favorita?">¿Película favorita?</option>
                                    <option value="¿Nombre de mi primer maestro?">¿Nombre de mi primer maestro?</option>
                                    <option value="¿Marca de mi primer automóvil?">¿Marca de mi primer automóvil?</option>
                                    <option value="¿Nombre de mi primer jefe?">¿Nombre de mi primer jefe?</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="inputRespuesta1" class="form-label fw-semibold">Respuesta 1 (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-check-double"></i></span>
                                <input id="inputRespuesta1" class="form-control" type="text" name="respuesta" placeholder="Tu respuesta...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="inputPregunta2" class="form-label fw-semibold">Pregunta 2 (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-question-circle"></i></span>
                                <select id="inputPregunta2" name="pregunta2" class="form-select" style="border-radius: 0 0.75rem 0.75rem 0;">
                                    <option value="">Seleccionar pregunta...</option>
                                    <option value="¿Comida favorita?">¿Comida favorita?</option>
                                    <option value="¿Color Preferido?">¿Color Preferido?</option>
                                    <option value="¿Nombre de mi mascota?">¿Nombre de mi mascota?</option>
                                    <option value="¿Deporte Favorito?">¿Deporte Favorito?</option>
                                    <option value="¿Lugar de nacimiento?">¿Lugar de nacimiento?</option>
                                    <option value="¿Nombre de mi mejor amigo de la infancia?">¿Nombre de mi mejor amigo de la infancia?</option>
                                    <option value="¿Película favorita?">¿Película favorita?</option>
                                    <option value="¿Nombre de mi primer maestro?">¿Nombre de mi primer maestro?</option>
                                    <option value="¿Marca de mi primer automóvil?">¿Marca de mi primer automóvil?</option>
                                    <option value="¿Nombre de mi primer jefe?">¿Nombre de mi primer jefe?</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="inputRespuesta2" class="form-label fw-semibold">Respuesta 2 (*)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-check-double"></i></span>
                                <input id="inputRespuesta2" class="form-control" type="text" name="respuesta2" placeholder="Tu respuesta...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="form-section-card">
                    <h5 class="section-title"><i class="fas fa-image"></i> Foto de Perfil</h5>
                    <div class="col-md-5">
                        <label for="imagen" class="form-label fw-semibold">Subir Imagen (Opcional)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-cloud-upload-alt"></i></span>
                            <input class="form-control" type="file" name="imagen" id="imagen" accept="image/*">
                        </div>
                        <small class="text-muted">JPG, JPEG o PNG. Máx 10MB.</small>
                    </div>
                </div>
            </div>

            <div class="col-12 text-center py-3">
                <button class="btn-submit-violet me-3" type="submit" id="btnGuardar">
                    <i class="fa fa-user-plus me-2"></i> Registrar Usuario
                </button>
                <a href="javascript:void(0);" onclick="navigateTo('usuario.php')" class="btn btn-outline-secondary btn-lg rounded-4 px-4">
                    <i class="fa fa-arrow-left me-1"></i> Cancelar
                </a>
            </div>

        </form>
    </div>
    <script src="../js/vali_login.js"></script>
<?php
require_once("../models/footer.php");
?>
