<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * SDGBP - Generador de Manuales Multi-Rol Ultra-Premium 2026
 * Genera 4 manuales: General, UPU, INV y CONT.
 */

// Datos de la empresa
$empresa = "EURIPYS 2024 C.A.";
$sistema = "SDGBP - Sistema de Gestión de Bienes y Pagos";
$version = "v2.0 Premium";
$fecha = date('d/m/Y');

// Logo Corporativo en Base64
$logo_path = __DIR__ . '/../img/Logo-OP2_V4.png';
$logo_base64 = '';
if (file_exists($logo_path)) {
    $logo_base64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_path));
}

// Estilos CSS "Ultra-Premium 2026"
$css = "
<style>
    @font-face { font-family: 'Helvetica'; font-weight: normal; }
    body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10.5pt; color: #1e293b; margin: 0; padding: 0; }
    @page { margin: 80px 50px 80px 50px; }
    
    header { position: fixed; top: -60px; left: 0; right: 0; height: 50px; border-bottom: 2px solid #f18000; color: #64748b; font-size: 8pt; }
    footer { position: fixed; bottom: -60px; left: 0; right: 0; height: 50px; border-top: 1px solid #e2e8f0; padding-top: 10px; font-size: 8pt; color: #94a3b8; text-align: center; }
    .pagenum:before { content: counter(page); }

    /* Portada */
    .cover { text-align: center; padding-top: 80px; page-break-after: always; }
    .cover-logo { width: 140px; margin-bottom: 30px; }
    .cover-tag { background: #f18000; color: white; display: inline-block; padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 10pt; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px; }
    .cover-title { font-size: 36pt; font-weight: bold; color: #0f172a; margin-bottom: 10px; line-height: 1.1; }
    .cover-subtitle { font-size: 16pt; color: #64748b; margin-bottom: 80px; }
    .cover-info { font-size: 11pt; color: #94a3b8; margin-top: 150px; }

    /* Títulos */
    h1 { color: #0f172a; font-size: 22pt; margin-top: 40px; border-bottom: 3px solid #f18000; padding-bottom: 10px; }
    h2 { color: #0284c7; font-size: 17pt; margin-top: 30px; border-left: 5px solid #0284c7; padding-left: 10px; }
    h3 { color: #f18000; font-size: 13pt; margin-top: 20px; font-weight: 700; }

    /* Contenido */
    .section { margin-bottom: 25px; line-height: 1.7; text-align: justify; }
    .module-card { background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; margin: 20px 0; border-radius: 12px; }
    .step-box { background: #ffffff; border: 1px solid #cbd5e1; padding: 15px; margin: 10px 0; border-radius: 8px; }
    .step-num { color: #0284c7; font-weight: 800; margin-right: 10px; }

    /* Badges */
    .role-badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 8.5pt; font-weight: 800; color: #fff; margin-right: 6px; }
    .bg-admin { background: #ef4444; }
    .bg-cont { background: #6366f1; }
    .bg-inv { background: #f59e0b; }
    .bg-upu { background: #10b981; }

    /* Alertas */
    .alert-premium { padding: 15px 20px; border-radius: 12px; border: 1px solid; margin: 20px 0; }
    .alert-info { background: #f0f9ff; border-color: #bae6fd; color: #0369a1; }
    .alert-warning { background: #fffbeb; border-color: #fef3c7; color: #b45309; }
    
    strong { color: #0f172a; }
</style>
";

// Definición de Manuales
$manualesDef = [
    'GENERAL' => [
        'titulo' => 'Manual Maestro de Operaciones SDGBP',
        'subtitle' => 'Guía Maestra 2026 para Administradores y Consultores',
        'archivo' => 'Manual_General_SDGBP.pdf',
        'roles' => ['ADMIN'],
        'intro' => 'Este manual maestro detalla cada rincón técnico y operativo del sistema SDGBP, diseñado para el control total administrativo.',
        'sections' => ['core', 'auth', 'upu_clientes', 'upu_operaciones', 'inv_bienes', 'cont_auditoria', 'admin_gestion', 'soporte']
    ],
    'UPU' => [
        'titulo' => 'Guía del Productor (UPU)',
        'subtitle' => 'Reporte de Ingresos, Egresos y Gestión de Clientes',
        'archivo' => 'Manual_UPU_SDGBP.pdf',
        'roles' => ['UPU'],
        'intro' => 'Guía exhaustiva para la autogestión financiera del Productor: registro de clientes, reporte de pagos y control de egresos.',
        'sections' => ['auth', 'upu_clientes', 'upu_operaciones', 'soporte']
    ],
    'INV' => [
        'titulo' => 'Guía de Gestión de Inventario (INV)',
        'subtitle' => 'Control de Activos Fisicos y Registro Técnico',
        'archivo' => 'Manual_INV_SDGBP.pdf',
        'roles' => ['INV'],
        'intro' => 'Documentación para el registro técnico de bienes nacionales mediante carga dinámica y categorización.',
        'sections' => ['auth', 'inv_bienes', 'soporte']
    ],
    'CONT' => [
        'titulo' => 'Manual de Auditoría y Finanzas (CONT)',
        'subtitle' => 'Centro de Aprobaciones y Conciliación Bancaria',
        'archivo' => 'Manual_CONT_SDGBP.pdf',
        'roles' => ['CONT'],
        'intro' => 'Guía para la validación de la red financiera, cálculo de comisiones y cierres de auditoría.',
        'sections' => ['auth', 'cont_auditoria', 'soporte']
    ]
];

// Contenido Modular Exhaustivo
$contentModules = [
    'core' => "
        <h1>1. El Ecosistema SDGBP</h1>
        <div class='section'>
            <p>El <strong>SDGBP Versión 2026</strong> es una arquitectura de software diseñada para la soberanía administrativa. Actúa como el centro neurálgico donde convergen los activos físicos y el flujo monetario de la empresa.</p>
            <div class='module-card'>
                <h3>Visión Operativa</h3>
                <ul>
                    <li><strong>Control de Bienes:</strong> Trazabilidad total mediante códigos internos únicos (BN-XXXX).</li>
                    <li><strong>Transparencia Financiera:</strong> Ciclo completo de Ingreso-Egreso con validación de saldo disponible.</li>
                    <li><strong>Soporte Proactivo:</strong> Comunicación directa mediante tickets con soporte de adjuntos multimedia.</li>
                </ul>
            </div>
        </div>
    ",
    'auth' => "
        <h1>2. Seguridad y Acceso Garantizado</h1>
        <div class='section'>
            <p>La seguridad es el pilar del SDGBP. El acceso está blindado mediante un panel de autenticación de alta disponibilidad.</p>
            <h3>2.1 Inicio de Sesión y Bloqueos</h3>
            <p>Para ingresar, utilice sus credenciales. Por seguridad:</p>
            <ul>
                <li><strong>Cifrado:</strong> Las claves están protegidas con algoritmos hash de un solo sentido.</li>
                <li><strong>Bloqueo Automático:</strong> Tras 3 intentos fallidos, la cuenta se bloquea por 15 minutos para prevenir ataques de fuerza bruta.</li>
                <li><strong>Preguntas de Seguridad:</strong> Utilícelas para autogestionar el desbloqueo o recuperación de cuenta.</li>
            </ul>
            <div class='alert-premium alert-warning'>
                <strong>Importante:</strong> Las contraseñas caducan cada 180 días. El sistema le forzará el cambio al vencer este periodo.
            </div>
        </div>
    ",
    'upu_clientes' => "
        <h1>3. Gestión de Clientes y Proveedores</h1>
        <div class='section'>
            <p>Como usuario UPU, usted debe mantener su directorio actualizado para poder procesar reportes financieros externos.</p>
            <div class='module-card'>
                <h3>Registro de Cliente</h3>
                <p>Ubicación: Menú <strong>'Mis Clientes'</strong> -> <strong>'Registrar Cliente'</strong>.</p>
                <ul>
                    <li><strong>Nombre/Razón Social:</strong> Identifique claramente a la entidad (ej. 'Comercializadora J&M').</li>
                    <li><strong>Vinculación:</strong> El cliente se asocia automáticamente a su código de productor para reportes exclusivos.</li>
                </ul>
                <p><em>Este paso es obligatorio para seleccionar destinatarios en el módulo de Egresos.</em></p>
            </div>
        </div>
    ",
    'upu_operaciones' => "
        <h1>4. Módulo de Operaciones Financieras UPU</h1>
        <div class='section'>
            <p>Este módulo permite reportar el flujo de caja diario con comprobantes de respaldo.</p>
            <h3>4.1 Registro de Ingreso (Abono a Saldo)</h3>
            <p>Cargue sus cobros indicando:</p>
            <ul>
                <li>Banco origen, referencia de transferencia y fecha exacta.</li>
                <li><strong>Comprobante:</strong> Imagen legible de la captura de pantalla o recibo.</li>
            </ul>
            <h3>4.2 Registro de Egreso (Salida de Fondos)</h3>
            <p>Utilice esta función para reportar pagos a terceros o gastos operativos.</p>
            <div class='step-box'>
                <span class='step-num'>1.</span> Seleccione el <strong>Cliente/Proveedor</strong> previamente registrado.<br>
                <span class='step-num'>2.</span> Ingrese el monto. El sistema <strong>validará automáticamente</strong> que tenga saldo suficiente.<br>
                <span class='step-num'>3.</span> Adjunte el recibo de egreso y describa el motivo (máx 50 caracteres).
            </div>
        </div>
    ",
    'inv_bienes' => "
        <h1>3. Control de Bienes Nacionales (Inventario)</h1>
        <div class='section'>
            <p>El rol INV es custodio de la información física del patrimonio institucional.</p>
            <h3>3.1 Registro Técnico de Activos</h3>
            <p>El sistema utiliza una interfaz de carga dinámica (AJAX) para agilizar el proceso:</p>
            <ol>
                <li><strong>Categoría:</strong> Seleccione el grupo (ej. Equipos de Computación) para filtrar items válidos.</li>
                <li><strong>Nombre del Bien:</strong> Seleccione el hardware específico.</li>
                <li><strong>Serial Físico:</strong> Ingrese el S/N del fabricante sin errores.</li>
                <li><strong>Código Interno:</strong> El sistema genera un ID único BN-XXXX que debe ser adherido al equipo físico.</li>
            </ol>
            <div class='alert-premium alert-info'>
                Nota: Se recomienda realizar el registro en el sitio de recepción del bien para verificar el serial físico.
            </div>
        </div>
    ",
    'cont_auditoria' => "
        <h1>3. Auditoría y Centro de Aprobaciones</h1>
        <div class='section'>
            <p>El rol CONT actúa como el validador final de la integridad monetaria.</p>
            <div class='module-card'>
                <h3>3.1 El Centro de Aprobaciones</h3>
                <p>Todos los pagos reportados por UPUs entran en una cola de espera organizada por 'Unidad'.</p>
                <ul>
                    <li><strong>Visión de Recibos:</strong> Previsualice el archivo adjunto para verificar autenticidad.</li>
                    <li><strong>Aprobar con Comisión:</strong> El sistema permite aplicar una tasa de comisión bancaria al momento de la liberación de fondos.</li>
                    <li><strong>Rechazo:</strong> Documente la razón exacta (ej. 'Referencia Duplicada') para alimentar la bitácora de seguridad.</li>
                </ul>
            </div>
        </div>
    ",
    'admin_gestion' => "
        <h1>3. Gestión Estratégica y Seguridad</h1>
        <div class='section'>
            <p>El Administrador garantiza la continuidad y seguridad extrema del ecosistema.</p>
            <h3>3.1 Control de Usuarios y Saldos</h3>
            <p>Solo el Super Administrador puede ajustar saldos manualmente o resetear contadores de intentos fallidos.</p>
            <h3>3.2 Seguridad Elevada (Borrado 2FA)</h3>
            <p>Eliminar un usuario es una acción crítica. Por ello, el sistema requiere una <strong>verificación de doble factor (2FA)</strong> enviada al correo del Super Admin para confirmar la eliminación definitiva solicitada por otros administradores inferiores.</p>
            <div class='module-card'>
                <h3>3.3 Infraestructura</h3>
                <ul>
                    <li><strong>Catalogo Marketing:</strong> Active o desactive la visibilidad pública de productos.</li>
                    <li><strong>Paradas Técnicas:</strong> Active el 'Modo Mantenimiento' para denegar accesos durante actualizaciones.</li>
                </ul>
            </div>
        </div>
    ",
    'soporte' => "
        <h1>Soporte Técnico y Tickets</h1>
        <div class='section'>
            <p>El sistema incluye una central de ayuda interactiva con historial persistente.</p>
            <ul>
                <li><strong>Tickets Admin:</strong> Gestión de cola de incidencias con alertas de borrado.</li>
                <li><strong>Interfaz Usuario:</strong> Chat con sugerencias inteligentes según el rol actual.</li>
            </ul>
        </div>
    "
];

// Loop de Generación
foreach ($manualesDef as $key => $def) {
    try {
        $html = "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'>$css</head><body>";
        
        // Footer (Fijo para todas las páginas)
        $html .= "<footer id='footer'>$sistema - {$def['titulo']} | Página <span class='pagenum'></span></footer>";

        // Portada
        $html .= "
            <div class='cover'>
                <div class='cover-tag'>{$key} VERSION</div>
                <img src='$logo_base64' class='cover-logo' alt='Logo'>
                <div class='cover-title'>{$def['titulo']}</div>
                <div class='cover-subtitle'>{$def['subtitle']}</div>
                <div class='cover-info'>
                    <strong>Plataforma SDGBP</strong><br>
                    <strong>Versión:</strong> $version<br>
                    <strong>Fecha de Documento:</strong> $fecha<br>
                    <strong>Autor:</strong> Cristian Arcaya | Euripys
                </div>
            </div>
        ";

        // Intro
        $html .= "<h1>Introducción del Manual</h1><div class='section'><p>{$def['intro']}</p></div>";

        // Módulos
        foreach ($def['sections'] as $mod) {
            if (isset($contentModules[$mod])) {
                $html .= $contentModules[$mod];
            }
        }

        $html .= "</body></html>";

        // Generación con Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output_file = __DIR__ . "/../manuales/{$def['archivo']}";
        file_put_contents($output_file, $dompdf->output());

        echo "[\xE2\x9C\x93] Generado: {$def['archivo']}\n";

    } catch (Exception $e) {
        echo "[X] Error en {$def['archivo']}: " . $e->getMessage() . "\n";
    }
}
?>
