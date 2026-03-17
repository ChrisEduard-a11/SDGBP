// Cargar las categorías desde la base de datos
function cargarCategorias() {
    fetch('obtener_categorias.php')
        .then(response => response.json())
        .then(categorias => {
            const categoriaFiltro = document.getElementById('categoriaFiltro');
            categorias.forEach(categoria => {
                const option = document.createElement('option');
                option.value = categoria.id;
                option.textContent = categoria.nombre;
                categoriaFiltro.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error al cargar las categorías:', error);
        });
}

// Función para cargar productos desde el backend
function cargarProductos(categoria = '') {
    fetch('obtener_productos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ categoria }) 
    })
    .then(response => response.json())
    .then(productos => {
        const productosGrid = document.getElementById('productosGrid');
        productosGrid.innerHTML = ''; 

        if (productos.length === 0) {
            productosGrid.innerHTML = '<div class="col-12 text-center py-5"><p class="text-muted">No hay productos disponibles.</p></div>';
            return;
        }

        // Agrupamos por categoría
        const productosPorCategoria = productos.reduce((acc, producto) => {
            const catNombre = producto.categoria || 'General';
            if (!acc[catNombre]) acc[catNombre] = [];
            acc[catNombre].push(producto);
            return acc;
        }, {});

        // Creamos la visualización en Grid
        for (const catNombre in productosPorCategoria) {
            const categoriaSection = document.createElement('div');
            categoriaSection.classList.add('categoria-section', 'mb-5');

            const categoriaTitulo = `<h3 class="section-title mb-4 text-uppercase fw-bold">${catNombre}</h3>`;
            
            // Aquí generamos el grid de 4 columnas (desktop)
            const productosHTML = `
                <div class="row g-4">
                    ${productosPorCategoria[catNombre].map(producto => {
                        const precio = parseFloat(producto.precio) || 0;
                        return `
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 product-container">
                                <div class="card h-100 shadow-sm border-0 product-card">
                                    <img src="${producto.imagen}" class="card-img-top" alt="${producto.nombre}" style="height: 180px; object-fit: cover;">
                                    <div class="card-body d-flex flex-column">
                                        <h6 class="card-title fw-bold">${producto.nombre}</h6>
                                        <p class="card-text small text-muted flex-grow-1">${producto.descripcion}</p>
                                        <p class="mb-1 small">Stock: <span id="stock-${producto.id}">${producto.stock}</span></p>
                                        <p class="fw-bold text-primary mb-2">$${precio.toFixed(2)}</p>
                                        <input type="number" class="form-control form-control-sm mb-2" id="cantidad-${producto.id}" min="1" max="${producto.stock}" value="1">
                                        <button class="btn btn-warning btn-sm fw-bold w-100" 
                                            onclick="agregarAlCarrito('${producto.nombre.replace(/'/g, "\\'")}', ${precio}, 'stock-${producto.id}', 'cantidad-${producto.id}', ${producto.id})">
                                            COMPRAR
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            `;

            categoriaSection.innerHTML = categoriaTitulo + productosHTML;
            productosGrid.appendChild(categoriaSection);
        }
    })
    .catch(error => console.error('Error al cargar productos:', error));
}

function filtrarProductosPorNombre() {
    const busqueda = document.getElementById('busquedaProducto').value.toLowerCase();
    const containers = document.querySelectorAll('.product-container');

    containers.forEach(container => {
        const nombre = container.querySelector('.card-title').textContent.toLowerCase();
        // Si el nombre coincide, mostramos; si no, ocultamos
        container.style.display = nombre.includes(busqueda) ? 'block' : 'none';
    });

    // Ocultamos el título de la categoría si no tiene productos visibles
    document.querySelectorAll('.categoria-section').forEach(seccion => {
        const productosVisibles = Array.from(seccion.querySelectorAll('.product-container'))
                                       .filter(p => p.style.display !== 'none');
        seccion.style.display = productosVisibles.length > 0 ? 'block' : 'none';
    });
}

// Función para agregar un producto al carrito
function agregarAlCarrito(nombre, precio, stockId, cantidadId, productoId) {
    const stockElement = document.getElementById(stockId);
    const cantidadElement = document.getElementById(cantidadId);

    if (!stockElement) return;

    const stockDisponible = parseInt(stockElement.textContent || 0);
    const cantidadSeleccionada = parseInt(cantidadElement?.value || 0);

    if (cantidadSeleccionada <= 0 || cantidadSeleccionada > stockDisponible) {
        mostrarToast('danger', 'Cantidad no válida o stock insuficiente.');
        return;
    }

    // Restamos visualmente antes de enviar al servidor
    stockElement.textContent = stockDisponible - cantidadSeleccionada;

    fetch('agregar_carrito.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nombre, precio, cantidad: cantidadSeleccionada, productoId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cargarCarritoDesdeBackend();
            actualizarCarritoBadge();
            mostrarToast('success', 'Producto añadido.');
        } else {
            stockElement.textContent = stockDisponible; // Revertimos si falla
            mostrarToast('danger', 'Error al añadir al carrito.');
        }
    })
    .catch(() => {
        stockElement.textContent = stockDisponible;
        mostrarToast('danger', 'Error de conexión.');
    });
}

// Llamar a la función al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    cargarProductos(); // Cargar todos los productos inicialmente
});

// Filtrar productos por categoría
function filtrarPorCategoria() {
    const categoria = document.getElementById('categoriaFiltro').value;
    cargarProductos(categoria); // Llamar a la función con la categoría seleccionada
}

// Filtrar productos por nombre
function filtrarProductosPorNombre() {
    const busqueda = document.getElementById('busquedaProducto').value.toLowerCase();
    const productos = document.querySelectorAll('#productosGrid .card');

    productos.forEach(producto => {
        const nombreProducto = producto.querySelector('.card-title').textContent.toLowerCase();
        if (nombreProducto.includes(busqueda)) {
            producto.parentElement.style.display = 'block'; // Mostrar el producto
        } else {
            producto.parentElement.style.display = 'none'; // Ocultar el producto
        }
    });
}

// Llamar a las funciones al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    cargarCategorias();
    cargarProductos(); // Cargar todos los productos inicialmente
});

// Array para almacenar los productos seleccionados
let carrito = [];

// Función para cargar el carrito desde el backend al cargar la página
function cargarCarritoDesdeBackend() {
    fetch('obtener_carrito.php')
        .then(response => response.json())
        .then(data => {
            carrito = data.carrito || []; // Sincronizar el array carrito con los datos del backend
            actualizarCarrito(); // Actualizar el contenido del carrito en el modal
            actualizarCarritoBadge(); // Actualizar el badge del carrito
        })
        .catch(error => {
            console.error('Error al cargar el carrito desde el backend:', error);
        });
}

// Llamar a la función al cargar la página
document.addEventListener('DOMContentLoaded', cargarCarritoDesdeBackend);

// Función para actualizar el contenido del carrito
function actualizarCarrito() {
    fetch('obtener_carrito.php')
    .then(response => response.json())
    .then(data => {
        const carritoContenido = document.getElementById('carritoContenido');
        if (data.carrito.length === 0) {
            carritoContenido.innerHTML = '<p class="text-center">Tu carrito está vacío.</p>';
        } else {
            let html = '<ul class="list-group">';
            let total = 0;
            data.carrito.forEach((producto, index) => {
                total += producto.precio * producto.cantidad;
                html += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        ${producto.nombre} - $${producto.precio.toFixed(2)} x ${producto.cantidad} unidades
                        <button class="btn btn-danger btn-sm" onclick="eliminarDelCarrito(${index})">Eliminar</button>
                    </li>
                `;
            });
            html += `</ul><p class="mt-3 text-end"><strong>Total: $${total.toFixed(2)}</strong></p>`;
            carritoContenido.innerHTML = html;
        }
        actualizarCarritoBadge();
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToast('error', 'Error al actualizar el carrito.');
        actualizarCarritoBadge();
    });
}

