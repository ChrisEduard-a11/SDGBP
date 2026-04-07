/* 
   ================================================================
   SDGBP - SISTEMA DE GESTIÓN DE BIENES Y PAGOS
   MÓDULO DE VALIDACIÓN INTERNA (validaciones.js)
   Gestión de Inventario, Pagos y Administración
   ================================================================
*/

// 1. CONFIGURACIÓN GLOBAL DE TOASTR Y SONIDO
toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": true,
    "progressBar": true,
    "positionClass": "toast-bottom-right", // Abajo a la derecha para no obstruir el menú superior
    "preventDuplicates": true,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn", // Animación más ligera
    "hideMethod": "fadeOut"
};

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
        
        let clienteText = "";
        const clienteEl = document.getElementById("cliente");
        if(clienteEl && clienteEl.selectedIndex >= 0) {
            clienteText = clienteEl.options[clienteEl.selectedIndex].text;
        }

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
        if(clienteEl && clienteEl.selectedIndex >= 0) {
            clienteText = clienteEl.options[clienteEl.selectedIndex].text;
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
    const fields = [
        "num_comprobante", "fecha", "monto", 
        "nombre", "rif", "telefono", "correo", 
        "ciudad", "direccion", "banco", 
        "tipo_pago", "cuenta", "referencia", "descripcion"
    ];
    let ok = true;
    fields.forEach(id => {
        const el = document.getElementById(id);
        if (!el || !el.value.trim()) { 
            setFieldInvalid(id, true); 
            ok = false; 
        } else {
            setFieldInvalid(id, false);
        }
    });

    if (!ok) {
        playErrorSound();
        toastr.error("Faltan campos obligatorios por completar para generar el documento.", "Datos Incompletos");
        return false;
    }

    // Validación numérica para monto
    const m = cleanNumericValue(document.getElementById("monto").value);
    if (isNaN(m) || parseFloat(m) <= 0) {
        playErrorSound();
        toastr.error("El monto debe ser un valor numérico válido y mayor a cero.", "Error de Datos");
        setFieldInvalid("monto", true);
        return false;
    }

    return true;
}

function validateFormEditarComprobante() {
    const fields = [
        "num_comprobante", "fecha", "monto", 
        "nombre", "rif", "telefono", "correo", 
        "ciudad", "direccion", "banco", 
        "tipo_pago", "cuenta", "referencia", "descripcion"
    ];
    let ok = true;
    fields.forEach(id => {
        const el = document.getElementById(id);
        if (!el || !el.value.trim()) { 
            setFieldInvalid(id, true); 
            ok = false; 
        } else {
            setFieldInvalid(id, false);
        }
    });

    if (!ok) {
        playErrorSound();
        toastr.error("Faltan campos obligatorios al intentar editar el documento.", "Campos Vacíos");
        return false;
    }

    // Validación numérica para monto
    const m = cleanNumericValue(document.getElementById("monto").value);
    if (isNaN(m) || parseFloat(m) <= 0) {
        playErrorSound();
        toastr.error("El monto debe ser un valor numérico válido y mayor a cero.", "Error de Datos");
        setFieldInvalid("monto", true);
        return false;
    }

    return true;
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
        { id: "inputRespuesta2", name: "Respuesta 2" }
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
    const fields = ["inputUsuario", "inputNacionalidad", "inputCedula", "inputNombre", "inputEmail"];
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
