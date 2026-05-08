/* 
   ================================================================
   SDGBP - SISTEMA DE GESTIÓN DE BIENES Y PAGOS
   MÓDULO DE VALIDACIÓN EXTERNA (vali_login.js)
   Protección de Acceso, Registro y Recuperación
   ================================================================
*/

// 1. CONFIGURACIÓN GLOBAL DE TOASTR Y SONIDO
toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": true,
    "progressBar": true,
    "positionClass": "toast-bottom-left",
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

function playErrorSound() {
    const audio = new Audio('../error/validation_error.mp3');
    audio.play().catch(err => console.log("Audio play blocked by browser."));
}

// 2. VALIDACIONES DE RECUPERACIÓN Y SEGURIDAD
function validateFormSD() { // Solicitar Desbloqueo
    const usuario = document.getElementById("inputUsuario")?.value.trim();
    if (!usuario) {
        playErrorSound();
        toastr.error("El campo de usuario es obligatorio.", "Validación");
        return false;
    }
    return true;
}

function validateFormRCU() { // Recuperar Cuenta
    const usuario = document.getElementById("inputUsuario")?.value.trim();
    if (!usuario) {
        playErrorSound();
        toastr.error("El campo de usuario es obligatorio.", "Validación");
        return false;
    }
    return true;
}

function validateFormRecuU() { // Recuperar Usuario (Olvidó Username)
    const ci = document.getElementById("inputCI")?.value.trim();
    if (!ci) {
        playErrorSound();
        toastr.error("La Cédula de Identidad es obligatoria.", "Formato Invalido");
        return false;
    }
    return true;
}

function validateFormCodigo() { // Confirmar Código 2FA
    const codigo = document.getElementsByName("codigo")[0]?.value.trim();
    if (!codigo || codigo.length < 6) {
        playErrorSound();
        toastr.warning("Debe ingresar el código de 6 dígitos enviado a su correo.", "Código Incompleto");
        return false;
    }
    return true;
}

function validateFormVEU() { // Verificar Email (Pista)
    const correo = document.getElementById("inputEmail")?.value.trim();
    if (!correo) {
        playErrorSound();
        toastr.error("Debe escribir el correo de la pista para continuar.", "Campo Requerido");
        return false;
    }
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!re.test(correo)) {
        playErrorSound();
        toastr.error("El formato del correo electrónico es inválido.", "Formato Incorrecto");
        return false;
    }
    return true;
}

function validateFormPS() { // Preguntas de Seguridad
    const res1 = document.getElementById("inputRespuesta") || document.forms["preguntaForm"]?.["respuesta"];
    const res2 = document.getElementById("inputRespuesta2") || document.forms["preguntaForm"]?.["respuesta2"];
    
    if(!res1?.value.trim() || (res2 && !res2.value.trim())) {
        playErrorSound();
        toastr.error("Debe completar las respuestas de seguridad.", "Seguridad Requerida");
        return false;
    }
    return true;
}

function validateFormNC() { // Nueva Clave (Recuperación)
    const c1 = document.getElementById("inputPassword") || document.getElementById("nueva_contrasena");
    const c2 = document.getElementById("inputPasswordConfirm") || document.getElementById("confirmar_contrasena");
    
    if(!c1?.value || !c2?.value) {
        playErrorSound();
        toastr.error("Todos los campos de contraseña son obligatorios.", "Campos Vacíos");
        return false;
    }
    if(c1.value !== c2.value) {
        playErrorSound();
        toastr.error("Las contraseñas no coinciden.", "Mismatch");
        return false;
    }
    const regexClave = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,16}$/;
    if(!regexClave.test(c1.value)) {
        playErrorSound();
        toastr.warning("La contraseña debe tener entre 8 y 16 caracteres, e incluir al menos una mayúscula, una minúscula, un número y un carácter especial.", "Seguridad");
        return false;
    }
    return true;
}

function validateFormMC() { // Método de Recuperación
    const met = document.getElementsByName("metodo");
    let selected = false;
    for(let i=0; i<met.length; i++) if(met[i].checked) selected = true;
    
    if(!selected && !document.getElementById("metodo")?.value) {
        playErrorSound();
        toastr.warning("Seleccione un método para continuar.", "Opción Requerida");
        return false;
    }
    return true;
}


