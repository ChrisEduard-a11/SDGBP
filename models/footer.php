    <footer class="py-4 mt-auto border-top shadow-lg" id="footer" style="background-color: var(--footer-bg); border-color: var(--sidebar-border) !important; transition: all 0.3s ease;">
        <div class="container-fluid px-4">
            
            <div class="row align-items-center justify-content-between flex-column flex-md-row">
                
                <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                    <p class="mb-0 small opacity-75" style="color: var(--footer-text);">
                        <i class="fas fa-code-branch me-1" style="color: var(--accent-color);"></i> 
                        <span class="fw-bold me-2" style="color: var(--text-main);">SDGBP v1.1</span>
                        | Desarrollado por Cristian Arcaya, Pedro Rivera y Daniel Espinoza | <span class="fw-bold me-2" style="color: var(--text-main);"> PNF Informatica </span>
                    </p>
                </div>
                
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0 small opacity-75" style="color: var(--footer-text);">
                        <i class="fas fa-copyright me-1"></i> &copy; <?php echo date("Y"); ?> Todos los derechos reservados.
                    </p>
                </div>
                
            </div>
            
            <hr class="my-2" style="border-color: var(--sidebar-border);">

            <div class="row">
                 <div class="col-12 text-center pt-1">
                    <p class="mb-0 small opacity-75" style="color: var(--footer-text);">
                        <span class="me-2">Licencia:</span>
                        <a href="https://creativecommons.org/licenses/by-nc/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer" class="text-decoration-none fw-semibold" style="color: var(--accent-color); transition: opacity 0.3s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                            Creative Commons BY-NC 4.0
                            <img style="height:18px!important;margin-left:3px;vertical-align:text-bottom; filter: brightness(1.1) contrast(0.9);" src="https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1" alt="CC">
                            <img style="height:18px!important;margin-left:3px;vertical-align:text-bottom; filter: brightness(1.1) contrast(0.9);" src="https://mirrors.creativecommons.org/presskit/icons/by.svg?ref=chooser-v1" alt="BY">
                            <img style="height:18px!important;margin-left:3px;vertical-align:text-bottom; filter: brightness(1.1) contrast(0.9);" src="https://mirrors.creativecommons.org/presskit/icons/nc.svg?ref=chooser-v1" alt="NC">
                        </a>
                    </p>
                 </div>
            </div>
        </div>
    </footer>
    
    <?php
// Incluye funciones PHP necesarias para el entorno
include("../models/funciones.php");
?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <!-- DATA TABLES JQUERY + BOOTSTRAP 5 -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script src="../js/scripts.js"></script>
    <script src="../js/validaciones.js"></script>

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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Global initialization for Flatpickr (Dates)
            flatpickr(".datepicker-flat", {
                dateFormat: "Y-m-d",
                locale: "es",
                allowInput: true,
                altInput: true,
                altFormat: "d/m/Y",
                onReady: function(selectedDates, dateStr, instance) {
                    instance.altInput.classList.add("form-control");
                }
            });

            // Global initialization for Select2 (Dropdowns)
            $('.form-select-select2').each(function() {
                $(this).select2({
                    theme: 'bootstrap-5',
                    placeholder: $(this).data('placeholder') || 'Seleccione una opción',
                    dropdownParent: $(this).parent(),
                    width: '100%'
                });
            });

            // Specific fix for existing fecha_pago if any
            const fechaPagoElement = document.getElementById("fecha_pago");
            if (fechaPagoElement && !fechaPagoElement.classList.contains('datepicker-flat')) {
                flatpickr(fechaPagoElement, {
                    dateFormat: "Y-m-d", 
                    maxDate: "today", 
                    locale: "es", 
                    allowInput: false
                });
            }

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

            // Limpieza global antes de enviar cualquier formulario
            document.addEventListener('submit', function(e) {
                const amounts = e.target.querySelectorAll('.campo-monto');
                amounts.forEach(input => {
                    if (input.value) {
                        // Limpiar: quitar miles (.) y cambiar decimal (,) por (.)
                        // Esto asegura que PHP maneje números estándar (1500.50)
                        let cleanValue = input.value.replace(/\./g, "").replace(",", ".");
                        input.value = cleanValue;
                    }
                });
            }, true); // useCapture para que ocurra ANTES de las validaciones individuales
        });
    </script>

</body>
</html>