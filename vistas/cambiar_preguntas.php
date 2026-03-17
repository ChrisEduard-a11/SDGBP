<?php
session_start();
require_once("../conexion.php");

if (empty($_SESSION["usuario"])) {
    header("Location: denegado_a.php");
    exit();
}

$usuario_id = $_SESSION['id_usuario']; // Obtén el ID del usuario actual

// Obtener las preguntas de seguridad actuales del usuario desde la base de datos
$sql = "SELECT pregunta, pregunta2 FROM usuario WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$pregunta1 = $row['pregunta'];
$pregunta2 = $row['pregunta2'];
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Validar - SDGBP</title>

        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="../img/favicon.ico">

        <!-- Bootstrap core CSS-->
        <link href="../css/styles.css" rel="stylesheet" />
        <link href="../css/estilo_login.css" rel="stylesheet" />
        <!--Icons-->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <!-- Toastr -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        
        <!--Sweetalert-->
        <link rel="stylesheet" type="text/css" href="../sweetalert/sweetalert2.min.css">
        <script src="../sweetalert/sweetalert2.js"></script>

        <!--font Google-->
        <link href="./css/font_google.css" rel="stylesheet">
        <style>
/* --- VISTA ACTUALIZAR PREGUNTAS (PREMIUM) --- */
/* Mitad Izquierda */
.login-image-container {
    flex: 1.2; /* Un poco más ancho para registro */
    position: relative;
}

.login-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.btn-secondary.btn-sm {
    background: transparent !important;
    color: #64748b !important;
    border: none !important;
    font-weight: 600;
}
.btn-secondary.btn-sm:hover {
    color: var(--text-main) !important;
}
/* Estilo para los selectores de preguntas */
select.form-control.select-picker {
    appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23f18000' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 14px;
    padding-right: 2.5rem !important;
    cursor: pointer;
    font-weight: 600;
}

/* Agrupación visual: Pregunta + Respuesta */
.form-floating:nth-child(odd) {
    margin-bottom: 5px !important;
}

.form-floating:nth-child(even) {
    margin-bottom: 25px !important; /* Espacio mayor después de la respuesta */
}

/* Efecto Focus para los selects */
select.form-control:focus {
    border-color: var(--primary) !important;
    background-color: var(--primary-light) !important;
}

/* Label de los selects para que no se encimen */
.form-floating > .form-control:not(:placeholder-shown) ~ label {
    transform: scale(.85) translateY(-0.7rem) translateX(0.15rem) !important;
    color: var(--primary) !important;
    font-weight: 700;
}

