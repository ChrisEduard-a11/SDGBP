/* 
   ================================================================
   SDGBP - SISTEMA DE GESTIÓN DE BIENES Y PAGOS
   MÓDULO DE VALIDACIÓN INTERNA (validaciones.js)
   Gestión de Inventario, Pagos y Administración
   ================================================================
*/

// 1. CONFIGURACIÓN GLOBAL DE TOASTR Y SONIDO
if (typeof toastr !== 'undefined') {
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-bottom-right",
        "preventDuplicates": true,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
}

// HELPER: Limpiar valores monetarios (Quitar puntos de miles y cambiar coma a punto decimal)
function cleanNumericValue(val) {
    if (!val) return "0";
    // Si ya es un número (raro desde un input.value), lo devolvemos
    if (typeof val === 'number') return val.toString();
    
    let clean = val.trim();
    // 1. Quitar los puntos que actúan como separadores de miles (ej: 1.500,00 -> 1500,00)
    // Usamos regex con 'g' para quitar todos
    clean = clean.replace(/\./g, '');
    // 2. Cambiar la coma decimal por punto decimal (ej: 1500,00 -> 1500.00)
    clean = clean.replace(',', '.');
    
    return clean;
}

function playErrorSound() {
    const audio = new Audio('../error/validation_error.mp3');
    audio.play().catch(err => {});
}

// Muestra modal de carga bloqueante (Preloader visual)
function showPreloader(title) {
    if (typeof Swal !== 'undefined') {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        Swal.fire({
            html: `
                <div style="padding: 15px 0;">
                    <div style="position: relative; width: 80px; height: 80px; margin: 0 auto 25px auto;">
                        <div class="spinner-border text-primary" role="status" style="width: 80px; height: 80px; border-width: 4px; position: relative; z-index: 2; border-right-color: transparent;"></div>
                        <i class="fas fa-shield-alt text-primary" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 24px; opacity: 0.8;"></i>
                    </div>
                    <h4 style="font-weight: 800; color: var(--text-main, ${isDark ? '#f8fafc' : '#0f172a'}); letter-spacing: -0.5px;">${title || 'Ejecutando Transacción...'}</h4>
                    <p style="color: var(--text-muted, ${isDark ? '#94a3b8' : '#64748b'}); font-size: 0.9rem; margin-top: 10px; margin-bottom: 0;">Asegurando la encriptación y comprobando datos en la bóveda, no cierres la ventana.</p>
                </div>
            `,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            background: isDark ? '#1e293b' : '#ffffff',
            customClass: {
                popup: 'border-0 shadow-lg',
                container: 'backdrop-blur'
            }
        });
        
        // Agregar plugin style for blur effect
        const blurStyle = document.createElement('style');
        blurStyle.innerHTML = `.backdrop-blur { backdrop-filter: blur(8px); background: rgba(15, 23, 42, 0.6) !important; } .swal2-popup { border-radius: 24px !important; }`;
        document.head.appendChild(blurStyle);
    }
}

// 0. UTILIDADES DE RESTRICCIÓN DE ENTRADA (Monto/Precios/Números)
// Bloquea teclas no numéricas y sanea el pegado de texto.
function setupNumericRestriction(selector, allowDecimal = true) {
    const inputs = document.querySelectorAll(selector);
    inputs.forEach(input => {
        // 1. Bloquear teclas no permitidas
        input.addEventListener('keypress', function(e) {
            const char = String.fromCharCode(e.which || e.keyCode);
            const isDigit = /\d/.test(char);
            const isDot = char === '.';
            const isComma = char === ',';
            
            if (!isDigit && (!allowDecimal || (!isDot && !isComma))) {
                if (e.ctrlKey || e.metaKey || e.altKey || e.which < 32) return; // Permitir comandos de teclado
                e.preventDefault();
                return false;
            }
            
            // Evitar múltiples puntos
            if (isDot && this.value.includes('.')) {
                e.preventDefault();
                return false;
            }
        });

        // 2. Sanear el contenido al pegar o mediante cualquier entrada (input event)
        input.addEventListener('input', function() {
            let val = this.value;
            if (allowDecimal) {
                // Permitir números, punto y coma
                val = val.replace(/[^0-9.,]/g, '');
                // No permitimos múltiples separadores decimales complejos aquí, 
                // ya que maskCurrency se encargará de la limpieza final.
            } else {
                // Solo números enteros
                val = val.replace(/[^0-9]/g, '');
            }
            if (this.value !== val) {
                this.value = val;
            }
        });
    });
}

