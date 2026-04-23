<?php
if (!isset($_SESSION['id'])) return;

$is_admin = ($_SESSION['tipo'] === 'admin');

if (!$is_admin) {
    // User Widget
?>
    <style>
    /* Support window hidden until triggered by sidebar */
    
    #soporte-window {
        position: fixed; bottom: 100px; right: 25px; width: 360px; height: 500px; max-height: calc(100vh - 120px);
        background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(0,0,0,0.05); border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        z-index: 10000; display: flex; flex-direction: column; overflow: hidden;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        opacity: 0; transform: translateY(30px); pointer-events: none;
    }
    #soporte-window.active { opacity: 1; transform: translateY(0); pointer-events: auto; }
    
    /* Header */
    .s-header {
        background: linear-gradient(135deg, #1e293b, #0f172a); color: white; padding: 18px 20px;
        display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f18000;
    }
    .s-header-title { font-weight: 800; font-size: 1.1rem; letter-spacing: -0.5px; }
    .s-header-close { cursor: pointer; opacity: 0.7; transition: 0.2s; font-size: 1.2rem; }
    .s-header-close:hover { opacity: 1; transform: rotate(90deg); }
    
    /* Views */
    #s-view-init { padding: 30px 20px; text-align: center; display: none; height: 100%; flex-direction: column; justify-content: center; }
    #s-view-init.active { display: flex; }
    #s-view-chat { display: none; height: 100%; flex-direction: column; }
    #s-view-chat.active { display: flex; }
    
    .s-body { flex: 1; padding: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; background: rgba(241, 245, 249, 0.5); }
    
    .s-footer { padding: 12px 15px; border-top: 1px solid rgba(0,0,0,0.05); background: white; display: flex; gap: 10px; align-items: center; }
    .s-footer input { flex: 1; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 20px; padding: 10px 15px; font-size: 0.95rem; outline: none; transition: 0.3s; }
    .s-footer input:focus { border-color: #f18000; box-shadow: 0 0 0 3px rgba(241,128,0,0.1); }
    .s-footer button { border: none; background: #f18000; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; }
    .s-footer button:hover { background: #ea580c; transform: scale(1.05); }
    .s-footer button:disabled { background: #cbd5e1; cursor: not-allowed; transform: none; }
    
    /* Bubbles */
    .msg-b { max-width: 85%; padding: 12px 16px; border-radius: 18px; font-size: 0.9rem; line-height: 1.4; animation: sFadeIn 0.3s ease; position: relative; }
    @keyframes sFadeIn { from{opacity:0; transform:translateY(10px);} to{opacity:1; transform:translateY(0);} }
    .msg-mine { background: linear-gradient(135deg, #f18000, #ea580c); color: white; align-self: flex-end; border-bottom-right-radius: 4px; box-shadow: 0 4px 6px rgba(241,128,0,0.15); }
    .msg-theirs { background: #ffffff; color: #1e293b; align-self: flex-start; border-bottom-left-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.03); }
    .msg-meta { font-size: 0.65rem; margin-top: 5px; opacity: 0.7; }
    .msg-theirs .msg-meta { text-align: left; }
    .msg-mine .msg-meta { text-align: right; color: rgba(255,255,255,0.8); }

    .s-status-badge { display: inline-block; padding: 3px 8px; border-radius: 10px; font-size: 0.7rem; font-weight: bold; background: rgba(255,255,255,0.2); margin-top: 5px; }
    
    /* Scrollbar */
    .s-body::-webkit-scrollbar { width: 5px; }
    .s-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    /* Typing indicator */
    .s-typing-bubble { display: none; align-items: center; gap: 4px; padding: 10px 14px; background: #ffffff; border-radius: 18px; border-bottom-left-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.06); width: fit-content; max-width: 65px; }
    .s-typing-bubble span { width: 7px; height: 7px; background: #94a3b8; border-radius: 50%; animation: sTypingDot 1.2s infinite ease-in-out; }
    .s-typing-bubble span:nth-child(2) { animation-delay: 0.2s; }
    .s-typing-bubble span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes sTypingDot { 0%, 80%, 100% { transform: translateY(0); opacity:0.5; } 40% { transform: translateY(-6px); opacity:1; } }
    </style>

    <!-- Triggers outside from Sidebar Toggle -->

    <div id="soporte-window">
        <div class="s-header">
            <div>
                <div class="s-header-title"><i class="fas fa-robot text-brand-400 me-2"></i>Soporte SDGBP</div>
                <div id="tk-status-bar" style="font-size: 0.75rem; color: #cbd5e1;">Conectando...</div>
            </div>
            <div class="s-header-close" onclick="toggleSoporteWindow()"><i class="fas fa-times"></i></div>
        </div>
        
        <!-- View: Iniciar Ticket -->
        <div id="s-view-init">
            <h4 style="font-weight: 800; color: #1e293b; margin-bottom: 10px;">¿Necesitas ayuda?</h4>
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 25px;">Abre un ticket para conversar con nuestro equipo de soporte técnico.</p>
            <input type="text" id="s-asunto-nuevo" placeholder="Asunto (Ej: Ayuda con mis pagos)" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; margin-bottom: 15px; outline:none;" required>
            <textarea id="s-mensaje-nuevo" placeholder="Describe tu problema con detalle..." rows="3" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; margin-bottom: 20px; outline:none; resize:none;" required></textarea>
            <button onclick="sCrearTicket()" style="width: 100%; background: #f18000; color: white; border: none; padding: 12px; border-radius: 12px; font-weight: bold; box-shadow: 0 4px 10px rgba(241,128,0,0.3); transition: 0.3s; cursor:pointer;" onmouseover="this.style.background='#ea580c'" onmouseout="this.style.background='#f18000'">
                Abrir Ticket de Soporte
            </button>
        </div>

        <!-- View: Chat Activo -->
        <div id="s-view-chat">
            <div class="s-body" id="s-chat-body">
                <!-- Mensajes dinámicos -->
                <div class="s-typing-bubble" id="s-typing-indicator"><span></span><span></span><span></span></div>
            </div>
            <div class="s-footer">
                <input type="text" id="s-input-msg" placeholder="Escribe un mensaje..." onkeypress="if(event.key === 'Enter') sEnviarMensaje()" oninput="sSendTyping()">
                <button id="s-btn-send" onclick="sEnviarMensaje()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>

    <script>
        let currentTicketId = null;
        let lastMessageId = 0;
        let pollInterval = null;

        function toggleSoporteWindow() {
            const w = document.getElementById('soporte-window');
            w.classList.toggle('active');
            if (w.classList.contains('active')) {
                sInicializar();
            } else {
                if (pollInterval) clearInterval(pollInterval);
            }
        }

        function sInicializar() {
            document.getElementById('tk-status-bar').innerText = 'Verificando sesión...';
            fetch('../acciones/soporte/verificar_usuario_ticket.php')
                .then(r => r.json())
                .then(data => {
                    if (data.has_ticket) {
                        currentTicketId = data.id_ticket;
                        document.getElementById('s-view-init').classList.remove('active');
                        document.getElementById('s-view-chat').classList.add('active');
                        document.getElementById('tk-status-bar').innerHTML = `Ticket: <b>${currentTicketId}</b> <span class="s-status-badge">${data.estado}</span>`;
                        sRefreshChat();
                        startPolling();
                    } else {
                        document.getElementById('tk-status-bar').innerText = 'Listo.';
                        document.getElementById('s-view-init').classList.add('active');
                        document.getElementById('s-view-chat').classList.remove('active');
                    }
                }).catch(e => console.error(e));
        }

        function sCrearTicket() {
            const asm = document.getElementById('s-asunto-nuevo').value;
            const msg = document.getElementById('s-mensaje-nuevo').value;
            if (!asm || !msg) { alert('Por favor llena los campos'); return; }

            const fd = new FormData();
            fd.append('asunto', asm);
            fd.append('mensaje', msg);

            fetch('../acciones/soporte/crear_ticket.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('s-asunto-nuevo').value = '';
                        document.getElementById('s-mensaje-nuevo').value = '';
                        sInicializar();
                    } else { alert(data.message); }
                });
        }

        function startPolling() {
            if (pollInterval) clearInterval(pollInterval);
            pollInterval = setInterval(sRefreshChat, 4000);
        }

        function sRefreshChat() {
            if(!currentTicketId) return;
            fetch(`../acciones/soporte/obtener_mensajes.php?id_ticket=${currentTicketId}&last_id=${lastMessageId}`)
                .then(r => r.json())
                .then(data => {
                    // Typing indicator
                    const typingEl = document.getElementById('s-typing-indicator');
                    if (typingEl) typingEl.style.display = data.typing ? 'flex' : 'none';

                    if(data.success && data.mensajes.length > 0) {
                        let hasNewTheirs = false;
                        const body = document.getElementById('s-chat-body');
                        data.mensajes.forEach(m => {
                            const c = m.es_mio ? 'msg-mine' : 'msg-theirs';
                            const sender = m.es_mio ? 'Yo' : m.emisor_nombre;
                            if (!m.es_mio && lastMessageId > 0) hasNewTheirs = true;

                            body.innerHTML += `
                                <div class="msg-b ${c}">
                                    <div style="font-weight:700; font-size:0.75rem; margin-bottom:3px; opacity:0.8;">${sender}</div>
                                    <div>${m.mensaje}</div>
                                    <div class="msg-meta">${m.fecha}</div>
                                </div>
                            `;
                            lastMessageId = m.id_mensaje;
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
                        document.getElementById('s-input-msg').disabled = true;
                        document.getElementById('s-btn-send').disabled = true;
                        document.getElementById('s-input-msg').placeholder = 'Ticket cerrado.';
                        document.getElementById('tk-status-bar').innerHTML = `Ticket: <b>${currentTicketId}</b> <span class="s-status-badge" style="background:#ef4444;">Resuelto</span>`;
                        clearInterval(pollInterval);
                    }
                });
        }

        let sTypingTimeout = null;
        function sSendTyping() {
            if (!currentTicketId) return;
            clearTimeout(sTypingTimeout);
            const fd = new FormData();
            fd.append('id_ticket', currentTicketId);
            fd.append('rol', 'guest'); // El usuario registrado es el 'guest' desde la perspectiva del admin
            fetch('../acciones/soporte/typing.php', { method: 'POST', body: fd });
        }

        function sEnviarMensaje() {
            const input = document.getElementById('s-input-msg');
            const msg = input.value.trim();
            if(!msg || !currentTicketId) return;

            input.value = '';
            
            const fd = new FormData();
            fd.append('id_ticket', currentTicketId);
            fd.append('mensaje', msg);

            fetch('../acciones/soporte/enviar_mensaje.php', { method:('POST'), body: fd })
                .then(r => r.json())
                .then(data => {
                    if(data.success) {
                        sRefreshChat();
                    } else { alert(data.message); }
                });
        }
    </script>
    <?php
}
?>
