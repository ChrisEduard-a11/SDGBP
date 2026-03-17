<script>
function generarCodigoAleatorio(longitud) {
    const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let codigo = '';
    for (let i = 0; i < longitud; i++) {
        const indiceAleatorio = Math.floor(Math.random() * caracteres.length);
        codigo += caracteres.charAt(indiceAleatorio);
    }
    return codigo;
}

function establecerCodigoAleatorio() {
    const longitud = 10; // Puedes ajustar la longitud según tus necesidades
    const codigo = generarCodigoAleatorio(longitud);
    document.getElementById('codigo_persona').value = codigo;
}
</script>
<script>
  function actualizarFechaHora() {
    const ahora = new Date();
    
    // Formato de fecha: DD/MM/YYYY
    const dia = String(ahora.getDate()).padStart(2, '0');
    const mes = String(ahora.getMonth() + 1).padStart(2, '0'); // Los meses son de 0 a 11
    const ano = ahora.getFullYear();

    // Formato de hora: HH:MM:SS
    const horas = String(ahora.getHours()).padStart(2, '0');
    const minutos = String(ahora.getMinutes()).padStart(2, '0');
    const segundos = String(ahora.getSeconds()).padStart(2, '0');
    
    const fecha = `${dia}/${mes}/${ano}`;
    const hora = `${horas}:${minutos}:${segundos}`;
    
    document.getElementById('fecha').innerText = `${fecha} - ${hora}`;
  }
  setInterval(actualizarFechaHora, 1000); // Actualiza cada segundo
</script>

<script>
    function navigateTo(url) {
        window.location.href = url;
    }

    function confirmDelete(url) {
        // Extraer el id del usuario a eliminar de la URL
        const urlParams = new URLSearchParams(url.split('?')[1]);
        const idEliminar = urlParams.get('id');

        if (loggedUserId === superAdminId) {
            // SUPER ADMIN: Confirmación normal
            Swal.fire({
                title: 'Confirmar Eliminación',
                text: '¿Estás seguro de que deseas eliminar este registro?',
                icon: 'warning',
                showCancelButton: true,
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../acciones/delete_u.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'id=' + encodeURIComponent(idEliminar)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Eliminado',
                                text: 'El registro ha sido eliminado con éxito.',
                                icon: 'success',
                                icon: 'success',
                                confirmButtonColor: '#007bff',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message || 'No se pudo eliminar el usuario.',
                                icon: 'error',
                                icon: 'error',
                                confirmButtonColor: '#007bff'
                            });
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Cancelado',
                        text: 'La eliminación ha sido cancelada',
                        icon: 'error',
                        icon: 'error',
                        confirmButtonColor: '#007bff'
                    });
                }
            });
        } else {
            // OTRO ADMIN: Pedir clave del super admin y validar que no esté vacía
            Swal.fire({
                title: 'Autorización requerida',
                text: 'Ingresa la contraseña del Super Admin para autorizar la eliminación.',
                input: 'text',
                inputLabel: 'Contraseña del Super Admin',
                inputPlaceholder: 'Contraseña',
                inputAttributes: {
                    autocapitalize: 'off',
                    autocorrect: 'off'
                },
                showCancelButton: true,
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Autorizar y eliminar',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (!value) {
                        return 'La contraseña no puede estar vacía';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    // Enviar petición AJAX al backend para validar la clave y eliminar
                    fetch('../acciones/delete_u.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'id=' + encodeURIComponent(idEliminar) + '&clave_superadmin=' + encodeURIComponent(result.value)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Eliminado',
                                text: 'El registro ha sido eliminado con éxito.',
                                icon: 'success',
                                icon: 'success',
                                confirmButtonColor: '#007bff',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: 'Contraseña incorrecta o no autorizado.',
                                icon: 'error',
                                icon: 'error',
                                confirmButtonColor: '#007bff'
                            });
                        }
                    });
                }
            });
        }
    }

    function confirmarEliminacionCliente(idCliente) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "No podrás revertir esta acción.",
            icon: 'warning',
            showCancelButton: true,
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            cancelButtonColor: '#d33' 
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirigir al controlador de eliminación
                window.location.href = `../acciones/controlador_eliminar_cliente.php?id=${idCliente}`;
            }
        });
    }