// Inicializar restricciones al cargar el DOM o cuando se inserten elementos (opcional)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeNumericRestrictions);
} else {
    initializeNumericRestrictions();
}

function initializeNumericRestrictions() {
    // Montos y Precios (permiten decimales)
    setupNumericRestriction('#monto', true);
    setupNumericRestriction('#precio', true);
    setupNumericRestriction('#precio_unitario', true);
    
    // Cantidades y Stock (solo enteros)
    setupNumericRestriction('#cantidad', false);
    setupNumericRestriction('#stock', false);

    // Nuevas restricciones para Comprobantes
    setupNumericRestriction('#num_comprobante', false);
    setupNumericRestriction('#cuenta', false);
    setupNumericRestriction('#referencia', false);
}

// 2. GESTIÓN DE PAGOS Y INGRESOS (UPU/ADMIN)
// Helper para aplicar bordes rojos a inputs complejos (Select2, Flatpickr, Hidden)
function setFieldInvalid(id, isInvalid) {
    const el = document.getElementById(id);
    if (!el) return;

    // Si validamos 'usuario_id', el visible es 'nombre_cliente'
    let target = (id === 'usuario_id' && document.getElementById('nombre_cliente')) ? document.getElementById('nombre_cliente') : el;

    if (isInvalid) {
        target.classList.add("is-invalid");
        // Soporte Select2
        if ($(target).hasClass("select2-hidden-accessible")) {
            $(target).next('.select2-container').find('.select2-selection').addClass('border-danger text-danger');
        }
        // Soporte Flatpickr (input alternativo visible)
        if (target.classList.contains("flatpickr-input") && target.nextElementSibling && target.nextElementSibling.classList.contains("form-control")) {
            target.nextElementSibling.classList.add("is-invalid");
        }
    } else {
        target.classList.remove("is-invalid");
        if ($(target).hasClass("select2-hidden-accessible")) {
            $(target).next('.select2-container').find('.select2-selection').removeClass('border-danger text-danger');
        }
        if (target.classList.contains("flatpickr-input") && target.nextElementSibling && target.nextElementSibling.classList.contains("form-control")) {
            target.nextElementSibling.classList.remove("is-invalid");
        }
    }
}

