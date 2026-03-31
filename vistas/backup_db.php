<?php
require_once("../models/header.php");
?>

<div id="layoutSidenav_content">
    <div class="container-fluid px-4">
        <h3 class="mt-4 text-center">Respaldo de Base de Datos</h3>
        <ol class="breadcrumb mb-4 bg-light p-3 rounded shadow-sm">
            <li class="breadcrumb-item"><a href="inicio.php" class="text-decoration-none">Inicio</a></li>
            <li class="breadcrumb-item"><a href="usuario.php" class="text-decoration-none">Usuario</a></li>
            </a></li>
            <li class="breadcrumb-item active">Respaldo BD</li>
        </ol>

        <div class="card mb-4 shadow-lg">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="fas fa-database"></i> Importar y Exportar Base de Datos</span>
            </div>
            <div class="card-body">
                <div class="row justify-content-center mb-4">
                    <div class="col-md-6 text-center">
                        <a href="../config/exportar_db.php" class="btn btn-success mb-3">
                            <i class="fas fa-download"></i> Descargar Respaldo (.sql)
                        </a>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <form action="../config/importar_db.php" method="POST" enctype="multipart/form-data" class="text-center" onsubmit="return validateFormImportBD()">
                            <div class="form-group mb-3">
                                <label for="archivoBD" class="form-label">Selecciona el archivo .sql para importar:</label>
                                <input type="file" name="archivoBD" id="archivoBD" class="form-control" accept=".sql">
                            </div>
                            <button type="submit" class="btn btn-primary mt-2">
                                <i class="fas fa-upload"></i> Importar Base de Datos
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-footer text-muted text-center">
                Puedes descargar un respaldo de la base de datos o restaurarla desde un archivo .sql.
            </div>
        </div>
    </div>
<?php require_once("../models/footer.php"); ?>