// 4. GUI / UX HELPERS
function togglePasswordVisibility(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function checkPasswordStrength() {
    const pass = document.getElementById("inputPassword")?.value || "";
    const indicator = document.getElementById("passwordStrength");
    if(!indicator) return;

    if(pass.length === 0) {
        indicator.innerHTML = "";
        return;
    }

    let s = 0;
    if(pass.length >= 8 && pass.length <= 16) s++;
    if(/[a-z]/.test(pass)) s++;
    if(/[A-Z]/.test(pass)) s++;
    if(/[0-9]/.test(pass)) s++;
    if(/[\W_]/.test(pass)) s++;

    let level = 0;
    let color = '';
    let text = '';

    if (s <= 2) {
        level = 1;
        color = '#ef4444'; // Red
        text = 'Débil';
    } else if (s === 3 || s === 4) {
        level = 2;
        color = '#f59e0b'; // Amber
        text = 'Media';
    } else {
        level = 3;
        color = '#10b981'; // Green
        text = 'Fuerte';
    }

    indicator.style.color = ""; // Limpiar color previo del small
    indicator.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; padding: 0 2px;">
            <span style="font-size: 0.70rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Seguridad de la clave</span>
            <span style="font-size: 0.70rem; font-weight: 800; color: ${color}; text-transform: uppercase; letter-spacing: 0.5px;">${text}</span>
        </div>
        <div style="display: flex; gap: 6px; height: 5px; width: 100%;">
            <div style="flex: 1; border-radius: 10px; transition: background-color 0.4s ease; background-color: ${level >= 1 ? color : '#e2e8f0'};"></div>
            <div style="flex: 1; border-radius: 10px; transition: background-color 0.4s ease; background-color: ${level >= 2 ? color : '#e2e8f0'};"></div>
            <div style="flex: 1; border-radius: 10px; transition: background-color 0.4s ease; background-color: ${level >= 3 ? color : '#e2e8f0'};"></div>
        </div>
    `;
}

function previewProfilePic(e) {
    const reader = new FileReader();
    reader.onload = () => document.getElementById('profilePicPreview').src = reader.result;
    reader.readAsDataURL(e.target.files[0]);
}

// 5. WOW LOGIN & CAPTCHA ENGINE
let generatedWowCaptcha = "";
function drawWowCaptcha() {
    const canvas = document.getElementById('captchaCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = '#f8fafc';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    const chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
    generatedWowCaptcha = "";
    for(let i = 0; i < 6; i++) generatedWowCaptcha += chars.charAt(Math.floor(Math.random() * chars.length));
    
    ctx.font = 'bold 38px "Inter", sans-serif';
    for(let i = 0; i < 6; i++) {
        ctx.save();
        ctx.translate(30 + i*35, 45);
        ctx.rotate((Math.random() - 0.5) * 0.2);
        ctx.fillStyle = "#1e293b";
        ctx.fillText(generatedWowCaptcha[i], 0, 0);
        ctx.restore();
    }
    checkWowLoginFields();
}

function checkWowLoginFields() {
    const u = document.getElementById('inputEmail')?.value.trim();
    const p = document.getElementById('inputPassword')?.value.trim();
    const c = document.getElementById('captchaInput')?.value.trim();
    const btn = document.getElementById('btnEntrar');
    if(btn) btn.disabled = !(u && p && c?.length === 6);
}

document.addEventListener('DOMContentLoaded', () => {
    if(document.getElementById('captchaCanvas')) {
        drawWowCaptcha();
        ['inputEmail', 'inputPassword', 'captchaInput'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', checkWowLoginFields);
        });
        
        document.getElementById('wowLoginForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            if(document.getElementById('captchaInput').value.toUpperCase() !== generatedWowCaptcha) {
                playErrorSound();
                toastr.error("Código Captcha incorrecto.", "Fallo de Seguridad");
                drawWowCaptcha();
                const ci = document.getElementById('captchaInput');
                ci.value = '';
                ci.focus();
                return;
            }
            this.submit();
        });
    }

    // Bloqueos de Seguridad
    document.addEventListener('keydown', e => {
        if(e.key === 'F12' || (e.ctrlKey && (e.key === 'u' || e.key === 'U'))) e.preventDefault();
    });
    document.addEventListener('contextmenu', e => e.preventDefault());
});