 <?php
require_once("../models/funciones.php");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mantenimiento</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<!-- GLOBAL PRELOADER -->
<style>.swal2-container { z-index: 9999999 !important; }</style>
<div id="global-preloader" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); z-index: 999999; display: flex; align-items: center; justify-content: center; transition: opacity 0.4s ease, visibility 0.4s ease;">
    <div style="color: #f18000; text-align: center; padding: 20px;">
        <i class="fas fa-circle-notch fa-spin" style="font-size: 4rem; filter: drop-shadow(0 0 10px rgba(255,255,255,0.3)); margin-bottom: 20px;"></i>
        <h5 style="font-family: 'Outfit', sans-serif; font-weight: 600; color: #ffffff; letter-spacing: 1px; margin: 0;">Cargando...</h5>
    </div>
</div>
<script>
    window.addEventListener('load', function() {
        const preloader = document.getElementById('global-preloader');
        if (preloader) {
            preloader.style.opacity = '0';
            preloader.style.visibility = 'hidden';
            setTimeout(() => preloader.remove(), 400);
        }
    });
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('a:not([target="_blank"]):not([href^="#"]):not([href^="javascript:"])').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!e.ctrlKey && !e.shiftKey && !e.metaKey && this.href) {
                    const preloader = document.getElementById('global-preloader');
                    if (preloader) {
                        preloader.style.visibility = 'visible';
                        preloader.style.opacity = '1';
                    }
                }
            });
        });
    });
</script>
<!-- END GLOBAL PRELOADER -->
<div id="layoutSidenav_content">
    <div id="layoutError">
        <div id="layoutError_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-6">
                            <div class="text-center mt-4">
                                <h1 class="display-1">401</h1>
                                <p class="lead">Unauthorized</p>
                                <p>Access to this resource is denied.</p>
                                <button class="btn btn-danger" onclick="showProcessingAndGoBack()">Volver</button>
                                    <i class="fas fa-arrow-left me-1"></i>
                                    Return to Dashboard
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
<?php
require_once("../models/footer.php");
?>