/* Botón Actualizar con ancho dinámico */
.boton[type="submit"] {
    max-width: 200px; /* Un poco más pequeño para esta vista */
    font-size: 0.9rem !important;
    height: 50px;
}
        </style>
    </head>
    <body>
        <div id="layoutAuthentication">
            <!-- Mitad izquierda: Imagen de fondo -->
            <div class="login-image-container d-none d-lg-block">
                <img src="../img/fondo_izq.webp" alt="Imagen de fondo" class="login-image">
            </div>

            <!-- Mitad derecha: Formulario -->
            <div class="login-form-container">
                <main>
                    <div class="form-content">
                        <!-- Nombre del sistema y logo -->
                        <div class="text-center mb-4">
                            <img src="../img/Logo-OP2_V4.webp" alt="Logo Empresa" class="logo mb-3">
                            <h1 class="system-name">Sistema de Gestión Bienes y Pagos</h1>
                        </div>
                        <!-- Formulario sin caja -->
                        <div class="form-container">
                            <form name="preguntaForm" action="../acciones/actualizar_preguntas.php" method="POST" onsubmit="return validateFormCPS()">
                                <div class="form-floating mb-3">
                                    <select name="pregunta1" id="inputPregunta1" class="form-control select-picker">
                                        <option><?php echo $pregunta1; ?></option>
                                        <option>¿Comida favorita?</option>
                                        <option>¿Color Preferido?</option>
                                        <option>¿Nombre de mi mascota?</option>
                                        <option>¿Deporte Favorito?</option>
                                        <option>¿Lugar de nacimiento?</option>
                                        <option>¿Nombre de mi mejor amigo de la infancia?</option>
                                        <option>¿Película favorita?</option>
                                        <option>¿Nombre de mi primer maestro?</option>
                                        <option>¿Marca de mi primer automóvil?</option>
                                        <option>¿Nombre de mi primer jefe?</option>
                                    </select>
                                    <label for="inputPregunta1">Pregunta 1</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="inputRespuesta1" type="text" placeholder="Respuesta 1" name="respuesta1" />
                                    <label for="inputRespuesta1">Respuesta 1</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <select name="pregunta2" id="inputPregunta2" class="form-control select-picker">
                                        <option><?php echo $pregunta2; ?></option>
                                        <option>¿Comida favorita?</option>
                                        <option>¿Color Preferido?</option>
                                        <option>¿Nombre de mi mascota?</option>
                                        <option>¿Deporte Favorito?</option>
                                        <option>¿Lugar de nacimiento?</option>
                                        <option>¿Nombre de mi mejor amigo de la infancia?</option>
                                        <option>¿Película favorita?</option>
                                        <option>¿Nombre de mi primer maestro?</option>
                                        <option>¿Marca de mi primer automóvil?</option>
                                        <option>¿Nombre de mi primer jefe?</option>
                                    </select>
                                    <label for="inputPregunta2">Pregunta 2</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="inputRespuesta2" type="text" placeholder="Respuesta 2" name="respuesta2" />
                                    <label for="inputRespuesta2">Respuesta 2</label>
                                </div>
                                <div class="text-center mb-4 mt-4">
                                    <button class="boton" type="submit">Actualizar</button>
                                </div>
                            </form>
                            <?php include("../models/sweetalert.php"); ?>
                            <!-- Botón para volver a la página principal -->
                            <div class="text-center mt-4">
                                <a class="btn btn-secondary btn-sm" href='pregunta.php'>
                                    <i class="fas fa-home"></i> Volver
                                </a>
                            </div>
                        </div>
                    </div>
                </main>
                <!-- Footer -->
                <footer class="footer_licencia text-center mt-4">
                    <p>
                        Este trabajo está licenciado bajo 
                        <a href="https://creativecommons.org/licenses/by-nc/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer">
                            Creative Commons BY-NC 4.0
                            <img src="https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1" alt="CC">
                            <img src="https://mirrors.creativecommons.org/presskit/icons/by.svg?ref=chooser-v1" alt="BY">
                            <img src="https://mirrors.creativecommons.org/presskit/icons/nc.svg?ref=chooser-v1" alt="NC">
                        </a>
                    </p>
                    <b><small>&copy; <?php echo date("Y"); ?> Sistema de Gestión de Bienes y Pagos. Todos los derechos reservados.</small></b>
                </footer>
                <?php include("../models/footer_index.php"); ?>
                <script src="../js/vali_login.js"></script>
                
                <script>
                    function validateFormCPS() {
                        const pregunta1 = document.getElementById("inputPregunta1").value.trim();
                        const respuesta1 = document.getElementById("inputRespuesta1").value.trim();
                        const pregunta2 = document.getElementById("inputPregunta2").value.trim();
                        const respuesta2 = document.getElementById("inputRespuesta2").value.trim();

                        // Crear un objeto de audio para el sonido de error
                        const audio = new Audio('../error/validation_error.mp3');

                        // Validar que las preguntas y respuestas no estén vacías
                        if (pregunta1 === "" || respuesta1 === "" || pregunta2 === "" || respuesta2 === "") {
                            audio.play(); // Reproducir el sonido de error
                            toastr.error("Todos los campos son obligatorios", "Error de Validación");
                            return false;
                        }

                        // Validar que las preguntas seleccionadas sean diferentes
                        if (pregunta1 === pregunta2) {
                            audio.play(); // Reproducir el sonido de error
                            toastr.error("Las preguntas de seguridad deben ser diferentes", "Error de Validación");
                            return false;
                        }

                        return true; // Permitir el envío del formulario si todo está correcto
                    }

                    // Configuración de Toastr
                    toastr.options = {
                        "closeButton": true,
                        "debug": false,
                        "newestOnTop": true,
                        "progressBar": true,
                        "positionClass": "toast-top-right",
                        "preventDuplicates": true,
                        "onclick": null,
                        "showDuration": "300",
                        "hideDuration": "1000",
                        "timeOut": "5000",
                        "extendedTimeOut": "1000",
                        "showEasing": "swing",
                        "hideEasing": "linear",
                        "showMethod": "fadeIn",
                        "hideMethod": "fadeOut"
                    };

                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            title: 'Verificación requerida',
                            text: 'Por favor, ingresa tu contraseña para continuar.',
                            input: 'password',
                            inputPlaceholder: 'Ingresa tu contraseña',
                            showCancelButton: true,
                            confirmButtonText: 'Verificar',
                            cancelButtonText: 'Cancelar',
                            allowOutsideClick: false,
                            allowOutsideClick: false,
                            confirmButtonColor: '#007bff',
                            cancelButtonColor: '#d33',
                            preConfirm: (password) => {
                                return fetch('../acciones/verificar_contraseña.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ password: password })
                                })
                                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                                .then(({ status, body }) => {
                                    if (status === 200) {
                                        return body.message; // Contraseña verificada
                                    } else if (status === 403) {
                                        // Usuario bloqueado, mostrar alerta de procesamiento y redirigir
                                        Swal.fire({
                                            title: 'Procesando...',
                                            html: 'Serás redirigido al login en <b>3</b> segundos.',
                                            icon: 'info',
                                            allowOutsideClick: false,
                                            showConfirmButton: false,
                                            icon: 'info',
                                            allowOutsideClick: false,
                                            showConfirmButton: false,
                                            didOpen: () => {
                                                const b = Swal.getHtmlContainer().querySelector('b');
                                                let timer = 3; // Tiempo en segundos
                                                const interval = setInterval(() => {
                                                    timer--;
                                                    b.textContent = timer;
                                                    if (timer === 0) {
                                                        clearInterval(interval);
                                                        window.location.href = body.redirect; // Redirigir al login
                                                    }
                                                }, 1000); // Actualizar cada segundo
                                            }
                                        });
                                        throw new Error('Usuario bloqueado'); // Detener el flujo
                                    } else if (status === 401) {
                                        throw new Error(body.message); // Contraseña incorrecta
                                    } else {
                                        throw new Error('Ocurrió un error inesperado.');
                                    }
                                })
                                .catch(error => {
                                    Swal.showValidationMessage(error.message);
                                });
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Éxito',
                                    text: result.value, // Mensaje del servidor
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: 'Continuar',
                                    background: '#1c1e21',
                                    color: '#ffffff',
                                    iconColor: '#007bff'
                                }).then(() => {
                                    document.getElementById('form-container').style.display = 'block';
                                });
                            } else {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Cancelado',
                                    text: 'La verificación fue cancelada.',
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: 'Aceptar',
                                    background: '#1c1e21',
                                    color: '#ffffff',
                                    iconColor: '#007bff'
                                }).then(() => {
                                    window.location.href = 'pregunta.php';
                                });
                            }
                        });
                    });
                </script>
            </div>
        </div>
    </body>
</html>