</script>
</script>
<script>
    function confsalir(event) {
        event.preventDefault(); // Prevenir la acción por defecto del enlace
        Swal.fire({
            title: 'Confirmar Salida',
            text: '¿Estás seguro de que deseas salir?',
            icon: 'warning',
            showCancelButton: true,
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, Salir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../acciones/salir.php';
            } else {
                Swal.fire({
                    title: 'Cancelado',
                    text: 'La acción ha sido cancelada',
                    icon: 'error',
                    icon: 'error',
                    confirmButtonColor: '#007bff'
                });
            }
        });
    }
</script>
<script>
    function goback(){
        window.history.back();
    }
</script>
<script>
    function previewImage1(event) {
        const input = event.target;
        const reader = new FileReader();

        reader.onload = function () {
            const preview = document.querySelector('.profile-picture');
            if (preview) {
                preview.src = reader.result; // Actualiza la imagen de vista previa
            } else {
                console.error("No se encontró el elemento con la clase 'profile-picture'.");
            }
        };

        if (input.files && input.files[0]) {
            reader.readAsDataURL(input.files[0]);
        } else {
            console.error("No se seleccionó ningún archivo.");
        }
    }
</script>
<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('imagenPreview');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
<script>
    function copyToClipboard(elementId) {
        var copyText = document.getElementById(elementId).innerText;
        navigator.clipboard.writeText(copyText).then(function() {
            Swal.fire({
                icon: 'success',
                title: 'Texto copiado al portapapeles',
                text: 'Código: ' + copyText,
                showConfirmButton: true,
                confirmButtonText: 'OK',
                showConfirmButton: true,
                confirmButtonText: 'OK',
                confirmButtonColor: '#007bff'
            });
        }, function(err) {
            Swal.fire({
                icon: 'error',
                title: 'Error al copiar el texto',
                text: err,
                showConfirmButton: true,
                confirmButtonText: 'OK',
                showConfirmButton: true,
                confirmButtonText: 'OK',
                confirmButtonColor: '#007bff'
            });
        });
    }
</script>
<script>
    function navigateTo(url) {
        window.location.href = url;
    }
</script>
<script>
    function showProcessingAlert() {
        Swal.fire({
            title: 'Procesando',
            text: 'Por favor, espere...',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }


    function navigateTo(url) {
        showProcessingAlert();
        setTimeout(() => {
            Swal.close(); // Cerrar el modal antes de redirigir
            window.location.href = url;
        }, 1000); // Espera 1 segundo antes de redirigir
    }
</script>
<script>
        function confirmarRechazo(usuarioId) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, rechazar',
                cancelButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Crear un formulario dinámico para enviar la solicitud de rechazo
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '../acciones/aprobar.php';

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'rechazar';
                    input.value = 'true';

                    const usuarioInput = document.createElement('input');
                    usuarioInput.type = 'hidden';
                    usuarioInput.name = 'usuario_id';
                    usuarioInput.value = usuarioId;

                    form.appendChild(input);
                    form.appendChild(usuarioInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
<script>
    function confirmDelete2(categoriaId) {
        // Verificar si la categoría tiene registros anclados
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "../acciones/verificar_registros_categoria.php?categoria_id=" + categoriaId, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.tieneRegistros) {
                    Swal.fire({
                        title: 'Categoría con registros anclados',
                        text: "Esta categoría tiene registros anclados. ¿Está seguro de que desea eliminarla?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#007bff',
                        cancelButtonColor: '#d33' // Color rojo para el botón de cancelar
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "../acciones/delete_categoria.php?categoria_id=" + categoriaId;
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Confirmación de eliminación',
                        text: "¿Está seguro de que desea eliminar esta categoría? ¡No podrás revertir esto!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#007bff',
                        cancelButtonColor: '#d33' // Color rojo para el botón de cancelar
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "../acciones/delete_categoria.php?categoria_id=" + categoriaId;
                        }
                    });
                }
            }
        };
        xhr.send();
    }
