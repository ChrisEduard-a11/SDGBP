    <footer class="py-4 bg-light mt-auto border-top" id="footer">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center justify-content-between small">
                <div class="text-muted">
                    <span class="fw-bold">SDGBP v2.0</span> &middot; 
                    Desarrollado por Cristian Arcaya, Pedro Rivera y Daniel Espinoza &middot; 
                    <span class="fw-bold">PNF Informática</span> &middot;
                    <a href="../vistas/terminos.php"  class="text-primary fw-bold text-decoration-none">Términos y Condiciones</a>
                </div>
                <div>
                    <div class="text-muted">
                        &copy; <?php echo date("Y"); ?> Todos los derechos reservados.
                    </div>
                </div>
            </div>
            <div class="text-center mt-3">
                <p class="mb-0 text-muted" style="font-size: 0.75rem;">
                    <span class="badge bg-secondary text-uppercase py-1 px-2 me-2">Licencia</span>
                    <a href="https://creativecommons.org/licenses/by-nc/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer" class="text-decoration-none text-primary fw-bold">
                        Creative Commons BY-NC 4.0
                    </a>
                </p>
            </div>
        </div>
    </footer>

    <!-- Overlay de Bloqueo para Términos -->
    <div id="terms-overlay" class="terms-overlay-premium d-none"></div>

    <!-- Banner de Aceptación de Términos (Cookies) -->
    <div id="terms-banner" class="terms-banner-premium d-none">
        <div class="container-fluid px-4 py-3">
            <div class="row align-items-center">
                <div class="col-lg-8 mb-3 mb-lg-0">
                    <div class="d-flex align-items-center text-start">
                        <div class="banner-icon me-3 d-none d-md-block">
                            <i class="fas fa-cookie-bite fa-2x text-primary animate__animated animate__pulse animate__infinite"></i>
                        </div>
                        <div>
                            <div id="terms-update-notice" class="d-none animate__animated animate__flash animate__infinite">
                                <span class="badge bg-warning text-dark mb-2 px-3 py-2 rounded-pill shadow-sm">
                                    <i class="fas fa-exclamation-circle me-1"></i> ¡Términos Actualizados! (Revisión Obligatoria)
                                </span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark">Política de Privacidad y Uso del Sistema</h6>
                            <p class="mb-0 text-muted small">
                                Utilizamos cookies para mejorar su experiencia y garantizar la seguridad de sus datos. 
                                Al continuar navegando, usted acepta nuestros 
                                <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modalTerminosFull" class="text-primary fw-bold text-decoration-none border-bottom border-primary border-2">Términos y Condiciones</a>.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end text-center">
                    <button id="btn-aceptar-terminos" class="btn btn-primary rounded-pill px-4 fw-bold me-2 shadow-sm">
                        <i class="fas fa-check me-1"></i> Aceptar
                    </button>
                    <button id="btn-rechazar-terminos" class="btn btn-outline-danger rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fas fa-times me-1"></i> Rechazar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Recuperar términos dinámicos y su estado de activación
    require_once("../conexion.php");
    $sql_terms = "SELECT clave, valor, ultima_actualizacion FROM ajustes_sistema WHERE clave IN ('terminos_condiciones', 'terminos_status')";
    $res_terms = mysqli_query($conexion, $sql_terms);
    $ajustes_f = [];
    while ($r_f = mysqli_fetch_assoc($res_terms)) {
        if ($r_f['clave'] == 'terminos_condiciones') {
            $texto_terminos_db = $r_f['valor'];
            $version_terminos = strtotime($r_f['ultima_actualizacion'] ?? 'now');
        }
        $ajustes_f[$r_f['clave']] = $r_f['valor'];
    }
    $texto_terminos_db = $texto_terminos_db ?? 'No se han definido términos.';
    $version_terminos = $version_terminos ?? time();
    $terminos_activos_f = ($ajustes_f['terminos_status'] ?? '1') == '1';
    ?>

    <!-- Modal de Términos y Condiciones Integrado (Lectura) -->
    <div class="modal fade" id="modalTerminosFull" tabindex="-1" aria-labelledby="modalTerminosLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 bg-gradient-primary text-white p-4">
                    <h5 class="modal-title fw-bold" id="modalTerminosLabel">
                        <i class="fas fa-file-contract me-2"></i> Términos y Condiciones
                    </h5>
                    <div class="mt-1 small bg-white bg-opacity-25 rounded-pill px-3 py-1">
                        <i class="fas fa-history me-1"></i> Actualizado: <?php echo date("d/m/Y", $version_terminos); ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 p-md-5 text-dark" style="background: rgba(255,255,255,0.95); line-height: 1.7;">
                    <div id="content-terminos-db">
                        <?php echo $texto_terminos_db; ?>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Entendido</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" onclick="window.aceptarTerminosDesdeModal()">Aceptar Términos</button>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    .bg-gradient-primary { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); }
    </style>
    
    <?php
