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
    <script src="../js/all.js"></script>
</head>
<body>
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
