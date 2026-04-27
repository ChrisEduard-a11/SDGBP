<?php
// chat_widget_guest.php
// Usado en el login para visitantes y usuarios no autenticados
?>
<link rel="stylesheet" href="../css/soporte_premium.css">
<style>
/* El botón flotante ha sido removido a petición del usuario para usar el botón integrado en el formulario */
#guest-soporte-trigger { display: none; }

/* Support window */
#soporte-window-guest {
    position: fixed; bottom: 100px; right: 25px; width: 380px; height: 600px; max-height: calc(100vh - 120px);
    z-index: 10000; display: flex; flex-direction: column; overflow: hidden;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    opacity: 0; transform: translateY(30px); pointer-events: none;
}
#soporte-window-guest.active { opacity: 1; transform: translateY(0); pointer-events: auto; }

/* Header */
.g-header {
    flex-shrink: 0;
    background: linear-gradient(135deg, #1e293b, #0f172a); color: white; padding: 20px;
    display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--brand-orange);
}
.g-header-title { font-weight: 800; font-size: 1.1rem; letter-spacing: -0.5px; }
.g-header-close { cursor: pointer; opacity: 0.7; transition: 0.2s; font-size: 1.2rem; }
.g-header-close:hover { opacity: 1; transform: rotate(90deg); }

/* Views */
#g-view-init { padding: 25px; text-align: center; display: none; flex: 1; min-height: 0; flex-direction: column; overflow-y: auto; background: white; }
#g-view-init.active { display: flex; }
#g-view-chat { display: none; flex: 1; min-height: 0; overflow: hidden; flex-direction: column; }
#g-view-chat.active { display: flex; }

.g-body { flex: 1; min-height: 0; padding: 25px; overflow-y: auto; display: flex; flex-direction: column; gap: 18px; background: transparent; }