// Incluye funciones PHP necesarias para el entorno
include("../models/funciones.php");
?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <!-- DATA TABLES JQUERY + BOOTSTRAP 5 -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script src="../js/scripts.js?v=<?php echo time(); ?>"></script>
    <script src="../js/validaciones.js?v=<?php echo time(); ?>"></script>

    <script>
    $(document).ready(function() {
        if ($('#datatablesSimple').length > 0) {
            $('#datatablesSimple').DataTable({
                "language": {
                    "sProcessing":     "Procesando...",
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "Ningún dato disponible en esta tabla",
                    "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix":    "",
                    "sSearch":         "Buscar:",
                    "sUrl":            "",
                    "sInfoThousands":  ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst":    "Primero",
                        "sLast":     "Último",
                        "sNext":     "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                },
                "order": []
            });
        }
    });
    </script>

    <script>
    document.addEventListener('click', function (e) {
        // Localizamos el botón submit
        const boton = e.target.closest('button[type="submit"], input[type="submit"]');
        
        if (boton) {
            const formulario = boton.form;

            // Si el formulario no es válido según el navegador, no hacemos nada
            if (formulario && !formulario.checkValidity()) {
                return; 
            }

            // Si llegamos aquí, el formulario parece estar bien
            const textoOriginal = boton.innerHTML || boton.value;
            
            // Bloqueamos el botón temporalmente
            setTimeout(() => {
                boton.disabled = true;
                if (boton.tagName === 'INPUT') {
                    boton.value = "Procesando...";
                } else {
                    boton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                }
                boton.style.opacity = "0.7";
            }, 50);

            // --- LA SOLUCIÓN AL PROBLEMA ---
            // Si después de 2 segundos la página NO se ha recargado (lo que significa que 
            // hubo un error de validación de Toastr o similar), reactivamos el botón.
            setTimeout(() => {
                if (boton.disabled) {
                    boton.disabled = false;
                    if (boton.tagName === 'INPUT') {
                        boton.value = textoOriginal;
                    } else {
                        boton.innerHTML = textoOriginal;
                    }
                    boton.style.opacity = "1";
                    console.log("Botón reactivado: Se detectó que el formulario no se envió.");
                }
            }, 2000); // 2 segundos es suficiente para que el usuario vea el error
        }
    });
    </script>
    
    <script>
        // Inicializar Tooltips de Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        // Función para mostrar el modal de motivo de rechazo
        function verMotivoRechazo(motivo) {
            document.getElementById('motivoRechazoTexto').innerText = motivo;
            var myModal = new bootstrap.Modal(document.getElementById('motivoRechazoModal'));
            myModal.show();
        }
    </script>
    <style>
        /* Estilos Ultra-Premium para Flatpickr (Matching SDGBP Design System) */
        .flatpickr-calendar {
            background: #ffffff !important;
            border: 1px solid rgba(241, 128, 0, 0.15) !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
            border-radius: 20px !important;
            padding: 15px !important;
            animation: fpFadeIn 0.3s ease-out;
            width: 320px !important;
        }
        @keyframes fpFadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .flatpickr-months .flatpickr-month {
            height: 50px !important;
            color: #1e293b !important;
        }
        .flatpickr-current-month {
            padding: 0 !important;
            font-size: 1.1rem !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .flatpickr-current-month .cur-month {
            font-weight: 800 !important;
            color: #1e293b !important;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .flatpickr-current-month .cur-year {
            font-weight: 400 !important;
            color: #94a3b8 !important;
            margin-left: 5px;
        }
        .flatpickr-months .flatpickr-prev-month, .flatpickr-months .flatpickr-next-month {
            top: 15px !important;
            color: #f18000 !important;
            fill: #f18000 !important;
            transition: all 0.2s ease;
            padding: 10px !important;
        }
        .flatpickr-months .flatpickr-prev-month:hover, .flatpickr-months .flatpickr-next-month:hover {
            color: #d67100 !important;
            background: rgba(241, 128, 0, 0.1);
            border-radius: 50%;
        }
        .flatpickr-weekday {
            color: #94a3b8 !important;
            font-weight: 700 !important;
            font-size: 0.8rem !important;
            text-transform: uppercase;
        }
        .flatpickr-day {
            border-radius: 50% !important;
            margin: 2px !important;
            height: 38px !important;
            line-height: 38px !important;
            transition: all 0.2s ease;
            font-weight: 500;
        }
        .flatpickr-day:hover {
            background: rgba(241, 128, 0, 0.1) !important;
            color: #f18000 !important;
        }
        .flatpickr-day.selected, .flatpickr-day.selected:hover {
            background: linear-gradient(135deg, #f18000 0%, #ffc107 100%) !important;
            border-color: transparent !important;
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(241, 128, 0, 0.4) !important;
            font-weight: 700;
        }
        .flatpickr-day.today {
            border-color: #f18000 !important;
            color: #f18000 !important;
        }
        .flatpickr-day.flatpickr-disabled, .flatpickr-day.flatpickr-disabled:hover {
            color: #e2e8f0 !important;
        }

        /* Fix Select2 en Input Group (Para que el icono y el select se unan sin bordes dobles) */
        .input-group > .select2-container {
            flex: 1 1 auto;
            width: 1% !important;
        }
        .input-group > .select2-container .select2-selection--single {
            height: 100% !important;
            min-height: 48px; /* Ajuste para coincidir con el addon de Bootstrap */
            display: flex;
            align-items: center;
            border: 1px solid #ced4da !important;
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
            padding-left: 10px !important;
        }
        .input-group-text {
            background-color: #f8f9fa !important;
            border: 1px solid #ced4da !important;
            color: #f18000 !important;
            min-width: 45px;
            justify-content: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            top: 0 !important;
            right: 10px !important;
        }
        /* Fix para permitir que las validaciones JS pinten rojo el contenedor de Select2 */
        .select2-container .select2-selection--single.border-danger {
            border-color: #dc3545 !important;
        }

        /* Banner de Términos Premium */
        .terms-banner-premium {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-top: 1px solid rgba(241, 128, 0, 0.2);
            box-shadow: 0 -10px 40px rgba(0,0,0,0.1);
            z-index: 9999;
            animation: slideInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes slideInUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        [data-theme="dark"] .terms-banner-premium {
            background: rgba(15, 23, 42, 0.95);
            border-top-color: rgba(255,255,255,0.05);
            box-shadow: 0 -10px 40px rgba(0,0,0,0.4);
        }

        /* Overlay de Bloqueo */
        .terms-overlay-premium {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 9998; /* Justo debajo del banner */
            animation: fadeInOverlay 0.4s ease-out;
        }
        @keyframes fadeInOverlay {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Fix Modal terms z-index (Must be above everything) */
        #modalTerminosFull {
            z-index: 10000 !important;
        }
        #modalTerminosFull .modal-backdrop {
            z-index: 9999 !important;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Re-inicialización de Flatpickr con MODO STATIC para evitar bugs de actualización
            flatpickr(".datepicker-flat", {
                dateFormat: "Y-m-d",
                locale: "es",
                allowInput: false,
                maxDate: "today",
                disableMobile: true,
                monthSelectorType: "static", // Evita el bug del dropdown que no actualiza texto
                altInput: true,
                altFormat: "d/m/Y",
                onReady: function(selectedDates, dateStr, instance) {
                    instance.altInput.classList.add("form-control");
                }
            });

            // Global Currency Masking (Banking Style: 1.500,00)
            document.body.addEventListener('input', function(e) {
                if (e.target.classList.contains('campo-monto')) {
                    maskCurrency(e.target);
                }
            });

            // Formatear campos cargados inicialmente
            document.querySelectorAll('.campo-monto').forEach(input => {
                if (input.value) maskCurrency(input, true);
            });

            function maskCurrency(element, isInitial = false) {
                let value = element.value;
                if (value === "") return;

                // Si es la carga inicial y detectamos un punto decimal (formato servidor: 1234.56)
                // lo convertimos a formato de centavos limpios (123456)
                if (isInitial && value.includes('.') && !value.includes(',')) {
                    // Aseguramos 2 decimales y quitamos el punto
                    value = (parseFloat(value) * 100).toFixed(0);
                } else if (isInitial && !value.includes('.') && !value.includes(',')) {
                    // Si viene un entero puro (ej: 100), asumimos que son 100 bolívares -> 10000 centavos
                    value = (parseInt(value) * 100).toString();
                } else {
                    // Comportamiento normal de escritura: limpiar todo lo que no sea número
                    value = value.replace(/\D/g, "");
                }

                if (value === "") return;
                
                // Convertir a representación numérica con 2 decimales
                let numericValue = (parseInt(value) / 100).toFixed(2);
                let parts = numericValue.split(".");
                let integerPart = parts[0];
                let decimalPart = parts[1];
                
                // Formatear parte entera con separador de miles (.)
                integerPart = parseInt(integerPart).toLocaleString('de-DE'); 
                
                element.value = integerPart + "," + decimalPart;
            }

            // Lógica de Aceptación de Términos (Banner Inferior Premium)
            const userType = "<?php echo $tipo_usuario ?? ''; ?>";
            const currentUrl = window.location.href;
            
            // Versión de los términos desde PHP
            const versionTerminosActual = "<?php echo $version_terminos; ?>";

            function checkTermsStatus(version) {
                const cookieVal = getCookie('sdgbp_terms_accepted');
                if (!cookieVal) return 'new'; // Primera vez
                if (cookieVal === version) return 'ok'; // Ya aceptó esta versión
                return 'updated'; // Aceptó una versión vieja, hubo cambios
            }

            const statusTerminos = checkTermsStatus(versionTerminosActual);

            // Solo mostrar si el sistema está activo, no es admin, no ha aceptado la versión actual y no está en denegado
            const terminosActivos = <?php echo $terminos_activos_f ? 'true' : 'false'; ?>;
            if (terminosActivos && userType !== 'admin' && statusTerminos !== 'ok' && !currentUrl.includes('denegado_a.php')) {
                const banner = document.getElementById('terms-banner');
                const overlay = document.getElementById('terms-overlay');
                if (banner && overlay) {
                    banner.classList.remove('d-none');
                    overlay.classList.remove('d-none');
                    
                    // Si hubo una actualización, mostramos el aviso visual
                    if (statusTerminos === 'updated') {
                        document.getElementById('terms-update-notice')?.classList.remove('d-none');
                    }

                    // Bloqueamos el scroll del body mientas no acepte
                    document.body.style.overflow = 'hidden';
                }
            }

            window.aceptarTerminosDesdeModal = function() {
                const modalElement = document.getElementById('modalTerminosFull');
                const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                modal.hide();
                aceptarTerminosAction();
            }

            function aceptarTerminosAction() {
                setCookie('sdgbp_terms_accepted', versionTerminosActual, 365);
                const banner = document.getElementById('terms-banner');
                const overlay = document.getElementById('terms-overlay');
                
                banner.classList.add('animate__animated', 'animate__fadeOutDown');
                overlay.style.transition = 'opacity 0.6s ease';
                overlay.style.opacity = '0';
                
                setTimeout(() => {
                    banner.classList.add('d-none');
                    overlay.classList.add('d-none');
                    document.body.style.overflow = 'auto'; // Restauramos scroll
                    Swal.fire({
                        icon: 'success',
                        title: 'Términos Aceptados',
                        text: 'Gracias por su compromiso con la seguridad institucional.',
                        timer: 2000,
                        showConfirmButton: false,
                        background: '#ffffff',
                        customClass: { popup: 'rounded-4 shadow-lg' }
                    }).then(() => {
                        // REVISAR SI HAY UNA BIENVENIDA PENDIENTE
                        const pendingWelcome = sessionStorage.getItem('pending_welcome');
                        if (pendingWelcome) {
                            try {
                                const data = JSON.parse(pendingWelcome);
                                // showPremiumWelcome está definida en sweetalert.php (que se incluye en el header/inicio)
                                if (typeof showPremiumWelcome === 'function') {
                                    showPremiumWelcome(data);
                                    sessionStorage.removeItem('pending_welcome');
                                }
                            } catch (e) {
                                console.error("Error al procesar bienvenida pendiente:", e);
                            }
                        }
                    });
                }, 600);
            }

            document.getElementById('btn-aceptar-terminos')?.addEventListener('click', aceptarTerminosAction);

            // Acción: Rechazar (Salir del sistema)
            document.getElementById('btn-rechazar-terminos')?.addEventListener('click', function() {
                Swal.fire({
                    title: '<span class="text-danger">¿Está seguro?</span>',
                    text: "Al rechazar los términos, su sesión será cerrada inmediatamente por seguridad.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, rechazar y salir',
                    cancelButtonText: 'Cancelar',
                    background: '#ffffff',
                    customClass: { popup: 'rounded-4' }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '../acciones/salir.php';
                    }
                });
            });

            function setCookie(name, value, days) {
                let expires = "";
                if (days) {
                    let date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = "; expires=" + date.toUTCString();
                }
                document.cookie = name + "=" + (value || "") + expires + "; path=/";
            }

            function getCookie(name) {
                let nameEQ = name + "=";
                let ca = document.cookie.split(';');
                for(let i=0;i < ca.length;i++) {
                    let c = ca[i];
                    while (c.charAt(0)==' ') c = c.substring(1,c.length);
                    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
                }
                return null;
            }

        });
    </script>

</body>
</html>