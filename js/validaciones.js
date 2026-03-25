document.addEventListener("DOMContentLoaded", () => {
    const formulario = document.querySelector("form");
    if (formulario && formulario.action.includes("controlador_pago.php")) {
        formulario.addEventListener("submit", (event) => {
            if (!validateFormRegistroP()) {
                event.preventDefault();
            }
        });
    }
});

function validateFormRegistroP() {
    // Obtener todos los campos requeridos
    const camposRequeridos = [
        { id: "usuario_id", name: "Usuario Asociado" },
        { id: "monto", name: "Monto del Pago" },
        { id: "metodo_pago", name: "Banco" },
        { id: "descripcion", name: "Descripción" }, // Campo añadido
        { id: "cliente", name: "Cliente" }, // Campo añadido
        { id: "codigo_pago", name: "Referencia del Pago" },
        { id: "fecha_pago", name: "Fecha del Pago" }
    ];

    // Crear un objeto de audio para el sonido de error
    const audio = new Audio('../error/validation_error.mp3');

    // Verificar si hay campos vacíos
    let camposVacios = [];
    camposRequeridos.forEach(campo => {
        const elemento = document.getElementById(campo.id);
        const valor = elemento ? elemento.value.trim() : "";
        if (valor === "") {
            camposVacios.push(campo.name);
            elemento?.classList.add("is-invalid"); // Agregar clase de error visual
        } else {
            elemento?.classList.remove("is-invalid"); // Quitar clase de error si está lleno
        }
    });

    if (camposVacios.length > 0) {
        const mensaje = `Los siguientes campos son obligatorios: ${camposVacios.join(", ")}`;
        audio.play(); // Reproducir el sonido de error
        toastr.error(mensaje, "Error de Validación");
        return false;
    }

    // Validar el formato del monto
    const monto = document.getElementById("monto").value.trim();
    const montoRegex = /^\d+(\.\d{2})?$/; // Acepta números con hasta dos decimales, como 3000.00
    if (!montoRegex.test(monto)) {
        const mensaje = "El monto debe tener un formato válido, por ejemplo: 3000.00";
        audio.play(); // Reproducir el sonido de error
        toastr.error(mensaje, "Error de Validación");
        document.getElementById("monto").classList.add("is-invalid");
        return false;
    } else {
        document.getElementById("monto").classList.remove("is-invalid");
    }

    // Validar el formato de la referencia
    const referencia = document.getElementById("codigo_pago").value.trim();
    if (referencia === "") {
        const mensaje = "El campo 'Referencia del Pago' es obligatorio.";
        audio.play(); // Reproducir el sonido de error
        toastr.error(mensaje, "Error de Validación");
        document.getElementById("codigo_pago").classList.add("is-invalid");
        return false;
    } else if (/^\d+$/.test(referencia)) {
        // Si es numérica, debe tener exactamente 6 dígitos
        if (referencia.length !== 6) {
            const mensaje = "La referencia numérica debe tener exactamente 6 dígitos.";
            audio.play(); // Reproducir el sonido de error
            toastr.error(mensaje, "Error de Validación");
            document.getElementById("codigo_pago").classList.add("is-invalid");
            return false;
        } else {
            document.getElementById("codigo_pago").classList.remove("is-invalid");
        }
    } else {
        // Si es texto, debe tener un máximo de 30 caracteres
        if (referencia.length > 30) {
            const mensaje = "La referencia en texto no debe exceder los 30 caracteres.";
            audio.play(); // Reproducir el sonido de error
            toastr.error(mensaje, "Error de Validación");
            document.getElementById("codigo_pago").classList.add("is-invalid");
            return false;
        } else {
            document.getElementById("codigo_pago").classList.remove("is-invalid");
        }
    }

    // Deshabilitar el botón de envío para evitar múltiples clics
    const btnSubmit = document.querySelector("#formRegistroPago button[type='submit']");
    if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i> Procesando...';
    }

    return true; // Permitir el envío del formulario si todo está correcto
}