let isConfirmedPago = false;
function validateFormRegistroP() {
    try {
        if (isConfirmedPago) return true;

        const campos = [
            { id: "usuario_id",  name: "Usuario Asociado" },
            { id: "monto",       name: "Monto del Pago" },
            { id: "metodo_pago", name: "Banco" },
            { id: "descripcion", name: "Motivo del pago" },
            { id: "cliente",     name: "Cliente" },
            { id: "codigo_pago", name: "Referencia" },
            { id: "fecha_pago",  name: "Fecha del Pago" }
        ];

        let faltantes = [];
        campos.forEach(c => {
            const el = document.getElementById(c.id);
            if (!el?.value || el.value.trim() === "") {
                faltantes.push(c.name);
                setFieldInvalid(c.id, true);
            } else {
                setFieldInvalid(c.id, false);
            }
        });

        if (faltantes.length > 0) {
            playErrorSound();
            toastr.error("Campos requeridos: " + faltantes.join(", "), "Información Faltante");
            return false;
        }

        // Validación extra para Monto (asegurar que es numérico y > 0)
        const montoValue = cleanNumericValue(document.getElementById("monto").value);
        if (isNaN(montoValue) || parseFloat(montoValue) <= 0) {
            playErrorSound();
            toastr.error("El monto debe ser un valor numérico válido y mayor a cero.", "Error de Validación");
            setFieldInvalid("monto", true);
            return false;
        }

        const refEl = document.getElementById("codigo_pago");
        const refValue = refEl?.value.trim() || "";
        if (refValue.length !== 6 || isNaN(refValue)) {
            playErrorSound();
            toastr.error("La referencia debe ser de exactamente 6 dígitos numéricos.", "Error de Validación");
            refEl?.classList.add("is-invalid");
            return false;
        }

        // Obtener datos para el resumen de confirmación
        const monto = document.getElementById("monto").value;
        const bancoSelect = document.getElementById("metodo_pago");
        const banco = bancoSelect.options[bancoSelect.selectedIndex].text;
        const ref = document.getElementById("codigo_pago").value;
        const desc = document.getElementById("descripcion").value || "Sin descripción";
        
        const clienteText = $('#cliente').val() || "";

        Swal.fire({
            title: 'Confirmar Registro de Ingreso',
            html: `
                <div style="text-align: left; font-size: 0.95rem; background: rgba(0,0,0,0.03); padding: 15px; border-radius: 8px; border: 1px solid rgba(0,0,0,0.05);">
                    <p class="mb-2"><strong>Monto:</strong> <span class="text-success fw-bold">Bs. ${monto}</span></p>
                    <p class="mb-2"><strong>Banco:</strong> ${banco}</p>
                    <p class="mb-2"><strong>Referencia:</strong> ${ref}</p>
                    <p class="mb-2"><strong>Cliente:</strong> ${clienteText}</p>
                    <p class="mb-0"><strong>Descripción:</strong> <i>${desc}</i></p>
                </div>
                <p class="mt-3 mb-0 text-muted small">¿Estás seguro de registrar este ingreso con los datos detallados?</p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2ec4b6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check"></i> Sí, registrar',
            cancelButtonText: '<i class="fas fa-edit"></i> No, revisar datos'
        }).then((result) => {
            if (result.isConfirmed) {
                isConfirmedPago = true;
                showPreloader("Procesando pago...");
                document.getElementById("formRegistroPago").submit();
            }
        });

        return false; // Prevenir envío automático
    } catch (e) {
        console.error(e);
        return false;
    }
}

let isConfirmedEgreso = false;
function validateFormRegistroEgreso() {
    try {
        if (isConfirmedEgreso) return true;

        const campos = [
            { id: "usuario_id",  name: "Usuario Asociado" },
            { id: "monto",       name: "Monto del Egreso" },
            { id: "metodo_pago", name: "Banco de Destino" },
            { id: "descripcion", name: "Descripción" },
            { id: "cliente",     name: "Cliente / Proveedor" },
            { id: "codigo_pago", name: "Referencia de Operación" },
            { id: "fecha_pago",  name: "Fecha de Egreso" }
        ];

        let faltantes = [];
        campos.forEach(c => {
            const el = document.getElementById(c.id);
            if (!el?.value || el.value.trim() === "") {
                faltantes.push(c.name);
                setFieldInvalid(c.id, true);
            } else {
                setFieldInvalid(c.id, false);
            }
        });

        if (faltantes.length > 0) {
            playErrorSound();
            toastr.error("Campos obligatorios: " + faltantes.join(", "), "Validación de Egreso");
            return false;
        }

        // Validación extra para Monto Egreso
        const montoValue = cleanNumericValue(document.getElementById("monto").value);
        if (isNaN(montoValue) || parseFloat(montoValue) <= 0) {
            playErrorSound();
            toastr.error("El monto debe ser un valor numérico válido y mayor a cero.", "Error de Validación");
            setFieldInvalid("monto", true);
            return false;
        }

        const refEl = document.getElementById("codigo_pago");
        const refValue = refEl?.value.trim() || "";
        if (refValue.length !== 6 || isNaN(refValue)) {
            playErrorSound();
            toastr.error("La referencia debe ser de exactamente 6 dígitos numéricos.", "Error de Validación");
            refEl?.classList.add("is-invalid");
            return false;
        }

        // Obtener datos para el resumen de confirmación
        const monto = document.getElementById("monto").value;
        const bancoSelect = document.getElementById("metodo_pago");
        const banco = bancoSelect.options[bancoSelect.selectedIndex].text;
        const ref = document.getElementById("codigo_pago").value;
        const desc = document.getElementById("descripcion").value || "Sin descripción";
        
        let clienteText = "";
        const clienteEl = document.getElementById("cliente");
        if (clienteEl) {
            // Funciona tanto para <input type="text"> como para <input type="hidden">
            clienteText = clienteEl.value.trim();
        }

        const userRoleEl = document.getElementById("user_role_global");
        const isAdminOrCont = userRoleEl && (userRoleEl.value === 'admin' || userRoleEl.value === 'cont');
        
        const termino = isAdminOrCont ? "comisión bancaria" : "egreso";
        const tituloConfirmacion = isAdminOrCont ? "Confirmar Registro de Comisión Bancaria" : "Confirmar Registro de Egreso";
        const txtProcesando = isAdminOrCont ? "Procesando comisión bancaria..." : "Procesando egreso...";
        const txtBotonConf = isAdminOrCont ? '<i class="fas fa-check"></i> Sí, registrar comisión' : '<i class="fas fa-check"></i> Sí, registrar';

        Swal.fire({
            title: tituloConfirmacion,
            html: `
                <div style="text-align: left; font-size: 0.95rem; background: rgba(0,0,0,0.03); padding: 15px; border-radius: 8px; border: 1px solid rgba(0,0,0,0.05);">
                    <p class="mb-2"><strong>Monto:</strong> <span class="text-danger fw-bold">Bs. ${monto}</span></p>
                    <p class="mb-2"><strong>Banco Destino:</strong> ${banco}</p>
                    <p class="mb-2"><strong>Referencia:</strong> ${ref}</p>
                    <p class="mb-2"><strong>Proveedor/Cliente:</strong> ${clienteText}</p>
                    <p class="mb-0"><strong>Descripción:</strong> <i>${desc}</i></p>
                </div>
                <p class="mt-3 mb-0 text-muted small">¿Estás seguro de registrar esta ${termino} con los datos detallados?</p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e71d36',
            cancelButtonColor: '#6c757d',
            confirmButtonText: txtBotonConf,
            cancelButtonText: '<i class="fas fa-edit"></i> No, revisar datos'
        }).then((result) => {
            if (result.isConfirmed) {
                isConfirmedEgreso = true;
                showPreloader("Procesando pago...");
                document.getElementById("formRegistroEgreso").submit();
            }
        });

        return false; // Prevenir envío automático
    } catch (e) { return false; }
}

