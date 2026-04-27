<?php
error_reporting(0);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once("../../conexion.php");

header('Content-Type: application/json');

$is_guest = !isset($_SESSION['id']);

$id_ticket = isset($_POST['id_ticket']) ? mysqli_real_escape_string($conexion, trim($_POST['id_ticket'])) : '';
$mensaje = isset($_POST['mensaje']) ? mysqli_real_escape_string($conexion, trim($_POST['mensaje'])) : '';
// The remitente can be user id or 'ADMIN' or 'guest_TICK-XX'

$is_admin = !$is_guest && ($_SESSION['tipo'] === 'admin');

if ($is_guest) {
    if (!isset($_SESSION['guest_ticket_id']) || $_SESSION['guest_ticket_id'] !== $id_ticket) {
        echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
        exit;
    }
    $enviado_por = 'guest_' . $id_ticket;
} else {
    $enviado_por = $_SESSION['id'];
}

if (empty($id_ticket) && empty($mensaje) && !isset($_FILES['imagen'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos.']);
    exit;
}

$archivo_adjunto = null;
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Error en la subida del archivo (Código: ' . $_FILES['imagen']['error'] . ')']);
        exit;
    }

    $temp_path = $_FILES['imagen']['tmp_name'];
    $file_info = pathinfo($_FILES['imagen']['name']);
    $ext = strtolower($file_info['extension']);
    
    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    if (!in_array($ext, $allowed_extensions)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido. Solo JPG, JPEG y PNG.']);
        exit;
    }

    $new_name = 'adjunto_' . uniqid() . '.' . $ext;
    $upload_dir = '../../assets/uploads/soporte/';
    
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            echo json_encode(['success' => false, 'message' => 'No se pudo crear el directorio de subida.']);
            exit;
        }
    }
    
    $dest_path = $upload_dir . $new_name;
    if (move_uploaded_file($temp_path, $dest_path)) {
        $archivo_adjunto = 'assets/uploads/soporte/' . $new_name;
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo mover el archivo al directorio de destino. Verifique los permisos de carpeta.']);
        exit;
    }
}