// Función para verificar si un valor es numérico
function isNumeric(value) {
    return /^\d+$/.test(value);
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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Registro Egreso
// Registro Egreso
document.addEventListener("DOMContentLoaded", () => {
    const formulario = document.querySelector("form");
    if (formulario && formulario.action.includes("controlador_pago_egreso.php")) {
        formulario.addEventListener("submit", (event) => {
            if (!validateFormRegistroEgreso()) {
                event.preventDefault();
            }
        });
    }
});

function validateFormRegistroEgreso() {
    // Obtener todos los campos requeridos
    const camposRequeridos = [
        { id: "usuario_id", name: "Usuario Asociado" },
        { id: "monto", name: "Monto del Egreso" },
        { id: "descripcion", name: "Descripción" },
        { id: "cliente", name: "Cliente" },
        { id: "codigo_pago", name: "Referencia del Egreso" },
        { id: "fecha_pago", name: "Fecha del Egreso" }
    ];

    // Crear un objeto de audio para el sonido de error
    const audio = new Audio('../error/validation_error.mp3');

    // Verificar si hay campos vacíos
    let camposVacios = [];
    camposRequeridos.forEach(campo => {
        const elemento = document.getElementById(campo.id);
        const valor = elemento ? elemento.value.trim() : "";
        if (!valor) {
            camposVacios.push(campo.name);
            elemento?.classList.add("is-invalid"); // Agregar clase de error visual
        } else {
            elemento?.classList.remove("is-invalid"); // Quitar clase de error si está lleno
        }
    });

    if (camposVacios.length > 0) {
        const mensaje = `Los siguientes campos son obligatorios: ${camposVacios.join(", ")}`;
        audio.play(); // Reproducir el sonido de error
        toastr.error(mensaje, "Error de Validación");
        return false;
    }

    // Validar el formato del monto
    const monto = document.getElementById("monto").value.trim();
    const montoRegex = /^\d+(\.\d{2})?$/; // Acepta números con hasta dos decimales, como 3000.00
    if (!montoRegex.test(monto)) {
        const mensaje = "El monto debe tener un formato válido, por ejemplo: 3000.00";
        audio.play(); // Reproducir el sonido de error
        toastr.error(mensaje, "Error de Validación");
        document.getElementById("monto").classList.add("is-invalid");
        return false;
    } else {
        document.getElementById("monto").classList.remove("is-invalid");
    }

    // Validar el formato de la descripción
    const descripcion = document.getElementById("descripcion").value.trim();
    const descripcionRegex = /^[a-zA-Z0-9\s]{1,50}$/; // Permite letras, números y espacios, máximo 50 caracteres

    if (!descripcionRegex.test(descripcion)) {
        const mensaje = "La descripción debe tener un máximo de 50 caracteres y no debe incluir caracteres especiales.";
        audio.play(); // Reproducir el sonido de error
        toastr.error(mensaje, "Error de Validación");
        document.getElementById("descripcion").classList.add("is-invalid");
        return false;
    } else {
        document.getElementById("descripcion").classList.remove("is-invalid");
    }

    // Validar el formato de la referencia
    const referencia = document.getElementById("codigo_pago").value.trim();
    if (referencia === "") {
        const mensaje = "El campo 'Referencia del Egreso' es obligatorio.";
        audio.play(); // Reproducir el sonido de error
        toastr.error(mensaje, "Error de Validación");
        document.getElementById("codigo_pago").classList.add("is-invalid");
        return false;
    } else if (/^\d+$/.test(referencia)) {
        // Si es numérica, debe tener exactamente 6 dígitos
        if (referencia.length !== 6) {
            const mensaje = "La referencia numérica debe tener exactamente 6 dígitos.";
            audio.play(); // Reproducir el sonido de error
            toastr.error(mensaje, "Error de Validación");
            document.getElementById("codigo_pago").classList.add("is-invalid");
            return false;
        } else {
            document.getElementById("codigo_pago").classList.remove("is-invalid");
        }
    } else {
        // Si es texto, debe tener un máximo de 30 caracteres
        if (referencia.length > 30) {
            const mensaje = "La referencia en texto no debe exceder los 30 caracteres.";
            audio.play(); // Reproducir el sonido de error
            toastr.error(mensaje, "Error de Validación");
            document.getElementById("codigo_pago").classList.add("is-invalid");
            return false;
        } else {
            document.getElementById("codigo_pago").classList.remove("is-invalid");
        }
    }

    // Deshabilitar el botón de envío para evitar múltiples clics
    const btnSubmit = document.querySelector("#formRegistroEgreso button[type='submit']");
    if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i> Procesando...';
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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Agregar Producto
// Agregar Producto
document.addEventListener("DOMContentLoaded", () => {
    const formulario = document.querySelector("form");
    if (formulario && formulario.action.includes("guardar_bien.php")) {
        formulario.addEventListener("submit", (event) => {
            if (!validateFormAgregarProducto()) {
                event.preventDefault();
            }
        });
    }
});

function validateFormAgregarProducto() {
    // Obtener todos los campos requeridos
    const camposRequeridos = [
        { id: "nombre", name: "Nombre del Producto" },
        { id: "precio", name: "Precio" },
        { id: "stock", name: "Stock" },
        { id: "categoria", name: "Categoría" }
    ];

    // Crear un objeto de audio para el sonido de error
    const audio = new Audio('../error/validation_error.mp3');

    // Verificar si hay campos vacíos
    let camposVacios = [];
    camposRequeridos.forEach(campo => {
        const elemento = document.getElementById(campo.id);
        const valor = elemento ? elemento.value.trim() : "";
        if (!valor) {
            camposVacios.push(campo.name);
            elemento?.classList.add("is-invalid"); // Agregar clase de error visual
        } else {
            elemento?.classList.remove("is-invalid"); // Quitar clase de error si está lleno
        }
    });

    if (camposVacios.length > 0) {
        const mensaje = `Los siguientes campos son obligatorios: ${camposVacios.join(", ")}`;
        audio.play(); // Reproducir el sonido de error
        toastr.error(mensaje, "Error de Validación");
        return false;
    }

    // Validar el formato del precio
    const precio = document.getElementById("precio").value.trim();
    const precioRegex = /^\d+(\.\d{2})?$/; // Acepta números con hasta dos decimales, como 3000.00
    if (!precioRegex.test(precio)) {
        const mensaje = "El precio debe tener un formato válido, por ejemplo: 3000.00";
        audio.play(); // Reproducir el sonido de error
        toastr.error(mensaje, "Error de Validación");
        document.getElementById("precio").classList.add("is-invalid");
        return false;
    } else {
        document.getElementById("precio").classList.remove("is-invalid");
    }

    // Validar el stock (debe ser un número entero positivo)
    const stock = document.getElementById("stock").value.trim();
    if (isNaN(stock) || parseInt(stock) <= 0) {
        const mensaje = "El stock debe ser un número entero positivo.";
        audio.play(); // Reproducir el sonido de error
        toastr.error(mensaje, "Error de Validación");
        document.getElementById("stock").classList.add("is-invalid");
        return false;
    } else {
        document.getElementById("stock").classList.remove("is-invalid");
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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Agregar Categoria Producto
// Agregar Categoria Producto
document.addEventListener("DOMContentLoaded", () => {
    const formulario = document.querySelector("form");
    if (formulario && formulario.action.includes("guardar_categoria.php")) {
        formulario.addEventListener("submit", (event) => {
            if (!validateFormAgregarCategoria()) {
                event.preventDefault();
            }
        });
    }
});

function validateFormAgregarCategoria() {
    // Obtener el campo requerido
    const nombreCategoria = document.getElementById("nombre");
    const audio = new Audio('../error/validation_error.mp3'); // Sonido de error

    // Verificar si el campo está vacío
    if (!nombreCategoria.value.trim()) {
        const mensaje = "El campo 'Nombre de la Categoría' es obligatorio.";
        audio.play(); // Reproducir el sonido de error
        toastr.error(mensaje, "Error de Validación");
        nombreCategoria.classList.add("is-invalid"); // Agregar clase de error visual
        return false;
    } else {
        nombreCategoria.classList.remove("is-invalid"); // Quitar clase de error si está lleno
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

function validarFormularioConfigU() {
    const passwordActual = document.getElementById("inputPasswordActual").value.trim();
    const nuevaPassword = document.getElementById("inputPassword").value.trim();
    const confirmarPassword = document.getElementById("inputPasswordConfirm").value.trim();

    // Crear un objeto de audio para el sonido de error
    const audio = new Audio('../error/validation_error.mp3');

    // Validar que la contraseña actual no esté vacía
    if (passwordActual === "") {
        audio.play();
        toastr.error("El campo 'Contraseña Actual' es obligatorio", "Error de Validación");
        return false;
    }

    // Ya NO se validan preguntas ni respuestas de seguridad

    // Validar que si se ingresa una nueva contraseña, cumpla con los requisitos
    if (nuevaPassword !== "" || confirmarPassword !== "") {
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

        if (!passwordRegex.test(nuevaPassword)) {
            audio.play();
            toastr.error("La nueva contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas, números y caracteres especiales.", "Error de Validación");
            return false;
        }

        // Validar que las contraseñas coincidan
        if (nuevaPassword !== confirmarPassword) {
            audio.play();
            toastr.error("Las contraseñas no coinciden", "Error de Validación");
            return false;
        }
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

function togglePasswordVisibilityCU(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Editar usuario
function validateFormEditU() {
    const audio = new Audio('../error/validation_error.mp3');

    // Obtener todos los campos requeridos
    const usuario = document.querySelector("[name='usuario']").value.trim();
    const nacionalidad = document.querySelector("[name='nacionalidad']").value.trim();
    const cedula = document.querySelector("[name='cedula']").value.trim();
    const nombre = document.querySelector("[name='nombre']").value.trim();
    const correo = document.querySelector("[name='correo']").value.trim();
    const tipo = document.querySelector("[name='tipo']").value.trim();

    // Validar campos obligatorios
    const camposVacios = [];
    if (usuario === "") camposVacios.push("Usuario");
    if (nacionalidad === "") camposVacios.push("Nacionalidad");
    if (cedula === "") camposVacios.push("Cédula");
    if (nombre === "") camposVacios.push("Nombre");
    if (correo === "") camposVacios.push("Correo");
    if (tipo === "") camposVacios.push("Tipo");

    if (camposVacios.length > 0) {
        const mensaje = `Los siguientes campos son obligatorios: ${camposVacios.join(", ")}`;
        audio.play();
        toastr.error(mensaje, "Error de Validación");
        return false;
    }

    // Validar usuario: mínimo 4 caracteres, al menos una mayúscula, una minúscula y un número, solo letras y números
    const usuarioRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z0-9]{4,}$/;
    if (!usuarioRegex.test(usuario)) {
        audio.play();
        toastr.error("El usuario debe tener mínimo 4 caracteres, incluir al menos una mayúscula, una minúscula y un número. Solo letras y números.", "Error de Validación");
        return false;
    }
    if (usuario.trim() !== usuario) {
        audio.play();
        toastr.error("El usuario no debe tener espacios al inicio o al final.", "Error de Validación");
        return false;
    }

    // Validar nacionalidad/letra de cédula (solo V-, E-, G-, J-)
    if (!["V-", "E-", "G-", "J-"].includes(nacionalidad)) {
        audio.play();
        toastr.error("Seleccione una letra válida para la cédula (V, E, G o J)", "Error de Validación");
        return false;
    }

    // Validar número de cédula (6 a 9 dígitos, solo números)
    const cedulaRegex = /^\d{6,9}$/;
    if (!cedulaRegex.test(cedula)) {
        audio.play();
        toastr.error("El número de cédula debe tener entre 6 y 9 dígitos y solo números.", "Error de Validación");
        return false;
    }

    // Validar si la cédula ya existe en la base de datos (AJAX síncrono)
    var xhr = new XMLHttpRequest();
    // Obtener el id del usuario (solo si existe el campo oculto en el formulario)
    var usuarioIdInput = document.getElementById("usuario_id");
    var usuarioId = usuarioIdInput ? usuarioIdInput.value : "";
    var postData = "cedula=" + encodeURIComponent(cedula);
    if (usuarioId !== "") {
        postData += "&usuario_id=" + encodeURIComponent(usuarioId);
    }
    xhr.open("POST", "../models/verificar_cedula.php", false); // false = síncrono
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send(postData);
    if (xhr.status === 200 && xhr.responseText.trim() === "existe") {
        audio.play();
        toastr.error("La cédula ya está registrada en el sistema.", "Error de Validación");
        return false;
    }

    // Validar nombre: solo letras y espacios, mínimo 2 caracteres
    const nombreRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,}$/;
    if (!nombreRegex.test(nombre)) {
        audio.play();
        toastr.error("El nombre solo debe contener letras y espacios, mínimo 2 caracteres.", "Error de Validación");
        return false;
    }

    // Validar correo electrónico (acepta cualquier dominio válido)
    const emailRegex = /^[a-zA-Z0-9._%+-ñÑ]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailRegex.test(correo.toLowerCase())) {
        audio.play();
        toastr.error("El correo electrónico debe ser válido (ejemplo: usuario@dominio.com).", "Error de Validación");
        return false;
    }

    // Validar contraseña si se ingresan
    const clave = document.getElementById("inputPassword").value.trim();
    const confirmar_clave = document.getElementById("inputPassword2").value.trim();

    if (clave !== "" || confirmar_clave !== "") {
        if (clave !== confirmar_clave) {
            audio.play();
            toastr.error("Las contraseñas no coinciden", "Error de Validación");
            return false;
        }
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!passwordRegex.test(clave)) {
            audio.play();
            toastr.error("La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas, números y caracteres especiales.", "Error de Validación");
            return false;
        }
    }

    return true;
}

function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

const togglePassword = document.getElementById('togglePassword');
if (togglePassword) {
    togglePassword.addEventListener('click', function () {
        togglePasswordVisibility('inputPassword', this);
    });
}

const togglePassword2 = document.getElementById('togglePassword2');
if (togglePassword2) {
    togglePassword2.addEventListener('click', function () {
        togglePasswordVisibility('inputPassword2', this);
    });
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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Registrar Bien
function validateFormRB() {
    // Obtener todos los campos requeridos
    const camposRequeridos = [
        { id: "categoria", name: "Categoría" },
        { id: "nombre", name: "Nombre del Bien" },
        { id: "descripcion", name: "Descripción" },
        { id: "serial", name: "Serial" },
        { id: "fecha_adquisicion", name: "Fecha de Adquisición" }
    ];

    // Crear un objeto de audio para el sonido de error
    const audio = new Audio('../error/validation_error.mp3');

    // Verificar si hay campos vacíos
    let camposVacios = [];
    camposRequeridos.forEach(campo => {
        const valor = document.getElementById(campo.id).value.trim();
        if (valor === "") {
            camposVacios.push(campo.name);
        }
    });

    if (camposVacios.length > 0) {
        const mensaje = `Los siguientes campos son obligatorios: ${camposVacios.join(", ")}`;
        audio.play(); // Reproducir el sonido de error
        toastr.error(mensaje, "Error de Validación");
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

function agregarNuevoBien() {
    alert("Función para registrar un nuevo bien. Aquí puedes redirigir a un formulario o abrir un modal.");
}

function autocompletarDescripcion() {
    const selectBien = document.getElementById('nombre');
    const descripcion = selectBien.options[selectBien.selectedIndex].getAttribute('data-descripcion');
    document.getElementById('descripcion').value = descripcion || '';
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Registrar Usuario
function validateFormRU() {
    const audio = new Audio('../error/validation_error.mp3');

    // Obtener todos los campos requeridos
    const usuario = document.getElementById("inputUsuario").value.trim();
    const nacionalidad = document.getElementById("inputNacionalidad").value.trim();
    const cedula = document.getElementById("inputCedula").value.trim();
    const nombre = document.getElementById("inputNombre").value.trim();
    const correo = document.getElementById("inputEmail").value.trim();
    const tipo = document.getElementById("inputTipo").value.trim();
    const clave = document.getElementById("inputPassword").value.trim();
    const confirmar_clave = document.getElementById("inputPassword2").value.trim();
    const pregunta1 = document.getElementById("inputPregunta1").value.trim();
    const respuesta1 = document.getElementById("inputRespuesta1").value.trim();
    const pregunta2 = document.getElementById("inputPregunta2").value.trim();
    const respuesta2 = document.getElementById("inputRespuesta2").value.trim();

    // Validar campos obligatorios
    const camposVacios = [];
    if (usuario === "") camposVacios.push("Usuario");
    if (nacionalidad === "") camposVacios.push("Nacionalidad");
    if (cedula === "") camposVacios.push("Cédula");
    if (nombre === "") camposVacios.push("Nombre");
    if (correo === "") camposVacios.push("Correo");
    if (tipo === "") camposVacios.push("Tipo");
    if (clave === "") camposVacios.push("Contraseña");
    if (confirmar_clave === "") camposVacios.push("Confirmar Contraseña");
    if (pregunta1 === "") camposVacios.push("Pregunta 1");
    if (respuesta1 === "") camposVacios.push("Respuesta 1");
    if (pregunta2 === "") camposVacios.push("Pregunta 2");
    if (respuesta2 === "") camposVacios.push("Respuesta 2");

    if (camposVacios.length > 0) {
        const mensaje = `Los siguientes campos son obligatorios: ${camposVacios.join(", ")}`;
        audio.play();
        toastr.error(mensaje, "Error de Validación");
        return false;
    }

    // Validar que las preguntas no sean iguales
    if (pregunta1 === pregunta2) {
        audio.play();
        toastr.error("Las preguntas de seguridad no pueden ser iguales.", "Error de Validación");
        return false;
    }

    // Validar que las respuestas no sean iguales
    if (respuesta1.toLowerCase() === respuesta2.toLowerCase()) {
        audio.play();
        toastr.error("Las respuestas de seguridad no pueden ser iguales.", "Error de Validación");
        return false;
    }

    // Validar nombre: solo letras y espacios, mínimo 2 caracteres
    const nombreRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,}$/;
    if (!nombreRegex.test(nombre)) {
        audio.play();
        toastr.error("El nombre solo debe contener letras y espacios, mínimo 2 caracteres.", "Error de Validación");
        return false;
    }

    // Validar usuario: mínimo 4 caracteres, al menos una mayúscula, una minúscula y un número, solo letras y números
    const usuarioRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z0-9]{4,}$/;
    if (!usuarioRegex.test(usuario)) {
        audio.play();
        toastr.error("El usuario debe tener mínimo 4 caracteres, incluir al menos una mayúscula, una minúscula y un número. Solo letras y números.", "Error de Validación");
        return false;
    }
    if (usuario.trim() !== usuario) {
        audio.play();
        toastr.error("El usuario no debe tener espacios al inicio o al final.", "Error de Validación");
        return false;
    }

    // Validar nacionalidad/letra de cédula (solo V-, E-, G-, J-)
    if (!["V-", "E-", "G-", "J-"].includes(nacionalidad)) {
        audio.play();
        toastr.error("Seleccione una letra válida para la cédula (V, E, G o J)", "Error de Validación");
        return false;
    }

    // Validar número de cédula (6 a 9 dígitos, solo números)
    const cedulaRegex = /^\d{6,9}$/;
    if (!cedulaRegex.test(cedula)) {
        audio.play();
        toastr.error("El número de cédula debe tener entre 6 y 9 dígitos y solo números.", "Error de Validación");
        return false;
    }

    // Validar si la cédula ya existe en la base de datos (AJAX síncrono)
    var existe = false;
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "../models/verificar_cedula.php", false); // false = síncrono
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("cedula=" + encodeURIComponent(cedula));
    if (xhr.status === 200 && xhr.responseText.trim() === "existe") {
        audio.play();
        toastr.error("La cédula ya está registrada en el sistema.", "Error de Validación");
        return false;
    }

    const emailRegex = /^[a-zA-Z0-9._%+-ñÑ]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailRegex.test(correo.toLowerCase())) {
        audio.play();
        toastr.error("El correo electrónico debe tener un formato válido.", "Error de Validación");
        return false;
    }

    // Validar contraseña
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    if (!passwordRegex.test(clave)) {
        audio.play();
        toastr.error("La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas, números y caracteres especiales.", "Error de Validación");
        return false;
    }

    // Validar que las contraseñas coincidan
    if (clave !== confirmar_clave) {
        audio.play();
        toastr.error("Las contraseñas no coinciden", "Error de Validación");
        return false;
    }

    // Validar respuestas: mínimo 3 caracteres
    if (respuesta1.length < 3 || respuesta2.length < 3) {
        audio.play();
        toastr.error("Las respuestas de seguridad deben tener al menos 3 caracteres.", "Error de Validación");
        return false;
    }
    // Validar imagen (ahora obligatoria)
    const imagenInput = document.getElementById("imagen");
    if (!imagenInput || imagenInput.files.length === 0) {
        audio.play();
        toastr.error("Debes seleccionar una imagen de perfil.", "Error de Validación");
        return false;
    } else {
        const archivo = imagenInput.files[0];
        const tiposPermitidos = ["image/jpeg", "image/png", "image/gif"];
        const maxSize = 2 * 1024 * 1024; // 2MB

        if (!tiposPermitidos.includes(archivo.type)) {
            audio.play();
            toastr.error("La imagen debe ser JPG, PNG o GIF.", "Error de Validación");
            return false;
        }
        if (archivo.size > maxSize) {
            audio.play();
            toastr.error("La imagen no debe superar los 2MB.", "Error de Validación");
            return false;
        }
    }

    return true;
}
document.addEventListener('DOMContentLoaded', function() {
    const pregunta1 = document.getElementById('inputPregunta1');
    const pregunta2 = document.getElementById('inputPregunta2');

    if (pregunta1 && pregunta2) {
        const opciones1 = Array.from(pregunta1.options).map(opt => opt.cloneNode(true));
        const opciones2 = Array.from(pregunta2.options).map(opt => opt.cloneNode(true));

        function actualizarSelect(origen, destino, opcionesOriginales) {
            const valorSeleccionado = destino.value;
            destino.innerHTML = '';
            opcionesOriginales.forEach(opt => {
                if (opt.value !== origen.value || opt.value === "") {
                    destino.appendChild(opt.cloneNode(true));
                }
            });
            // Si el valor seleccionado sigue disponible, lo volvemos a seleccionar
            if (destino.querySelector('option[value="' + valorSeleccionado + '"]')) {
                destino.value = valorSeleccionado;
            } else {
                destino.value = ""; // Selecciona la opción por defecto si no está disponible
            }
        }

        pregunta1.addEventListener('change', function() {
            actualizarSelect(pregunta1, pregunta2, opciones2);
        });

        pregunta2.addEventListener('change', function() {
            actualizarSelect(pregunta2, pregunta1, opciones1);
        });

        // Inicializa al cargar la página
        actualizarSelect(pregunta1, pregunta2, opciones2);
        actualizarSelect(pregunta2, pregunta1, opciones1);
    }
});
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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Cerrar Sesión por Inactividad

// Detectar el cierre del navegador o pestaña
window.addEventListener("unload", () => {
    // Enviar una solicitud al servidor para marcar la sesión como "pendiente de destrucción"
    navigator.sendBeacon("../models/marcar_sesion_para_destruir.php");
});

// Verificar la sesión cada 30 segundos
setInterval(() => {
    fetch("../models/verificar_sesion.php")
        .then(response => response.json())
        .then(data => {
            if (data.status === "destroyed") {
                // Si la sesión fue destruida, redirigir al usuario a la página de inicio de sesión
                window.location.href = "../vistas/login.php";
            }
        })
        .catch(error => console.error("Error al verificar la sesión:", error));
}, 30000); // 30 segundos