</script>
<script>
function AgregarNuevoBien() {
    Swal.fire({
        icon: 'info',
        title: 'Agregar nuevo bien',
        html: `
            <input type="text" id="nuevoNombre" class="swal2-input" placeholder="Nombre del bien">
            <textarea id="nuevaDescripcion" class="swal2-textarea" placeholder="Descripción"></textarea>
            <div class="d-flex flex-column mt-3">
                <button type="button" class="btn btn-primary mb-2" id="agregarBien">Agregar</button>
                <button type="button" class="btn btn-danger" id="cerrarBien">Cerrar</button>
            </div>
        `,
        showConfirmButton: false,
        showConfirmButton: false,
        didOpen: () => {
            const popup = Swal.getPopup();
            const btnAgregar = popup.querySelector('#agregarBien');
            const btnCerrar = popup.querySelector('#cerrarBien');

            btnAgregar.addEventListener('click', () => {
                const nuevoNombre = popup.querySelector('#nuevoNombre').value.trim();
                const nuevaDescripcion = popup.querySelector('#nuevaDescripcion').value.trim();

                if (!nuevoNombre || !nuevaDescripcion) {
                    Swal.showValidationMessage('Por favor ingrese el nombre y la descripción');
                    return;
                }

                // Agregar la opción "nuevo" al select y seleccionarla (SIN sufijo " (nuevo)")
                const select = document.getElementById('nombre');
                const opcionPrev = select.querySelector('option[value="nuevo"]');
                if (opcionPrev) opcionPrev.remove();

                const option = document.createElement('option');
                option.value = 'nuevo';
                option.text = nuevoNombre; // SIN " (nuevo)"
                option.selected = true;
                option.setAttribute('data-descripcion', nuevaDescripcion);
                select.appendChild(option);

                // Guardar valores en campos ocultos (crearlos si no existen)
                let inputNombre = document.getElementById('nuevo_nombre');
                if (!inputNombre) {
                    inputNombre = document.createElement('input');
                    inputNombre.type = 'hidden';
                    inputNombre.id = 'nuevo_nombre';
                    inputNombre.name = 'nuevo_nombre';
                    select.parentNode.appendChild(inputNombre);
                }
                inputNombre.value = nuevoNombre;

                let inputDesc = document.getElementById('nueva_descripcion');
                if (!inputDesc) {
                    inputDesc = document.createElement('input');
                    inputDesc.type = 'hidden';
                    inputDesc.id = 'nueva_descripcion';
                    inputDesc.name = 'nueva_descripcion';
                    select.parentNode.appendChild(inputDesc);
                }
                inputDesc.value = nuevaDescripcion;

                // Rellenar textarea de descripción visible con la descripción real
                const descTextarea = document.getElementById('descripcion');
                if (descTextarea) descTextarea.value = nuevaDescripcion;

                Swal.close();
            });

            if (btnCerrar) btnCerrar.addEventListener('click', () => Swal.close());
        }
    });
}

function autocompletarDescripcion() {
    const select = document.getElementById('nombre');
    const descripcion = select.options[select.selectedIndex]?.getAttribute('data-descripcion');
    const descField = document.getElementById('descripcion');
    if (descField) descField.value = descripcion || '';
}
</script>

