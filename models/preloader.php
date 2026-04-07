<?php
/**
 * SDGBP - Premium Global Preloader 2026
 * Centralized component for page transitions and action feedback.
 */
?>
<!-- GLOBAL PRELOADER -->
<style>
    #custom-global-preloader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(248, 250, 252, 0.4);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        z-index: 9999999;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.4s;
        opacity: 0;
        visibility: hidden;
    }

    [data-theme="dark"] #custom-global-preloader {
        background: rgba(0, 0, 0, 0.45);
    }

    .preloader-content {
        text-align: center;
        padding: 45px 60px;
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        border-radius: 35px;
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.15);
        transform: scale(0.9);
        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    [data-theme="dark"] .preloader-content {
        background: rgba(15, 23, 42, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 30px 70px -15px rgba(0, 0, 0, 0.6);
    }

    #custom-global-preloader.show {
        opacity: 1;
        visibility: visible;
    }

    #custom-global-preloader.show .preloader-content {
        transform: scale(1);
    }

    .preloader-icon-container {
        position: relative;
        width: 80px;
        height: 80px;
        margin: 0 auto 25px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .preloader-spinner {
        font-size: 3.8rem;
        color: #f18000;
        filter: drop-shadow(0 0 20px rgba(241, 128, 0, 0.5));
    }

    .preloader-text {
        font-family: 'Outfit', 'Plus Jakarta Sans', sans-serif;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: 1px;
        margin: 0;
        font-size: 1.25rem;
        text-transform: uppercase;
    }

    [data-theme="dark"] .preloader-text {
        color: #ffffff;
    }

    .preloader-pulse {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: rgba(241, 128, 0, 0.2);
        animation: preloader-pulse 2s infinite;
    }

    @keyframes preloader-pulse {
        0% { transform: scale(1); opacity: 0.6; }
        100% { transform: scale(1.6); opacity: 0; }
    }
</style>

<div id="custom-global-preloader">
    <div class="preloader-content">
        <div class="preloader-icon-container">
            <div class="preloader-pulse"></div>
            <i class="fas fa-circle-notch fa-spin preloader-spinner"></i>
        </div>
        <h5 class="preloader-text">Procesando...</h5>
    </div>
</div>

<script>
    (function() {
        const preloader = document.getElementById('custom-global-preloader');
        
        // No mostrar automáticamente al inicio para evitar bloqueos si hay carga lenta de recursos

        // Función global para mostrar el preloader
        window.showPreloader = function(text = "Procesando...") {
            if (preloader) {
                const textEl = preloader.querySelector('.preloader-text');
                if (textEl) textEl.textContent = text;
                preloader.classList.add('show');
            }
        };

        // Función global para ocultar el preloader
        window.hidePreloader = function() {
            if (preloader) {
                preloader.classList.remove('show');
            }
        };

        // Ocultar al cargar completamente la página
        window.addEventListener('load', function() {
            setTimeout(hidePreloader, 1000); // Aumentado para que el preloader sea más visible
        });

        // Intercepción Global de Clics
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Manejar Enlaces <a>
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (!link) return;

                const href = link.getAttribute('href');
                const target = link.getAttribute('target');
                
                // Condiciones para NO mostrar el preloader
                if (!href || 
                    href.startsWith('#') || 
                    href.startsWith('javascript:') || 
                    href.startsWith('tel:') || 
                    href.startsWith('mailto:') ||
                    target === '_blank' ||
                    e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0
                ) return;

                showPreloader("Navegando...");
            });

            // 2. Manejar Botones <button>
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('button');
                if (!btn) return;
                
                // Excluir si ya se va a manejar por el evento 'submit' (para evitar doble trigger)
                if (btn.type === 'submit' && btn.form) return;

                // Excluir botones que solo controlan la UI local
                const btnId = btn.id || "";
                const onclickAttr = btn.getAttribute('onclick') || "";

                if (btn.hasAttribute('data-bs-dismiss') || 
                    btn.hasAttribute('data-bs-toggle') || 
                    btn.getAttribute('role') === 'tab' ||
                    btn.getAttribute('aria-expanded') === 'true' ||
                    btn.classList.contains('navbar-toggler') ||
                    btn.classList.contains('btn-close') ||
                    btn.classList.contains('swal2-confirm') ||
                    btn.classList.contains('swal2-cancel') ||
                    btnId.includes('toggle') ||
                    btnId.includes('draw') ||
                    btnId === 'sidebarToggle' ||
                    btn.hasAttribute('data-no-preloader')
                ) return;

                // Excluir por contenido del onclick
                const excludedKeywords = ['confirmar', 'swal', 'delete', 'eliminar', 'borrar', 'abrir', 'rechazar', 'aprobar', 'preview', 'modal', 'show', 'toggle', 'draw', 'stoppropagation', 'history.back', 'copy'];
                const onclickLower = onclickAttr.toLowerCase();
                if (excludedKeywords.some(key => onclickLower.includes(key))) return;

                // Excluir si está dentro de un modal y no es submit
                if (btn.closest('.modal') && btn.type !== 'submit') return;

                showPreloader("Procesando...");
            });

            // 3. Manejar Submits de formularios
            document.addEventListener('submit', function(e) {
                const form = e.target;
                
                // Excluir si tiene data-no-preloader o target _blank (como reportes PDF)
                if (form.hasAttribute('data-no-preloader') || form.getAttribute('target') === '_blank') return;

                if (form.getAttribute('data-ajax') === 'true' && !form.getAttribute('data-show-preloader')) return;
                
                setTimeout(() => {
                    if (!e.defaultPrevented) {
                        showPreloader("Validando...");
                    }
                }, 0);
            });

            // 4. Definición Global de navigateTo
            window.navigateTo = function(url) {
                showPreloader("Cargando...");
                window.location.href = url;
            };
        });
    })();
</script>
