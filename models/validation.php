<script>
    function validateForm() {
        var cedula = document.forms[0]["cedula"].value;
        var codigo = document.forms[0]["codigo"].value;
        if (cedula == "" || codigo == "") {
            alertify.error("Todos los campos son obligatorios");
            return false;
        }
        return true;
    }
</script> 
<script>
setInterval(function() {
    fetch('../models/verificar_token.php')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'logout') {
                Swal.fire({
                    title: 'Sesión cerrada',
                    text: 'Tu sesión ha sido cerrada porque iniciaste sesión en otro dispositivo o navegador.',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#d33'
                }).then(() => {
                    window.location.href = '../vistas/login.php?msg=Sesion%20cerrada%20por%20otro%20inicio';
                });
            }
        });
}, 8000); // Verifica cada 8 segundos
</script>