function validateFormComprobante() {
    try {
        console.log("Iniciando validación de comprobante...");
        const campos = [
            { id: "num_comprobante", name: "N° de Comprobante" },
            { id: "fecha_comprobante", name: "Fecha de Emisión" },
            { id: "monto",           name: "Monto Total" },
            { id: "nombre",          name: "Nombre / Razón Social" },
            { id: "rif",             name: "RIF / C.I." },
            { id: "telefono",        name: "Teléfono" },
            { id: "correo",          name: "Correo Electrónico" },
            { id: "ciudad",          name: "Ciudad / Estado" },
            { id: "direccion",       name: "Dirección Fiscal" },
            { id: "banco",           name: "Banco de Destino" },
            { id: "tipo_pago",       name: "Tipo de Cuenta" },
            { id: "cuenta",          name: "Número de Cuenta (20 dígitos)" },
            { id: "referencia",      name: "Referencia Bancaria" },
            { id: "descripcion",     name: "Concepto del Egreso" }
        ];

        let ok = true;
        let faltantes = [];

        // 1. Validar campos vacíos
        campos.forEach(c => {
            const el = document.getElementById(c.id);
            let val = "";
            
            if (el) {
                // Si es un input de Flatpickr con altInput, el valor visible está en el siguiente elemento
                // pero el valor real debería estar en el original. Si el original está vacío pero hay altInput visible,
                // tratamos de obtenerlo del altInput (aunque lo ideal es que Flatpickr lo sincronice).
                val = el.value || "";
                
                // Caso especial: Flatpickr altInput
                if (!val.trim() && el.classList.contains("flatpickr-input") && el.nextElementSibling && el.nextElementSibling.classList.contains("form-control")) {
                    val = el.nextElementSibling.value || "";
                }
            }

            if (!el || !val.trim()) { 
                setFieldInvalid(c.id, true); 
                faltantes.push(c.name);
                ok = false; 
            } else {
                setFieldInvalid(c.id, false);
            }
        });

        if (!ok) {
            playErrorSound();
            const msg = "Campos obligatorios faltantes: " + faltantes.join(", ");
            if (typeof toastr !== 'undefined') {
                toastr.error(msg, "Información Incompleta");
            } else {
                alert(msg);
            }
            return false;
        }

        // 2. Validaciones Específicas
        
        // Monto
        const montoEl = document.getElementById("monto");
        const m = cleanNumericValue(montoEl ? montoEl.value : "");
        if (isNaN(m) || parseFloat(m) <= 0) {
            playErrorSound();
            const msg = "El monto debe ser un valor numérico superior a cero.";
            if (typeof toastr !== 'undefined') {
                toastr.error(msg, "Error de Monto");
            } else {
                alert(msg);
            }
            setFieldInvalid("monto", true);
            return false;
        }

        // RIF / C.I.
        const rifEl = document.getElementById("rif");
        const rif = rifEl ? rifEl.value.trim().toUpperCase() : "";
        const rifRegex = /^[VJGPE]-[0-9]{7,10}-[0-9]$|^[VJGPE][0-9]{7,10}$/;
        if (!rifRegex.test(rif)) {
            playErrorSound();
            const msg = "Formato de RIF/C.I. inválido. Use el formato: V-00000000-0 o el número directo.";
            if (typeof toastr !== 'undefined') {
                toastr.warning(msg, "Validación de Identidad");
            } else {
                alert(msg);
            }
            setFieldInvalid("rif", true);
            return false;
        }

        // Correo
        const correoEl = document.getElementById("correo");
        const correo = correoEl ? correoEl.value.trim() : "";
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(correo)) {
            playErrorSound();
            const msg = "La dirección de correo electrónico no es válida.";
            if (typeof toastr !== 'undefined') {
                toastr.error(msg, "Error de Contacto");
            } else {
                alert(msg);
            }
            setFieldInvalid("correo", true);
            return false;
        }

        // Número de Cuenta (Exactamente 20 dígitos)
        const cuentaEl = document.getElementById("cuenta");
        const cuenta = cuentaEl ? cuentaEl.value.trim() : "";
        if (cuenta.length !== 20 || isNaN(cuenta)) {
            playErrorSound();
            const msg = "El número de cuenta bancaria debe tener exactamente 20 dígitos numéricos.";
            if (typeof toastr !== 'undefined') {
                toastr.error(msg, "Error Bancario");
            } else {
                alert(msg);
            }
            setFieldInvalid("cuenta", true);
            return false;
        }

        console.log("Validación exitosa.");
        return true;
    } catch (e) {
        console.error("Error en validateFormComprobante:", e);
        alert("Error crítico en la validación: " + e.message);
        return false;
    }
}