// Función para eliminar un producto del carrito
function eliminarDelCarrito(index) {
    // Enviar la solicitud al backend para eliminar el producto del carrito
    fetch('eliminar_carrito.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ index })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            actualizarCarrito();
            actualizarCarritoBadge();
            mostrarToast('info', 'Producto eliminado del carrito.');
        } else {
            mostrarToast('error', 'Error al eliminar el producto del carrito.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToast('error', 'Error al eliminar el producto del carrito.');
    });
}

// Función para mostrar un toast
function mostrarToast(tipo, mensaje) {
    const toastContainer = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${tipo} border-0`;
    toast.role = 'alert';
    toast.ariaLive = 'assertive';
    toast.ariaAtomic = 'true';
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${mensaje}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    toastContainer.appendChild(toast);

    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();

    // Eliminar el toast después de que desaparezca
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

// Abrir el modal de pago desde el botón del carrito
document.getElementById('btnAbrirPago').addEventListener('click', () => {
    if (carrito.length === 0) {
        mostrarToast('error', 'El carrito está vacío.');
        return;
    }

    // Calcular el monto total del carrito en dólares
    const totalUSD = carrito.reduce((acc, producto) => acc + producto.precio * producto.cantidad, 0);
    document.getElementById('monto').value = totalUSD.toFixed(2);

    // Obtener la tasa del USD y calcular el monto en bolívares
    fetch('https://api.dolarvzla.com/public/exchange-rate')
        .then(response => response.json())
        .then(data => {
            const tasaBCV = data.current.usd;
            if (!tasaBCV) {
                throw new Error('No se pudo obtener la tasa del BCV.');
            }
            const totalBs = totalUSD * tasaBCV;
            document.getElementById('montoBs').value = totalBs.toFixed(2);

            // Mostrar el modal de pago
            const pagoModal = new bootstrap.Modal(document.getElementById('pagoModal'));
            pagoModal.show();
        })
        .catch(error => {
            console.error('Error al obtener la tasa del BCV:', error);
            mostrarToast('error', 'No se pudo obtener la tasa del BCV.');
        });
});
// Manejar el cambio del método de pago
document.getElementById('metodoPago').addEventListener('change', (event) => {
const metodo = event.target.value;
const formPagoDinamico = document.getElementById('formPagoDinamico');

formPagoDinamico.innerHTML = ''; // Limpiar los campos dinámicos

if (metodo === 'Pago Móvil') {
    formPagoDinamico.innerHTML = `
        <div class="mb-1 text-center">
            <img src="../img/BDV.png" alt="Logo Pago Móvil" style="max-width: 150px; margin-bottom: 10px;">
        </div>
        <div class="mb-1">
            <ul class="list-group text-center">
            <h5>Datos del Pago Móvil</h5>
                <li class="list-group-item"><strong>Banco:</strong> Banco de Venezuela</li>
                <li class="list-group-item"><strong>Telefono:</strong> 0412-54328765</li>
                <li class="list-group-item"><strong>Titular:</strong> EURIPYS 2024, C.A.</li>
                <li class="list-group-item"><strong>RIF:</strong> G-2006357217</li>
            </ul>
        </div>
        <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono</label>
            <input type="text" class="form-control" id="telefono" name="telefono" required>
        </div>
        <div class="mb-3">
            <label for="cedula" class="form-label">Cédula</label>
            <input type="text" class="form-control" id="cedula" name="cedula" required>
        </div>
        <div class="mb-3">
            <label for="referencia" class="form-label">Referencia Bancaria</label>
            <input type="text" class="form-control" id="referencia" name="referencia" required>
        </div>
    `;
} else if (metodo === 'Transferencia') {
    formPagoDinamico.innerHTML = `
        <div class="mb-1 text-center">
                <img src="../img/BDV.png" alt="Logo Transferencia" style="max-width: 150px; margin-bottom: 10px;">
            </div>
            <div class="mb-1">
                <ul class="list-group text-center">
                <h5>Datos de la Cuenta Bancaria</h5>
                    <li class="list-group-item"><strong>Banco:</strong> Banco de Venezuela</li>
                    <li class="list-group-item"><strong>Número de Cuenta:</strong> 0102-0123-4567-8901-2345</li>
                    <li class="list-group-item"><strong>Titular:</strong> EURIPYS 2024, C.A.</li>
                    <li class="list-group-item"><strong>RIF:</strong> J-12345678-9</li>
                </ul>
            </div>
        <div class="mb-3">
            <label for="numeroCuenta" class="form-label">Número de Cuenta</label>
            <input type="text" class="form-control" id="numeroCuenta" name="numeroCuenta" required>
        </div>
        <div class="mb-3">
            <label for="referencia" class="form-label">Referencia Bancaria</label>
            <input type="text" class="form-control" id="referencia" name="referencia" required>
        </div>
    `;
}
});

// Manejar el envío del formulario de pago
document.getElementById('formPago').addEventListener('submit', (event) => {
    event.preventDefault();

    const btnEnviar = document.getElementById('enviarCompra');
    btnEnviar.disabled = true;
    btnEnviar.innerHTML = 'Procesando...';

    // Obtener los valores del formulario
    const nombreComprador = document.getElementById('nombreComprador').value;
    const correoComprador = document.getElementById('correoComprador').value;
    const telefonoComprador = document.getElementById('telefonoComprador').value;
    const monto = document.getElementById('monto').value;
    const montoBs = document.getElementById('montoBs').value;
    const montoBsPago = document.getElementById('montoBsPago').value;
    const metodoPago = document.getElementById('metodoPago').value;
    const fechaPago = document.getElementById('fechaPago')?.value || null;
    const telefono = document.getElementById('telefono')?.value || null;
    const cedula = document.getElementById('cedula')?.value || null;
    const numeroCuenta = document.getElementById('numeroCuenta')?.value || null;
    const referencia = document.getElementById('referencia')?.value || null;
    const banco = document.getElementById('banco').value;

    // --- VALIDACIONES VENEZUELA ---
    // Teléfono: 11 dígitos, inicia con 0412, 0414, 0416, 0424, 0426, 0212
    if (telefono) {
        const regexTelefono = /^(0412|0414|0416|0424|0426|0212)\d{7}$/;
        if (!regexTelefono.test(telefono)) {
            mostrarToast('error', 'El teléfono debe ser venezolano válido (ej: 04121234567).');
            btnEnviar.disabled = false;
            btnEnviar.innerHTML = 'Procesar Pago';
            return;
        }
    }
    // Cédula: solo números, 6 a 9 dígitos
    if (cedula) {
        const regexCedula = /^\d{6,9}$/;
        if (!regexCedula.test(cedula)) {
            mostrarToast('error', 'La cédula debe tener solo números (6 a 9 dígitos).');
            btnEnviar.disabled = false;
            btnEnviar.innerHTML = 'Procesar Pago';
            return;
        }
    }
    // Referencia: solo números, 6 a 20 dígitos
    if (referencia) {
        const regexReferencia = /^\d{6,20}$/;
        if (!regexReferencia.test(referencia)) {
            mostrarToast('error', 'La referencia debe tener solo números (6 a 20 dígitos).');
            btnEnviar.disabled = false;
            btnEnviar.innerHTML = 'Procesar Pago';
            return;
        }
    }
    // --- FIN VALIDACIONES ---

    // Depuración: Verificar los datos antes de enviarlos
    console.log({
        carrito,
        nombreComprador,
        correoComprador,
        telefonoComprador,
        monto,
        montoBsPago,
        montoBs,
        metodoPago,
        fechaPago,
        telefono,
        cedula,
        numeroCuenta,
        referencia,
        banco
    });

    // Enviar los datos al backend
    fetch('procesar_pago.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            carrito,
            nombreComprador,
            correoComprador,
            telefonoComprador,
            monto,
            montoBsPago,
            montoBs,
            metodoPago,
            fechaPago,
            telefono,
            cedula,
            numeroCuenta,
            referencia,
            banco
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            mostrarToast('success', data.message);
            carrito = []; // Vaciar el carrito
            actualizarCarrito();
            const pagoModal = bootstrap.Modal.getInstance(document.getElementById('pagoModal'));
            pagoModal.hide(); // Cerrar el modal de pago
        } else {
            mostrarToast('error', data.message || 'Error al procesar el pago.');
            btnEnviar.disabled = false;
            btnEnviar.innerHTML = 'Procesar Pago';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToast('error', 'Error al procesar el pago. Inténtalo de nuevo.');
        btnEnviar.disabled = false;
        btnEnviar.innerHTML = 'Procesar Pago';
    });
});
// Bloquear F12, Ctrl+Shift+I, Ctrl+U y clic derecho
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

