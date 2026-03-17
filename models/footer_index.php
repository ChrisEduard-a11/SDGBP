
    <script>
    document.addEventListener('click', function (e) {
        // Localizamos el botón submit
        const boton = e.target.closest('button[type="submit"], input[type="submit"]');
        
        if (boton) {
            const formulario = boton.form;

            // Si el formulario no es válido según el navegador, no hacemos nada
            if (formulario && !formulario.checkValidity()) {
                return; 
            }

            // Si llegamos aquí, el formulario parece estar bien
            const textoOriginal = boton.innerHTML || boton.value;
            
            // Bloqueamos el botón temporalmente
            setTimeout(() => {
                boton.disabled = true;
                if (boton.tagName === 'INPUT') {
                    boton.value = "Procesando...";
                } else {
                    boton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                }
                boton.style.opacity = "0.7";
            }, 50);

            // --- LA SOLUCIÓN AL PROBLEMA ---
            // Si después de 2 segundos la página NO se ha recargado (lo que significa que 
            // hubo un error de validación de Toastr o similar), reactivamos el botón.
            setTimeout(() => {
                if (boton.disabled) {
                    boton.disabled = false;
                    if (boton.tagName === 'INPUT') {
                        boton.value = textoOriginal;
                    } else {
                        boton.innerHTML = textoOriginal;
                    }
                    boton.style.opacity = "1";
                    console.log("Botón reactivado: Se detectó que el formulario no se envió.");
                }
            }, 2000); // 2 segundos es suficiente para que el usuario vea el error
        }
    });
    </script>
    </body>
</html>