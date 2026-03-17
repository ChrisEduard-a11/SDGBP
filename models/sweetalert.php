<?php if (isset($_SESSION["estatus"]) && isset($_SESSION["mensaje"])): ?>
    <script>
        Swal.fire({
            icon: '<?php echo $_SESSION["estatus"]; ?>',
            title: '<?php echo $_SESSION["mensaje"]; ?>',
            showConfirmButton: true,
            confirmButtonText: 'OK',
            confirmButtonText: 'OK',
            confirmButtonColor: '#007bff'
        });
    </script>
 <?php
// Limpiar las variables de sesión después de mostrar la alerta
    unset($_SESSION["estatus"]);
    unset($_SESSION["mensaje"]);
?>                
<?php endif; ?>

<?php if (isset($_SESSION['type']) && isset($_SESSION['alert'])): ?>
    <script>
        Swal.fire({
            title: '<?php echo $_SESSION["alert"]; ?>',
            text: '',
            imageUrl: '<?php echo $_SESSION["foto"]; ?>',
            imageWidth: 100,
            imageHeight: 100,
            imageAlt: 'Foto de perfil',
            imageAlt: 'Foto de perfil',
            confirmButtonColor: '#007bff',
            confirmButtonText: 'Continuar',
            customClass: {
                image: 'rounded-circle'
            }
        });
    </script>
    <?php
    // Limpiar las variables de sesión después de mostrar la alerta
    unset($_SESSION['type']);
    unset($_SESSION['alert']);
    ?>
<?php endif; ?>
