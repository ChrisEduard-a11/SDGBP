<?php
require_once("../models/header.php");
include('../conexion.php');

// Solo administradores pueden ver esto
if ($_SESSION['tipo'] !== 'admin') {
    echo "<script>window.location.href='inicio.php';</script>";
    exit();
}

$sqlTickets = "SELECT t.*, u.nombre AS u_nombre, u.usuario AS u_usuario, u.foto AS u_foto 
               FROM soporte_tickets t 
               LEFT JOIN usuario u ON t.id_usuario = u.id_usuario 
               ORDER BY t.estado ASC, t.fecha_apertura DESC";
$resTickets = mysqli_query($conexion, $sqlTickets);
$tickets = [];
while ($row = mysqli_fetch_assoc($resTickets)) {
    $tickets[] = $row;
}
?>

<style>
    /* Estilos Premium para Ventana de Tickets */
    .tk-container {
        display: flex; height: calc(100vh - 120px); min-height: 500px;
        background: var(--glass-bg, #fff);
        border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.05); overflow: hidden;
    }
    
    /* Panel Izquierdo: Lista de Tickets */
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
    
    /* Panel Derecho: Chat Activo */
    .tk-chat-panel { flex: 1; display: flex; flex-direction: column; background: #fff; }
    .tk-chat-header {
        padding: 15px 25px; border-bottom: 1px solid rgba(0,0,0,0.05); background: #f8fafc;
        display: flex; justify-content: space-between; align-items: center; height: 80px;
    }
    .tk-chat-body { flex: 1; padding: 25px; overflow-y: auto; background: rgba(241, 245, 249, 0.3); display: flex; flex-direction: column; gap: 15px; }
    .tk-chat-footer { padding: 20px; border-top: 1px solid rgba(0,0,0,0.05); background: #fff; display: flex; gap: 15px; align-items: center; }
    
    .tk-empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #94a3b8; }
    
    /* Burbujas del Chat Admin */
    .c-bubble { max-width: 75%; padding: 12px 18px; border-radius: 18px; line-height: 1.4; position: relative; }
    .c-theirs { background: #f1f5f9; color: #1e293b; align-self: flex-start; border-bottom-left-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
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
        width: 250px;
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

    /* Typing indicator (admin panel) */
    .tk-typing-bubble { display: none; align-items: center; gap: 4px; padding: 10px 14px; background: #f1f5f9; border-radius: 18px; border-bottom-left-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); width: fit-content; max-width: 65px; margin-bottom: 5px; }
    .tk-typing-bubble span { width: 7px; height: 7px; background: #94a3b8; border-radius: 50%; animation: tkTypingDot 1.2s infinite ease-in-out; }
    .tk-typing-bubble span:nth-child(2) { animation-delay: 0.2s; }
    .tk-typing-bubble span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes tkTypingDot { 0%, 80%, 100% { transform: translateY(0); opacity:0.5; } 40% { transform: translateY(-6px); opacity:1; } }

    /* Dark Mode Support */
    [data-theme="dark"] .tk-container { background: #0f172a; border-color: #1e293b; }
    [data-theme="dark"] .tk-list-panel { background: #1e293b; border-color: #334155; }
    [data-theme="dark"] .tk-item { background: #0f172a; border-color: #334155; color: #f8fafc; }
    [data-theme="dark"] .tk-item.active { background: #1e293b; border-left-color: #f18000; }
    [data-theme="dark"] .tk-list-header { background: #1e293b; border-color: #334155; color: #fff; }
    [data-theme="dark"] .tk-chat-header { background: #1e293b; border-color: #334155; color: #fff; }
    [data-theme="dark"] .tk-chat-panel, [data-theme="dark"] .tk-chat-footer { background: #0f172a; border-color: #334155; }
    [data-theme="dark"] .tk-chat-footer input { background: #1e293b; border-color: #334155; color: #fff; }
    [data-theme="dark"] .c-theirs { background: #1e293b; color: #f8fafc; border-color: #334155; }

    /* =============================================
       TABLET: < 900px — Panel izquierdo más compacto
    ============================================= */
    @media (max-width: 900px) {
        .tk-list-panel { width: 240px; }
        .tk-chat-body { padding: 15px; gap: 10px; }
        .tk-chat-header { padding: 12px 18px; height: auto; flex-wrap: wrap; gap: 6px; }
        .tk-chat-footer input { padding: 12px 15px; }
        .c-bubble { max-width: 85%; }
    }

    /* =============================================
       MOBILE: < 680px — Chat como OVERLAY FIJO
    ============================================= */
    @media (max-width: 680px) {

        /* El contenedor muestra SOLO la lista */
        .tk-container {
            height: calc(100dvh - 100px);
            min-height: unset;
            border-radius: 14px;
        }
        .tk-list-panel {
            width: 100%;
            border-right: none;
            height: 100%;
            flex: 1 1 auto;
        }
        .tk-list-header { padding: 14px 16px; }
        .tk-list-body { padding: 8px; }
        .tk-item { padding: 12px; margin-bottom: 6px; }

        /* El panel de chat SALE DEL LAYOUT — es un overlay fijo */
        .tk-chat-panel {
            position: fixed !important;
            inset: 0 !important;           /* top:0; right:0; bottom:0; left:0 */
            width: 100% !important;
            height: 100% !important;
            z-index: 99999 !important;
            border-radius: 0 !important;
            transform: translateX(100%);
            transition: transform 0.32s cubic-bezier(0.4, 0, 0.2, 1);
            background: #fff;
            display: flex !important;
            flex-direction: column !important;
        }
        [data-theme="dark"] .tk-chat-panel { background: #0f172a !important; }

        .tk-chat-panel.mobile-open {
            transform: translateX(0) !important;
        }

        /* Botón "← Atrás" */
        #btn-back-mobile {
            display: inline-flex !important;
            align-items: center;
            gap: 6px;
            background: none;
            border: none;
            color: #f18000;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            padding: 0;
            flex-shrink: 0;
        }

        /* Header del chat en móvil: fila compacta con atrás + avatar + acciones */
        .tk-chat-header {
            padding: 10px 14px;
            height: auto;
            flex-wrap: wrap;
            gap: 8px;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .tk-chat-header > div:last-child {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .tk-chat-header .btn {
            font-size: 0.72rem;
            padding: 4px 10px;
        }
        #tk-user-avatar { width: 36px !important; height: 36px !important; }
        #tk-user-name { font-size: 0.95rem; }

        /* Mensajes — ocupa todo el espacio restante con scroll */
        .tk-chat-body {
            flex: 1 1 0;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding: 14px 12px;
            gap: 10px;
        }
        .c-bubble { max-width: 88%; padding: 10px 13px; font-size: 0.88rem; }

        /* Footer pegado abajo */
        .tk-chat-footer {
            padding: 10px 12px;
            gap: 8px;
            flex-shrink: 0;
        }
        .tk-chat-footer input {
            padding: 11px 14px;
            font-size: 0.9rem;
        }
        .tk-chat-footer button {
            width: 44px;
            height: 44px;
            font-size: 1rem;
            flex-shrink: 0;
        }
    }
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4" id="main-container-tk">
        <header class="page-header-standard mb-4">
            <h1 class="fw-bold mb-0 text-primary"><i class="fas fa-headset me-2"></i>Centro de Soporte</h1>
            <p class="text-muted">Gestiona los tickets de ayuda y comunícate con los usuarios</p>
        </header>

        <div class="tk-container">
            <!-- Panel Izquierdo: Lista -->
            <div class="tk-list-panel">
                <div class="tk-list-header">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-inbox me-2"></i>Bandeja</h5>
                    <span class="badge bg-primary rounded-pill"><?php echo count($tickets); ?> Tickets</span>
                </div>
                <div class="tk-list-body">
                    <?php if(count($tickets)>0): ?>
                        <?php foreach($tickets as $t): 
                            $esInvitado = is_null($t['id_usuario']);
                            $displayName = $esInvitado ? $t['nombre_visitante'] : $t['u_nombre'];
                            $displayUser = $esInvitado ? 'Visitante (CI: '.$t['cedula_visitante'].')' : '@'.$t['u_usuario'];
                            // Si es invitado usa default_profile.png, si es usuario y no tiene foto también.
                            $displayFoto = $esInvitado ? '../img/default_profile.png' : (!empty($t['u_foto']) ? $t['u_foto'] : '../img/default_profile.png');
                        ?>
                            <div class="tk-item position-relative" onclick="loadTicket('<?php echo $t['id_ticket']; ?>', '<?php echo htmlspecialchars($displayName); ?>', '<?php echo $t['estado']; ?>', '<?php echo $displayFoto; ?>', '<?php echo $esInvitado ? '1' : '0'; ?>', '<?php echo $t['cedula_visitante']; ?>')" id="item-<?php echo $t['id_ticket']; ?>">
                                <div class="tk-list-delete" onclick="borrarTicketDirecto(event, '<?php echo $t['id_ticket']; ?>')">
                                    <i class="fas fa-trash-alt tk-delete-btn" style="font-size:0.85rem;"></i>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2 pe-4">
                                    <span class="fw-bold fs-0-9"><?php echo $t['id_ticket']; ?></span>
                                    <?php 
                                        $bColor = $t['estado']=='Abierto' ? 'bg-success' : ($t['estado']=='En Proceso' ? 'bg-warning text-dark' : 'bg-secondary');
                                    ?>
                                    <span class="badge <?php echo $bColor; ?>" style="font-size:0.65rem;"><?php echo $t['estado']; ?></span>
                                </div>
                                <div class="fw-bold text-truncate" style="max-width:100%; font-size:0.95rem; color:#f18000;">
                                    <?php 
                                        $caliEmoji = '';
                                        if (isset($t['calificacion'])) {
                                            if ($t['calificacion'] === 'bien') $caliEmoji = '👍';
                                            if ($t['calificacion'] === 'mal') $caliEmoji = '👎';
                                        }
                                        echo htmlspecialchars($t['asunto']) . ' <span style="font-size:0.8rem;">' . $caliEmoji . '</span>'; 
                                    ?>
                                </div>
                                <div class="text-muted small mt-1">
                                    <i class="fas <?php echo $esInvitado ? 'fa-user-secret' : 'fa-user'; ?> border-0 text-muted me-1"></i> 
                                    <?php echo htmlspecialchars($displayName); ?> 
                                    <span class="ms-1 <?php echo $esInvitado ? 'text-danger fw-bold' : 'text-muted'; ?>">(<?php echo htmlspecialchars($displayUser); ?>)</span>
                                </div>
                                <div class="text-muted" style="font-size: 0.7rem; text-align:right; margin-top:5px; border-top:1px solid rgba(0,0,0,0.05); padding-top:5px;"><?php echo date('d/m/y H:i', strtotime($t['fecha_apertura'])); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center p-4 text-muted">No hay tickets.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Panel Derecho: Chat -->
            <div class="tk-chat-panel">
                <div id="tk-empty-view" class="tk-empty-state">
                    <i class="fas fa-comments fa-4x mb-3 text-muted" style="opacity:0.3;"></i>
                    <h4 class="fw-bold">Selecciona un Ticket</h4>
                    <p>Para ver el historial y responder al usuario.</p>
                </div>

                <div id="tk-chat-view" style="display:none; height:100%; width:100%; flex:1; flex-direction:column;">
                    <div class="tk-chat-header">
                        <div class="d-flex align-items-center gap-3">
                            <img src="" id="tk-user-avatar" class="rounded-circle" width="45" height="45" style="object-fit:cover; border:2px solid #e2e8f0;">
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="mb-0 fw-bold" id="tk-user-name">Usuario</h5>
                                    <div id="tk-calificacion-admin"></div>
                                </div>
                                <div class="small text-muted" id="tk-id-label">TICK-XXXX</div>
                                <div id="tk-timer-wrapper" class="mt-1" style="display:none; font-size:0.75rem;">
                                    <span class="badge bg-light text-dark border"><i class="far fa-clock me-1 text-primary"></i>Cierra en: <span id="tk-timer-val" class="fw-bold text-danger">30:00</span></span>
                                </div>
                                <div id="tk-extra-info" class="text-danger fw-bold" style="font-size: 0.75rem; display:none;"></div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                            <!-- Botón de volver (solo visible en móvil) -->
                            <button id="btn-back-mobile" style="display:none; padding: 4px 10px; font-size: 0.8rem; background: #fff8f0; border: 1px solid #f18000; border-radius: 20px;" onclick="closeMobileChat()">
                                <i class="fas fa-arrow-left"></i> Volver
                            </button>
                            <button class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold" id="btn-cerrar-tk" onclick="cerrarTicket()">
                                <i class="fas fa-lock me-1"></i> Marcar Resuelto
                            </button>
                            <button class="btn btn-success btn-sm rounded-pill px-3 fw-bold" id="btn-confirm-id" style="display:none;" onclick="toggleSearchPanel()">
                                <i class="fas fa-id-card me-1"></i> Confirmar y Seleccionar Usuario
                            </button>
                            <button class="btn btn-danger btn-sm rounded-pill px-3 fw-bold" id="btn-eliminar-tk" onclick="borrarTicket()">
                                <i class="fas fa-trash me-1"></i> Eliminar Ticket
                            </button>
                        </div>
                    </div>
                    
                    <div class="tk-chat-body" id="tk-chat-msgs">
                        <!-- Mensajes aquí -->
                    </div>
                    <div id="tk-typing-indicator" class="tk-typing-bubble mx-3 my-2" style="display:none; align-self:flex-start;"><span></span><span></span><span></span></div>
                    
                    <!-- Panel de Búsqueda de Usuario (Solo para Invitados) -->
                    <div id="tk-search-panel" style="display:none; padding: 15px; background: #fff8f0; border-top: 1px solid #f18000; border-bottom: 1px solid #f18000;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold small text-primary"><i class="fas fa-search me-1"></i> Vincular Usuario del Sistema</span>
                            <button class="btn-close small" style="font-size:0.7rem;" onclick="toggleSearchPanel()"></button>
                        </div>
                        <input type="text" id="tk-search-input" class="form-control form-control-sm mb-2 rounded-pill" placeholder="Buscar por Nombre o Cédula..." onkeyup="searchSystemUser()">
                        <div id="tk-search-results" style="max-height: 150px; overflow-y: auto; display: flex; flex-direction: column; gap: 5px;"></div>
                    </div>
                    
                    <div class="tk-chat-footer" style="position:relative;">
                        <input type="file" id="tk-image-input" accept="image/jpeg,image/png,image/jpg" style="display:none;" onchange="handleImageSelect()">
                        <button class="tk-emoji-btn" onclick="document.getElementById('tk-image-input').click()" title="Adjuntar Imagen"><i class="fas fa-image"></i></button>
                        <button class="tk-emoji-btn" onclick="toggleEmojiPicker()"><i class="far fa-smile"></i></button>
                        <div class="emoji-picker" id="emoji-picker">
                            <span onclick="addEmoji('😀')">😀</span><span onclick="addEmoji('😃')">😃</span><span onclick="addEmoji('😄')">😄</span><span onclick="addEmoji('😁')">😁</span><span onclick="addEmoji('😆')">😆</span><span onclick="addEmoji('😅')">😅</span>
                            <span onclick="addEmoji('😂')">😂</span><span onclick="addEmoji('😉')">😉</span><span onclick="addEmoji('😊')">😊</span><span onclick="addEmoji('😇')">😇</span><span onclick="addEmoji('🥰')">🥰</span><span onclick="addEmoji('😍')">😍</span>
                            <span onclick="addEmoji('🤩')">🤩</span><span onclick="addEmoji('😘')">😘</span><span onclick="addEmoji('😜')">😜</span><span onclick="addEmoji('🤑')">🤑</span><span onclick="addEmoji('🤔')">🤔</span><span onclick="addEmoji('🤫')">🤫</span>
                            <span onclick="addEmoji('🧐')">🧐</span><span onclick="addEmoji('😏')">😏</span><span onclick="addEmoji('🥳')">🥳</span><span onclick="addEmoji('😎')">😎</span><span onclick="addEmoji('🥺')">🥺</span><span onclick="addEmoji('😭')">😭</span>
                            <span onclick="addEmoji('👍')">👍</span><span onclick="addEmoji('👎')">👎</span><span onclick="addEmoji('👏')">👏</span><span onclick="addEmoji('🙌')">🙌</span><span onclick="addEmoji('🔥')">🔥</span><span onclick="addEmoji('✨')">✨</span>
                        </div>
                        <input type="text" id="tk-chat-input" placeholder="Escribe tu respuesta como Administrador..." onkeypress="if(event.key==='Enter') enviarMensajeAdmin()" oninput="tkSendTyping()">
                        <button id="tk-chat-send" onclick="enviarMensajeAdmin()"><i class="fas fa-paper-plane"></i></button>
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
                document.getElementById('btn-cerrar-tk').style.display = 'none';
            }
        }
    }

    function loadTicket(idTicket, userName, estado, foto, isGuest, cedula) {
        cTickId = idTicket;
        cLastMsgId = 0;
        cUserName = userName;
        cCedula = cedula;
        
        // UI Updates
        document.querySelectorAll('.tk-item').forEach(el => el.classList.remove('active'));
        document.getElementById(`item-${idTicket}`).classList.add('active');
        
        document.getElementById('tk-empty-view').style.display = 'none';
        document.getElementById('tk-chat-view').style.display = 'flex';
        
        document.getElementById('tk-user-name').innerText = userName;
        document.getElementById('tk-id-label').innerText = idTicket;
        document.getElementById('tk-user-avatar').src = foto || '../img/default_profile.png';
        
        const extra = document.getElementById('tk-extra-info');
        if (isGuest == '1') {
            extra.innerText = 'CÉDULA: ' + cedula;
            extra.style.display = 'block';
        } else {
            extra.style.display = 'none';
        }
        
        document.getElementById('tk-chat-msgs').innerHTML = ''; // clear

        if(estado === 'Resuelto') {
            document.getElementById('tk-chat-input').disabled = true;
            document.getElementById('tk-chat-send').disabled = true;
            document.getElementById('btn-cerrar-tk').style.display = 'none';
            document.getElementById('btn-confirm-id').style.display = 'none';
            document.getElementById('btn-eliminar-tk').style.display = 'inline-block';
            document.getElementById('tk-chat-input').placeholder = 'Ticket Cerrado. No se pueden enviar mensajes.';
        } else {
            document.getElementById('tk-chat-input').disabled = false;
            document.getElementById('tk-chat-send').disabled = false;
            document.getElementById('btn-cerrar-tk').style.display = 'inline-block';
            document.getElementById('btn-confirm-id').style.display = isGuest == '1' ? 'inline-block' : 'none';
            document.getElementById('btn-eliminar-tk').style.display = 'inline-block';
            document.getElementById('tk-chat-input').placeholder = 'Escribe tu respuesta...';
        }

        fetchMsgsAdmin();
        if(pollInterval) clearInterval(pollInterval);
        if(timerInterval) clearInterval(timerInterval);

        if(estado !== 'Resuelto'){
            pollInterval = setInterval(fetchMsgsAdmin, 3000);
            timerInterval = setInterval(runLocalTimer, 1000);
        } else {
            document.getElementById('tk-timer-wrapper').style.display = 'none';
        }

        // En móvil, deslizar el panel de chat
        openMobileChat();
    }

    function openMobileChat() {
        if (window.innerWidth <= 680) {
            document.querySelector('.tk-chat-panel').classList.add('mobile-open');
            document.body.style.overflow = 'hidden'; // Prevent background scroll on mobile
            const backBtn = document.getElementById('btn-back-mobile');
            if (backBtn) backBtn.style.display = 'inline-flex';
        }
    }

    function closeMobileChat() {
        document.querySelector('.tk-chat-panel').classList.remove('mobile-open');
        document.body.style.overflow = ''; // Restore scroll
        const backBtn = document.getElementById('btn-back-mobile');
        if (backBtn) backBtn.style.display = 'none';
        if (pollInterval) clearInterval(pollInterval);
    }

    // On resize to desktop, always reset the mobile overlay state
    window.addEventListener('resize', () => {
        if (window.innerWidth > 680) {
            document.querySelector('.tk-chat-panel').classList.remove('mobile-open');
            document.body.style.overflow = '';
            const backBtn = document.getElementById('btn-back-mobile');
            if (backBtn) backBtn.style.display = 'none';
        }
    });

    function fetchMsgsAdmin() {
        if(!cTickId || isFetching) return;
        isFetching = true;
        fetch(`../acciones/soporte/obtener_mensajes.php?id_ticket=${cTickId}&last_id=${cLastMsgId}`)
            .then(r=>r.json())
            .then(data=>{
                isFetching = false;
                // Typing indicator
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

                if(data.success && data.mensajes.length>0){
                    let hasNewTheirs = false;
                    const b = document.getElementById('tk-chat-msgs');
                    const indicator = document.getElementById('tk-typing-indicator');

                    data.mensajes.forEach(m => {
                        const isAdmin = m.es_mio;
                        if (!isAdmin && cLastMsgId > 0) hasNewTheirs = true;

                        const c = isAdmin ? 'c-mine' : 'c-theirs';
                        const statusTicks = m.leido === 1 
                            ? `<span id="tick-${m.id_mensaje}" style="color: #34b7f1; margin-left: 3px;" title="Leído"><i class="fas fa-check-double"></i></span>`
                            : `<span id="tick-${m.id_mensaje}" style="margin-left: 3px;" title="Enviado"><i class="fas fa-check"></i></span>`;

                        let imgHtml = '';
                        if (m.archivo_adjunto) {
                            imgHtml = `<div class="mt-2"><img src="../${m.archivo_adjunto}" style="max-width:100%; border-radius:10px; cursor:pointer;" onclick="window.open('../${m.archivo_adjunto}')"></div>`;
                        }

                        const msgDiv = document.createElement('div');
                        msgDiv.className = `c-bubble ${c}`;
                        msgDiv.setAttribute('data-id', m.id_mensaje);
                        msgDiv.innerHTML = `
                            <div style="font-size:0.95rem;">${m.mensaje}</div>
                            ${imgHtml}
                            <div style="font-size:0.65rem; opacity:0.7; margin-top:5px; display: flex; align-items: center; justify-content: ${isAdmin?'flex-end':'flex-start'}">
                                ${m.fecha} ${isAdmin ? statusTicks : ''}
                            </div>
                        `;
                        b.appendChild(msgDiv);
                        cLastMsgId = m.id_mensaje;
                    });
                    b.scrollTop = b.scrollHeight;
                    if (hasNewTheirs) {
                        try {
                            const audio = new Audio('data:audio/mp3;base64,SUQzBAAAAAAAI1RTU0UAAAAPAAADTGF2ZjU5LjE2LjEwMAAAAAAAAAAAAAAA//tQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAASW5mbwAAAA8AAAAJAAABXQAAxMTEw8PDx8fHx8vLy8vLy8vLy8/Pz8/P0NfX19fX2dnZ2d/f39/f4eHh4eHj4+Pj5eXl5ebm5ubm5+fn5+fn5+fo6Ojo6erq6urr6+vr6+/v7+/v7+/v7/Hx8fHy8vLy8vPz8/Pz9PT09PT19fX19fX29vb29/f39/f39/f4+Pj4+fn5+fn6+vr6+vv7+/v7/Pz8/Pz8/Pz9/f39/f7+/v7+/v7+/v7/Pz8/AAAAAAAAAAAAAAD/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//tQwAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqhgx+AABz/MAAAAE1h48qgVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVT/7UMAAAAwDSDAAAAABMAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//tQwAAADANIAAAAAEwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//tQwAAADANIAAAAAEwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//tQwAAADANIAAAAAEwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA');
                            audio.play().catch(()=>{});
                        } catch(e){}
                    }
                }
                
                if (data.calificacion) {
                    let caliText = data.calificacion === 'bien' ? '👍' : '👎';
                    document.getElementById('tk-calificacion-admin').innerHTML = `<span style="font-size:1.1rem; line-height:1;" title="Calificación del Usuario">${caliText}</span>`;
                } else {
                    document.getElementById('tk-calificacion-admin').innerHTML = '';
                }
            });
    }    let tkTypingTimeout = null;
    function tkSendTyping() {
        if (!cTickId) return;
        clearTimeout(tkTypingTimeout);
        const fd = new FormData();
        fd.append('id_ticket', cTickId);
        fd.append('rol', 'admin');
        fetch('../acciones/soporte/typing.php', { method: 'POST', body: fd });
    }

    function toggleSearchPanel() {
        const p = document.getElementById('tk-search-panel');
        p.style.display = p.style.display === 'none' ? 'block' : 'none';
        if (p.style.display === 'block') document.getElementById('tk-search-input').focus();
    }

    function searchSystemUser() {
        const q = document.getElementById('tk-search-input').value;
        const res = document.getElementById('tk-search-results');
        if (q.length < 3) {
            res.innerHTML = '';
            return;
        }

        fetch(`../acciones/soporte/buscar_usuario.php?q=${q}`)
            .then(r=>r.json())
            .then(data => {
                if(data.success) {
                    res.innerHTML = '';
                    data.usuarios.forEach(u => {
                        res.innerHTML += `
                            <div class="d-flex justify-content-between align-items-center p-2 border rounded bg-white hover:bg-light" style="cursor:pointer; font-size:0.85rem;" onclick="vincularYConfirmar('${u.id_usuario}', '${u.nombre}', '${u.cedula}', '${u.usuario}')">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="${u.foto || '../img/default_profile.png'}" width="25" height="25" class="rounded-circle">
                                    <div><strong>${u.nombre}</strong> <span class="text-muted small">(${u.cedula})</span></div>
                                </div>
                                <i class="fas fa-link text-success"></i>
                            </div>
                        `;
                    });
                    if(data.usuarios.length === 0) res.innerHTML = '<div class="text-muted small text-center p-2">No se encontraron coincidencias.</div>';
                }
            });
    }

    function vincularYConfirmar(idU, nombreU, cedulaU, userU) {
        Swal.fire({
            title: '¿Confirmar Identidad?',
            text: `Se vinculará este ticket a la cuenta de ${nombreU} (@${userU}) y se enviará el mensaje de confirmación.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Sí, Vincular y Confirmar'
        }).then((result) => {
            if (result.isConfirmed) {
                // 1. Vincular en DB
                const fd = new FormData();
                fd.append('id_ticket', cTickId);
                fd.append('id_usuario', idU);
                
                fetch('../acciones/soporte/vincular_usuario_ticket.php', { method:'POST', body:fd })
                    .then(r=>r.json())
                    .then(data => {
                        if(data.success) {
                            // 2. Enviar mensaje de confirmación
                            const msgText = `✅ IDENTIDAD CONFIRMADA Y VINCULADA.\nEl Centro de Soporte ha verificado que usted es el usuario: ${nombreU}\nID de Sistema: @${userU}\nCédula: ${cedulaU}`;
                            const fdMsg = new FormData();
                            fdMsg.append('id_ticket', cTickId);
                            fdMsg.append('mensaje', msgText);
                            
                            fetch('../acciones/soporte/enviar_mensaje.php', { method:'POST', body:fdMsg })
                                .then(rr=>rr.json())
                                .then(dataMsg => {
                                    if(dataMsg.success) {
                                        Swal.fire('¡Éxito!', 'Usuario vinculado e identidad confirmada.', 'success').then(() => location.reload());
                                    }
                                });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
            }
        });
    }

    function confirmarIdentidad() {
        if(!cTickId || !cCedula) return;
        const msg = `✅ EL CENTRO DE SOPORTE HA CONFIRMADO SU IDENTIDAD.\nUsuario: ${cUserName}\nCédula: ${cCedula}`;
        
        const fd = new FormData();
        fd.append('id_ticket', cTickId);
        fd.append('mensaje', msg);

        fetch('../acciones/soporte/enviar_mensaje.php', { method:'POST', body:fd })
            .then(r=>r.json())
            .then(data=>{
                if(data.success) {
                    fetchMsgsAdmin();
                    Swal.fire({icon:'success', title:'Identidad Confirmada', text:'Se ha enviado el mensaje de confirmación al usuario.', confirmButtonColor:'#f18000'});
                }
                else alert(data.message);
            });
    }

    function enviarMensajeAdmin() {
        const inp = document.getElementById('tk-chat-input');
        const imgInput = document.getElementById('tk-image-input');
        const txt = inp.value.trim();
        const hasFile = imgInput.files.length > 0;

        if (!txt && !hasFile) return;

        const fd = new FormData();
        fd.append('id_ticket', cTickId);
        fd.append('mensaje', txt);
        if (hasFile) fd.append('imagen', imgInput.files[0]);

        inp.value = '';
        imgInput.value = '';

        fetch('../acciones/soporte/enviar_mensaje.php', { method:'POST', body:fd })
            .then(r=>r.json())
            .then(data=>{
                if(data.success) fetchMsgsAdmin();
                else alert(data.message);
            });
    }

    function handleImageSelect() {
        Swal.fire({
            title: '¿Enviar imagen?',
            text: "Se enviará la imagen seleccionada al chat.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f18000',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Sí, enviar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                enviarMensajeAdmin();
            } else {
                document.getElementById('tk-image-input').value = '';
            }
        });
    }

    function cerrarTicket() {
        Swal.fire({
            title: '¿Confirmar cierre?',
            text: "El ticket pasará a estado de solo-lectura y se marcará como Resuelto.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f18000',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Sí, cerrar ticket',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('id_ticket', cTickId);
                fetch('../acciones/soporte/cerrar_ticket.php', { method:'POST', body:fd })
                    .then(r=>r.json())
                    .then(data => {
                        if(data.success) {
                            Swal.fire('¡Cerrado!', 'El ticket ha sido resuelto.', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
            }
        });
    }

    function borrarTicket() {
        if(!cTickId) return;
        borrarTicketDirecto(null, cTickId);
    }

    function borrarTicketDirecto(e, id) {
        if(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        if(!id) return;

        Swal.fire({
            title: '¿Eliminar Ticket?',
            text: "Esta acción borrará definitivamente este ticket y todos sus mensajes. ¡No se puede deshacer!",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('id_ticket', id);
                fetch('../acciones/soporte/borrar_ticket.php', { method:'POST', body:fd })
                    .then(r=>r.json())
                    .then(data => {
                        if(data.success) {
                            Swal.fire('¡Eliminado!', 'El historial ha sido borrado por completo.', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message? data.message : "Error desconocido", 'error');
                        }
                    });
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
</script>

<?php require_once("../models/footer.php"); ?>