<!--AJAX LISTA BINES-->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const categoriaSelect = document.getElementById('categoria');
        const nombreBienSelect = document.getElementById('nombre_bien');
        const bienesList = document.getElementById('bienes-list');

        // Cargar nombres de bienes al seleccionar una categoría
        categoriaSelect.addEventListener('change', function () {
            const categoriaId = this.value;

            if (categoriaId) {
                fetch(`../acciones/obtener_nombres_bienes.php?categoria_id=${categoriaId}`)
                    .then(response => response.json())
                    .then(data => {
                        nombreBienSelect.innerHTML = '<option value="">Seleccione un nombre</option>';
                        data.forEach(nombre => {
                            const option = document.createElement('option');
                            option.value = nombre;
                            option.textContent = nombre;
                            nombreBienSelect.appendChild(option);
                        });
                        nombreBienSelect.disabled = false;
                    });
            } else {
                nombreBienSelect.innerHTML = '<option value="">Seleccione un nombre</option>';
                nombreBienSelect.disabled = true;
                bienesList.innerHTML = '<tr><td colspan="9" class="text-center">Seleccione una categoría y un nombre para ver los bienes.</td></tr>';
            }
        });

        // Cargar bienes al seleccionar un nombre
        nombreBienSelect.addEventListener('change', function () {
            const categoriaId = categoriaSelect.value;
            const nombreBien = this.value;

            if (categoriaId && nombreBien) {
                fetch(`../acciones/obtener_bienes.php?categoria_id=${categoriaId}&nombre_bien=${nombreBien}`)
                    .then(response => response.json())
                    .then(data => {
                        bienesList.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach((bien, index) => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${index + 1}</td>
                                    <td>${bien.nombre}</td>
                                    <td>${bien.descripcion}</td>
                                    <td>${bien.categoria}</td>
                                    <td>${bien.codigo}</td>
                                    <td>${bien.serial}</td>
                                    <td>${bien.fecha_adquisicion}</td>
                                    <td>
                                        <a href="../fpdf/etiqueta_bien.php?id=${bien.id}" class="btn btn-outline-success btn-sm d-flex align-items-center justify-content-center" target="_blank">
                                            <i class="fas fa-tag me-1"></i> Generar
                                        </a>
                                    </td>
                                    <td>
                                        <button class="btn btn-danger btn-sm d-flex align-items-center justify-content-center" onclick="eliminarBien(${bien.id})">
                                            <i class="fas fa-trash-alt me-1"></i> Borrar
                                        </button>
                                    </td>
                                `;
                                bienesList.appendChild(row);
                            });
                        } else {
                            bienesList.innerHTML = '<tr><td colspan="9" class="text-center">No se encontraron bienes para los filtros seleccionados.</td></tr>';
                        }
                    });
            } else {
                bienesList.innerHTML = '<tr><td colspan="9" class="text-center">Seleccione una categoría y un nombre para ver los bienes.</td></tr>';
            }
        });

        // Función para eliminar un bien con SweetAlert2
        window.eliminarBien = function (id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás revertir esta acción.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../acciones/eliminar_bien.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: id }), // Enviar el ID en formato JSON
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                title: 'Eliminado',
                                text: data.message,
                                icon: 'success',
                                icon: 'success',
                                confirmButtonColor: '#3085d6' // Color del botón de confirmación
                            });
                                // Recargar la lista de bienes
                                const categoriaId = document.getElementById('categoria').value;
                                const nombreBien = document.getElementById('nombre_bien').value;
                                if (categoriaId && nombreBien) {
                                    fetch(`../acciones/obtener_bienes.php?categoria_id=${categoriaId}&nombre_bien=${nombreBien}`)
                                        .then(response => response.json())
                                        .then(data => {
                                            const bienesList = document.getElementById('bienes-list');
                                            bienesList.innerHTML = '';
                                            if (data.length > 0) {
                                                data.forEach((bien, index) => {
                                                    const row = document.createElement('tr');
                                                    row.innerHTML = `
                                                        <td>${index + 1}</td>
                                                        <td>${bien.nombre}</td>
                                                        <td>${bien.descripcion}</td>
                                                        <td>${bien.categoria}</td>
                                                        <td>${bien.codigo}</td>
                                                        <td>${bien.serial}</td>
                                                        <td>${bien.fecha_adquisicion}</td>
                                                        <td>
                                                            <a href="../fpdf/etiqueta_bien.php?id=${bien.id}" class="btn btn-outline-success btn-sm d-flex align-items-center justify-content-center" target="_blank">
                                                                <i class="fas fa-tag me-1"></i> Generar Etiqueta
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center" onclick="eliminarBien(${bien.id})">
                                                                <i class="fas fa-trash-alt me-1"></i> Borrar
                                                            </button>
                                                        </td>
                                                    `;
                                                    bienesList.appendChild(row);
                                                });
                                            } else {
                                                bienesList.innerHTML = '<tr><td colspan="9" class="text-center">No se encontraron bienes para los filtros seleccionados.</td></tr>';
                                            }
                                        });
                                }
                            } else {
                                Swal.fire(
                                    'Error',
                                    data.message,
                                    'error'
                                );
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire(
                                'Error',
                                'Ocurrió un error al intentar eliminar el bien.',
                                'error'
                            );
                        });
                }
            });
        };
    });
</script>
<script>
    function confirmarEliminacion(archivoUrl, archivoNombre) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `El archivo "${archivoNombre}" será eliminado permanentemente.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor:  '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirigir a la acción de eliminar
                window.location.href = `../acciones/eliminar_comprobante.php?archivo=${archivoUrl}`;
            }
        });
    }
</script>
<script>
    <?php include('config.php'); ?>
    // Tiempo restante de la sesión en segundos
    var timeRemaining = <?php echo $time_remaining; ?>;

    // Mostrar alerta 20 segundos antes de que la sesión expire
    if (timeRemaining > 20) {
        setTimeout(function() {
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia',
                text: 'Tu sesión está a punto de expirar. ¿Deseas extenderla?',
                confirmButtonText: 'Extender sesión',
                showCancelButton: true,
                cancelButtonText: 'Salir',
                allowOutsideClick: false,
                allowOutsideClick: false,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#d33' // Color rojo para el botón de cancelar
            }).then((result) => {
                if (result.isConfirmed) {
                    // Extender la sesión
                    fetch('../acciones/extender_sesion.php')
                        .then(response => {
                            if (response.ok) {
                                location.reload(); // Recargar la página para extender la sesión
                            }
                        });
                } else {
                    // Redirigir a salir.php si el usuario no extiende la sesión
                    window.location.href = '../acciones/salir.php';
                }
            });
        }, (timeRemaining - 20) * 1000); // Mostrar la alerta 20 segundos antes de que expire
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Tu sesión está a punto de expirar. ¿Deseas extenderla?',
            confirmButtonText: 'Extender sesión',
            showCancelButton: true,
            cancelButtonText: 'Salir',
            allowOutsideClick: false,
            allowOutsideClick: false,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#d33' // Color rojo para el botón de cancelar
        }).then((result) => {
            if (result.isConfirmed) {
                // Extender la sesión
                fetch('../acciones/extender_sesion.php')
                    .then(response => {
                        if (response.ok) {
                            location.reload(); // Recargar la página para extender la sesión
                        }
                    });
            } else {
                // Redirigir a salir.php si el usuario no extiende la sesión
                window.location.href = '../acciones/salir.php';
            }
        });
    }

    // Redirigir a salir.php cuando la sesión expire
    setTimeout(function() {
        window.location.href = '../acciones/salir.php';
    }, timeRemaining * 1000);
