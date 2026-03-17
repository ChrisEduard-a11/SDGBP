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

     <!--Sweetalert-->
     <link rel="stylesheet" type="text/css" href="../sweetalert/sweetalert2.min.css">
    <script src="../sweetalert/sweetalert2.js"></script>

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
                                <img class="mb-4 img-error" src="../img/error-404-monochrome.svg" />
                                <p class="lead">Pagina en Mantenimiento.</p>
                                <a class="btn btn-danger" href="../vistas/inicio.php"><i class="fas fa-arrow-left me-1"></i>
                                Volver</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
 <?php
require_once("../models/footer.php");
require_once("../models/funciones.php");
?>