function validateFormEditarComprobante() {
    return validateFormComprobante();
}

// 3. GESTIÓN DE INVENTARIO Y BIENES
function validateFormRB() { // Registro de Bienes
    const ids = ["categoria", "nombre", "descripcion", "serial", "fecha_adquisicion"];
    let ok = true;
    ids.forEach(id => {
        const el = document.getElementById(id);
        if(!el || !el.value.trim()) {
            setFieldInvalid(id, true);
            ok = false;
        } else setFieldInvalid(id, false);
    });

    if(!ok) {
        playErrorSound();
        toastr.error("Debe completar todos los detalles del bien, incluyendo seleccionar categoría y un bien existente.", "Error de Inventario");
        return false;
    }

    return ok;
}

function validateFormAgregarProducto() {
    const ids = ["nombre", "categoria", "descripcion", "precio", "stock"];
    let ok = true;
    ids.forEach(id => {
        const el = document.getElementById(id);
        if(!el?.value || el.value.trim() === "") {
            setFieldInvalid(id, true);
            ok = false;
        } else {
            setFieldInvalid(id, false);
        }
    });

    if(!ok) {
        playErrorSound();
        toastr.error("Complete todos los datos obligatorios del producto.", "Registro Incompleto");
        return false;
    }

    // Validación numérica para precio y stock
    const p = cleanNumericValue(document.getElementById("precio").value);
    const s = document.getElementById("stock").value;
    if (isNaN(p) || parseFloat(p) < 0 || isNaN(s) || parseInt(s) < 0) {
        playErrorSound();
        toastr.error("El precio y el stock deben ser valores numéricos válidos.", "Error de Datos");
        setFieldInvalid("precio", isNaN(p) || parseFloat(p) < 0);
        setFieldInvalid("stock", isNaN(s) || parseInt(s) < 0);
        return false;
    }

    return ok;
}