// Ensure the ticket isn't closed
$q_ticket = "SELECT estado, id_usuario FROM soporte_tickets WHERE id_ticket = '$id_ticket'";
$res_t = mysqli_query($conexion, $q_ticket);
if ($row_t = mysqli_fetch_assoc($res_t)) {
    if ($row_t['estado'] === 'Resuelto') {
        echo json_encode(['success' => false, 'message' => 'El ticket ya ha sido cerrado.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'El ticket no existe.']);
    exit;
}

// =====================================================================
// LÓGICA DE BOT Y BIENVENIDA AUTO
// =====================================================================
$pre_messages = [];
$post_messages = [];


// 2. Lógica de Respuestas Automáticas del Bot (SUG_)
if (!$is_admin && strpos($mensaje, 'SUG_') === 0) {
    $bot_response = "";
    switch ($mensaje) {
        case 'SUG_ACCESS_FAIL':
            $bot_response = "1. Verifica que la tecla Bloq Mayús esté desactivada.<br>2. Asegúrate de escribir tu usuario exactamente igual, sin espacios adicionales.<br>3. Recarga la página o inténtalo desde una pestaña de Incógnito.<br>4. Si el error persiste, utiliza el botón de 'Desbloquear' ubicado en el panel principal del Login.<br><br><b>Si aún no logras entrar, por favor espera un momento y un asesor te atenderá.</b>";
            break;
        case 'SUG_USER_LOST':
            $bot_response = "Para recuperar tu usuario, realiza lo siguiente:<br>1. Ve al panel de inicio de sesión (Login).<br>2. Haz clic en el botón 'Recuperar' que se encuentra bajo el formulario.<br>3. Selecciona la opción para recuperar usuario.<br>4. Ingresa tu número de Cédula de Identidad y sigue los pasos de validación.";
            break;
        case 'SUG_PWD_LOST':
            $bot_response = "Para recuperar tu contraseña, realiza lo siguiente:<br>1. Ve al panel principal de inicio de sesión (Login).<br>2. Haz clic en el botón 'Recuperar' y selecciona la opción de recuperar clave.<br>3. Ingresa tu nombre de Usuario.<br>4. Verifica tu identidad mediante tu correo electrónico o preguntas de seguridad para crear una nueva clave.";
            break;
        case 'SUG_UPU_INGRESO':
            $bot_response = "Para reportar ingresos de UPU:<br>1. Dirígete a la barra lateral de tu panel y entra a <b>Pagos</b> > <b>Reportar Pago</b>.<br>2. Ingresa el monto (usa puntos para miles y comas para decimales).<br>3. Selecciona tu banco emisor e introduce la referencia.<br>4. Es <b>obligatorio</b> adjuntar el capture del comprobante antes de Guardar.";
            break;
        case 'SUG_UPU_EGRESO':
            $bot_response = "Para reportar egresos de UPU:<br>1. Dirígete a la barra lateral y entra al módulo <b>Pagos</b> > <b>Reportar Egreso</b>.<br>2. Selecciona detalladamente el concepto de tu gasto.<br>3. Ingresa el monto exacto debitado.<br>4. Adjunta la foto física o digital del comprobante del gasto como aval y procede a guardar.";
            break;
        case 'SUG_CONT_COMM':
            $bot_response = "Para registrar comisiones:<br>1. Accede al módulo <b>Aprobar Pagos</b> desde tu panel.<br>2. Busca el pago pendiente y pulsa el botón verde de validación.<br>3. En la ventana emergente, completa el campo 'Comisión' con el monto descontado.<br>4. Finaliza pulsando <b>Liberar Pago</b> para que se asiente la operación con la deducción correspondiente en el saldo.";
            break;
        case 'SUG_GENERAL':
            $bot_response = "Por favor, redacta de forma detallada tu consulta en este recuadro. Un asesor analista evaluará tu situación y te brindará soporte paso a paso. Recuerda que este ticket expirará en una ventana de 30 minutos.";
            break;
    }

    if (!empty($bot_response)) {
        $bot_response_db = mysqli_real_escape_string($conexion, $bot_response);
        $post_messages[] = "INSERT INTO soporte_mensajes (id_ticket, enviado_por, mensaje) VALUES ('$id_ticket', 'admin', '$bot_response_db')";
        
        // Map SUG_ codes to readable labels for the database entry
        $mapping = [
            'SUG_ACCESS_FAIL' => 'Consulta: No puedo acceder',
            'SUG_USER_LOST'   => 'Consulta: ¿Cómo recupero mi usuario?',
            'SUG_PWD_LOST'    => 'Consulta: ¿Cómo recupero mi clave?',
            'SUG_UPU_INGRESO' => 'Consulta: ¿Cómo reporto un ingreso?',
            'SUG_UPU_EGRESO'  => 'Consulta: ¿Cómo reporto un egreso?',
            'SUG_CONT_COMM'   => 'Consulta: ¿Cómo registro comisiones?',
            'SUG_INV_BIEN'    => 'Consulta: ¿Cómo registro un bien?',
            'SUG_GENERAL'     => 'Consulta: Otras dudas/Sugerencias'
        ];
        if (isset($mapping[$mensaje])) {
            $mensaje = $mapping[$mensaje];
        }
    }
}

// 3. Ejecutar mensajes PREVIOS
foreach ($pre_messages as $sql_pre) {
    mysqli_query($conexion, $sql_pre);
}

// 4. Ejecutar el mensaje principal del usuario/admin
$sql_msg = "INSERT INTO soporte_mensajes (id_ticket, enviado_por, mensaje, archivo_adjunto) VALUES ('$id_ticket', '$enviado_por', '$mensaje', " . ($archivo_adjunto ? "'$archivo_adjunto'" : "NULL") . ")";
if (mysqli_query($conexion, $sql_msg)) {
    // 5. Ejecutar mensajes POSTERIORES DESPUÉS del mensaje principal
    foreach ($post_messages as $sql_post) {
        mysqli_query($conexion, $sql_post);
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al enviar: ' . mysqli_error($conexion)]);
}
?>