.g-footer {
    flex-shrink: 0;
    padding: 15px 20px; border-top: 1px solid rgba(0,0,0,0.05); background: rgba(255,255,255,0.8);
    display: flex; gap: 10px; align-items: center; min-height: 65px;
}
.g-footer input, .g-footer textarea {
    flex: 1; min-width: 0;
    border: 1px solid #e2e8f0; background: white; border-radius: 20px;
    padding: 12px 18px; font-size: 0.95rem; outline: none; transition: 0.2s;
    box-sizing: border-box; font-family: inherit;
}
.g-footer input:focus, .g-footer textarea:focus { border-color: var(--brand-orange); box-shadow: 0 0 0 3px rgba(241,128,0,0.1); }
.g-footer button {
    border: none; background: var(--brand-orange); color: white; border-radius: 50%;
    width: 42px; height: 42px; min-width: 42px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: 0.2s;
}
.g-footer button:hover { background: #ea580c; transform: scale(1.05); }

.g-emoji-btn { background: none !important; color: #94a3b8 !important; font-size: 1.4rem !important; width: 35px !important; }
.g-emoji-btn:hover { color: var(--brand-orange) !important; transform: scale(1.1) !important; }

/* Emoji Picker */
.emoji-picker-g {
    position: absolute; bottom: 85px; left: 15px; right: 15px; background: white;
    border-radius: 18px; box-shadow: var(--premium-shadow);
    border: 1px solid rgba(0,0,0,0.05); padding: 12px; display: none;
    grid-template-columns: repeat(6, 1fr); gap: 6px; z-index: 100;
    max-height: 250px; overflow-y: auto;
}

/* Scrollbar */
.g-body::-webkit-scrollbar { width: 5px; }
.g-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

/* Mobile */
@media (max-width: 480px) {
    #soporte-window-guest {
        width: 100vw; right: 0; left: 0; bottom: 0;
        border-radius: 0; height: 100dvh; max-height: 100dvh;
    }
}
</style>

<!-- El botón gatillo flotante ha sido eliminado -->

<div id="soporte-window-guest" class="premium-glass">
    <div class="g-header">
        <div>
            <div class="g-header-title"><i class="fas fa-headset text-brand-400 me-2"></i>Centro de Soporte</div>
            <div id="gtk-status-bar" style="font-size: 0.75rem; color: #cbd5e1;">Conectando...</div>
            <div id="g-timer-wrapper" style="display:none; font-size:0.65rem; color:#f87171; font-weight:bold;"><i class="far fa-clock me-1"></i>Expira: <span id="g-timer-val">30:00</span></div>
        </div>
        <div class="g-header-close" onclick="tgGuestSoporteWindow()"><i class="fas fa-times"></i></div>
    </div>
    
    <!-- View: Iniciar Ticket Invitado -->
    <div id="g-view-init">
        <h4 style="font-weight: 800; color: #1e293b; margin-bottom: 5px; font-size: 1.1rem;">¡Hola!</h4>
        <p style="color: #64748b; font-size: 0.8rem; margin-bottom: 15px;">Por favor indícanos tus datos para iniciar la atención.</p>
        
        <input type="text" id="g-nombre-nuevo" placeholder="Nombre completo" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px; margin-bottom: 8px; font-size: 0.95rem; outline:none;" required>
        <input type="text" id="g-cedula-nuevo" placeholder="Nro de Cédula" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px; margin-bottom: 8px; font-size: 0.95rem; outline:none;" required>
        <input type="email" id="g-correo-nuevo" placeholder="Correo electrónico" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px; margin-bottom: 8px; font-size: 0.95rem; outline:none;" required>
        <textarea id="g-mensaje-nuevo" placeholder="¿En qué podemos ayudarte? Escribe tu mensaje aquí..." rows="4" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px; margin-bottom: 12px; font-size: 0.95rem; outline:none; resize:none; background: #fff;" required></textarea>
        
        <button onclick="gCrearTicket()" style="width: 100%; background: #f18000; color: white; border: none; padding: 12px; border-radius: 10px; font-weight: bold; box-shadow: 0 4px 10px rgba(241,128,0,0.2); transition: 0.3s; cursor:pointer;" onmouseover="this.style.background='#ea580c'" onmouseout="this.style.background='#f18000'">
            Iniciar Chat
        </button>
    </div>

    <!-- View: Chat Activo -->
    <div id="g-view-chat">
        <div class="g-body" id="g-chat-body">
            <div id="g-typing-indicator" class="typing-dots" style="display:none; align-self:flex-start;">
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
            </div>
        </div>
        
        <!-- Suggestion Chips -->
        <div id="g-suggestions-container" style="padding: 15px 20px 10px; background: rgba(255,255,255,0.8); border-top: 1px solid rgba(0,0,0,0.05); display: flex; flex-wrap: wrap; gap: 8px;">
            <div class="premium-chip" onclick="gSendSuggestion(this, 'SUG_ACCESS_FAIL')">🔒 No puedo acceder</div>
            <div class="premium-chip" onclick="gSendSuggestion(this, 'SUG_USER_LOST')">👤 Recuperar Usuario</div>
            <div class="premium-chip" onclick="gSendSuggestion(this, 'SUG_PWD_LOST')">🔑 Recuperar Clave</div>
            <div class="premium-chip" onclick="gSendSuggestion(this, 'SUG_GENERAL')">💬 Otras dudas</div>
        </div>

        <div class="g-footer">
            <input type="file" id="g-image-input" accept="image/jpeg,image/png,image/jpg" style="display:none;" onchange="handleImageSelectG()">
            <button class="g-emoji-btn" onclick="document.getElementById('g-image-input').click()" title="Adjuntar Imagen" style="background:none; color:#1e293b;"><i class="fas fa-image"></i></button>
            <button class="g-emoji-btn" onclick="toggleEmojiPickerG()"><i class="far fa-smile"></i></button>
            <div class="emoji-picker-g" id="emoji-picker-g">
                <span onclick="addEmojiG('😀')">😀</span><span onclick="addEmojiG('😃')">😃</span><span onclick="addEmojiG('😄')">😄</span><span onclick="addEmojiG('😁')">😁</span><span onclick="addEmojiG('😆')">😆</span><span onclick="addEmojiG('😅')">😅</span>
                <span onclick="addEmojiG('🤣')">🤣</span><span onclick="addEmojiG('😂')">😂</span><span onclick="addEmojiG('🙂')">🙂</span><span onclick="addEmojiG('🙃')">🙃</span><span onclick="addEmojiG('😉')">😉</span><span onclick="addEmojiG('😊')">😊</span>
                <span onclick="addEmojiG('😇')">😇</span><span onclick="addEmojiG('🥰')">🥰</span><span onclick="addEmojiG('😍')">😍</span><span onclick="addEmojiG('🤩')">🤩</span><span onclick="addEmojiG('😘')">😘</span><span onclick="addEmojiG('😗')">😗</span>
                <span onclick="addEmojiG('😋')">😋</span><span onclick="addEmojiG('😛')">😛</span><span onclick="addEmojiG('😜')">😜</span><span onclick="addEmojiG('🤪')">🤪</span><span onclick="addEmojiG('😝')">😝</span><span onclick="addEmojiG('🤑')">🤑</span>
                <span onclick="addEmojiG('🤗')">🤗</span><span onclick="addEmojiG('🤭')">🤭</span><span onclick="addEmojiG('🤫')">🤫</span><span onclick="addEmojiG('🤔')">🤔</span><span onclick="addEmojiG('🤐')">🤐</span><span onclick="addEmojiG('🤨')">🤨</span>
                <span onclick="addEmojiG('😐')">😐</span><span onclick="addEmojiG('😑')">😑</span><span onclick="addEmojiG('😶')">😶</span><span onclick="addEmojiG('😏')">😏</span><span onclick="addEmojiG('😒')">😒</span><span onclick="addEmojiG('🙄')">🙄</span>
                <span onclick="addEmojiG('😬')">😬</span><span onclick="addEmojiG('🤥')">🤥</span><span onclick="addEmojiG('😌')">😌</span><span onclick="addEmojiG('😔')">😔</span><span onclick="addEmojiG('😪')">😪</span><span onclick="addEmojiG('🤤')">🤤</span>
                <span onclick="addEmojiG('😴')">😴</span><span onclick="addEmojiG('😷')">😷</span><span onclick="addEmojiG('🤒')">🤒</span><span onclick="addEmojiG('🤕')">🤕</span><span onclick="addEmojiG('🤢')">🤢</span><span onclick="addEmojiG('🤮')">🤮</span>
                <span onclick="addEmojiG('🤧')">🤧</span><span onclick="addEmojiG('🥵')">🥵</span><span onclick="addEmojiG('🥶')">🥶</span><span onclick="addEmojiG('🥴')">🥴</span><span onclick="addEmojiG('😵')">😵</span><span onclick="addEmojiG('🤯')">🤯</span>
                <span onclick="addEmojiG('🥳')">🥳</span><span onclick="addEmojiG('😎')">😎</span><span onclick="addEmojiG('🤓')">🤓</span><span onclick="addEmojiG('🧐')">🧐</span><span onclick="addEmojiG('😕')">😕</span><span onclick="addEmojiG('😟')">😟</span>
                <span onclick="addEmojiG('🙁')">🙁</span><span onclick="addEmojiG('😮')">😮</span><span onclick="addEmojiG('😯')">😯</span><span onclick="addEmojiG('😲')">😲</span><span onclick="addEmojiG('😳')">😳</span><span onclick="addEmojiG('🥺')">🥺</span>
                <span onclick="addEmojiG('😦')">😦</span><span onclick="addEmojiG('😧')">😧</span><span onclick="addEmojiG('😨')">😨</span><span onclick="addEmojiG('😰')">😰</span><span onclick="addEmojiG('😥')">😥</span><span onclick="addEmojiG('😢')">😢</span>
                <span onclick="addEmojiG('😭')">😭</span><span onclick="addEmojiG('😱')">😱</span><span onclick="addEmojiG('😖')">😖</span><span onclick="addEmojiG('😣')">😣</span><span onclick="addEmojiG('😞')">😞</span><span onclick="addEmojiG('😓')">😓</span>
                <span onclick="addEmojiG('😩')">😩</span><span onclick="addEmojiG('😫')">😫</span><span onclick="addEmojiG('🥱')">🥱</span><span onclick="addEmojiG('😤')">😤</span><span onclick="addEmojiG('😡')">😡</span><span onclick="addEmojiG('😠')">😠</span>
                <span onclick="addEmojiG('👍')">👍</span><span onclick="addEmojiG('👎')">👎</span><span onclick="addEmojiG('👌')">👌</span><span onclick="addEmojiG('✌️')">✌️</span><span onclick="addEmojiG('🤞')">🤞</span><span onclick="addEmojiG('🤟')">🤟</span>
                <span onclick="addEmojiG('🤘')">🤘</span><span onclick="addEmojiG('🤙')">🤙</span><span onclick="addEmojiG('🖐')">🖐</span><span onclick="addEmojiG('✋')">✋</span><span onclick="addEmojiG('👋')">👋</span><span onclick="addEmojiG('👏')">👏</span>
                <span onclick="addEmojiG('🙌')">🙌</span><span onclick="addEmojiG('👐')">👐</span><span onclick="addEmojiG('🤲')">🤲</span><span onclick="addEmojiG('🙏')">🙏</span><span onclick="addEmojiG('🤝')">🤝</span><span onclick="addEmojiG('💪')">💪</span>
                <span onclick="addEmojiG('❤️')">❤️</span><span onclick="addEmojiG('🧡')">🧡</span><span onclick="addEmojiG('💛')">💛</span><span onclick="addEmojiG('💚')">💚</span><span onclick="addEmojiG('💙')">💙</span><span onclick="addEmojiG('💜')">💜</span>
                <span onclick="addEmojiG('🤎')">🤎</span><span onclick="addEmojiG('🖤')">🖤</span><span onclick="addEmojiG('🤍')">🤍</span><span onclick="addEmojiG('💔')">💔</span><span onclick="addEmojiG('💯')">💯</span><span onclick="addEmojiG('💢')">💢</span>
                <span onclick="addEmojiG('💬')">💬</span><span onclick="addEmojiG('🗯')">🗯</span><span onclick="addEmojiG('💭')">💭</span><span onclick="addEmojiG('💤')">💤</span><span onclick="addEmojiG('✅')">✅</span><span onclick="addEmojiG('❎')">❎</span>
                <span onclick="addEmojiG('⚠️')">⚠️</span><span onclick="addEmojiG('❌')">❌</span><span onclick="addEmojiG('❓')">❓</span><span onclick="addEmojiG('❕')">❕</span><span onclick="addEmojiG('💡')">💡</span><span onclick="addEmojiG('🔥')">🔥</span>
                <span onclick="addEmojiG('✨')">✨</span><span onclick="addEmojiG('🌟')">🌟</span><span onclick="addEmojiG('🎉')">🎉</span><span onclick="addEmojiG('✅')">✅</span><span onclick="addEmojiG('🇻🇪')">🇻🇪</span><span onclick="addEmojiG('💼')">💼</span>
                <span onclick="addEmojiG('📅')">📅</span><span onclick="addEmojiG('🔔')">🔔</span><span onclick="addEmojiG('📢')">📢</span><span onclick="addEmojiG('📊')">📊</span><span onclick="addEmojiG('📈')">📈</span><span onclick="addEmojiG('📉')">📉</span>
                <span onclick="addEmojiG('📋')">📋</span><span onclick="addEmojiG('📝')">📝</span><span onclick="addEmojiG('📁')">📁</span><span onclick="addEmojiG('📂')">📂</span><span onclick="addEmojiG('📄')">📄</span><span onclick="addEmojiG('📑')">📑</span>
            </div>
            <textarea id="g-input-msg" placeholder="Escribe un mensaje..." onkeypress="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); gEnviarMensaje(); }" oninput="gSendTyping(); this.style.height='auto'; this.style.height=Math.min(this.scrollHeight, 120)+'px';" rows="1" style="resize:none; overflow-y:auto; line-height:1.5; min-height:41px; max-height:120px;"></textarea>
            <button id="g-btn-send" onclick="gEnviarMensaje()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<script>
    function gSendSuggestion(btn, code) {
        if (!gCurrentTicketId) {
            console.error("No hay ticket activo para enviar sugerencia.");
            return;
        }

        // Evitar doble clic y mostrar carga
        btn.classList.add('loading');
        const originalText = btn.innerText;
        btn.innerText = '...';

        const fd = new FormData();
        fd.append('id_ticket', gCurrentTicketId);
        fd.append('mensaje', code);
        
        fetch('../acciones/soporte/enviar_mensaje.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                btn.classList.remove('loading');
                btn.innerText = originalText;
                if (data.success) {
                    gRefreshChat();
                } else {
                    console.error("Error al enviar sugerencia:", data.message);
                }
            }).catch(e => {
                btn.classList.remove('loading');
                btn.innerText = originalText;
                console.error(e);
            });
    }
    var gCurrentTicketId = null;
    var gLastMessageId = 0;
    var gPollInterval = null;
    var isFetchingG = false;
    var remainingSecsG = 0;
    var timerIntervalG = null;

    function formatTimeG(s) {
        if (s <= 0) return "00:00";
        const m = Math.floor(s / 60);
        const sec = s % 60;
        return (m < 10 ? '0' : '') + m + ":" + (sec < 10 ? '0' : '') + sec;
    }

    function runLocalTimerG() {
        if (remainingSecsG > 0) {
            remainingSecsG--;
            const el = document.getElementById('g-timer-val');
            if (el) el.innerText = formatTimeG(remainingSecsG);
            if (remainingSecsG <= 0) {
                const inp = document.getElementById('g-input-msg');
                if (inp) inp.disabled = true;
            }
        }
    }

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
                    if (timerIntervalG) clearInterval(timerIntervalG);
                    timerIntervalG = setInterval(runLocalTimerG, 1000);
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
        const msg = document.getElementById('g-mensaje-nuevo').value;
        
        if (!nom || !ced || !cor || !msg) { 
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
        fd.append('asunto', 'Soporte Invitado');
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
        if(!gCurrentTicketId || isFetchingG) return;
        isFetchingG = true;
        fetch(`../acciones/soporte/obtener_mensajes.php?id_ticket=${gCurrentTicketId}&last_id=${gLastMessageId}`)
            .then(r => r.json())
            .then(data => {
                isFetchingG = false;
                if (!data.success) {
                    console.error("Error en el chat:", data.message);
                    return;
                }

                // Typing indicator
                const typingEl = document.getElementById('g-typing-indicator');
                if (typingEl) typingEl.style.display = data.typing ? 'flex' : 'none';

                // Update Timer
                if (data.tiempo_restante !== undefined) {
                    remainingSecsG = data.tiempo_restante;
                    const tw = document.getElementById('g-timer-wrapper');
                    if (tw) tw.style.display = 'block';
                    const tv = document.getElementById('g-timer-val');
                    if (tv) tv.innerText = formatTimeG(remainingSecsG);
                }

                // Sincronizar estados de lectura (leído = ✓✓)
                if (data.id_leidos && data.id_leidos.length > 0) {
                    data.id_leidos.forEach(id => {
                        const tickSpan = document.getElementById(`tick-g-${id}`);
                        if (tickSpan && tickSpan.querySelector('.fa-check')) {
                            tickSpan.innerHTML = '<i class="fas fa-check-double"></i>';
                            tickSpan.style.color = '#34b7f1';
                            tickSpan.title = 'Leído';
                        }
                    });
                }

                if(data.mensajes.length > 0) {
                    let hasNewTheirs = false;
                    const body = document.getElementById('g-chat-body');
                    
                    data.mensajes.forEach(m => {
                        const c = m.es_mio ? 'msg-mine' : 'msg-theirs';
                        const sender = m.es_mio ? 'Yo' : m.emisor_nombre;
                        if (!m.es_mio && gLastMessageId > 0) hasNewTheirs = true;

                        const statusTicks = m.leido === 1 
                            ? `<span id="tick-g-${m.id_mensaje}" style="color: #34b7f1; margin-left: 3px;" title="Leído"><i class="fas fa-check-double"></i></span>`
                            : `<span id="tick-g-${m.id_mensaje}" style="margin-left: 3px;" title="Enviado"><i class="fas fa-check"></i></span>`;

                        let imgHtml = '';
                        if (m.archivo_adjunto) {
                            imgHtml = `<div class="mt-2"><img src="../${m.archivo_adjunto}" style="max-width:100%; max-height:250px; object-fit:cover; border-radius:10px; cursor:zoom-in;" onclick="tkOpenLightbox('../${m.archivo_adjunto}')"></div>`;
                        }

                        const msgDiv = document.createElement('div');
                        msgDiv.className = `chat-bubble ${m.es_mio ? 'bubble-mine' : 'bubble-theirs'}`;
                        msgDiv.setAttribute('data-id', m.id_mensaje);
                        msgDiv.innerHTML = `
                            <div class="bubble-sender">${sender}</div>
                            <div>${m.mensaje}</div>
                            ${imgHtml}
                            <div class="bubble-meta" style="justify-content: ${m.es_mio ? 'flex-end' : 'flex-start'}">
                                ${m.fecha} ${m.es_mio ? statusTicks : ''}
                            </div>
                        `;
                        body.appendChild(msgDiv);
                        gLastMessageId = m.id_mensaje;
                    });
                    body.scrollTop = body.scrollHeight;

                    if (hasNewTheirs) {
                        try {
                            const audio = new Audio('../assets/audio/notificacion.mp3');
                            audio.volume = 0.5;
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
                    
                    const sugContainer = document.getElementById('g-suggestions-container');
                    if (sugContainer) sugContainer.style.display = 'none';

                    let gbody = document.getElementById('g-chat-body');
                    if (!document.getElementById('g-rating-box')) {
                        if (!data.calificacion) {
                            gbody.innerHTML += `
                                <div id="g-rating-box" style="text-align:center; padding:15px; margin-top:10px; background:rgba(255,255,255,0.8); border-radius:15px; box-shadow:0 2px 10px rgba(0,0,0,0.05);">
                                    <p style="margin-bottom:10px; font-weight:bold; font-size:0.9rem; color:#1e293b;">Por favor, califica nuestra atención:</p>
                                    <div style="display:flex; justify-content:center; gap:15px;">
                                        <button onclick="gCalificarTicket('bien')" title="Bien" style="background:none; border:none; font-size:2rem; cursor:pointer; transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='none'">👍</button>
                                        <button onclick="gCalificarTicket('mal')" title="Mal" style="background:none; border:none; font-size:2rem; cursor:pointer; transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='none'">👎</button>
                                    </div>
                                </div>
                            `;
                            gbody.scrollTop = gbody.scrollHeight;
                        } else {
                            let cmoji = data.calificacion === 'bien' ? '👍 (Bien)' : '👎 (Mal)';
                            gbody.innerHTML += `
                                <div id="g-rating-box" style="text-align:center; padding:10px; margin-top:10px; color:#64748b; font-size:0.85rem;">
                                    Has calificado esta atención como: <strong>${cmoji}</strong>
                                </div>
                            `;
                            gbody.scrollTop = gbody.scrollHeight;
                        }
                    }
                }
            });
    }

    let gTypingTimeout = null;
    function gSendTyping() {
        if (!gCurrentTicketId) return;
        clearTimeout(gTypingTimeout);
        const fd = new FormData();
        fd.append('id_ticket', gCurrentTicketId);
        fd.append('rol', 'guest');
        fetch('../acciones/soporte/typing.php', { method: 'POST', body: fd });
        // Si deja de escribir por 4s lo reseteamos en DB (simplemente no actualizamos, y el backend lo detecta como inactivo)
    }

    function gEnviarMensaje() {
        const input = document.getElementById('g-input-msg');
        const imgInput = document.getElementById('g-image-input');
        const msg = input.value.trim();
        const hasFile = imgInput.files.length > 0;

        if (!msg && !hasFile || !gCurrentTicketId) return;

        const fd = new FormData();
        fd.append('id_ticket', gCurrentTicketId);
        fd.append('mensaje', msg);
        if (hasFile) fd.append('imagen', imgInput.files[0]);

        input.value = '';
        input.style.height = 'auto';
        imgInput.value = '';
        isFetchingG = false;
        
        fetch('../acciones/soporte/enviar_mensaje.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    gRefreshChat();
                } else { alert(data.message); }
            });
    }

    function handleImageSelectG() {
        const imgInput = document.getElementById('g-image-input');
        if (imgInput.files.length === 0) return;
        const fileName = imgInput.files[0].name;

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Adjuntar imagen',
                text: `Archivo: ${fileName}`,
                input: 'text',
                inputPlaceholder: 'Añadir un comentario (opcional)...',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#f18000',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (result.value) {
                        const inp = document.getElementById('g-input-msg');
                        inp.value = (inp.value.trim() + " " + result.value).trim();
                    }
                    gEnviarMensaje();
                } else {
                    imgInput.value = '';
                }
            });
        } else {
            if (confirm("¿Enviar imagen seleccionada?")) gEnviarMensaje();
        }
    }

    function gCalificarTicket(rating) {
        const fd = new FormData();
        fd.append('id_ticket', gCurrentTicketId);
        fd.append('calificacion', rating);

        fetch('../acciones/soporte/calificar_ticket.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    let box = document.getElementById('g-rating-box');
                    let cmoji = rating === 'bien' ? '👍 (Bien)' : '👎 (Mal)';
                    box.innerHTML = `<div style="text-align:center; padding:10px; color:#64748b; font-size:0.85rem;">Gracias. Has calificado esta atención como: <strong>${cmoji}</strong></div>`;
                }
            });
    }

    function toggleEmojiPickerG() {
        const p = document.getElementById('emoji-picker-g');
        p.style.display = p.style.display === 'grid' ? 'none' : 'grid';
    }

    function addEmojiG(e) {
        const inp = document.getElementById('g-input-msg');
        inp.value += e;
        inp.focus();
        document.getElementById('emoji-picker-g').style.display = 'none';
        gSendTyping();
    }

    function tkOpenLightbox(url) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                html: `
                    <div style="position:relative; display:inline-block; max-width:90vw;">
                        <button onclick="Swal.close()" style="position:absolute; top:-15px; right:-15px; background:#ef4444; color:white; border:none; border-radius:50%; width:35px; height:35px; font-size:1.2rem; cursor:pointer; display:flex; justify-content:center; align-items:center; box-shadow:0 4px 10px rgba(0,0,0,0.3); z-index:10; transition:0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='none'"><i class="fas fa-times"></i></button>
                        <img src="${url}" style="max-width:100%; max-height:85vh; object-fit:contain; border-radius:12px; display:block; margin:0 auto; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
                    </div>
                `,
                showConfirmButton: false,
                showCloseButton: false,
                width: 'auto',
                padding: 0,
                background: 'transparent',
                backdrop: 'rgba(15, 23, 42, 0.92)'
            });
        } else {
            window.open(url);
        }
    }

    // Cerrar moji (Click Afuera)
    document.addEventListener('click', function(e) {
        const ep = document.getElementById('emoji-picker-g');
        const isClickInsideEP = ep && ep.contains(e.target);
        const isToggleBtn = e.target.closest('.g-emoji-btn');
        if (!isClickInsideEP && !isToggleBtn && ep && ep.style.display === 'grid') {
            ep.style.display = 'none';
        }
    });
</script>
