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
        display: flex; height: calc(100vh - 120px); min-height: 600px;
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
    .tk-chat-footer button { width: 50px; height: 50px; border-radius: 50%; background: #f18000; color: white; border: none; font-size: 1.2rem; cursor: pointer; transition: 0.2s; display:flex; align-items:center; justify-content:center; }
    .tk-chat-footer button:hover { background: #ea580c; transform: scale(1.05); }
    .tk-chat-footer button:disabled { background: #cbd5e1; cursor:not-allowed; }

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
</style>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4 py-4 animate__animated animate__fadeIn">
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
                            $displayFoto = $esInvitado ? '../img/default-user.png' : $t['u_foto'];
                        ?>
                            <div class="tk-item" onclick="loadTicket('<?php echo $t['id_ticket']; ?>', '<?php echo htmlspecialchars($displayName); ?>', '<?php echo $t['estado']; ?>', '<?php echo $displayFoto; ?>', '<?php echo $esInvitado ? '1' : '0'; ?>', '<?php echo $t['cedula_visitante']; ?>')" id="item-<?php echo $t['id_ticket']; ?>">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold fs-0-9"><?php echo $t['id_ticket']; ?></span>
                                    <?php 
                                        $bColor = $t['estado']=='Abierto' ? 'bg-success' : ($t['estado']=='En Proceso' ? 'bg-warning text-dark' : 'bg-secondary');
                                    ?>
                                    <span class="badge <?php echo $bColor; ?>" style="font-size:0.65rem;"><?php echo $t['estado']; ?></span>
                                </div>
                                <div class="fw-bold text-truncate" style="max-width:100%; font-size:0.95rem; color:#f18000;"><?php echo htmlspecialchars($t['asunto']); ?></div>
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

                <div id="tk-chat-view" style="display:none; height:100%; flex-direction:column;">
                    <div class="tk-chat-header">
                        <div class="d-flex align-items-center gap-3">
                            <img src="" id="tk-user-avatar" class="rounded-circle" width="45" height="45" style="object-fit:cover; border:2px solid #e2e8f0;">
                            <div>
                                <h5 class="mb-0 fw-bold" id="tk-user-name">Usuario</h5>
                                <div class="small text-muted" id="tk-id-label">TICK-XXXX</div>
                                <div id="tk-extra-info" class="text-danger fw-bold" style="font-size: 0.75rem; display:none;"></div>
                            </div>
                        </div>
                        <div>
                            <button class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold" id="btn-cerrar-tk" onclick="cerrarTicket()">
                                <i class="fas fa-lock me-1"></i> Marcar Resuelto
                            </button>
                            <button class="btn btn-success btn-sm rounded-pill px-3 fw-bold" id="btn-confirm-id" style="display:none;" onclick="confirmarIdentidad()">
                                <i class="fas fa-id-card me-1"></i> Confirmar Identidad
                            </button>
                            <button class="btn btn-danger btn-sm rounded-pill px-3 fw-bold" id="btn-eliminar-tk" style="display:none;" onclick="borrarTicket()">
                                <i class="fas fa-trash me-1"></i> Eliminar Ticket
                            </button>
                        </div>
                    </div>
                    
                    <div class="tk-chat-body" id="tk-chat-msgs"></div>
                    
                    <div class="tk-chat-footer">
                        <input type="text" id="tk-chat-input" placeholder="Escribe tu respuesta como Administrador..." onkeypress="if(event.key==='Enter') enviarMensajeAdmin()">
                        <button id="tk-chat-send" onclick="enviarMensajeAdmin()"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    let cTickId = null;
    let cLastMsgId = 0;
    let cUserName = '';
    let cCedula = '';
    let pollInterval = null;

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
        document.getElementById('tk-user-avatar').src = foto || '../img/default-user.png';
        
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
            document.getElementById('btn-eliminar-tk').style.display = 'none';
            document.getElementById('tk-chat-input').placeholder = 'Escribe tu respuesta...';
        }

        fetchMsgsAdmin();
        if(pollInterval) clearInterval(pollInterval);
        if(estado !== 'Resuelto'){
            pollInterval = setInterval(fetchMsgsAdmin, 3000);
        }
    }

    function fetchMsgsAdmin() {
        if(!cTickId) return;
        fetch(`../acciones/soporte/obtener_mensajes.php?id_ticket=${cTickId}&last_id=${cLastMsgId}`)
            .then(r=>r.json())
            .then(data=>{
                if(data.success && data.mensajes.length>0){
                    let hasNewTheirs = false;
                    const b = document.getElementById('tk-chat-msgs');
                    data.mensajes.forEach(m => {
                        const isAdmin = m.es_mio;
                        if (!isAdmin && cLastMsgId > 0) hasNewTheirs = true;

                        const c = isAdmin ? 'c-mine' : 'c-theirs';
                        b.innerHTML += `
                            <div class="c-bubble ${c}">
                                <div style="font-size:0.95rem;">${m.mensaje}</div>
                                <div style="font-size:0.65rem; opacity:0.7; margin-top:5px; text-align:${isAdmin?'right':'left'}">${m.fecha}</div>
                            </div>
                        `;
                        cLastMsgId = m.id_mensaje;
                    });
                    b.scrollTop = b.scrollHeight;
                    if (hasNewTheirs) {
                        try {
                            const audio = new Audio('data:audio/mp3;base64,SUQzBAAAAAAAI1RTU0UAAAAPAAADTGF2ZjU5LjE2LjEwMAAAAAAAAAAAAAAA//tQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAASW5mbwAAAA8AAAAJAAABXQAAxMTEw8PDx8fHx8vLy8vLy8vLy8/Pz8/P0NfX19fX2dnZ2d/f39/f4eHh4eHj4+Pj5eXl5ebm5ubm5+fn5+fn5+fo6Ojo6erq6urr6+vr6+/v7+/v7+/v7/Hx8fHy8vLy8vPz8/Pz9PT09PT19fX19fX29vb29/f39/f39/f4+Pj4+fn5+fn6+vr6+vv7+/v7/Pz8/Pz8/Pz9/f39/f7+/v7+/v7+/v7/Pz8/AAAAAAAAAAAAAAD/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//tQwAAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqhgx+AABz/MAAAAE1h48qgVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVT/7UMAAAAwDSDAAAAABMAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//tQwAAADANIAAAAAEwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//tQwAAADANIAAAAAEwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//tQwAAADANIAAAAAEwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA');
                            audio.play().catch(()=>{});
                        } catch(e){}
                    }
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
        const txt = inp.value.trim();
        if(!txt || !cTickId) return;

        inp.value = '';
        const fd = new FormData();
        fd.append('id_ticket', cTickId);
        fd.append('mensaje', txt);

        fetch('../acciones/soporte/enviar_mensaje.php', { method:'POST', body:fd })
            .then(r=>r.json())
            .then(data=>{
                if(data.success) fetchMsgsAdmin();
                else alert(data.message);
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
                fd.append('id_ticket', cTickId);
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
</script>

<?php require_once("../models/footer.php"); ?>
