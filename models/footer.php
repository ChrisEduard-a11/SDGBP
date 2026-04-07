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

        });
    </script>

</body>
</html>