function validateFormCategoria() {
    const n = document.getElementById("nombre");
    if(!n?.value.trim()) {
        setFieldInvalid("nombre", true);
        playErrorSound();
        toastr.error("El nombre de la categoría es obligatorio.", "Faltan Datos");
        return false;
    }
    setFieldInvalid("nombre", false);
    return true;
}

// 4. ADMINISTRACIÓN DE USUARIOS
function validateFormRU() { // Registro Interno (Admin)
    const campos = [
        { id: "inputUsuario", name: "Usuario" },
        { id: "inputNacionalidad", name: "Nacionalidad" },
        { id: "inputCedula", name: "Cédula" },
        { id: "inputNombre", name: "Nombre" },
        { id: "inputEmail", name: "Correo" },
        { id: "inputPassword", name: "Contraseña" },
        { id: "inputPassword2", name: "Confirmar Contraseña" },
        { id: "inputTipo", name: "Rol" },
        { id: "inputPregunta1", name: "Pregunta 1" },
        { id: "inputRespuesta1", name: "Respuesta 1" },
        { id: "inputPregunta2", name: "Pregunta 2" },
        { id: "inputRespuesta2", name: "Respuesta 2" },
        { id: "inputTelefono", name: "Teléfono" }
    ];

    let faltantes = [];
    campos.forEach(c => {
        const el = document.getElementById(c.id);
        if(!el || !el.value.trim()) {
            faltantes.push(c.name);
            setFieldInvalid(c.id, true);
        } else {
            setFieldInvalid(c.id, false);
        }
    });

    if(faltantes.length > 0) {
        playErrorSound();
        toastr.error("Campos faltantes: " + faltantes.join(", "), "Error de Datos");
        return false;
    }

    const p1 = document.getElementById("inputPassword").value;
    const p2 = document.getElementById("inputPassword2").value;
    if (p1 !== p2) {
        setFieldInvalid("inputPassword", true);
        setFieldInvalid("inputPassword2", true);
        playErrorSound();
        toastr.error("Las contraseñas no coinciden.", "Validación");
        return false;
    }

    const regexClave = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,16}$/;
    if (!regexClave.test(p1)) {
        setFieldInvalid("inputPassword", true);
        playErrorSound();
        toastr.error("La contraseña debe tener entre 8 y 16 caracteres, e incluir al menos una mayúscula, una minúscula, un número y un carácter especial.", "Seguridad");
        return false;
    }

    const cedula = document.getElementById("inputCedula").value;
    if (!/^\d{6,9}$/.test(cedula)) {
        setFieldInvalid("inputCedula", true);
        playErrorSound();
        toastr.error("Cédula inválida (debe tener entre 6 y 9 dígitos).", "Formato");
        return false;
    }

    const t = document.getElementById("inputTelefono")?.value.trim();
    if (t && !/^\d{10,11}$/.test(t)) {
        setFieldInvalid("inputTelefono", true);
        playErrorSound();
        toastr.error("El teléfono debe tener entre 10 y 11 dígitos.", "Formato");
        return false;
    }

    const pr1 = document.getElementById("inputPregunta1")?.value;
    const pr2 = document.getElementById("inputPregunta2")?.value;
    if (pr1 && pr2 && pr1 === pr2) {
        setFieldInvalid("inputPregunta1", true);
        setFieldInvalid("inputPregunta2", true);
        playErrorSound();
        toastr.error("Las dos preguntas de seguridad deben ser distintas.", "Seguridad");
        return false;
    }

    return true;
}