</script>
<script>
    function verMotivoRechazo(motivo) {
        Swal.fire({
            title: 'Motivo del Rechazo:',
            text: motivo ? motivo : 'No se proporcionó un motivo.',
            icon: 'info',
            confirmButtonText: 'Cerrar',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
        });
    }
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.campo-monto').forEach(function(input) {
        input.addEventListener('input', function(e) {
            // Elimina comas y puntos de miles
            this.value = this.value.replace(/,/g, '').replace(/(\d+)\.(?=\d{3,})/g, '$1');
        });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('btn-confirmar-aprobar').onclick = function() {
        const comisionInput = document.getElementById('modal-comision');
        const comision = comisionInput.value.trim();

        // Validación: solo permitir vacío o número positivo
        if (comision !== "" && (isNaN(comision) || parseFloat(comision) < 0)) {
            Swal.fire({
                icon: 'error',
                title: 'Comisión inválida',
                text: 'Ingrese una comisión válida (mayor o igual a 0) o deje el campo vacío.',
                background: '#1c1e21',
                color: '#ffffff'
            });
            comisionInput.focus();
            return;
        }

        // Aquí sigue tu lógica para enviar el formulario...
        // Por ejemplo:
        // document.getElementById(`comision-${idPagoSeleccionado}`).value = comision ? comision : 0;
        // document.getElementById(`form-aprobar-${idPagoSeleccionado}`).submit();
    };
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let idPagoSeleccionado = null;

    // Hacer la función global para que el onclick del botón la encuentre
    window.abrirModalAprobar = function(id, monto, referencia) {
        if (!puedeAprobarORechazar(id)) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Debe aprobar o rechazar primero los pagos anteriores.',
                confirmButtonColor: '#007bff'
            });
            return;
        }

        idPagoSeleccionado = id;
        document.getElementById('modal-monto').textContent = parseFloat(monto).toLocaleString('es-VE', {minimumFractionDigits: 2}) + " Bs";
        document.getElementById('modal-referencia').textContent = referencia;
        document.getElementById('modal-comision').value = "";
        let modal = new bootstrap.Modal(document.getElementById('modalAprobarPago'));
        modal.show();
    };

    // Evento para el botón de aprobar dentro del modal
    document.getElementById('btn-confirmar-aprobar').onclick = function() {
        const comision = document.getElementById('modal-comision').value;
        if (comision && parseFloat(comision) < 0) {
            Swal.fire({
                icon: 'error',
                title: 'Comisión inválida',
                text: 'La comisión no puede ser negativa.',
                confirmButtonColor: '#007bff'
            });
            return;
        }
        Swal.fire({
            title: '¿Estás seguro de aprobar este pago?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, aprobar',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`comision-${idPagoSeleccionado}`).value = comision ? comision : 0;
                document.getElementById(`form-aprobar-${idPagoSeleccionado}`).submit();
            }
        });
        // Cierra el modal Bootstrap
        bootstrap.Modal.getInstance(document.getElementById('modalAprobarPago')).hide();
    };

    // Función para rechazar pago
    window.rechazarPago = function(id) {
        if (!puedeAprobarORechazar(id)) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Debe aprobar o rechazar primero los pagos anteriores.',
                confirmButtonColor: '#007bff'
            });
            return;
        }

        Swal.fire({
            title: 'Rechazar Pago',
            input: 'textarea',
            inputLabel: 'Motivo del rechazo',
            inputPlaceholder: 'Escribe el motivo del rechazo aquí...',
            inputAttributes: {
                'aria-label': 'Motivo del rechazo'
            },
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Rechazar',
            cancelButtonText: 'Cancelar',
            preConfirm: (descripcion) => {
                if (!descripcion) {
                    Swal.showValidationMessage('Debes ingresar un motivo para rechazar el pago');
                }
                return descripcion;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`descripcion-${id}`).value = result.value;
                document.getElementById(`form-rechazar-${id}`).submit();
            }
        });
    };

    // Función para validar si se puede aprobar o rechazar
    window.puedeAprobarORechazar = function(idActual) {
        var tabla = document.getElementById('datatablesSimple');
        if (!tabla) return true;
        var filas = tabla.querySelectorAll('tbody tr');
        for (var i = 0; i < filas.length; i++) {
            var inputId = filas[i].querySelector('input[name="id"]');
            if (!inputId) continue;
            var idFila = inputId.value;
            if (idFila == idActual) {
                return true;
            }
            var btnAprobar = filas[i].querySelector('button[id^="btn-aprobar-"]');
            if (btnAprobar && !btnAprobar.disabled) {
                return false;
            }
        }
        return true;
    };
});
</script>
<script>
window.addEventListener('offline', function() {
    var audio = new Audio('../error/validation_error.mp3');
    audio.play();
    toastr.error("No hay conexión a internet. Algunas funciones pueden no estar disponibles.", "Sin conexión");
});

