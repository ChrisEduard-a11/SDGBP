<?php
require_once("../models/header.php");

// Obtener el parámetro de la URL
$seccion_anterior = isset($_GET['seccion']) ? $_GET['seccion'] : 'Página Anterior';
?>
<div id="layoutSidenav_content">
    <div class="container-fluid px-4">
        <h3 class="mt-4">Donaciones</h3>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="javascript:history.back()"><?php echo htmlspecialchars($seccion_anterior); ?></a></li>
            <li class="breadcrumb-item active">Donaciones</li>
        </ol>
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-donate me-1"></i>
                Métodos de Pago
            </div>
            <div class="card-body">
                <p>Si deseas apoyar el desarrollo de este sistema, puedes realizar una donación a través de los siguientes métodos de pago:</p>
                <div class="list-group">
                    <div class="list-group-item">
                        <h5 class="mb-1"><i class="fas fa-coins"></i> Pago Movil (BS.S)</h5>
                        <p class="mb-1">Puedes realizar una donación a través de Pago Movil utilizando los siguientes datos:</p>
                        <h6>Bancos:</h6>
                        <p>- <b>Banco de Venezuela</b></p>
                        <p>- <b>Banco Banesco</b></p>
                        <p>- <b>Banco del Tesoro</b></p>
                        <p>Cedula:</p><code id="cedula">28651980</code>
                        <button class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard('cedula')"><i class="fas fa-copy"></i></button>
                        <p>Telefono:</p><code id="telefono">0412-9796940</code>
                        <button class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard('telefono')"><i class="fas fa-copy"></i></button>
                    </div>
                    <div class="list-group-item">
                        <h5 class="mb-1"><i class="fab fa-paypal"></i> PayPal</h5>
                        <p class="mb-1">Puedes realizar una donación a través de PayPal utilizando el siguiente enlace:</p>
                        <a href="https://paypal.me/cristianarcaya?country.x=VE&locale.x=es_XC" target="_blank" class="btn btn-primary">Donar con PayPal</a>
                    </div>
                    <div class="list-group-item">
                        <h5 class="mb-1"><i class="fas fa-coins"></i> Binance Pay</h5>
                        <p class="mb-1">Puedes realizar una donación a través de Binance Pay utilizando la siguiente UID:</p>
                        <code id="binanceUID">524941464</code>
                        <button class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard('binanceUID')"><i class="fas fa-copy"></i></button>
                    </div>
                    <div class="list-group-item">
                        <h5 class="mb-1"><i class="fab fa-bitcoin"></i> Bitcoin</h5>
                        <p class="mb-1">Puedes realizar una donación a través de Bitcoin utilizando la siguiente dirección:</p>
                        <code id="bitcoinAddress">15MaBMyg3PVvFYU1tMgYk2oqACMUhaUZPf</code>
                        <button class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard('bitcoinAddress')"><i class="fas fa-copy"></i></button>
                    </div>
                    <div class="list-group-item">
                        <h5 class="mb-1"><i class="fab fa-ethereum"></i> Ethereum</h5>
                        <p class="mb-1">Puedes realizar una donación a través de Ethereum (ERC20), utilizando la siguiente dirección:</p>
                        <code id="ethereumAddress">0x55bd821b08a8bf91121246a6188e45550d999335</code>
                        <button class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard('ethereumAddress')"><i class="fas fa-copy"></i></button>
                    </div>
                    <!-- Agrega más métodos de pago según sea necesario -->
                </div>
                <p class="mt-3">¡Gracias por tu apoyo!</p>
            </div>
        </div>
    </div>
<?php
require_once("../models/footer.php");
?>
