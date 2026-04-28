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

    <link rel="stylesheet" href="../css/soporte_premium.css">
<style>
    /* Specific overrides for User Support Center */
    #layoutSidenav_content { background-color: #f1f5f9; }
    
    .tk-container {
        display: flex; height: calc(100vh - 140px); min-height: 550px;
        margin-bottom: 20px;
    }

    .tk-item {
        padding: 16px; border-radius: 18px; margin-bottom: 10px; cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid transparent; background: #fff;
    }
    .tk-item:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.04); border-color: rgba(241,128,0,0.1); }
    .tk-item.active { background: white; border-color: var(--brand-orange); border-left-width: 5px; box-shadow: 0 8px 25px rgba(241,128,0,0.12); }
    
    .tk-list-panel { width: 320px; flex-shrink: 0; border-right: 1px solid rgba(0,0,0,0.05); display: flex; flex-direction: column; background: rgba(255,255,255,0.4); }
    .tk-list-header { 
        padding: 18px 20px; border-bottom: 1px solid rgba(0,0,0,0.05); background: rgba(255,255,255,0.6);
        display: flex; justify-content: space-between; align-items: center; min-height: 85px;
    }
    .tk-chat-panel { flex: 1; display: flex; flex-direction: column; background: transparent; min-width: 0; }
    
    .tk-chat-header {
        padding: 18px 28px; border-bottom: 1px solid rgba(0,0,0,0.05); background: rgba(255,255,255,0.6);
        display: flex; justify-content: space-between; align-items: center; min-height: 85px;
    }
    .tk-chat-body { flex: 1; padding: 25px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; }
    .tk-chat-footer { padding: 15px 20px; border-top: 1px solid rgba(0,0,0,0.05); background: rgba(255,255,255,0.6); display: flex; gap: 10px; align-items: center; }
    
    .tk-empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #94a3b8; }

    .rating-box { text-align:center; padding:20px; margin-top:15px; background:white; border-radius:18px; box-shadow: var(--premium-shadow); border: 1px solid rgba(0,0,0,0.03); }

    /* Dark Mode Support */
    [data-theme="dark"] .tk-container { background: rgba(15, 23, 42, 0.7); border-color: rgba(255, 255, 255, 0.1); }
    [data-theme="dark"] .tk-list-panel { background: rgba(30, 41, 59, 0.4); border-color: rgba(255, 255, 255, 0.05); }
    [data-theme="dark"] .tk-item { background: rgba(15, 23, 42, 0.5); border-color: rgba(255, 255, 255, 0.05); color: #f8fafc; }
    [data-theme="dark"] .tk-item.active { background: rgba(30, 41, 59, 0.8); border-color: var(--brand-orange); }
    [data-theme="dark"] .tk-chat-header, [data-theme="dark"] .tk-chat-footer { background: rgba(30, 41, 59, 0.6); border-color: rgba(255, 255, 255, 0.05); color: #fff; }
    [data-theme="dark"] .tk-chat-footer input { background: rgba(15, 23, 42, 0.5); border-color: rgba(255, 255, 255, 0.1); color: #fff; }
    [data-theme="dark"] .bubble-theirs { background: #1e293b; color: #f8fafc; border-color: #334155; }

    @media (max-width: 768px) {
        .tk-container { height: calc(100vh - 120px); min-height: 450px; border-radius: 0; margin: -1.5rem; width: calc(100% + 3rem); border: transparent; }
        .tk-list-panel { width: 100%; border-right: none; height: 100%; }
        .tk-chat-panel { position: fixed; inset: 0; z-index: 2000; display: none; background: #f8fafc; height: 100vh; width: 100vw; }
        [data-theme="dark"] .tk-chat-panel { background: #0f172a; }
        .tk-chat-panel.active { display: flex; }
        .tk-chat-header { min-height: 70px; padding: 12px 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .tk-chat-footer { padding: 10px 12px; }
        .tk-chip { padding: 8px 16px; font-size: 0.8rem; }
        #tk-suggestions-container { padding: 12px; gap: 10px; background: rgba(255, 255, 255, 0.95); }
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

        <div class="tk-container premium-glass">
            <!-- Panel Izquierdo: Mis Tickets -->
            <div class="tk-list-panel">
                <div class="tk-list-header">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>Mis Tickets</h5>
                    <?php $count = count($tickets); ?>
                    <span class="badge rounded-pill" style="font-size:0.75rem; background: #e2e8f0; color: #475569;">
                        <?php echo $count; ?> Ticket<?php echo $count != 1 ? 's' : ''; ?>
                    </span>
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
                                <div id="tk-timer-wrapper" class="mt-1" style="display:none; font-size:0.7rem;">
                                    <span class="badge bg-light text-dark border p-1 px-2"><i class="far fa-clock me-1 text-primary"></i>Cierra en: <span id="tk-timer-val" class="fw-bold text-danger">30:00</span></span>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-link link-danger p-0" onclick="eliminarTicket(event, cTickId)" title="Eliminar Ticket">
                            <i class="fas fa-trash-alt fs-5"></i>
                        </button>
                    </div>
                    
                    <div class="tk-chat-body" id="tk-chat-msgs">
                        <!-- Mensajes aquí -->
                    </div>
                    <div id="tk-typing-indicator" class="typing-dots mx-3 my-2" style="display:none; align-self:flex-start;">
                        <span class="typing-dot"></span>
                        <span class="typing-dot"></span>
                        <span class="typing-dot"></span>
                    </div>
                    
                    <!-- Suggestion Chips for Registered Users -->
                    <div id="tk-suggestions-container" class="px-3 py-3 border-top d-none" style="display: flex; flex-wrap: wrap; gap: 8px; z-index: 5; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px);">
                        <style>
                            .tk-chip {
                                flex: 1 1 calc(50% - 8px);
                                display: flex; align-items: center; justify-content: center;
                                text-align: center; padding: 6px 10px; background: #fff; color: #1e293b;
                                border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.75rem; font-weight: 600;
                                cursor: pointer; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                                box-shadow: 0 2px 4px rgba(0,0,0,0.03); min-height: 38px; line-height: 1.1;
                            }
                            .tk-chip:hover { background: #f18000; color: white; border-color: #f18000; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(241,128,0,0.2); }
                            .tk-chip.loading { opacity: 0.6; pointer-events: none; background: #f1f5f9; }
                            
                            @media (max-width: 576px) {
                                .tk-chip { flex: 1 1 100%; }
                            }

                            [data-theme="dark"] .tk-chip {
                                background: rgba(30, 41, 59, 0.8);
                                color: #f8fafc;
                                border-color: rgba(255, 255, 255, 0.1);
                            }
                            [data-theme="dark"] .tk-chip:hover {
                                background: #f18000;
                            }
                        </style>
                        <?php 
                        $tipo = $_SESSION['tipo'] ?? '';
                        if ($tipo === 'upu'): ?>
                            <div class="tk-chip" onclick="tkSendSuggestion(this, 'SUG_UPU_INGRESO')">¿Cómo reporto Ingresos?</div>
                            <div class="tk-chip" onclick="tkSendSuggestion(this, 'SUG_UPU_EGRESO')">¿Cómo reporto Egresos?</div>
                            <div class="tk-chip" onclick="tkSendSuggestion(this, 'SUG_UPU_REPORTE')">¿Cómo descargo mi reporte de pagos?</div>
                            <div class="tk-chip" onclick="tkSendSuggestion(this, 'SUG_UPU_CIERRE_FAIL')">¿Por qué mi mes sigue pendiente?</div>
                            <div class="tk-chip" onclick="tkSendSuggestion(this, 'SUG_UPU_PAGO_BLOC')">¿Por qué no puedo cargar un pago?</div>
                        <?php elseif ($tipo === 'cont'): ?>
                            <div class="tk-chip" onclick="tkSendSuggestion(this, 'SUG_CONT_COMM')">¿Cómo registro comisiones?</div>
                        <?php elseif ($tipo === 'inv'): ?>
                            <div class="tk-chip" onclick="tkSendSuggestion(this, 'SUG_INV_BIEN')">¿Cómo registro un bien?</div>
                        <?php endif; ?>
                        <div class="tk-chip" onclick="tkSendSuggestion(this, 'SUG_GENERAL')">Otras dudas</div>
                    </div>

                    <div class="tk-chat-footer" id="tk-footer" style="position:relative;">
                        <input type="file" id="tk-image-input" accept="image/jpeg,image/png,image/jpg" style="display:none;" onchange="handleImageSelect()">
                        <button class="tk-emoji-btn" onclick="document.getElementById('tk-image-input').click()" title="Adjuntar Imagen"><i class="fas fa-image"></i></button>
                        <button class="tk-emoji-btn" onclick="toggleEmojiPicker()"><i class="far fa-smile"></i></button>
                        <div class="emoji-picker" id="emoji-picker">
                            <span onclick="addEmoji('😀')">😀</span><span onclick="addEmoji('😃')">😃</span><span onclick="addEmoji('😄')">😄</span><span onclick="addEmoji('😁')">😁</span><span onclick="addEmoji('😆')">😆</span><span onclick="addEmoji('😅')">😅</span>
                            <span onclick="addEmoji('🤣')">🤣</span><span onclick="addEmoji('😂')">😂</span><span onclick="addEmoji('🙂')">🙂</span><span onclick="addEmoji('🙃')">🙃</span><span onclick="addEmoji('😉')">😉</span><span onclick="addEmoji('😊')">😊</span>
                            <span onclick="addEmoji('😇')">😇</span><span onclick="addEmoji('🥰')">🥰</span><span onclick="addEmoji('😍')">😍</span><span onclick="addEmoji('🤩')">🤩</span><span onclick="addEmoji('😘')">😘</span><span onclick="addEmoji('😗')">😗</span>
                            <span onclick="addEmoji('😋')">😋</span><span onclick="addEmoji('😛')">😛</span><span onclick="addEmoji('😜')">😜</span><span onclick="addEmoji('🤪')">🤪</span><span onclick="addEmoji('😝')">😝</span><span onclick="addEmoji('🤑')">🤑</span>
                            <span onclick="addEmoji('🤗')">🤗</span><span onclick="addEmoji('🤭')">🤭</span><span onclick="addEmoji('🤫')">🤫</span><span onclick="addEmoji('🤔')">🤔</span><span onclick="addEmoji('🤐')">🤐</span><span onclick="addEmoji('🤨')">🤨</span>
                            <span onclick="addEmoji('😐')">😐</span><span onclick="addEmoji('😑')">😑</span><span onclick="addEmoji('😶')">😶</span><span onclick="addEmoji('😏')">😏</span><span onclick="addEmoji('😒')">😒</span><span onclick="addEmoji('🙄')">🙄</span>
                            <span onclick="addEmoji('😬')">😬</span><span onclick="addEmoji('🤥')">🤥</span><span onclick="addEmoji('😌')">😌</span><span onclick="addEmoji('😔')">😔</span><span onclick="addEmoji('😪')">😪</span><span onclick="addEmoji('🤤')">🤤</span>
                            <span onclick="addEmoji('😴')">😴</span><span onclick="addEmoji('😷')">😷</span><span onclick="addEmoji('🤒')">🤒</span><span onclick="addEmoji('🤕')">🤕</span><span onclick="addEmoji('🤢')">🤢</span><span onclick="addEmoji('🤮')">🤮</span>
                            <span onclick="addEmoji('🤧')">🤧</span><span onclick="addEmoji('🥵')">🥵</span><span onclick="addEmoji('🥶')">🥶</span><span onclick="addEmoji('🥴')">🥴</span><span onclick="addEmoji('😵')">😵</span><span onclick="addEmoji('🤯')">🤯</span>
                            <span onclick="addEmoji('🥳')">🥳</span><span onclick="addEmoji('😎')">😎</span><span onclick="addEmoji('🤓')">🤓</span><span onclick="addEmoji('🧐')">🧐</span><span onclick="addEmoji('😕')">😕</span><span onclick="addEmoji('😟')">😟</span>
                            <span onclick="addEmoji('🙁')">🙁</span><span onclick="addEmoji('😮')">😮</span><span onclick="addEmoji('😯')">😯</span><span onclick="addEmoji('😲')">😲</span><span onclick="addEmoji('😳')">😳</span><span onclick="addEmoji('🥺')">🥺</span>
                            <span onclick="addEmoji('😦')">😦</span><span onclick="addEmoji('😧')">😧</span><span onclick="addEmoji('😨')">😨</span><span onclick="addEmoji('😰')">😰</span><span onclick="addEmoji('😥')">😥</span><span onclick="addEmoji('😢')">😢</span>
                            <span onclick="addEmoji('😭')">😭</span><span onclick="addEmoji('😱')">😱</span><span onclick="addEmoji('😖')">😖</span><span onclick="addEmoji('😣')">😣</span><span onclick="addEmoji('😞')">😞</span><span onclick="addEmoji('😓')">😓</span>
                            <span onclick="addEmoji('😩')">😩</span><span onclick="addEmoji('😫')">😫</span><span onclick="addEmoji('🥱')">🥱</span><span onclick="addEmoji('😤')">😤</span><span onclick="addEmoji('😡')">😡</span><span onclick="addEmoji('😠')">😠</span>
                            <span onclick="addEmoji('👍')">👍</span><span onclick="addEmoji('👎')">👎</span><span onclick="addEmoji('👌')">👌</span><span onclick="addEmoji('✌️')">✌️</span><span onclick="addEmoji('🤞')">🤞</span><span onclick="addEmoji('🤟')">🤟</span>
                            <span onclick="addEmoji('🤘')">🤘</span><span onclick="addEmoji('🤙')">🤙</span><span onclick="addEmoji('🖐')">🖐</span><span onclick="addEmoji('✋')">✋</span><span onclick="addEmoji('👋')">👋</span><span onclick="addEmoji('👏')">👏</span>
                            <span onclick="addEmoji('🙌')">🙌</span><span onclick="addEmoji('👐')">👐</span><span onclick="addEmoji('🤲')">🤲</span><span onclick="addEmoji('🙏')">🙏</span><span onclick="addEmoji('🤝')">🤝</span><span onclick="addEmoji('💪')">💪</span>
                            <span onclick="addEmoji('❤️')">❤️</span><span onclick="addEmoji('🧡')">🧡</span><span onclick="addEmoji('💛')">💛</span><span onclick="addEmoji('💚')">💚</span><span onclick="addEmoji('💙')">💙</span><span onclick="addEmoji('💜')">💜</span>
                            <span onclick="addEmoji('🤎')">🤎</span><span onclick="addEmoji('🖤')">🖤</span><span onclick="addEmoji('🤍')">🤍</span><span onclick="addEmoji('💔')">💔</span><span onclick="addEmoji('💯')">💯</span><span onclick="addEmoji('💢')">💢</span>
                            <span onclick="addEmoji('💬')">💬</span><span onclick="addEmoji('🗯')">🗯</span><span onclick="addEmoji('💭')">💭</span><span onclick="addEmoji('💤')">💤</span><span onclick="addEmoji('✅')">✅</span><span onclick="addEmoji('❎')">❎</span>
                            <span onclick="addEmoji('⚠️')">⚠️</span><span onclick="addEmoji('❌')">❌</span><span onclick="addEmoji('❓')">❓</span><span onclick="addEmoji('❕')">❕</span><span onclick="addEmoji('💡')">💡</span><span onclick="addEmoji('🔥')">🔥</span>
                            <span onclick="addEmoji('✨')">✨</span><span onclick="addEmoji('🌟')">🌟</span><span onclick="addEmoji('🎉')">🎉</span><span onclick="addEmoji('✅')">✅</span><span onclick="addEmoji('🇻🇪')">🇻🇪</span><span onclick="addEmoji('💼')">💼</span>
                            <span onclick="addEmoji('📅')">📅</span><span onclick="addEmoji('🔔')">🔔</span><span onclick="addEmoji('📢')">📢</span><span onclick="addEmoji('📊')">📊</span><span onclick="addEmoji('📈')">📈</span><span onclick="addEmoji('📉')">📉</span>
                            <span onclick="addEmoji('📋')">📋</span><span onclick="addEmoji('📝')">📝</span><span onclick="addEmoji('📁')">📁</span><span onclick="addEmoji('📂')">📂</span><span onclick="addEmoji('📄')">📄</span><span onclick="addEmoji('📑')">📑</span>
                        </div>
                        <textarea id="tk-chat-input" placeholder="Escribe tu mensaje..." onkeypress="if(event.key==='Enter' && !event.shiftKey) { event.preventDefault(); enviarMensaje(); }" oninput="tkSendTyping(); this.style.height='auto'; this.style.height=Math.min(this.scrollHeight, 120)+'px';" rows="1" style="resize:none; overflow-y:auto; line-height:1.5; min-height:45px; max-height:120px;"></textarea>
                        <button id="tk-chat-send" onclick="enviarMensaje()"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    let cTickId = null;
    let cTickEstado = null;   // Estado actual del ticket abierto
    let cLastMsgId = 0;
    let pollInterval = null;
    let isFetching = false;
    let remainingSecs = 0;
    let timerInterval = null;

    function formatTime(s) {
        if (s <= 0) return "00:00";
        const m = Math.floor(s / 60);
        const sec = s % 60;
        return (m < 10 ? '0' : '') + m + ":" + (sec < 10 ? '0' : '') + sec;
    }

    function runLocalTimer() {
        if (remainingSecs > 0) {
            remainingSecs--;
            document.getElementById('tk-timer-val').innerText = formatTime(remainingSecs);
            if (remainingSecs <= 0) {
                document.getElementById('tk-chat-input').disabled = true;
                document.getElementById('tk-chat-send').disabled = true;
                document.getElementById('tk-footer').style.display = 'none';
            }
        }
    }

    function tkSendSuggestion(btn, code) {
        if (!cTickId) return;

        // Evitar doble clic y mostrar carga
        btn.classList.add('loading');
        const originalText = btn.innerText;
        btn.innerText = '...';

        const fd = new FormData();
        fd.append('id_ticket', cTickId);
        fd.append('mensaje', code);

        fetch('../acciones/soporte/enviar_mensaje.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                btn.classList.remove('loading');
                btn.innerText = originalText;
                if (data.success) fetchMsgs();
            }).catch(e => {
                btn.classList.remove('loading');
                btn.innerText = originalText;
                console.error(e);
            });
    }

    function loadTicket(idTicket, asunto, estado) {
        cTickId = idTicket;
        cTickEstado = estado;
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

        document.getElementById('tk-chat-msgs').innerHTML = '<div class="typing-dots" id="tk-typing-indicator" style="display:none;"><span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span></div>';

        const sugContainer = document.getElementById('tk-suggestions-container');
        if(estado === 'Resuelto') {
            document.getElementById('tk-footer').style.display = 'none';
            if (sugContainer) sugContainer.classList.add('d-none');
        } else {
            document.getElementById('tk-footer').style.display = 'flex';
            if (sugContainer) sugContainer.classList.remove('d-none');
        }

        fetchMsgs();
        if(pollInterval) clearInterval(pollInterval);
        if(timerInterval) clearInterval(timerInterval);

        if(estado !== 'Resuelto') {
            pollInterval = setInterval(fetchMsgs, 4000);
            timerInterval = setInterval(runLocalTimer, 1000);
        } else {
            document.getElementById('tk-timer-wrapper').style.display = 'none';
        }
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
                if (data.estado) cTickEstado = data.estado; // Sincronizar estado

                const typingEl = document.getElementById('tk-typing-indicator');
                if (typingEl) typingEl.style.display = data.typing ? 'flex' : 'none';

                // Update Timer
                if (data.tiempo_restante !== undefined) {
                    remainingSecs = data.tiempo_restante;
                    document.getElementById('tk-timer-wrapper').style.display = 'block';
                    document.getElementById('tk-timer-val').innerText = formatTime(remainingSecs);
                }

                // Sincronizar estados de lectura (leído = ✓✓)
                if (data.id_leidos && data.id_leidos.length > 0) {
                    data.id_leidos.forEach(id => {
                        const tickSpan = document.getElementById(`tick-${id}`);
                        if (tickSpan && tickSpan.querySelector('.fa-check')) {
                            tickSpan.innerHTML = '<i class="fas fa-check-double"></i>';
                            tickSpan.style.color = '#34b7f1';
                            tickSpan.title = 'Leído';
                        }
                    });
                }

                if(data.success && data.mensajes.length > 0) {
                    const b = document.getElementById('tk-chat-msgs');
                    let hasNewTheirs = false;
                    data.mensajes.forEach(m => {
                        const c = m.es_mio ? 'c-mine' : 'c-theirs';
                        const sender = m.es_mio ? 'Tú' : m.emisor_nombre;
                        if (!m.es_mio && cLastMsgId > 0) hasNewTheirs = true;

                        const statusTicks = m.leido === 1 
                            ? `<span id="tick-${m.id_mensaje}" style="color: #34b7f1; margin-left: 3px;" title="Leído"><i class="fas fa-check-double"></i></span>`
                            : `<span id="tick-${m.id_mensaje}" style="margin-left: 3px;" title="Enviado"><i class="fas fa-check"></i></span>`;

                        let imgHtml = '';
                        if (m.archivo_adjunto) {
                            imgHtml = `<div class="mt-2"><img src="../${m.archivo_adjunto}" style="max-width:100%; max-height:250px; object-fit:cover; border-radius:10px; cursor:zoom-in;" onclick="tkOpenLightbox('../${m.archivo_adjunto}')"></div>`;
                        }

                        const msgDiv = document.createElement('div');
                        msgDiv.className = `chat-bubble ${m.es_mio ? 'bubble-mine' : 'bubble-theirs'}`;
                        msgDiv.setAttribute('data-id', m.id_mensaje);
                        msgDiv.innerHTML = `
                            <div class="bubble-sender">${sender}</div>
                            <div style="font-size:0.95rem;">${m.mensaje}</div>
                            ${imgHtml}
                            <div class="bubble-meta" style="justify-content: ${m.es_mio ? 'flex-end' : 'flex-start'}">
                                ${m.fecha} ${m.es_mio ? statusTicks : ''}
                            </div>
                        `;
                        b.appendChild(msgDiv);
                        cLastMsgId = m.id_mensaje;
                    });
                    b.scrollTop = b.scrollHeight;

                    if (hasNewTheirs) {
                        try {
                            const audio = new Audio('../assets/audio/notificacion.mp3');
                            audio.volume = 0.5;
                            audio.play().catch(()=>{});
                        } catch(e){}
                    }
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
        const imgInput = document.getElementById('tk-image-input');
        const txt = inp.value.trim();
        const hasFile = imgInput.files.length > 0;

        if (!txt && !hasFile || !cTickId) return;

        const fd = new FormData();
        fd.append('id_ticket', cTickId);
        fd.append('mensaje', txt);
        if (hasFile) fd.append('imagen', imgInput.files[0]);

        inp.value = '';
        inp.style.height = 'auto';
        imgInput.value = '';

        fetch('../acciones/soporte/enviar_mensaje.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) fetchMsgs();
                else alert(data.message);
            });
    }

    function handleImageSelect() {
        if (typeof Swal !== 'undefined') {
            const imgInput = document.getElementById('tk-image-input');
            if (imgInput.files.length === 0) return;
            const fileName = imgInput.files[0].name;

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
                        const inp = document.getElementById('tk-chat-input');
                        inp.value = (inp.value.trim() + " " + result.value).trim();
                    }
                    enviarMensaje();
                } else {
                    imgInput.value = '';
                }
            });
        } else {
            if (confirm("¿Enviar imagen seleccionada?")) enviarMensaje();
        }
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

    // Cerrar modales (Click Afuera)
    document.addEventListener('click', function(e) {
        const ep = document.getElementById('emoji-picker');
        const isClickInsideEP = ep && ep.contains(e.target);
        const isToggleBtn = e.target.closest('.tk-emoji-btn');
        if (!isClickInsideEP && !isToggleBtn && ep && ep.style.display === 'grid') {
            ep.style.display = 'none';
        }
    });

    function eliminarTicket(e, id) {
        if(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        if(!id) return;

        // ── Restricción: solo se puede eliminar si está Resuelto o Expirado ──
        const estaResuelto  = (cTickEstado === 'Resuelto');
        const estaExpirado  = (remainingSecs <= 0 && cTickId === id);

        if (!estaResuelto && !estaExpirado) {
            Swal.fire({
                icon: 'info',
                title: 'No puedes eliminar este ticket',
                text: 'Solo puedes eliminar tickets que ya hayan sido resueltos o cuyo tiempo haya expirado.',
                confirmButtonColor: '#f18000',
                confirmButtonText: 'Entendido'
            });
            return;
        }

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

                fetch('../acciones/soporte/borrar_ticket.php', { method: 'POST', body: fd })
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