window.addEventListener('online', function() {
    toastr.success("Conexión a internet restablecida.", "Conectado");
});
</script>
<script>
    //Bloquear F12, Ctrl+Shift+I, Ctrl+U y clic derecho
    document.addEventListener('keydown', function(e) {
         if (
             e.key === 'F12' ||
             (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === 'i') ||
             (e.ctrlKey && e.key.toLowerCase() === 'u')
         ) {
             e.preventDefault();
             return false;
         }
    });
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });
</script>
<script>
function showSuperAdminModal(idUsuario) {
    document.getElementById('usuarioEliminar').value = idUsuario;
    var modal = new bootstrap.Modal(document.getElementById('superAdminModal'));
    modal.show();
}

const superAdminForm = document.getElementById('superAdminForm');
if (superAdminForm) {
    superAdminForm.addEventListener('submit', function(e) {
        e.preventDefault();
        var idUsuario = document.getElementById('usuarioEliminar').value;
        var clave = document.getElementById('claveSuperAdmin').value;

        fetch('../acciones/delete_u.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + encodeURIComponent(idUsuario) + '&clave_superadmin=' + encodeURIComponent(clave)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Contraseña incorrecta o no autorizado');
            }
        });
    });
}
</script>
<script>
function cargarSaldoUPUPorMes(idUsuario) {
    document.getElementById('idUpuSaldo').value = idUsuario;
    document.getElementById('resultadoSaldoMes').innerHTML = '';
    document.getElementById('mesFiltro').value = ''; // Limpia el mes seleccionado
}

