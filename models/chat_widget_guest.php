<?php
// chat_widget_guest.php
// Usado en el login para visitantes y usuarios no autenticados
?>
<style>
/* El botón flotante ha sido removido a petición del usuario para usar el botón integrado en el formulario */
#guest-soporte-trigger {
    display: none;
}

/* Support window */
#soporte-window-guest {
    position: fixed; bottom: 100px; right: 25px; width: 360px; height: 500px; max-height: calc(100vh - 120px);
    background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(0,0,0,0.05); border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    z-index: 10000; display: flex; flex-direction: column; overflow: hidden;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    opacity: 0; transform: translateY(30px); pointer-events: none;
}
#soporte-window-guest.active { opacity: 1; transform: translateY(0); pointer-events: auto; }

/* Header */
.g-header {
    background: linear-gradient(135deg, #1e293b, #0f172a); color: white; padding: 18px 20px;
    display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f18000;
}
.g-header-title { font-weight: 800; font-size: 1.1rem; letter-spacing: -0.5px; }
.g-header-close { cursor: pointer; opacity: 0.7; transition: 0.2s; font-size: 1.2rem; }
.g-header-close:hover { opacity: 1; transform: rotate(90deg); }

/* Views */
#g-view-init { padding: 30px 20px; text-align: center; display: none; height: 100%; flex-direction: column; justify-content: flex-start; overflow-y: auto; }
#g-view-init.active { display: flex; }
#g-view-chat { display: none; height: 100%; flex-direction: column; }
#g-view-chat.active { display: flex; }

.g-body { flex: 1; padding: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; background: rgba(241, 245, 249, 0.5); }

.g-footer { padding: 12px 15px; border-top: 1px solid rgba(0,0,0,0.05); background: white; display: flex; gap: 10px; align-items: center; }
.g-footer input { flex: 1; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 20px; padding: 10px 15px; font-size: 0.95rem; outline: none; transition: 0.3s; }
.g-footer input:focus { border-color: #f18000; box-shadow: 0 0 0 3px rgba(241,128,0,0.1); }
.g-footer button { border: none; background: #f18000; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; }
.g-footer button:hover { background: #ea580c; transform: scale(1.05); }
.g-footer button:disabled { background: #cbd5e1; cursor: not-allowed; transform: none; }

/* Bubbles */
.msg-b { max-width: 85%; padding: 12px 16px; border-radius: 18px; font-size: 0.9rem; line-height: 1.4; animation: gFadeIn 0.3s ease; position: relative; }
@keyframes gFadeIn { from{opacity:0; transform:translateY(10px);} to{opacity:1; transform:translateY(0);} }
.msg-mine { background: linear-gradient(135deg, #f18000, #ea580c); color: white; align-self: flex-end; border-bottom-right-radius: 4px; box-shadow: 0 4px 6px rgba(241,128,0,0.15); }
.msg-theirs { background: #ffffff; color: #1e293b; align-self: flex-start; border-bottom-left-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.03); }
.msg-meta { font-size: 0.65rem; margin-top: 5px; opacity: 0.7; }
.msg-theirs .msg-meta { text-align: left; }
.msg-mine .msg-meta { text-align: right; color: rgba(255,255,255,0.8); }

.g-status-badge { display: inline-block; padding: 3px 8px; border-radius: 10px; font-size: 0.7rem; font-weight: bold; background: rgba(255,255,255,0.2); margin-top: 5px; }

/* Scrollbar */
.g-body::-webkit-scrollbar { width: 5px; }
.g-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
#g-view-init::-webkit-scrollbar { width: 5px; }
#g-view-init::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>

<!-- El botón gatillo flotante ha sido eliminado -->

<div id="soporte-window-guest">
    <div class="g-header">
        <div>
            <div class="g-header-title"><i class="fas fa-headset text-brand-400 me-2"></i>Centro de Soporte</div>
            <div id="gtk-status-bar" style="font-size: 0.75rem; color: #cbd5e1;">Conectando...</div>
        </div>
        <div class="g-header-close" onclick="tgGuestSoporteWindow()"><i class="fas fa-times"></i></div>
    </div>
    
    <!-- View: Iniciar Ticket Invitado -->
    <div id="g-view-init">
        <h4 style="font-weight: 800; color: #1e293b; margin-bottom: 5px;">¡Hola!</h4>
        <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">Por favor indícanos tus datos para iniciar la atención.</p>
        
        <input type="text" id="g-nombre-nuevo" placeholder="Nombre completo" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; margin-bottom: 15px; outline:none;" required>
        <input type="text" id="g-cedula-nuevo" placeholder="Nro de Cédula" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; margin-bottom: 15px; outline:none;" required>
        <input type="email" id="g-correo-nuevo" placeholder="Correo electrónico" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; margin-bottom: 15px; outline:none;" required>
        <input type="text" id="g-asunto-nuevo" placeholder="Asunto (Ej: Problema con cuenta)" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; margin-bottom: 15px; outline:none;" required>
        <textarea id="g-mensaje-nuevo" placeholder="Describe tu problema con detalle..." rows="3" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; margin-bottom: 20px; outline:none; resize:none;" required></textarea>
        
        <button onclick="gCrearTicket()" style="width: 100%; background: #f18000; color: white; border: none; padding: 12px; border-radius: 12px; font-weight: bold; box-shadow: 0 4px 10px rgba(241,128,0,0.3); transition: 0.3s; cursor:pointer;" onmouseover="this.style.background='#ea580c'" onmouseout="this.style.background='#f18000'">
            Iniciar Chat
        </button>
    </div>

    <!-- View: Chat Activo -->
    <div id="g-view-chat">
        <div class="g-body" id="g-chat-body">
            <!-- Mensajes dinámicos -->
        </div>
        <div class="g-footer">
            <input type="text" id="g-input-msg" placeholder="Escribe un mensaje..." onkeypress="if(event.key === 'Enter') gEnviarMensaje()">
            <button id="g-btn-send" onclick="gEnviarMensaje()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<script>
    let gCurrentTicketId = null;
    let gLastMessageId = 0;
    let gPollInterval = null;

    function tgGuestSoporteWindow() {
        const w = document.getElementById('soporte-window-guest');
        w.classList.toggle('active');
        if (w.classList.contains('active')) {
            gInicializar();
        } else {
            if (gPollInterval) clearInterval(gPollInterval);
        }
    }

    function gInicializar() {
        document.getElementById('gtk-status-bar').innerText = 'Verificando...';
        fetch('../acciones/soporte/verificar_usuario_ticket.php') // En el login.php la ruta cambia porque estamos en /vistas
            .then(r => r.json())
            .then(data => {
                if (data.has_ticket) {
                    gCurrentTicketId = data.id_ticket;
                    document.getElementById('g-view-init').classList.remove('active');
                    document.getElementById('g-view-chat').classList.add('active');
                    document.getElementById('gtk-status-bar').innerHTML = `Ticket: <b>${gCurrentTicketId}</b> <span class="g-status-badge">${data.estado}</span>`;
                    gRefreshChat();
                    gStartPolling();
                } else {
                    document.getElementById('gtk-status-bar').innerText = 'Listo.';
                    document.getElementById('g-view-init').classList.add('active');
                    document.getElementById('g-view-chat').classList.remove('active');
                }
            }).catch(e => console.error(e));
    }

    function gCrearTicket() {
        const nom = document.getElementById('g-nombre-nuevo').value;
        const ced = document.getElementById('g-cedula-nuevo').value;
        const cor = document.getElementById('g-correo-nuevo').value;
        const asm = document.getElementById('g-asunto-nuevo').value;
        const msg = document.getElementById('g-mensaje-nuevo').value;
        
        if (!nom || !ced || !cor || !asm || !msg) { 
            /* Se asume el uso de SweetAlert porque el usuario lo mencionó para otras cosas, pero usemos un alert básico si no está seguro, o Swal si login lo tiene */
            if (typeof Swal !== 'undefined') {
                Swal.fire({icon: 'warning', title: 'Campos Vacíos', text: 'Debes completar todos tus datos para iniciar el chat.'});
            } else {
                alert('Por favor llena todos los campos'); 
            }
            return; 
        }

        const fd = new FormData();
        fd.append('nombre', nom);
        fd.append('cedula', ced);
        fd.append('correo', cor);
        fd.append('asunto', asm);
        fd.append('mensaje', msg);

        fetch('../acciones/soporte/crear_ticket.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    gInicializar();
                } else { 
                    if (typeof Swal !== 'undefined') Swal.fire({icon: 'error', title: 'Error', text: data.message});
                    else alert(data.message); 
                }
            });
    }

    function gStartPolling() {
        if (gPollInterval) clearInterval(gPollInterval);
        gPollInterval = setInterval(gRefreshChat, 4000);
    }

    function gRefreshChat() {
        if(!gCurrentTicketId) return;
        fetch(`../acciones/soporte/obtener_mensajes.php?id_ticket=${gCurrentTicketId}&last_id=${gLastMessageId}`)
            .then(r => r.json())
            .then(data => {
                if(data.success && data.mensajes.length > 0) {
                    let hasNewTheirs = false;
                    const body = document.getElementById('g-chat-body');
                    data.mensajes.forEach(m => {
                        const c = m.es_mio ? 'msg-mine' : 'msg-theirs';
                        const sender = m.es_mio ? 'Yo' : m.emisor_nombre;
                        if (!m.es_mio && gLastMessageId > 0) hasNewTheirs = true;

                        body.innerHTML += `
                            <div class="msg-b ${c}">
                                <div style="font-weight:700; font-size:0.75rem; margin-bottom:3px; opacity:0.8;">${sender}</div>
                                <div>${m.mensaje}</div>
                                <div class="msg-meta">${m.fecha}</div>
                            </div>
                        `;
                        gLastMessageId = m.id_mensaje;
                    });
                    body.scrollTop = body.scrollHeight;

                    if (hasNewTheirs) {
                        try {
                            const audio = new Audio('data:audio/mp3;base64,SUQzBAAAAAAAI1RTU0UAAAAPAAADTGF2ZjU5LjE2LjEwMAAAAAAAAAAAAAAA//tQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAASW5mbwAAAA8AAAAJAAABXQAAxMTEw8PDx8fHx8vLy8vLy8vLy8/Pz8/P0NfX19fX2dnZ2d/f39/f4eHh4eHj4+Pj5eXl5ebm5ubm5+fn5+fn5+fo6Ojo6erq6urr6+vr6+/v7+/v7+/v7/Hx8fHy8vLy8vPz8/Pz9PT09PT19fX19fX29vb29/f39/f39/f4+Pj4+fn5+fn6+vr6+vv7+/v7/Pz8/Pz8/Pz9/f39/f7+/v7+/v7+/v7/Pz8/AAAAAAAAAAAAAAD/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//tQwAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqhgx+AABz/MAAAAE1h48qgVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVT/7UMAAAAwDSDAAAAABMAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//tQwAAADANIAAAAAEwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//tQwAAADANIAAAAAEwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//tQwAAADANIAAAAAEwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA');
                            audio.play().catch(()=>{});
                        } catch(e){}
                    }
                }
                if(data.estado === 'Resuelto') {
                    document.getElementById('g-input-msg').disabled = true;
                    document.getElementById('g-btn-send').disabled = true;
                    document.getElementById('g-input-msg').placeholder = 'Ticket cerrado.';
                    document.getElementById('gtk-status-bar').innerHTML = `Ticket: <b>${gCurrentTicketId}</b> <span class="g-status-badge" style="background:#ef4444;">Resuelto</span>`;
                    clearInterval(gPollInterval);
                }
            });
    }

    function gEnviarMensaje() {
        const input = document.getElementById('g-input-msg');
        const msg = input.value.trim();
        if(!msg || !gCurrentTicketId) return;

        input.value = '';
        
        const fd = new FormData();
        fd.append('id_ticket', gCurrentTicketId);
        fd.append('mensaje', msg);

        fetch('../acciones/soporte/enviar_mensaje.php', { method:('POST'), body: fd })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    gRefreshChat();
                } else { alert(data.message); }
            });
    }
</script>