function validateFormEditU() {
    const fields = ["inputUsuario", "inputNacionalidad", "inputCedula", "inputNombre", "inputEmail", "inputTelefono"];
    let faltantes = [];
    fields.forEach(id => {
        const el = document.getElementById(id);
        if (!el || !el.value.trim()) {
            faltantes.push(id.replace("input", ""));
            setFieldInvalid(id, true);
        } else {
            setFieldInvalid(id, false);
        }
    });

    if (faltantes.length > 0) {
        playErrorSound();
        toastr.error("Existen campos obligatorios vacíos: " + faltantes.join(", "), "Edición Incompleta");
        return false;
    }

    const cedula = document.getElementById("inputCedula").value;
    if (!/^\d{6,9}$/.test(cedula)) {
        setFieldInvalid("inputCedula", true);
        playErrorSound();
        toastr.error("Cédula inválida (debe tener entre 6 y 9 dígitos).", "Formato");
        return false;
    }

    const t = document.getElementById("inputTelefono")?.value.trim();
    if (t && !/^\d{10,11}$/.test(t)) {
        setFieldInvalid("inputTelefono", true);
        playErrorSound();
        toastr.error("El teléfono debe tener entre 10 y 11 dígitos.", "Formato");
        return false;
    }

    const p1 = document.getElementById("inputPassword").value;
    const p2 = document.getElementById("inputPassword2").value;
    if (p1 || p2) {
        if (p1 !== p2) {
            setFieldInvalid("inputPassword", true);
            setFieldInvalid("inputPassword2", true);
            playErrorSound();
            toastr.error("Las contraseñas nuevas no coinciden.", "Validación");
            return false;
        }
        const regexClave = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,16}$/;
        if (!regexClave.test(p1)) {
            setFieldInvalid("inputPassword", true);
            playErrorSound();
            toastr.error("La nueva contraseña debe tener entre 8 y 16 caracteres, e incluir al menos una mayúscula, una minúscula, un número y un carácter especial.", "Seguridad");
            return false;
        }
        setFieldInvalid("inputPassword", false);
        setFieldInvalid("inputPassword2", false);
    } else {
        setFieldInvalid("inputPassword", false);
        setFieldInvalid("inputPassword2", false);
    }

    return true;
}

