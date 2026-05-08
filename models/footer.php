    <footer class="py-4 bg-light mt-auto border-top" id="footer">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center justify-content-between small">
                <div class="text-muted">
                    <span class="fw-bold">SDGBP v2.0</span> &middot; 
                    Desarrollado por Cristian Arcaya, Pedro Rivera y Daniel Espinoza &middot; 
                    <span class="fw-bold">PNF Informática</span>
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
    </div>

    <?php
// Incluye funciones PHP necesarias para el entorno
include("../models/funciones.php");

// MODULO DE SOPORTE TICKETS (REEMPLAZO DE TAWK.TO)
// Solo se muestra el widget si NO hay sesión activa (para invitados en Login)
if (!isset($_SESSION['id']) && file_exists("../models/chat_widget.php")) {
    include("../models/chat_widget.php");
}
?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <!-- DATA TABLES JQUERY + BOOTSTRAP 5 -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script src="../js/scripts.js?v=<?php echo time(); ?>"></script>
    <script src="../js/validaciones.js?v=<?php echo time(); ?>"></script>

    <script>
      $(document).ready(function() {
        $('table[id^="datatablesSimple"]').each(function() {
            const $table = $(this);
            const isPagingEnabled = $table.attr('data-paging') !== 'false';
            const isSearchingEnabled = $table.attr('data-searching') !== 'false';
            
            $table.DataTable({
                "paging": isPagingEnabled,
                "searching": isSearchingEnabled,
                "language": {
                    "sProcessing":     "Procesando...",
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "Ningún dato disponible en esta tabla",
                    "sInfo":           isPagingEnabled ? "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros" : "Mostrando total de _TOTAL_ registros en esta página",
                    "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                    "sSearch":         "Buscar:",
                    "oPaginate": {
                        "sFirst":    "Primero",
                        "sLast":     "Último",
                        "sNext":     "Siguiente",
                        "sPrevious": "Anterior"
                    }
                },
                "order": []
            });
        });
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
            color: #cbd5e1 !important;
            background: rgba(239, 68, 68, 0.05) !important;
            cursor: not-allowed !important;
        }

        /* Indicadores de Estado Neutros (Solo para Módulos Financieros) */
        .datepicker-finance-calendar .flatpickr-day {
            position: relative !important;
        }
        .datepicker-finance-calendar .flatpickr-day::after {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 25%;
            width: 50%;
            height: 3px;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        /* Línea Naranja (Estándar): Disponible */
        .datepicker-finance-calendar .flatpickr-day:not(.flatpickr-disabled):not(.prevMonthDay):not(.nextMonthDay)::after {
            background: #f18000 !important; /* Naranja Institucional */
            box-shadow: 0 1px 4px rgba(241, 128, 0, 0.3);
        }

        /* Línea Gris: Bloqueado (Passado/Futuro/Cerrado) */
        .datepicker-finance-calendar .flatpickr-day.flatpickr-disabled::after {
            background: #94a3b8 !important; /* Gris Neutro Slated */
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
        }

        /* SOBREESCRITURA PARA MÓDULOS FINANCIEROS (Verde=Ideal, Rojo=Bloqueado, Naranja=Aviso) */
        
        /* 1. Naranja para registros "retroactivos" o anteriores al último (Alta prioridad) */
        .datepicker-finance-calendar .flatpickr-day.is-back-dated:not(.flatpickr-disabled):not(.prevMonthDay):not(.nextMonthDay)::after {
            background: #f59e0b !important; /* Naranja Advertencia */
            box-shadow: 0 1px 4px rgba(245, 158, 11, 0.4);
            z-index: 2;
        }

        /* 2. Verde Ingreso por defecto (Solo si no es retroactivo) */
        .datepicker-finance-calendar .flatpickr-day:not(.is-back-dated):not(.flatpickr-disabled):not(.prevMonthDay):not(.nextMonthDay)::after {
            background: #10b981 !important; 
            box-shadow: 0 1px 4px rgba(16, 185, 129, 0.4);
        }

        .datepicker-finance-calendar .flatpickr-day.flatpickr-disabled::after {
            background: #ef4444 !important; /* Rojo Bloqueado */
            box-shadow: 0 1px 4px rgba(239, 68, 68, 0.3);
        }

        /* Ocultar en días de otros meses para no confundir */
        .flatpickr-day.prevMonthDay::after, .flatpickr-day.nextMonthDay::after {
            display: none !important;
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Re-inicialización de Flatpickr con MODO STATIC y Bloqueo de Períodos Cerrados
            document.querySelectorAll(".datepicker-flat").forEach(function(el) {
                let options = {
                    dateFormat: "Y-m-d",
                    locale: "es",
                    allowInput: false,
                    maxDate: "today",
                    disableMobile: true,
                    monthSelectorType: "static", 
                    altInput: true,
                    altFormat: "d/m/Y",
                    onReady: function(selectedDates, dateStr, instance) {
                        instance.altInput.classList.add("form-control");
                    }
                };

                // Si el input está dentro de un formulario de filtro (GET), NO lo bloqueamos
                const originForm = el.closest('form');
                const isGetForm = originForm && originForm.method.toUpperCase() === 'GET';
                const isFilterInput = el.name && (el.name.includes('filtro') || el.name.includes('fecha_inicio') || el.name.includes('fecha_fin'));

                if (!isGetForm && !isFilterInput) {
                    // Es un formulario POST (Registro y creación), aplicar bloqueo UPU de mes cerrado
                    options.disable = [
                        function(date) {
                            // Solo restringir si el usuario es UPU
                            if (window.USER_ROLE !== 'upu') return false;
                            
                            const now = new Date();
                            const year = date.getFullYear();
                            const month = (date.getMonth() + 1).toString().padStart(2, '0');
                            const period = `${year}-${month}`;
                            
                            // 1. Bloquear MESES CERRADOS (Auditoría finalizada)
                            if (window.SDGBP_CLOSED_PERIODS && window.SDGBP_CLOSED_PERIODS.includes(period)) {
                                return true;
                            }
                            
                            // 2. REGLA DE CASCADA: No se puede registrar en un mes si el anterior está abierto.
                            // Esto solo aplica si ya existe al menos un periodo cerrado (para tener un punto de partida).
                            if (window.SDGBP_CLOSED_PERIODS && window.SDGBP_CLOSED_PERIODS.length > 0) {
                                const sorted = [...window.SDGBP_CLOSED_PERIODS].sort();
                                const lastClosed = sorted[sorted.length - 1]; // "YYYY-MM"
                                
                                // Calcular el siguiente mes permitido (Last Closed + 1)
                                const [ly, lm] = lastClosed.split('-').map(Number);
                                let nextY = ly, nextM = lm + 1;
                                if (nextM > 12) { nextM = 1; nextY++; }
                                const nextAllowed = `${nextY}-${nextM.toString().padStart(2, '0')}`;
                                
                                // Si el mes que evaluamos es posterior al "siguiente permitido", se bloquea.
                                if (period > nextAllowed) {
                                    return true;
                                }
                            } else {
                                // Caso especial: Si NO hay cierres registrados aún (sistema nuevo),
                                // permitimos registrar en el mes actual y meses anteriores, 
                                // pero aplicamos la regla de "si abril abierto, mayo bloqueado"
                                // comparando con el mes actual de la vida real.
                                const realNow = new Date();
                                const currentPeriod = `${realNow.getFullYear()}-${(realNow.getMonth() + 1).toString().padStart(2, '0')}`;
                                
                                const prevDate = new Date(realNow.getFullYear(), realNow.getMonth() - 1, 1);
                                const prevPeriod = `${prevDate.getFullYear()}-${(prevDate.getMonth() + 1).toString().padStart(2, '0')}`;
                                
                                // Si estamos evaluando el mes actual, y el anterior no está cerrado, bloqueamos el actual.
                                if (period === currentPeriod && !window.SDGBP_CLOSED_PERIODS.includes(prevPeriod)) {
                                    return true;
                                }
                            }
                            
                            return false;
                        }
                    ];
                }

                // Personalización para módulos financieros (Inyectar clase en el contenedor del calendario)
                if (el.classList.contains('datepicker-finance')) {
                    options.onOpen = function(selectedDates, dateStr, instance) {
                        instance.calendarContainer.classList.add("datepicker-finance-calendar");
                    };
                    
                    // Lógica para marcar días anteriores a HOY como "Aviso" (Naranja)
                    options.onDayCreate = function(dObj, dStr, fp, dayElem) {
                        if (dayElem.dateObj) {
                            const y = dayElem.dateObj.getFullYear();
                            const m = String(dayElem.dateObj.getMonth() + 1).padStart(2, '0');
                            const d = String(dayElem.dateObj.getDate()).padStart(2, '0');
                            const dateStr = `${y}-${m}-${d}`;
                            
                            // Obtener fecha de hoy
                            const realNow = new Date();
                            const todayStr = `${realNow.getFullYear()}-${String(realNow.getMonth() + 1).padStart(2, '0')}-${String(realNow.getDate()).padStart(2, '0')}`;
                            
                            if (dateStr < todayStr) {
                                dayElem.classList.add("is-back-dated");
                            }
                        }
                    };

                    // Alerta de advertencia al seleccionar una fecha naranja (Solo una vez por sesión de formulario)
                    let alreadyWarned = false;
                    options.onChange = function(selectedDates, dateStr, instance) {
                        if (selectedDates.length > 0) {
                            const selDate = selectedDates[0];
                            const y = selDate.getFullYear();
                            const m = String(selDate.getMonth() + 1).padStart(2, '0');
                            const d = String(selDate.getDate()).padStart(2, '0');
                            const selStr = `${y}-${m}-${d}`;
                            
                            // Obtener fecha de hoy
                            const realNow = new Date();
                            const todayStr = `${realNow.getFullYear()}-${String(realNow.getMonth() + 1).padStart(2, '0')}-${String(realNow.getDate()).padStart(2, '0')}`;
                            
                            if (selStr < todayStr) {
                                if (!alreadyWarned) {
                                    Swal.fire({
                                        title: 'Aviso: Registro en Fecha Anterior',
                                        text: 'Estás seleccionando una fecha anterior al día de hoy. Asegúrate de que esto sea correcto para tu declaración de balance.',
                                        icon: 'warning',
                                        confirmButtonColor: '#f59e0b',
                                        confirmButtonText: 'Entendido'
                                    }).then(() => {
                                        alreadyWarned = true;
                                    });
                                }
                            } else {
                                // Si cambia a una fecha correcta, reiniciamos el flag por si vuelve a equivocarse
                                alreadyWarned = false;
                            }
                        }
                    };
                }

                flatpickr(el, options);
            });

            // =====================================================================
            // LOGICA DE INPUT HÍBRIDO (CLIENTE/PROVEEDOR) CON DATALIST
            // =====================================================================
            $(document).on('input', '.hybrid-client-input', function() {
                const val = $(this).val().trim();
                const listId = $(this).attr('list');
                const datalist = document.getElementById(listId);
                
                if (!datalist) return;

                const options = datalist.options;
                let found = false;
                
                for (let i = 0; i < options.length; i++) {
                    if (options[i].value === val) {
                        found = true;
                        break;
                    }
                }

                const $saveContainer = $('#save-client-container');
                if (val === '') {
                    $saveContainer.slideUp(150);
                    $('#save_client_db').prop('checked', false);
                } else if (!found) {
                    $saveContainer.slideDown(200);
                } else {
                    $saveContainer.slideUp(150);
                    $('#save_client_db').prop('checked', false);
                }
            });

            // Handler para selección desde el dropdown de clientes guardados
            $(document).on('click', '.hybrid-client-pick', function(e) {
                e.preventDefault();
                const targetId   = $(this).data('target');          // id del input de texto
                const clientName = $(this).data('name');             // nombre del cliente
                const saveContId = $(this).data('save-container');   // id del div save-toggle

                const $input = $('#' + targetId);
                if ($input.length) {
                    $input.val(clientName);
                    // Ocultar el toggle de guardar (es un cliente existente)
                    if (saveContId) {
                        $('#' + saveContId).slideUp(150);
                        $('#save_client_db').prop('checked', false);
                    }
                    // Pequeña animación de confirmación en el input
                    $input.addClass('border-success');
                    setTimeout(() => $input.removeClass('border-success'), 800);
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

        });
    </script>

</body>
</html>