<?php include("../models/toastr_css.php"); ?>
<?php if (isset($_SESSION["estatus"]) && isset($_SESSION["mensaje"])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const preloader = document.getElementById('custom-global-preloader');
            const delay = preloader ? 1500 : 0;
            setTimeout(() => {
                Swal.fire({
                    icon: '<?php echo $_SESSION["estatus"]; ?>',
                    title: '<?php echo $_SESSION["mensaje"]; ?>',
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#007bff'
                });
            }, delay);
        });
    </script>
 <?php
// Limpiar las variables de sesión después de mostrar la alerta
    unset($_SESSION["estatus"]);
    unset($_SESSION["mensaje"]);
?>                
<?php endif; ?>

<style>
    .swal2-container { z-index: 20000000 !important; }
</style>

<?php if (isset($_SESSION['type']) && isset($_SESSION['alert'])): ?>
    <style>
        .premium-swal-popup {
            border-radius: 1.5rem !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            background: var(--glass-bg, #ffffff) !important;
            backdrop-filter: blur(15px) !important;
            -webkit-backdrop-filter: blur(15px) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
            padding-bottom: 2rem !important;
        }
        [data-theme="dark"] .premium-swal-popup, body.dark-mode .premium-swal-popup {
            background: rgba(30, 41, 59, 0.95) !important;
            color: #f8fafc !important;
            border: 1px solid rgba(255,255,255,0.05) !important;
        }
        .custom-swal-img {
            border: 4px solid #17a2b8 !important;
            padding: 4px !important;
            background: white !important;
            box-shadow: 0 10px 25px rgba(23, 162, 184, 0.3) !important;
        }
        [data-theme="dark"] .custom-swal-img, body.dark-mode .custom-swal-img {
            background: #1e293b !important;
            border-color: #4facfe !important;
        }
        .custom-swal-title {
            color: #17a2b8 !important;
            font-weight: 800 !important;
            margin-bottom: 0 !important;
        }
        [data-theme="dark"] .custom-swal-title, body.dark-mode .custom-swal-title {
            color: #4facfe !important;
        }
        .swal-shortcut-btn {
            border-radius: 50px !important;
            padding: 0.6rem 1.5rem !important;
            font-weight: 600 !important;
            transition: all 0.3s ease !important;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-transform: none !important;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05) !important;
        }
        .swal-shortcut-btn:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 15px rgba(0,0,0,0.1) !important;
        }
        /* Fix text colors for dark mode context inside the modal */
        [data-theme="dark"] .swal2-html-container .text-muted, body.dark-mode .swal2-html-container .text-muted {
            color: #94a3b8 !important;
        }
        [data-theme="dark"] .swal2-html-container .alert-text, body.dark-mode .swal2-html-container .alert-text {
            color: #cbd5e1 !important;
        }
    </style>    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const preloader = document.getElementById('custom-global-preloader');
            const delay = preloader ? 1500 : 0;

            const userType = "<?php echo $_SESSION['tipo'] ?? ''; ?>";
            
            const welcomeData = {
                title: '¡Bienvenido!',
                alert: `<?php echo $_SESSION["alert"]; ?>`,
                type: "<?php echo $_SESSION['type']; ?>",
                tipo_usuario: userType,
                foto: '<?php echo isset($_SESSION["foto"]) && !empty($_SESSION["foto"]) ? $_SESSION["foto"] : "../img/default-user.png"; ?>'
            };

            // Mostramos normalmente
            setTimeout(() => {
                showPremiumWelcome(welcomeData);
            }, delay);
        });

        // Función global para mostrar el saludo (se usará también en el footer tras aceptar)
        function showPremiumWelcome(data) {
            let shortcuts = '';
            if (data.tipo_usuario == "admin") {
                shortcuts = `
                    <button class="btn btn-primary swal-shortcut-btn w-100" onclick="Swal.close(); navigateTo('registro_u.php');">
                        <i class="fas fa-user-plus"></i> Registrar Nuevo Usuario
                    </button>
                    <button class="btn btn-outline-info swal-shortcut-btn w-100" onclick="Swal.close(); navigateTo('registro_bien.php');">
                        <i class="fas fa-box-open"></i> Registrar Nuevo Bien
                    </button>`;
            } else if (data.tipo_usuario == "cont") {
                shortcuts = `
                    <button class="btn btn-primary swal-shortcut-btn w-100" onclick="Swal.close(); navigateTo('registro_pagos_egresos.php');">
                        <i class="fas fa-file-invoice-dollar"></i> Registrar Nuevo Egreso
                    </button>
                    <button class="btn btn-outline-info swal-shortcut-btn w-100" onclick="Swal.close(); navigateTo('ver_pagos_cont.php');">
                        <i class="fas fa-search-dollar"></i> Revisar Pagos
                    </button>`;
            } else if (data.tipo_usuario == "inv") {
                shortcuts = `
                    <button class="btn btn-primary swal-shortcut-btn w-100" onclick="Swal.close(); navigateTo('registro_bien.php');">
                        <i class="fas fa-plus-circle"></i> Registrar Bien
                    </button>
                    <button class="btn btn-outline-info swal-shortcut-btn w-100" onclick="Swal.close(); navigateTo('lista_bienes.php');">
                        <i class="fas fa-list"></i> Ver Inventario
                    </button>`;
            } else if (data.tipo_usuario == "upu") {
                shortcuts = `
                    <button class="btn btn-primary swal-shortcut-btn w-100" onclick="Swal.close(); navigateTo('registro_pagos.php');">
                        <i class="fas fa-upload"></i> Reportar Nuevo Pago
                    </button>
                    <button class="btn btn-outline-info swal-shortcut-btn w-100" onclick="Swal.close(); navigateTo('ver_pagos.php');">
                        <i class="fas fa-history"></i> Ver Mi Historial
                    </button>`;
            }

            Swal.fire({
                title: `<h2 class="custom-swal-title">${data.title}</h2>`,
                html: `
                    <div class="alert-text" style="font-size: 1.15rem; font-weight: 600; color: #6c757d; margin-bottom: 25px;">
                        ${data.alert}
                    </div>
                    <div class="px-3">
                        <p class="text-muted mb-3" style="font-size: 0.95rem; font-weight: 500;">¿Qué te gustaría hacer ahora?</p>
                        ${shortcuts}
                    </div>
                `,
                imageUrl: data.foto,
                imageWidth: 90,
                imageHeight: 90,
                imageAlt: 'Foto de perfil',
                showConfirmButton: true,
                confirmButtonText: 'Ir al Panel Principal <i class="fas fa-arrow-right ms-2"></i>',
                confirmButtonColor: '#6c757d',
                customClass: {
                    popup: 'premium-swal-popup',
                    image: 'rounded-circle shadow-sm custom-swal-img',
                    confirmButton: 'btn btn-secondary rounded-pill px-4 fw-bold mt-3 shadow-sm'
                },
                backdrop: 'rgba(15, 23, 42, 0.75)'
            });
        }
    </script>
    <?php
    // Limpiar las variables de sesión después de mostrar la alerta
    unset($_SESSION['type']);
    unset($_SESSION['alert']);
    ?>
<?php endif; ?>