// Captura el submit del formulario del modal
document.addEventListener('DOMContentLoaded', function() {
    const formFiltroMes = document.getElementById('formFiltroMes');
    if (formFiltroMes) {
        formFiltroMes.addEventListener('submit', function(e) {
            e.preventDefault();
            var mes = document.getElementById('mesFiltro').value;
            var idUsuario = document.getElementById('idUpuSaldo').value;
            var resultado = document.getElementById('resultadoSaldoMes');
            resultado.innerHTML = 'Cargando...';

            // AJAX para consultar el saldo por mes
            fetch('../acciones/obtener_saldo_mes.php?mes=' + mes + '&id=' + idUsuario)
                .then(response => response.text())
                .then(data => {
                    resultado.innerHTML = data;
                })
                .catch(() => {
                    resultado.innerHTML = 'Error al obtener el saldo.';
                });
        });
    }
});
</script>
<script>
    $(function(){
        $('#mesFiltro').datepicker({
            format: "yyyy-mm",
            startView: "months", 
            minViewMode: "months",
            language: "es",
            autoclose: true
        });
    });
</script>
<script>
function confirmDeleteProducto(url) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'El producto será eliminado permanentemente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}
</script>
<script>
    function toggleBloqueo(idUsuario, bloquear) {
    const accion = bloquear ? 'bloquear' : 'desbloquear';
    Swal.fire({
        title: (bloquear ? '¿Bloquear usuario?' : '¿Desbloquear usuario?'),
        text: '¿Estás seguro que deseas ' + accion + ' este usuario?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, ' + accion,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../acciones/bloquear_usuario.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id_usuario=' + encodeURIComponent(idUsuario) + '&bloquear=' + encodeURIComponent(bloquear)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Listo',
                        text: 'El usuario ha sido actualizado.',
                        icon: 'success',
                        confirmButtonColor: '#3085d6'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message || 'No se pudo actualizar el usuario.', 'error');
                }
            });
        }
    });
}
</script>
<script>
function confirmDeleteCategoria(url) {
    Swal.fire({
        title: '¿Eliminar categoría?',
        text: 'Esta acción no se puede deshacer. ¿Desea continuar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}
</script>