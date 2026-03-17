-- SDGBP - MIGRACIÓN DE BASE DE DATOS
-- Orden cronológico de cambios realizados hoy 2026-03-14

-- [13:07] Vencimiento de Contraseña
ALTER TABLE usuario ADD COLUMN fecha_cambio_clave DATE DEFAULT CURRENT_TIMESTAMP;

-- [13:18] Verificación 2FA por Correo (PHPMailer)
ALTER TABLE usuario ADD COLUMN codigo_verificacion VARCHAR(6) DEFAULT NULL;

-- [15:15] Subida de Comprobantes y Auto-limpieza
ALTER TABLE pagos ADD COLUMN comprobante_archivo VARCHAR(255) DEFAULT NULL;

-- -------------------------------------------------------------
-- Ejecutar estos comandos en la pestaña SQL de phpMyAdmin
-- en tu servidor de InfinityFree.
-- -------------------------------------------------------------

-- [2026-03-16] MODERNIZACIÓN UI PREMIUM, MODO OSCURO & PDF ENGINE
-- Nota: Estas mejoras no requieren cambios estructurales en las tablas.
-- INTERFAZ: Modernización total de Bienes, Productos, Categorías y Comprobantes (Glassmorphism).
-- PDF: Implementación de motor FPDF para etiquetas Ultra Premium (Solución sin dependencia de GD).
-- MODO OSCURO: Soporte completo mediante variables CSS dinámicas y fix de visibilidad en móvil.
-- ESTABILIDAD: Corrección de errores de conteo de columnas en DataTables y sintaxis PHP.