function validarFormularioConfigU() {
    const q1 = document.getElementById("pregunta1")?.value;
    const q2 = document.getElementById("pregunta2")?.value;
    const r1 = document.getElementById("respuesta1")?.value;
    const r2 = document.getElementById("respuesta2")?.value;
    const passActual = document.getElementById("inputPasswordActual")?.value;

    // La contraseña actual siempre es obligatoria para cualquier cambio
    if (!passActual) {
        setFieldInvalid("inputPasswordActual", true);
        playErrorSound();
        toastr.error("Debes ingresar tu contraseña actual para confirmar los cambios.", "Autenticación Requerida");
        return false;
    }
    setFieldInvalid("inputPasswordActual", false);

    // Validación de nueva contraseña
    const newPass = document.getElementById("inputPassword")?.value;
    const confirmPass = document.getElementById("inputPasswordConfirm")?.value;

    if (newPass || confirmPass) {
        if (newPass !== confirmPass) {
            setFieldInvalid("inputPassword", true);
            setFieldInvalid("inputPasswordConfirm", true);
            playErrorSound();
            toastr.error("Las nuevas contraseñas no coinciden.", "Error de Contraseña");
            return false;
        }
        const regexClave = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,16}$/;
        if (!regexClave.test(newPass)) {
            setFieldInvalid("inputPassword", true);
            playErrorSound();
            toastr.warning("La nueva contraseña debe tener entre 8 y 16 caracteres, e incluir al menos una mayúscula, una minúscula, un número y un carácter especial.", "Contraseña Débil");
            return false;
        }
        setFieldInvalid("inputPassword", false);
        setFieldInvalid("inputPasswordConfirm", false);
    }

    // Si ambos campos de respuesta están vacíos (no desea cambiar preguntas)
    if (!r1 && !r2) {
        return true;
    }

    // Si llenó una respuesta pero no la otra
    if(!r1 || !r2) {
        playErrorSound();
        toastr.warning("Si deseas cambiar tus preguntas de seguridad, debes responder ambas.", "Seguridad Incompleta");
        return false;
    }

    // Si el usuario eligió la misma pregunta
    if(q1 === q2) {
        playErrorSound();
        toastr.error("Las preguntas de seguridad deben ser distintas para mayor seguridad.", "Configuración");
        return false;
    }

    return true;
}

// 5. UTILIDADES DE SISTEMA
function validateFormImportBD() {
    const file = document.getElementById("archivo_sql")?.value;
    if (!file || !file.endsWith(".sql")) {
        setFieldInvalid("archivo_sql", true);
        playErrorSound();
        toastr.error("Debe seleccionar un archivo .sql válido.", "Importación Fallida");
        return false;
    }
    setFieldInvalid("archivo_sql", false);
    return true;
}

function validateFormExportPDF() {
    const i = document.getElementsByName("filtro_fecha_inicio")[0];
    const f = document.getElementsByName("filtro_fecha_fin")[0];
    let ok = true;
    if(!i?.value) { i?.classList.add("is-invalid"); ok = false; } else { i?.classList.remove("is-invalid"); }
    if(!f?.value) { f?.classList.add("is-invalid"); ok = false; } else { f?.classList.remove("is-invalid"); }
    
    if(!ok) {
        playErrorSound();
        toastr.warning("Seleccione un rango de fechas para el reporte.", "Rango Incompleto");
        return false;
    }
    return true;
}

function validateFormFiltroBienes() {
    const c = document.getElementById("categoria");
    const n = document.getElementById("nombre_bien");
    if(!c?.value && !n?.value) {
        setFieldInvalid("categoria", true);
        setFieldInvalid("nombre_bien", true);
        playErrorSound();
        toastr.info("Seleccione al menos un criterio para filtrar.", "Filtro Vacío");
        return false;
    }
    setFieldInvalid("categoria", false);
    setFieldInvalid("nombre_bien", false);
    return true;
}

// 6. EXTRAS (Mantenimiento de compatibilidad)
function validateFormAgregarCategoria() { return validateFormCategoria(); }
function validateFormAgregarCliente() {
    const n = document.getElementById("nombre");
    if(!n?.value.trim()) {
        setFieldInvalid("nombre", true);
        playErrorSound();
        toastr.error("El nombre del cliente es obligatorio.", "Datos Faltantes");
        return false;
    }
    setFieldInvalid("nombre", false);
    return true;
}

// TOGGLE PASSWORD CONFIGURACION USUARIO
function togglePasswordVisibilityCU(inputId, btnElement) {
    const input = document.getElementById(inputId);
    const icon = btnElement.querySelector('i');
    if(input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// 7. SEGURIDAD INTERNA (Global)
document.addEventListener('keydown', e => {
    if(e.key === 'F12' || (e.ctrlKey && (e.key === 'u' || e.key === 'U'))) e.preventDefault();
});
document.addEventListener('contextmenu', e => e.preventDefault());
