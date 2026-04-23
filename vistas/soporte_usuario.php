<?php
require_once("../models/header.php");
include('../conexion.php');

$id_usuario = $_SESSION['id'];

// Obtener mis tickets
$sqlTickets = "SELECT id_ticket, asunto, estado, fecha_apertura, calificacion 
               FROM soporte_tickets 
               WHERE id_usuario = '$id_usuario' 
               ORDER BY estado ASC, fecha_apertura DESC";
$resTickets = mysqli_query($conexion, $sqlTickets);
$tickets = [];
while ($row = mysqli_fetch_assoc($resTickets)) {
    $tickets[] = $row;
}
?>

<style>
    /* Estilos Premium para Centro de Soporte Usuario */
    .tk-container {
        display: flex; height: calc(100vh - 120px); min-height: 500px;
        background: var(--glass-bg, #fff);
        border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.05); overflow: hidden;
    }
    
    .tk-list-panel {
        width: 350px; background: rgba(248, 250, 252, 0.8);
        border-right: 1px solid rgba(0,0,0,0.05);
        display: flex; flex-direction: column;
    }
    .tk-list-header {
        padding: 20px; background: #fff; border-bottom: 1px solid rgba(0,0,0,0.05);
        display: flex; justify-content: space-between; align-items: center;
    }
    .tk-list-body { flex: 1; overflow-y: auto; padding: 10px; }
    
    .tk-item {
        padding: 15px; border-radius: 12px; margin-bottom: 8px; cursor: pointer;
        transition: 0.2s; border: 1px solid transparent; background: #fff;
    }
    .tk-item:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-color: rgba(0,0,0,0.05); }
    .tk-item.active { background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-color: #f18000; border-left: 4px solid #f18000; box-shadow: 0 4px 15px rgba(241,128,0,0.1); }
    
    .tk-chat-panel { flex: 1; display: flex; flex-direction: column; background: #fff; }
    .tk-chat-header {
        padding: 15px 25px; border-bottom: 1px solid rgba(0,0,0,0.05); background: #f8fafc;
        display: flex; justify-content: space-between; align-items: center; height: 80px;
    }
    .tk-chat-body { flex: 1; padding: 25px; overflow-y: auto; background: rgba(241, 245, 249, 0.3); display: flex; flex-direction: column; gap: 15px; }
    .tk-chat-footer { padding: 20px; border-top: 1px solid rgba(0,0,0,0.05); background: #fff; display: flex; gap: 15px; align-items: center; }
    
    .tk-empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #94a3b8; }
    
    /* Burbujas del Chat */
    .c-bubble { max-width: 75%; padding: 12px 18px; border-radius: 18px; line-height: 1.4; position: relative; }
    .c-theirs { background: #ffffff; color: #1e293b; align-self: flex-start; border-bottom-left-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.03); }
    .c-mine { background: linear-gradient(135deg, #f18000, #ea580c); color: white; align-self: flex-end; border-bottom-right-radius: 4px; box-shadow: 0 4px 10px rgba(241,128,0,0.2); }
    
    .tk-chat-footer input { flex:1; padding: 15px 20px; border-radius: 30px; border: 1px solid #e2e8f0; background: #f8fafc; outline: none; transition: 0.3s; }
    .tk-chat-footer input:focus { border-color: #f18000; box-shadow: 0 0 0 3px rgba(241,128,0,0.1); }
    .tk-chat-footer button { width: 45px; height: 45px; border-radius: 50%; background: #f18000; color: white; border: none; font-size: 1.1rem; cursor: pointer; transition: 0.2s; display:flex; align-items:center; justify-content:center; }
    .tk-chat-footer button:hover { background: #ea580c; transform: scale(1.05); }
    .tk-chat-footer button:disabled { background: #cbd5e1; cursor:not-allowed; }
    
    .tk-emoji-btn { background: none !important; color: #94a3b8 !important; font-size: 1.4rem !important; width: 40px !important; }
    .tk-emoji-btn:hover { color: #f18000 !important; transform: scale(1.2) !important; }

    /* Emoji Picker Minimalista */
    .emoji-picker {
        position: absolute; bottom: calc(100% + 15px); right: 0; background: white;
        border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        border: 1px solid rgba(0,0,0,0.05); padding: 12px; display: none;
        grid-template-columns: repeat(6, 1fr); gap: 8px; z-index: 100;
        width: 250px; transform: translateX(0);
    }
    @media (max-width: 500px) {
        .emoji-picker { width: 90vw; max-width: 280px; right: -40px; grid-template-columns: repeat(5, 1fr); }
    }
    .emoji-picker span { font-size: 1.4rem; cursor: pointer; transition: 0.2s; padding: 5px; border-radius: 8px; text-align: center; }
    .emoji-picker span:hover { background: #f1f5f9; transform: scale(1.2); }

    .tk-delete-btn { color: #f87171; cursor: pointer; transition: 0.2s; padding: 5px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .tk-delete-btn:hover { background: rgba(248, 113, 113, 0.1); color: #ef4444; transform: scale(1.1); }
    
    .tk-list-delete { position: absolute; top: 10px; right: 10px; opacity: 0; transition: 0.2s; z-index: 5; }
    .tk-item:hover .tk-list-delete { opacity: 1; }

    /* Typing indicator */
    .tk-typing-bubble { display: none; align-items: center; gap: 4px; padding: 10px 14px; background: #ffffff; border-radius: 18px; border-bottom-left-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); width: fit-content; max-width: 65px; margin-bottom: 5px; }
    .tk-typing-bubble span { width: 7px; height: 7px; background: #94a3b8; border-radius: 50%; animation: tkTypingDot 1.2s infinite ease-in-out; }
    .tk-typing-bubble span:nth-child(2) { animation-delay: 0.2s; }
    .tk-typing-bubble span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes tkTypingDot { 0%, 80%, 100% { transform: translateY(0); opacity:0.5; } 40% { transform: translateY(-6px); opacity:1; } }

    /* Estilos para Calificación */
    .rating-box { text-align:center; padding:15px; margin-top:10px; background:rgba(255,255,255,0.8); border-radius:15px; box-shadow:0 2px 10px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.03); }

    /* Mobile handling (Simplified) */
    @media (max-width: 768px) {
        .tk-list-panel { width: 100%; border-right: none; }
        .tk-chat-panel { position: fixed; inset: 0; z-index: 1050; display: none; }
        .tk-chat-panel.active { display: flex; }
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4">
        <header class="page-header-standard mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-headset me-2"></i>Centro de Soporte</h1>
                <p class="text-muted">Conversa con nuestro equipo técnico para resolver tus dudas</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="nuevoTicketModal()">
                <i class="fas fa-plus-circle me-2"></i>Nuevo Ticket
            </button>
        </header>

        <div class="tk-container">
            <!-- Panel Izquierdo: Mis Tickets -->
            <div class="tk-list-panel">
                <div class="tk-list-header">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>Mis Tickets</h5>
                    <span class="badge bg-primary rounded-pill"><?php echo count($tickets); ?></span>
                </div>
                <div class="tk-list-body">
                    <?php if(count($tickets)>0): ?>
                        <?php foreach($tickets as $t): ?>
                            <div class="tk-item position-relative" onclick="loadTicket('<?php echo $t['id_ticket']; ?>', '<?php echo htmlspecialchars($t['asunto']); ?>', '<?php echo $t['estado']; ?>')" id="item-<?php echo $t['id_ticket']; ?>">
                                <div class="tk-list-delete" onclick="eliminarTicket(event, '<?php echo $t['id_ticket']; ?>')">
                                    <i class="fas fa-trash-alt tk-delete-btn" style="font-size:0.85rem;"></i>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1 pe-4">
                                    <span class="fw-bold small"><?php echo $t['id_ticket']; ?></span>
                                    <?php 
                                        $bColor = $t['estado']=='Abierto' ? 'bg-success' : ($t['estado']=='En Proceso' ? 'bg-warning text-dark' : 'bg-secondary');
                                    ?>
                                    <span class="badge <?php echo $bColor; ?>" style="font-size:0.6rem;"><?php echo $t['estado']; ?></span>
                                </div>
                                <div class="fw-bold text-truncate" style="font-size:0.9rem; color:#f18000;"><?php echo htmlspecialchars($t['asunto']); ?></div>
                                <div class="text-muted small mt-1" style="font-size:0.7rem;">
                                    <i class="far fa-clock me-1"></i> <?php echo date('d/m/y H:i', strtotime($t['fecha_apertura'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center p-4 text-muted small">No has abierto ningún ticket aún.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Panel Derecho: Chat -->
            <div class="tk-chat-panel">
                <div id="tk-empty-view" class="tk-empty-state">
                    <i class="fas fa-comments fa-4x mb-3 text-muted" style="opacity:0.2;"></i>
                    <h4 class="fw-bold">Bandeja de Entrada</h4>
                    <p>Selecciona un ticket para ver la conversación.</p>
                </div>

                <div id="tk-chat-view" style="display:none; height:100%; width:100%; flex:1; flex-direction:column;">
                    <div class="tk-chat-header">
                        <div class="d-flex align-items-center gap-3">
                            <button class="btn btn-sm btn-outline-secondary rounded-circle d-md-none" onclick="closeChatMobile()"><i class="fas fa-arrow-left"></i></button>
                            <div>
                                <h5 class="mb-0 fw-bold" id="tk-asunto-label">Asunto</h5>
                                <div class="small text-muted d-flex align-items-center gap-2">
                                    <span id="tk-id-label">TICK-XXXX</span>
                                    <span id="tk-status-badge" class="badge rounded-pill">Abierto</span>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-link link-danger p-0" onclick="eliminarTicket(event, cTickId)" title="Eliminar Ticket">
                            <i class="fas fa-trash-alt fs-5"></i>
                        </button>
                    </div>
                    
                    <div class="tk-chat-body" id="tk-chat-msgs">
                        <div class="tk-typing-bubble" id="tk-typing-indicator"><span></span><span></span><span></span></div>
                    </div>
                    
                    <div class="tk-chat-footer" id="tk-footer" style="position:relative;">
                        <button class="tk-emoji-btn" onclick="toggleEmojiPicker()"><i class="far fa-smile"></i></button>
                        <div class="emoji-picker" id="emoji-picker">
                            <span onclick="addEmoji('😀')">😀</span><span onclick="addEmoji('😃')">😃</span><span onclick="addEmoji('😄')">😄</span><span onclick="addEmoji('😁')">😁</span><span onclick="addEmoji('😆')">😆</span><span onclick="addEmoji('😅')">😅</span>
                            <span onclick="addEmoji('😂')">😂</span><span onclick="addEmoji('😉')">😉</span><span onclick="addEmoji('😊')">😊</span><span onclick="addEmoji('😇')">😇</span><span onclick="addEmoji('🥰')">🥰</span><span onclick="addEmoji('😍')">😍</span>
                            <span onclick="addEmoji('🤩')">🤩</span><span onclick="addEmoji('😘')">😘</span><span onclick="addEmoji('😜')">😜</span><span onclick="addEmoji('🤑')">🤑</span><span onclick="addEmoji('🤔')">🤔</span><span onclick="addEmoji('🤫')">🤫</span>
                            <span onclick="addEmoji('🧐')">🧐</span><span onclick="addEmoji('😏')">😏</span><span onclick="addEmoji('🥳')">🥳</span><span onclick="addEmoji('😎')">😎</span><span onclick="addEmoji('🥺')">🥺</span><span onclick="addEmoji('😭')">😭</span>
                            <span onclick="addEmoji('👍')">👍</span><span onclick="addEmoji('👎')">👎</span><span onclick="addEmoji('👏')">👏</span><span onclick="addEmoji('🙌')">🙌</span><span onclick="addEmoji('🔥')">🔥</span><span onclick="addEmoji('✨')">✨</span>
                        </div>
                        <input type="text" id="tk-chat-input" placeholder="Escribe tu mensaje..." onkeypress="if(event.key==='Enter') enviarMensaje()" oninput="tkSendTyping()">
                        <button id="tk-chat-send" onclick="enviarMensaje()"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    let cTickId = null;
    let cLastMsgId = 0;
    let pollInterval = null;
    let isFetching = false;

    function loadTicket(idTicket, asunto, estado) {
        cTickId = idTicket;
        cLastMsgId = 0;
        
        // UI
        document.querySelectorAll('.tk-item').forEach(el => el.classList.remove('active'));
        document.getElementById(`item-${idTicket}`).classList.add('active');
        
        document.getElementById('tk-empty-view').style.display = 'none';
        document.getElementById('tk-chat-view').style.display = 'flex';
        if(window.innerWidth <= 768) document.querySelector('.tk-chat-panel').classList.add('active');

        document.getElementById('tk-asunto-label').innerText = asunto;
        document.getElementById('tk-id-label').innerText = idTicket;
        
        const badge = document.getElementById('tk-status-badge');
        badge.innerText = estado;
        badge.className = 'badge rounded-pill ' + (estado==='Abierto' ? 'bg-success' : (estado==='En Proceso' ? 'bg-warning text-dark' : 'bg-secondary'));

        document.getElementById('tk-chat-msgs').innerHTML = '<div class="tk-typing-bubble" id="tk-typing-indicator"><span></span><span></span><span></span></div>';

        if(estado === 'Resuelto') {
            document.getElementById('tk-footer').style.display = 'none';
        } else {
            document.getElementById('tk-footer').style.display = 'flex';
        }

        fetchMsgs();
        if(pollInterval) clearInterval(pollInterval);
        if(estado !== 'Resuelto') pollInterval = setInterval(fetchMsgs, 4000);
    }

    function closeChatMobile() {
        document.querySelector('.tk-chat-panel').classList.remove('active');
        if(pollInterval) clearInterval(pollInterval);
    }

    function fetchMsgs() {
        if(!cTickId || isFetching) return;
        isFetching = true;
        fetch(`../acciones/soporte/obtener_mensajes.php?id_ticket=${cTickId}&last_id=${cLastMsgId}`)
            .then(r=>r.json())
            .then(data => {
                isFetching = false;
                const typingEl = document.getElementById('tk-typing-indicator');
                if (typingEl) typingEl.style.display = data.typing ? 'flex' : 'none';

                if(data.success && data.mensajes.length > 0) {
                    const b = document.getElementById('tk-chat-msgs');
                    data.mensajes.forEach(m => {
                        const c = m.es_mio ? 'c-mine' : 'c-theirs';
                        const sender = m.es_mio ? 'Tú' : m.emisor_nombre;

                        const msgDiv = document.createElement('div');
                        msgDiv.className = `c-bubble ${c}`;
                        msgDiv.innerHTML = `
                            <div style="font-weight:700; font-size:0.75rem; margin-bottom:3px; opacity:0.8;">${sender}</div>
                            <div style="font-size:0.95rem;">${m.mensaje}</div>
                            <div style="font-size:0.65rem; opacity:0.7; margin-top:5px; text-align:${m.es_mio?'right':'left'}">${m.fecha}</div>
                        `;
                        b.appendChild(msgDiv);
                        cLastMsgId = m.id_mensaje;
                    });
                    b.scrollTop = b.scrollHeight;
                }

                // Si está resuelto y no hay calificación, mostrar box
                if(data.estado === 'Resuelto' && !document.getElementById('u-rating-box')) {
                    const b = document.getElementById('tk-chat-msgs');
                    if (!data.calificacion) {
                        const rBox = document.createElement('div');
                        rBox.id = 'u-rating-box';
                        rBox.className = 'rating-box';
                        rBox.innerHTML = `
                            <p style="margin-bottom:10px; font-weight:bold; font-size:0.9rem; color:#1e293b;">¿Cómo calificarías nuestra atención?</p>
                            <div style="display:flex; justify-content:center; gap:15px;">
                                <button onclick="calificarTicket('bien')" title="Bien" style="background:none; border:none; font-size:2rem; cursor:pointer; transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='none'">👍</button>
                                <button onclick="calificarTicket('mal')" title="Mal" style="background:none; border:none; font-size:2rem; cursor:pointer; transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='none'">👎</button>
                            </div>
                        `;
                        b.appendChild(rBox);
                    } else {
                        const rBox = document.createElement('div');
                        rBox.id = 'u-rating-box';
                        rBox.className = 'text-center p-3 text-muted';
                        rBox.style.fontSize = '0.85rem';
                        let cmoji = data.calificacion === 'bien' ? '👍 (Bien)' : '👎 (Mal)';
                        rBox.innerHTML = `Calificaste esta atención como: <strong>${cmoji}</strong>`;
                        b.appendChild(rBox);
                    }
                    b.scrollTop = b.scrollHeight;
                }
            });
    }

    let tkTypingTimeout = null;
    function tkSendTyping() {
        if (!cTickId) return;
        clearTimeout(tkTypingTimeout);
        const fd = new FormData();
        fd.append('id_ticket', cTickId);
        fd.append('rol', 'guest'); // El usuario para el sistema es el 'guest' de cara a las tablas de typing
        fetch('../acciones/soporte/typing.php', { method: 'POST', body: fd });
    }

    function enviarMensaje() {
        const inp = document.getElementById('tk-chat-input');
        const txt = inp.value.trim();
        if(!txt || !cTickId) return;

        inp.value = '';
        const fd = new FormData();
        fd.append('id_ticket', cTickId);
        fd.append('mensaje', txt);

        fetch('../acciones/soporte/enviar_mensaje.php', { method:'POST', body:fd })
            .then(r=>r.json())
            .then(data=>{
                if(data.success) fetchMsgs();
                else Swal.fire('Error', data.message, 'error');
            });
    }

    function calificarTicket(rating) {
        const fd = new FormData();
        fd.append('id_ticket', cTickId);
        fd.append('calificacion', rating);

        fetch('../acciones/soporte/calificar_ticket.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    let box = document.getElementById('u-rating-box');
                    let cmoji = rating === 'bien' ? '👍 (Bien)' : '👎 (Mal)';
                    box.innerHTML = `<div style="text-align:center; padding:10px; color:#64748b; font-size:0.85rem;">Gracias. Has calificado esta atención como: <strong>${cmoji}</strong></div>`;
                }
            });
    }

    function toggleEmojiPicker() {
        const p = document.getElementById('emoji-picker');
        p.style.display = p.style.display === 'grid' ? 'none' : 'grid';
    }

    function addEmoji(e) {
        const inp = document.getElementById('tk-chat-input');
        inp.value += e;
        inp.focus();
        document.getElementById('emoji-picker').style.display = 'none';
        tkSendTyping();
    }

    function eliminarTicket(e, id) {
        if(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        if(!id) return;

        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción eliminará el ticket y todos sus mensajes permanentemente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('id_ticket', id);

                fetch('../acciones/soporte/eliminar_ticket.php', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Eliminado', data.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
            }
        });
    }

    function nuevoTicketModal() {
        Swal.fire({
            title: 'Nuevo Ticket de Soporte',
            html: `
                <input id="swal-asunto" class="swal2-input" placeholder="Asunto" style="width: 80%;">
                <textarea id="swal-mensaje" class="swal2-textarea" placeholder="Describe tu consulta..." style="width: 80%; height: 100px;"></textarea>
            `,
            showCancelButton: true,
            confirmButtonText: 'Abrir Ticket',
            confirmButtonColor: '#f18000',
            preConfirm: () => {
                const asunto = Swal.getPopup().querySelector('#swal-asunto').value;
                const mensaje = Swal.getPopup().querySelector('#swal-mensaje').value;
                if (!asunto || !mensaje) {
                    Swal.showValidationMessage('Por favor completa todos los campos');
                }
                return { asunto, mensaje };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('asunto', result.value.asunto);
                fd.append('mensaje', result.value.mensaje);

                fetch('../acciones/soporte/crear_ticket.php', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('¡Éxito!', 'Ticket creado correctamente.', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
            }
        });
    }
</script>

<?php require_once("../models/footer.php"); ?>
