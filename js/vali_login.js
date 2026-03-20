/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Solicitar Desbloqueo
function validateFormSD() {
    // Obtener el valor del campo de usuario
    var usuario = document.getElementById("inputUsuario").value;

    // Crear un objeto de audio para el sonido de error
    const audio = new Audio('../error/validation_error.mp3');

    // Validar que el campo de usuario no esté vacío
    if (usuario === "") {
        audio.play(); // Reproducir el sonido de error
        toastr.error("El campo de usuario es obligatorio", "Error de Validación");
        return false;
    }

    // Si todo está correcto, permitir el envío del formulario
    return true;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Recuperar Cuenta Usuario
function validateFormRCU() {
    var usuario = document.getElementById("inputUsuario").value;
    const audio = new Audio('../error/validation_error.mp3');
    if (usuario === "") {
        audio.play();
        toastr.error("El campo de usuario es obligatorio", "Error de Validación");
        return false;
    }
    return true;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Recuperar Usuario (Olvidó su nombre)
function validateFormRU() {
    var ci = document.getElementById("inputCI").value;
    const audio = new Audio('../error/validation_error.mp3');
    if (ci === "") {
        audio.play();
        toastr.error("El campo Cédula de Identidad es obligatorio", "Error de Validación");
        return false;
    }
    return true;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Validar Código de 6 dígitos
function validateFormCodigo() {
    var codigo = document.getElementsByName("codigo")[0].value;
    const audio = new Audio('../error/validation_error.mp3');
    if (codigo === "" || codigo.length < 6) {
        audio.play();
        toastr.error("Debe ingresar el código válido de 6 dígitos enviado a su correo", "Error de Validación");
        return false;
    }
    return true;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Validar Confirmar Email (Recuperar Usuario)
function validateFormVEU() {
    var correo = document.getElementById("inputEmail").value;
    const audio = new Audio('../error/validation_error.mp3');
    if (correo === "") {
        audio.play();
        toastr.error("Debe escribir el correo de la pista para continuar", "Error de Validación");
        return false;
    }
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!re.test(correo)) {
        audio.play();
        toastr.error("El formato del correo electrónico es inválido", "Error de Validación");
        return false;
    }
    return true;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Validar Restablecer Contraseña (desde enlace de correo)
function validateFormRC() {
    var clave = document.getElementById("nueva_contrasena").value;
    var clave1 = document.getElementById("confirmar_contrasena").value;
    const audio = new Audio('../error/validation_error.mp3');

    if (clave === "" || clave1 === "") {
        audio.play();
        toastr.error("Ambos campos de contraseña son obligatorios", "Error de Validación");
        return false;
    }

    if (clave !== clave1) {
        audio.play();
        toastr.error("Las contraseñas no coinciden", "Error de Validación");
        return false;
    }

    if (clave.length < 8) {
        audio.play();
        toastr.error("La contraseña debe tener al menos 8 caracteres", "Error de Seguridad");
        return false;
    }

    return true;
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
//Metodo de Recuperacion 
function validateFormMC() {
    const metodo = document.getElementById("metodo").value;

    // Crear un objeto de audio para el sonido de error
    const audio = new Audio('../error/validation_error.mp3');

    // Validar que se haya seleccionado un método
    if (metodo === "") {
        audio.play(); // Reproducir el sonido de error
        toastr.error("Por favor, seleccione un método de recuperación.", "Error de Validación");
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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Restableser Contraseña
function checkPasswordStrength() {
    const password = document.getElementById("nueva_contrasena").value;
    const strengthIndicator = document.getElementById("passwordStrength");
    let strength = 0;

    // Verificar la longitud de la contraseña
    if (password.length >= 8) strength++;

    // Verificar si contiene letras mayúsculas
    if (/[A-Z]/.test(password)) strength++;

    // Verificar si contiene letras minúsculas
    if (/[a-z]/.test(password)) strength++;

    // Verificar si contiene números
    if (/\d/.test(password)) strength++;

    // Verificar si contiene caracteres especiales
    if (/[@$!%*?&]/.test(password)) strength++;

    // Mostrar la fuerza de la contraseña
    switch (strength) {
        case 0:
            strengthIndicator.textContent = "";
            strengthIndicator.style.color = "";
            break;
        case 1:
        case 2:
            strengthIndicator.textContent = "Contraseña débil";
            strengthIndicator.style.color = "red";
            break;
        case 3:
            strengthIndicator.textContent = "Contraseña moderada";
            strengthIndicator.style.color = "orange";
            break;
        case 4:
        case 5:
            strengthIndicator.textContent = "Contraseña fuerte";
            strengthIndicator.style.color = "green";
            break;
    }
}

function validateFormRC() {
    const password = document.getElementById("nueva_contrasena").value;
    const confirmPassword = document.getElementById("confirmar_contrasena").value;

    // Crear un objeto de audio para el sonido de error
    const audio = new Audio('../error/validation_error.mp3');

    // Validar que la contraseña cumpla con los requisitos
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    if (!passwordRegex.test(password)) {
        audio.play(); // Reproducir el sonido de error
        toastr.error("La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas, números y caracteres especiales.", "Error de Validación");
        return false;
    }

    // Validar que las contraseñas coincidan
    if (password !== confirmPassword) {
        audio.play(); // Reproducir el sonido de error
        toastr.error("Las contraseñas no coinciden", "Error de Validación");
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

function togglePasswordVisibility(inputId, toggleButton) {
    const input = document.getElementById(inputId);
    const icon = toggleButton.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Registar Login
function previewProfilePic(event) {
    const input = event.target;
    const preview = document.getElementById('profilePicPreview');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function (e) {
            preview.src = e.target.result;
        };

        reader.readAsDataURL(input.files[0]);
    }
}

function checkPasswordStrength() {
    const password = document.getElementById("inputPassword").value;
    const strengthIndicator = document.getElementById("passwordStrength");
    let strength = 0;

    // Verificar la longitud de la contraseña
    if (password.length >= 8) strength++;

    // Verificar si contiene letras mayúsculas
    if (/[A-Z]/.test(password)) strength++;

    // Verificar si contiene letras minúsculas
    if (/[a-z]/.test(password)) strength++;

    // Verificar si contiene números
    if (/\d/.test(password)) strength++;

    // Verificar si contiene caracteres especiales
    if (/[@$!%*?&]/.test(password)) strength++;

    // Mostrar la fuerza de la contraseña
    switch (strength) {
        case 0:
            strengthIndicator.textContent = "";
            strengthIndicator.style.color = "";
            break;
        case 1:
        case 2:
            strengthIndicator.textContent = "Contraseña débil";
            strengthIndicator.style.color = "red";
            break;
        case 3:
            strengthIndicator.textContent = "Contraseña moderada";
            strengthIndicator.style.color = "orange";
            break;
        case 4:
        case 5:
            strengthIndicator.textContent = "Contraseña fuerte";
            strengthIndicator.style.color = "green";
            break;
    }
}
function validateFormRL() {
    var usuario = document.getElementById("inputFirstName").value;
    var nombre = document.getElementById("inputLastName").value;
    var nacionalidad = document.getElementById("inputNACI").value; // V-, E-, G-, J-
    var dni = document.getElementById("inputDNI").value;
    var correo = document.getElementById("inputEmail").value;
    var clave = document.getElementById("inputPassword").value;
    var confirmar_clave = document.getElementById("inputPasswordConfirm").value;
    var pregunta = document.getElementById("inputPregunta1").value;
    var respuesta = document.getElementById("inputRespuesta").value;
    var pregunta2 = document.getElementById("inputPregunta2").value;
    var respuesta2 = document.getElementById("inputRespuesta2").value;

    const audio = new Audio('../error/validation_error.mp3');

    // Validar que todos los campos obligatorios estén llenos
    if (
        usuario === "" || 
        nombre === "" || 
        nacionalidad === "" || 
        dni === "" ||
        correo === "" || 
        clave === "" || 
        confirmar_clave === "" || 
        pregunta === "" || 
        respuesta === "" || 
        pregunta2 === "" || 
        respuesta2 === ""
    ) {
        audio.play();
        toastr.error("Todos los campos son obligatorios", "Error de Validación");
        return false;
    }

    // Validar que las preguntas no sean iguales
    if (pregunta === pregunta2 && pregunta !== "") {
        audio.play();
        toastr.error("Las preguntas de seguridad no pueden ser iguales.", "Error de Validación");
        return false;
    }

    // Validar que las respuestas no sean iguales
    if (respuesta.trim().toLowerCase() === respuesta2.trim().toLowerCase() && respuesta !== "") {
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

    // Validar nacionalidad/letra de cédula (solo V-, E-, G-, J-)
    if (!["V-", "E-", "G-", "J-"].includes(nacionalidad)) {
        audio.play();
        toastr.error("Seleccione una letra válida para la cédula (V, E, G o J)", "Error de Validación");
        return false;
    }

    // Validar número de cédula (6 a 9 dígitos, solo números)
    const dniRegex = /^\d{6,9}$/;
    if (!dniRegex.test(dni)) {
        audio.play();
        toastr.error("El número de cédula debe tener entre 6 y 9 dígitos y solo números.", "Error de Validación");
        return false;
    }

    // Validar si la cédula ya existe en la base de datos (AJAX síncrono)
    var existe = false;
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "../models/verificar_cedula.php", false); // false = síncrono
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("cedula=" + encodeURIComponent(dni));
    if (xhr.status === 200 && xhr.responseText.trim() === "existe") {
        audio.play();
        toastr.error("La cédula ya está registrada en el sistema.", "Error de Validación");
        return false;
    }

    // Validar usuario: mínimo 4 caracteres, al menos una mayúscula, una minúscula y un número, solo letras y números
    const usuarioRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z0-9]{4,}$/;
    if (!usuarioRegex.test(usuario)) {
        audio.play();
        toastr.error("El usuario debe tener mínimo 4 caracteres, incluir al menos una mayúscula, una minúscula y un número. No se permiten espacios ni caracteres especiales.", "Error de Validación");
        return false;
    }
    if (usuario.trim() !== usuario) {
        audio.play();
        toastr.error("El usuario no debe tener espacios al inicio o al final.", "Error de Validación");
        return false;
    }

    // Validar correo (acepta cualquier dominio válido, pero exige formato correcto)
    const emailRegex = /^[a-zA-Z0-9._%+-ñÑ]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailRegex.test(correo.toLowerCase())) {
        audio.play();
        toastr.error("El correo electrónico debe ser válido (ejemplo: usuario@dominio.com).", "Error de Validación");
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

    return true;
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
document.addEventListener('DOMContentLoaded', function() {
    const pregunta1 = document.getElementById('inputPregunta1');
    const pregunta2 = document.getElementById('inputPregunta2');

    if (!pregunta1 || !pregunta2) return;

    // Guarda las opciones originales
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
        if (destino.querySelector('option[value="' + valorSeleccionado + '"]')) {
            destino.value = valorSeleccionado;
        } else {
            destino.selectedIndex = 0;
        }
    }

    pregunta1.addEventListener('change', function() {
        actualizarSelect(pregunta1, pregunta2, opciones2);
    });

    pregunta2.addEventListener('change', function() {
        actualizarSelect(pregunta2, pregunta1, opciones1);
    });

    actualizarSelect(pregunta1, pregunta2, opciones2);
    actualizarSelect(pregunta2, pregunta1, opciones1);
});
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Preguntas de Seguridad
function validateFormPS() {
    const respuesta = document.forms["preguntaForm"]["respuesta"].value.trim();
    const respuesta2 = document.forms["preguntaForm"]["respuesta2"].value.trim();

    // Crear un objeto de audio para el sonido de error
    const audio = new Audio('../error/validation_error.mp3');

    // Validar que ambos campos no estén vacíos
    if (respuesta === "" || respuesta2 === "") {
        audio.play(); // Reproducir el sonido de error
        toastr.error("Todos los campos son obligatorios", "Error de Validación");
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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Nueva Clave
function validateFormNC() {
    const password = document.getElementById("inputPassword").value;
    const confirmPassword = document.getElementById("inputPasswordConfirm").value;

    // Crear un objeto de audio para el sonido de error
    const audio = new Audio('../error/validation_error.mp3');

    // Validar que los campos no estén vacíos
    if (password === "" || confirmPassword === "") {
        audio.play(); // Reproducir el sonido de error
        toastr.error("Todos los campos son obligatorios", "Error de Validación");
        return false;
    }

    // Validar que la contraseña cumpla con los requisitos
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    if (!passwordRegex.test(password)) {
        audio.play(); // Reproducir el sonido de error
        toastr.error("La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas, números y caracteres especiales.", "Error de Validación");
        return false;
    }

    // Validar que las contraseñas coincidan
    if (password !== confirmPassword) {
        audio.play(); // Reproducir el sonido de error
        toastr.error("Las contraseñas no coinciden", "Error de Validación");
        return false;
    }

    return true; // Permitir el envío del formulario si todo está correcto
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

function checkPasswordStrength() {
    const password = document.getElementById("inputPassword").value;
    const strengthText = document.getElementById("passwordStrength");
    const regexWeak = /(?=.{6,})/; // Al menos 6 caracteres
    const regexMedium = /(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.{8,})/; // Minúscula, mayúscula, número, 8 caracteres
    const regexStrong = /(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{10,}/; // Incluye carácter especial, 10 caracteres

    if (regexStrong.test(password)) {
        strengthText.textContent = "Fortaleza: Fuerte 💪";
        strengthText.style.color = "green";
    } else if (regexMedium.test(password)) {
        strengthText.textContent = "Fortaleza: Media ⚠️";
        strengthText.style.color = "orange";
    } else if (regexWeak.test(password)) {
        strengthText.textContent = "Fortaleza: Débil ❌";
        strengthText.style.color = "red";
    } else {
        strengthText.textContent = "Fortaleza: Muy débil ❌";
        strengthText.style.color = "darkred";
    }
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

function togglePasswordVisibility(inputId, button) {
    var input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
        button.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
        input.type = "password";
        button.innerHTML = '<i class="fas fa-eye"></i>';
    }
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ====== WOW LOGIN & CAPTCHA ======
let generatedWowCaptcha = "";

function drawWowCaptcha() {
    const canvas = document.getElementById('captchaCanvas');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Background
    ctx.fillStyle = '#f8fafc';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // Generate Random Text (6 chars: letters & numbers)
    // Removed confusing characters like 0, O, 1, I, l
    const chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789";
    generatedWowCaptcha = "";
    for(let i = 0; i < 6; i++) {
        generatedWowCaptcha += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    // Add noise (lines) - fewer and lighter lines
    for(let i = 0; i < 4; i++) {
        ctx.beginPath();
        ctx.moveTo(Math.random() * canvas.width, Math.random() * canvas.height);
        ctx.lineTo(Math.random() * canvas.width, Math.random() * canvas.height);
        ctx.strokeStyle = `rgba(${Math.random()*100+150},${Math.random()*100+150},${Math.random()*150}, 0.4)`;
        ctx.lineWidth = 1;
        ctx.stroke();
    }
    
    // Add noise (dots) - fewer and lighter dots
    for(let i = 0; i < 30; i++) {
        ctx.beginPath();
        ctx.arc(Math.random() * canvas.width, Math.random() * canvas.height, Math.random() * 2, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(${Math.random()*150+100},${Math.random()*150+100},${Math.random()*150+100}, 0.4)`;
        ctx.fill();
    }
    
    // Draw Text with clear visibility for larger 240x70 canvas
    ctx.font = 'bold 40px "Outfit", Arial, sans-serif';
    ctx.textBaseline = 'middle';
    
    for(let i = 0; i < generatedWowCaptcha.length; i++) {
        // Ancho 240 / 6 caracteres = ~40 de espacio por letra
        const x = 20 + (i * 36);
        const y = canvas.height / 2 + (Math.random() * 4 - 2); // Variación vertical mínima
        const angle = (Math.random() * 0.16) - 0.08; 
        
        ctx.save();
        ctx.translate(x, y);
        ctx.rotate(angle);
        
        // Colores oscuros muy legibles
        ctx.fillStyle = `rgb(${Math.random()*40},${Math.random()*40},${Math.random()*40})`;
        ctx.fillText(generatedWowCaptcha[i], 0, 0);
        ctx.restore();
    }
    
    checkWowLoginFields();
}

function checkWowLoginFields() {
    const usuario = document.getElementById('inputEmail');
    const clave = document.getElementById('inputPassword');
    const captchaInput = document.getElementById('captchaInput');
    const btnEntrar = document.getElementById('btnEntrar');
    
    if(!usuario || !clave || !captchaInput || !btnEntrar) return;

    if (usuario.value.trim() !== "" && clave.value.trim() !== "" && captchaInput.value.trim().length === 6) {
        btnEntrar.disabled = false;
    } else {
        btnEntrar.disabled = true;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar WOW Captcha si estamos en la vista
    const canvas = document.getElementById('captchaCanvas');
    if (canvas) {
        drawWowCaptcha();
        
        document.getElementById('inputEmail').addEventListener('input', checkWowLoginFields);
        document.getElementById('inputPassword').addEventListener('input', checkWowLoginFields);
        document.getElementById('captchaInput').addEventListener('input', checkWowLoginFields);
        
        const wowForm = document.forms['wowLoginForm'];
        if(wowForm) {
            wowForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const userCaptcha = document.getElementById('captchaInput').value.trim();
                const audio = new Audio('../error/validation_error.mp3');
                
                if(userCaptcha !== generatedWowCaptcha) {
                    audio.play().catch(err => console.log(err));
                    toastr.error("El código Captcha es incorrecto. Inténtalo de nuevo.", "Validación Fallida");
                    drawWowCaptcha();
                    document.getElementById('captchaInput').value = '';
                    document.getElementById('captchaInput').focus();
                    checkWowLoginFields();
                    return;
                }
                
                var usuario = wowForm["usuario"].value;
                fetch("../models/validar_bloqueo.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "usuario=" + encodeURIComponent(usuario)
                })
                .then(response => response.text())
                .then(text => {
                    if (text.trim() === "bloqueado") {
                        audio.play().catch(err => console.log(err));
                        toastr.error("Este usuario está bloqueado por el administrador.", "Acceso Denegado");
                        drawWowCaptcha();
                        document.getElementById('captchaInput').value = '';
                        checkWowLoginFields();
                        return;
                    }
                    wowForm.submit();
                })
                .catch(err => {
                    console.error("Fetch Error:", err);
                    wowForm.submit();
                });
            });
        }
    }
});

function toggleWowPassword() {
    const input = document.getElementById('inputPassword');
    const icon = document.getElementById('wowEyeIcon');
    if(!input || !icon) return;
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Configuración de Toastr y Bloqueos
document.querySelectorAll('.login-image, .wow-logo').forEach(img => {
    img.addEventListener('contextmenu', event => event.preventDefault());
});

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
// Conexión a Internet
// Mostrar notificaciones de conexión a internet

window.addEventListener('offline', function() {
    var audio = new Audio('../error/validation_error.mp3');
    audio.play();
    toastr.error("No hay conexión a internet. Algunas funciones pueden no estar disponibles.", "Sin conexión");
});

window.addEventListener('online', function() {
    toastr.success("Conexión a internet restablecida.", "Conectado");
});

 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Bloquear F12, Ctrl+Shift+I, Ctrl+U y clic derecho
    document.addEventListener('keydown', function(e) {
        if (
            e.key === 'F12' ||
            (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'i') ||
            (e.ctrlKey && e.key.toLowerCase() === 'u')
        ) {
            e.preventDefault();
            return false;
        }
    });
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });