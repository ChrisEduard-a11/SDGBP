<!-- Carga de FontAwesome para asegurar iconos en Toastr en vistas externas -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
    /* ================= TOASTR ULTRA-PREMIUM DESIGN (STATIC VERSION) ================= */
    #toast-container > .toast {
        background-image: none !important;
        border-radius: 16px !important;
        box-shadow: 0 15px 20px -5px rgba(0, 0, 0, 0.15), 0 8px 8px -5px rgba(0, 0, 0, 0.04), 
                    0 0 10px rgba(241, 128, 0, 0.05) !important;
        backdrop-filter: blur(16px) saturate(160%) !important;
        -webkit-backdrop-filter: blur(16px) saturate(160%) !important;
        opacity: 0.98 !important;
        padding: 15px 15px 15px 55px !important;
        font-family: 'Plus Jakarta Sans', 'Outfit', sans-serif !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        width: auto !important;
        max-width: 320px !important;
    }

    /* Ultra-Premium Gradients */
    #toast-container > .toast-success { 
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.92) 0%, rgba(5, 150, 105, 0.8) 100%) !important;
        border-left: 5px solid #10b981 !important;
    }
    #toast-container > .toast-error { 
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.92) 0%, rgba(185, 28, 28, 0.8) 100%) !important;
        border-left: 5px solid #ef4444 !important;
    }
    #toast-container > .toast-warning { 
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.92) 0%, rgba(217, 119, 6, 0.8) 100%) !important;
        border-left: 5px solid #f59e0b !important;
    }
    #toast-container > .toast-info { 
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.92) 0%, rgba(37, 99, 235, 0.8) 100%) !important;
        border-left: 5px solid #3b82f6 !important;
    }

    /* Icons - FontAwesome Support */
    #toast-container > .toast:before {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free";
        font-weight: 900;
        font-size: 1.6rem;
        color: white;
        -webkit-font-smoothing: antialiased;
        text-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    #toast-container > .toast-success:before { content: "\f058"; }
    #toast-container > .toast-error:before { content: "\f057"; }
    #toast-container > .toast-warning:before { content: "\f06a"; }
    #toast-container > .toast-info:before { content: "\f05a"; }

    #toast-container .toast-title {
        font-weight: 800 !important;
        font-size: 0.90rem !important;
        margin-bottom: 3px !important;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    
    #toast-container .toast-message {
        font-weight: 500 !important;
        font-size: 0.82rem !important;
        line-height: 1.4;
        opacity: 0.95;
    }

    /* ProgressBar Premium */
    #toast-container > .toast .toast-progress {
        height: 5px !important;
        opacity: 0.4 !important;
        background-color: white !important;
        border-radius: 0 0 20px 20px !important;
    }
</style>
