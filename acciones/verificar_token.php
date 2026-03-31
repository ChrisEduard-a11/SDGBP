<?php
session_start();

if (isset($_POST['token'])) {
    $token = $_POST['token'];
    
    if (empty($token) || !isset($_SESSION['form_tokens'][$token])) {
        echo "invalido";
    } else {
        echo "valido";
    }
} else {
    echo "invalido";
}
?>
