<?php
$guest_ticket = 'TICK-2674FD95';
session_start();
$_SESSION['guest_ticket_id'] = $guest_ticket;
$_GET['id_ticket'] = $guest_ticket;

require_once('obtener_mensajes.php');
