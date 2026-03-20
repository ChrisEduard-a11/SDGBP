const API_URL = 'api.php';
let carrito = [];
let TASA_BCV = null;

document.addEventListener('DOMContentLoaded', async () => {
    ObtenerTasaBCV();
    await CargarCategorias();
    await CargarProductos();
    await CargarCarrito();
    ConfigurarFlujoCheckout();
});

// -------------------------------------------------------------
// NATIVE TAILWIND TOAST SYSTEM
// -------------------------------------------------------------
function MostrarToast(tipo, mensaje) {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    
    // Config colors based on type
    let colorClasses = '';
    let icon = '';
    
    if(tipo === 'danger') {
        colorClasses = 'bg-white border-l-4 border-red-500 text-slate-800';
        icon = '<i class="fas fa-exclamation-circle text-red-500 text-xl"></i>';
    } else if (tipo === 'success') {
        colorClasses = 'bg-white border-l-4 border-emerald-500 text-slate-800';
        icon = '<i class="fas fa-check-circle text-emerald-500 text-xl"></i>';
    } else if (tipo === 'warning') {
        colorClasses = 'bg-slate-800 border-l-4 border-amber-400 text-white';
        icon = '<i class="fas fa-radiation text-amber-400 text-xl"></i>';
    } else {
        colorClasses = 'bg-brand-600 border border-brand-500 text-white';
        icon = '<i class="fas fa-info-circle text-white text-xl"></i>';
    }

    const toast = document.createElement('div');
    toast.className = `flex items-center w-full max-w-sm p-4 rounded-lg shadow-[0_10px_40px_rgba(0,0,0,0.15)] pointer-events-auto transform transition-all duration-300 translate-y-10 opacity-0 ${colorClasses}`;
    
    toast.innerHTML = `
        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg bg-transparent">
            ${icon}
        </div>
        <div class="ml-3 text-sm font-semibold tracking-wide">${mensaje}</div>
        <button type="button" class="ml-auto -mx-1.5 -my-1.5 rounded-lg focus:ring-2 focus:ring-slate-300 p-1.5 inline-flex items-center justify-center h-8 w-8 text-slate-400 hover:text-slate-600 hover:bg-slate-100" onclick="this.parentElement.remove()">
            <span class="sr-only">Cerrar</span>
            <i class="fas fa-times"></i>
        </button>
    `;
    
    container.appendChild(toast);
    
    // Animate in
    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-10', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
    });

    // Auto remove
    setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('opacity-0', 'scale-95');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// -------------------------------------------------------------
// RUTINAS GLOBALES
// -------------------------------------------------------------
async function ObtenerTasaBCV() {
    try {
        const r = await fetch('https://ve.dolarapi.com/v1/dolares/oficial');
        const j = await r.json();
        TASA_BCV = parseFloat(j.promedio || j.venta);
        if(!TASA_BCV) throw new Error("API vacía");
        document.getElementById('tasaBcvValue').innerHTML = `<strong>${TASA_BCV.toFixed(2)} Bs/USD</strong>`;
    } catch(e) {
        document.getElementById('tasaBcvValue').innerHTML = `<span class="text-amber-400">Portal BCV Caído - Monto Fijo</span>`;
    }
}

function Navegar(vista) {
    document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
    
    if(vista === 'catalogo') {
        document.getElementById('viewCatalogo').classList.add('active');
        window.scrollTo({top:0, behavior:'smooth'});
    } 
    else if(vista === 'carrito') {
        if(carrito.length === 0) return MostrarToast('warning', 'Tu requisición actual está vacía.');
        document.getElementById('viewCarrito').classList.add('active');
        RenderResumenCarrito();
        window.scrollTo({top:0, behavior:'smooth'});
    }
    else if(vista === 'checkout') {
        if(carrito.length === 0) return Navegar('catalogo');
        if(!TASA_BCV) return MostrarToast('warning', 'Calculando BCV. Intente en unos segundos.');
        document.getElementById('viewCheckout').classList.add('active');
        RenderResumenCheckout();
        window.scrollTo({top:0, behavior:'smooth'});
    }
}

// -------------------------------------------------------------
// CATÁLOGO
// -------------------------------------------------------------
async function CargarCategorias() {
    try {
        const res = await fetch(`${API_URL}?action=get_categorias`);
        const json = await res.json();
        if (json.status !== 'success') return;
        const c = document.getElementById('pillsContainer');
        if(!c) return;
        
        const cssBase = "px-6 py-2.5 rounded-full text-sm font-semibold whitespace-nowrap cursor-pointer transition-all duration-300 border category-pill ";
        const cssActive = "bg-slate-900 border-slate-900 text-white shadow-md active-pill";
        const cssInactive = "bg-white border-slate-200 text-slate-600 hover:border-slate-400 hover:text-slate-900";

        c.innerHTML = `<div class="${cssBase} ${cssActive}" onclick="FiltrarPorCategoria(this, '')">Todas las Áreas</div>`;
        
        json.data.forEach(cat => {
            const div = document.createElement('div');
            div.className = `${cssBase} ${cssInactive}`;
            div.textContent = cat.nombre;
            div.onclick = function() { FiltrarPorCategoria(this, cat.id) };
            c.appendChild(div);
        });
    } catch (e) {}
}

function FiltrarPorCategoria(elemento, catId) {
    document.querySelectorAll('.category-pill').forEach(p => {
        p.className = p.className.replace('bg-slate-900 border-slate-900 text-white shadow-md active-pill', 'bg-white border-slate-200 text-slate-600 hover:border-slate-400 hover:text-slate-900');
    });
    if(elemento) {
        elemento.className = elemento.className.replace('bg-white border-slate-200 text-slate-600 hover:border-slate-400 hover:text-slate-900', 'bg-slate-900 border-slate-900 text-white shadow-md active-pill');
    }
    CargarProductos(catId);
}

function FiltrarBusquedaGlobal() {
    const q = document.getElementById('busquedaProducto')?.value.toLowerCase() || '';
    document.querySelectorAll('.product-container').forEach(c => {
        const nom = c.getAttribute('data-nombre');
        c.style.display = nom.includes(q) ? 'block' : 'none';
    });
    document.querySelectorAll('.categoria-section').forEach(s => {
        const vis = Array.from(s.querySelectorAll('.product-container')).filter(p => p.style.display !== 'none');
        s.style.display = vis.length ? 'block' : 'none';
    });
}

async function CargarProductos(catId = '') {
    const grid = document.getElementById('productosGrid');
    if (!grid) return;
    try {
        grid.innerHTML = '<div class="text-center py-20"><i class="fas fa-circle-notch fa-spin text-4xl text-brand-500"></i></div>';
        const res = await fetch(`${API_URL}?action=get_productos&categoria=${catId}`);
        const json = await res.json();
        if (json.status !== 'success') throw new Error();
        if (json.data.length === 0) { grid.innerHTML = '<div class="text-center py-20 text-slate-400 font-medium text-lg">Catálogo virtual vacío.</div>'; return; }

        const porCat = {};
        json.data.forEach(p => { const n = p.categoria || 'Catálogo'; if(!porCat[n]) porCat[n]=[]; porCat[n].push(p); });

        grid.innerHTML = '';
        for (const cat in porCat) {
            let html = `
            <div class="categoria-section">
                <div class="flex items-center mb-8 mt-12">
                    <h4 class="text-xl md:text-2xl font-display font-extrabold text-slate-900 tracking-tight uppercase">${cat}</h4>
                    <div class="ml-4 flex-grow h-px bg-slate-200"></div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">`;
            
            porCat[cat].forEach(p => {
                const precio = parseFloat(p.precio) || 0;
                html += `
                    <div class="product-container flex" data-nombre="${p.nombre.toLowerCase().replace(/"/g, '')}">
                        <div class="group flex flex-col w-full bg-white rounded-2xl border border-slate-100 shadow-[0_4px_20px_rgb(0,0,0,0.03)] hover:shadow-[0_20px_40px_rgb(0,0,0,0.08)] hover:-translate-y-1 transition-all duration-300 overflow-hidden relative">
                            <!-- Image -->
                            <div class="h-48 p-6 flex justify-center items-center bg-white border-b border-slate-50 relative overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                <img src="${p.imagen}" class="max-h-full object-contain mix-blend-multiply group-hover:scale-105 transition-transform duration-500">
                            </div>
                            <!-- Content -->
                            <div class="p-6 flex flex-col flex-grow">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="text-slate-900 font-semibold text-[15px] leading-snug line-clamp-2">${p.nombre}</h3>
                                </div>
                                <p class="text-slate-500 text-xs mb-4 line-clamp-2 flex-grow">${p.descripcion}</p>
                                
                                <div class="mb-4">
                                    <span class="text-2xl font-display font-black text-slate-900">$${precio.toFixed(2)}</span>
                                </div>
                                
                                <!-- Meta actions -->
                                <div class="flex items-center gap-3 mt-auto pt-4 border-t border-slate-100">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] uppercase font-bold text-slate-400 mb-1 text-center">Inv: <span id="s_${p.id}">${parseInt(p.stock)||0}</span></span>
                                        <input type="number" id="c_${p.id}" value="1" min="1" max="${p.stock}" class="w-14 h-10 text-center border border-slate-200 rounded-lg text-sm font-semibold focus:ring-2 focus:ring-brand-500 outline-none transition-shadow bg-slate-50">
                                    </div>
                                    <button onclick="AgregarC(${p.id}, '${p.nombre.replace(/'/g, "\\'")}', ${precio})" class="flex-grow h-10 bg-slate-900 hover:bg-brand-600 text-white rounded-lg text-sm font-bold flex items-center justify-center gap-2 transition-colors shadow-sm focus:ring-4 focus:ring-brand-500/30 outline-none">
                                        <i class="fas fa-plus"></i> Añadir
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>`;
            });
            html += `</div></div>`;
            const div = document.createElement('div');
            div.innerHTML = html;
            grid.appendChild(div.firstElementChild);
        }
    } catch (e) { grid.innerHTML = '<div class="text-center py-20 text-red-500"><i class="fas fa-wifi text-3xl mb-4"></i><p>Fallo de Conexión</p></div>'; }
}

// -------------------------------------------------------------
// CARRITO
// -------------------------------------------------------------
async function CargarCarrito() {
    try {
        const r = await fetch(`${API_URL}?action=get_carrito`);
        const j = await r.json();
        if(j.status === 'success') { carrito = j.data || []; ActualizarInsigniaBus(); }
    } catch(e) {}
}

function ActualizarInsigniaBus() {
    const badge = document.getElementById('carritoBadge');
    if(!badge) return;
    badge.textContent = carrito.length;
    if(carrito.length) badge.classList.remove('hidden'); else badge.classList.add('hidden');
}

function AgregarC(id, nombre, precio) {
    const sEl = document.getElementById(`s_${id}`);
    const cEl = document.getElementById(`c_${id}`);
    const stk = parseInt(sEl.textContent) || 0;
    const cnt = parseInt(cEl.value) || 0;

    if (cnt <= 0 || cnt > stk) return MostrarToast('danger', 'Cantidad inválida según stock físico.');
    sEl.textContent = stk - cnt;
    AgregarAPI(id, nombre, precio, cnt).catch(() => { sEl.textContent = stk; });
}

async function AgregarAPI(id, nombre, precio, cantidad) {
    try {
        const r = await fetch(`${API_URL}?action=add_carrito`, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ productoId: id, nombre, precio, cantidad }) });
        const j = await r.json();
        if(j.status === 'success') { MostrarToast('success', 'Añadido satisfactoriamente.'); await CargarCarrito(); }
        else throw new Error();
    } catch(e) { MostrarToast('danger', 'Error de red.'); throw e; }
}

async function QuitarC(i) {
    try {
        const r = await fetch(`${API_URL}?action=remove_carrito`, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ index: i }) });
        const j = await r.json();
        if(j.status === 'success') {
            await CargarCarrito();
            if(carrito.length === 0) Navegar('catalogo');
            else { RenderResumenCarrito(); RenderResumenCheckout(); }
        }
    } catch(e) {}
}

function RenderResumenCarrito() {
    const c = document.getElementById('carritoItemsContainer');
    if(!c) return;
    let totalU = 0; let h = '';
    carrito.forEach((p, i) => {
        const pr = parseFloat(p.precio) || 0; const cn = parseInt(p.cantidad) || 0;
        totalU += pr * cn;
        h += `<div class="bg-white border border-slate-100 rounded-2xl p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between shadow-sm hover:shadow-md transition-shadow">
                <div class="mb-4 sm:mb-0">
                    <h4 class="text-slate-900 font-bold text-[15px] sm:text-base mb-1">${p.nombre}</h4>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                        Cantidad: ${cn} uds
                    </span>
                </div>
                <div class="flex items-center gap-6 self-end sm:self-auto">
                    <div class="text-2xl font-display font-black text-brand-600">$${(pr*cn).toFixed(2)}</div>
                    <button onclick="QuitarC(${i})" class="w-10 h-10 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white flex items-center justify-center transition-all focus:outline-none focus:ring-4 focus:ring-red-100 group">
                        <i class="fas fa-trash-alt group-hover:scale-90 transition-transform"></i>
                    </button>
                </div>
              </div>`;
    });
    c.innerHTML = h;
    document.getElementById('resumenSubtotal').textContent = `$${totalU.toFixed(2)}`;
    document.getElementById('resumenTasa').textContent = TASA_BCV ? `${TASA_BCV.toFixed(2)} Bs` : 'Cotizando...';
    document.getElementById('resumenTotalBs').textContent = TASA_BCV ? `${(totalU * TASA_BCV).toFixed(2)} Bs` : '...';
}

function RenderResumenCheckout() {
    const l = document.getElementById('checkoutResumeList');
    if(!l) return;
    let totalU = 0; let h = '';
    carrito.forEach(p => {
        const pr = parseFloat(p.precio) || 0; const cn = parseInt(p.cantidad) || 0;
        totalU += pr * cn;
        h += `<div class="flex justify-between items-start py-3 border-b border-slate-100 last:border-0 last:pb-0">
            <div class="pr-3">
                <p class="text-sm font-semibold text-slate-800 line-clamp-1">${p.nombre}</p>
                <p class="text-xs text-slate-500">${cn} x $${pr.toFixed(2)}</p>
            </div>
            <div class="text-sm font-bold text-slate-900 whitespace-nowrap">$${(pr*cn).toFixed(2)}</div>
        </div>`;
    });
    l.innerHTML = h;
    document.getElementById('checkoutTotalBs').textContent = TASA_BCV ? `${(totalU * TASA_BCV).toFixed(2)} Bs` : '...';
    if(TASA_BCV) document.getElementById('coMontoPagado').value = (totalU * TASA_BCV).toFixed(2);
}

// -------------------------------------------------------------
// CHECKOUT FORM VAL & API
// -------------------------------------------------------------
function ConfigurarFlujoCheckout() {
    const selM = document.getElementById('coMetodoPago');
    if(selM) {
        selM.addEventListener('change', e => {
            const di = document.getElementById('bancaInstrucciones');
            di.classList.remove('hidden');
            di.classList.add('animate-fade-in-up');
            
            const m = e.target.value;
            document.getElementById('coDynamicWrapper1').style.display = m==='Pago Móvil' ? 'block' : 'none';
            document.getElementById('coDynamicWrapper2').style.display = m==='Pago Móvil' ? 'block' : 'none';
            document.getElementById('coDynamicWrapper3').style.display = m==='Transferencia' ? 'block' : 'none';
            
            if(m === 'Pago Móvil') {
                di.innerHTML = `<h5 class="font-bold text-brand-700 mb-2 flex items-center"><i class="fas fa-mobile-alt mr-2"></i>Pago Móvil Oficial</h5>
                                <div class="grid grid-cols-2 gap-2 text-sm text-slate-600">
                                    <div><span class="opacity-70">Bco:</span> <strong class="text-slate-900">Venezuela (0102)</strong></div>
                                    <div><span class="opacity-70">CI/RIF:</span> <strong class="text-slate-900">J-200635721</strong></div>
                                    <div class="col-span-2"><span class="opacity-70">Celular:</span> <strong class="text-slate-900 text-base">0412-5432876</strong></div>
                                </div>`;
            } else {
                di.innerHTML = `<h5 class="font-bold text-brand-700 mb-2 flex items-center"><i class="fas fa-university mr-2"></i>Transferencia Interbancaria</h5>
                                <div class="text-sm text-slate-600 space-y-1">
                                    <p><span class="opacity-70">Destino:</span> <strong class="text-slate-900">Banco de Vzla - EURIPYS C.A.</strong></p>
                                    <p><span class="opacity-70">Nro Cuenta:</span> <strong class="text-slate-900 font-mono text-base block mt-1 px-3 py-2 bg-white border border-brand-200 rounded">0102-0123-4567-8901-2345</strong></p>
                                    <p><span class="opacity-70">RIF Jurídico:</span> <strong class="text-slate-900">J-20063572-1</strong></p>
                                </div>`;
            }
        });
    }

    // LISTENER LOGISTICA (RADIO BUTTONS)
    const radiosEntrega = document.getElementsByName('coTipoEntrega');
    radiosEntrega.forEach(r => {
        r.addEventListener('change', e => {
            const val = e.target.value;
            const det = document.getElementById('coEnvioDetalles');
            if (val === 'Envio') {
                det.classList.remove('hidden');
            } else {
                det.classList.add('hidden');
            }
        });
    });

    const form = document.getElementById('formCheckout');
    if(form) {
        form.addEventListener('submit', async e => {
            e.preventDefault();

            // JS EXPLICIT VALIDATION FOR TOASTS
            const regexNombres = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,100}$/;
            const regexTelefono = /^(0412|0414|0416|0424|0426|0212)\d{7}$/;
            const regexCedula = /^\d{6,9}$/;
            const regexCuenta = /^\d{20}$/;
            const regexReferencia = /^\d{4,20}$/;
            const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            const nombre = document.getElementById('coNombre').value.trim();
            if (!regexNombres.test(nombre)) return MostrarToast('danger', 'Nombre inválido: Usa solo letras reales.');

            const correo = document.getElementById('coCorreo').value.trim();
            if (!regexCorreo.test(correo)) return MostrarToast('danger', 'Correo electrónico inválido.');

            const telf = document.getElementById('coTelefono').value.trim();
            if (!regexTelefono.test(telf)) return MostrarToast('danger', 'Celular principal inválido.');

            // VALIDAR LOGISTICA
            let tipoEntrega = '';
            const radios = document.getElementsByName('coTipoEntrega');
            radios.forEach(r => { if(r.checked) tipoEntrega = r.value; });
            if (!tipoEntrega) return MostrarToast('danger', 'Declara tu Método de Logística (Retiro o Envío).');

            let agenciaEnvio = '';
            let direccionEnvio = '';
            if (tipoEntrega === 'Envio') {
                agenciaEnvio = document.getElementById('coAgencia').value;
                if (!agenciaEnvio) return MostrarToast('danger', 'Selecciona tu paquetería de confianza.');
                
                direccionEnvio = document.getElementById('coDireccion').value.trim();
                if (direccionEnvio.length < 10) return MostrarToast('danger', 'Especifica con exactitud de Estado a Agencia destino.');
            }

            const m = document.getElementById('coMetodoPago').value;
            if (!m) return MostrarToast('danger', 'Por favor, selecciona vía de pago.');

            const ref = document.getElementById('coReferencia').value.trim();
            if (!regexReferencia.test(ref)) return MostrarToast('danger', 'Referencia inválida: Puros dígitos.');

            if (m === 'Pago Móvil') {
                const ci = document.getElementById('coCedula').value.trim();
                const telfO = document.getElementById('coTelefonoOrigen').value.trim();
                if (!regexCedula.test(ci)) return MostrarToast('danger', 'Cédula origen inválida.');
                if (!regexTelefono.test(telfO)) return MostrarToast('danger', 'Teléfono emisor inválido.');
            } else if (m === 'Transferencia') {
                const cta = document.getElementById('coCuenta').value.trim();
                if (!regexCuenta.test(cta)) return MostrarToast('danger', 'Inserta los 20 dígitos de tu cuenta de origen.');
            }

            const pmonto = document.getElementById('coMontoPagado').value.trim();
            if (!pmonto || isNaN(pmonto) || parseFloat(pmonto) <= 0) return MostrarToast('danger', 'Monto de soporte bancario inválido.');
            
            const fecha = document.getElementById('coFecha').value.trim();
            if (!fecha) return MostrarToast('danger', 'Selecciona fecha de la transferencia.');

            // LOCK BUTTON
            const b = document.getElementById('btnConfirmarPedido');
            b.disabled = true; b.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Asegurando...';

            const totalU = carrito.reduce((a, p) => a + (parseFloat(p.precio)||0)*(parseInt(p.cantidad)||0), 0);

            const datos = {
                carrito,
                nombreComprador: nombre,
                correoComprador: correo,
                telefonoComprador: telf,
                monto: totalU.toFixed(2),
                montoBs: (totalU * TASA_BCV).toFixed(2),
                montoBsPago: pmonto,
                metodoPago: m,
                banco: document.getElementById('coBanco').value,
                fechaPago: fecha,
                referencia: ref,
                telefono: document.getElementById('coTelefonoOrigen')?.value,
                cedula: document.getElementById('coCedula')?.value,
                numeroCuenta: document.getElementById('coCuenta')?.value,
                
                // NUEVOS CAMPOS DE LOGÍSTICA
                tipoEntrega: tipoEntrega,
                agenciaEnvio: agenciaEnvio,
                direccionEnvio: direccionEnvio
            };

            try {
                const r = await fetch('procesar_pago.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(datos) });
                const j = await r.json();
                if(j.status === 'success') {
                    // WOW SUCCESS PAGE
                    document.getElementById('viewCheckout').innerHTML = `
                        <div class="max-w-2xl mx-auto mt-10">
                            <div class="bg-white rounded-[2.5rem] p-10 md:p-14 shadow-[0_20px_60px_rgba(0,0,0,0.06)] border border-slate-100 text-center relative overflow-hidden">
                                <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-emerald-400 to-teal-500"></div>
                                <div class="w-24 h-24 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-8">
                                    <i class="fas fa-check text-5xl text-emerald-500"></i>
                                </div>
                                <h2 class="text-4xl font-display font-black text-slate-900 mb-4 tracking-tight">Transacción <br/>Exitosa</h2>
                                <p class="text-slate-500 text-lg mb-10 leading-relaxed">Tu soporte ha sido encriptado y enviado al dpto de cobranzas. Validaremos el recibo en base de datos.</p>
                                
                                <div class="bg-slate-50 rounded-2xl p-6 text-left border border-slate-100 mb-10">
                                    <div class="grid grid-cols-2 gap-y-4 text-sm">
                                        <div class="text-slate-500 font-medium">Referencia</div>
                                        <div class="font-mono font-bold text-slate-900 text-right">${ref}</div>
                                        <div class="text-slate-500 font-medium">Bolsa Aprobada</div>
                                        <div class="font-bold text-brand-600 font-display text-right">${parseFloat(pmonto).toFixed(2)} Bs</div>
                                        <div class="col-span-2 pt-4 mt-2 border-t border-slate-200">
                                            <span class="text-xs text-slate-400 flex items-center justify-center"><i class="fas fa-envelope mr-1"></i> Factura despachada a: <b>${correo}</b></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <button onclick="window.location.reload();" class="text-brand-600 font-bold hover:text-brand-700 transition-colors uppercase tracking-widest text-sm">
                                    Volver al Inicio
                                </button>
                            </div>
                        </div>`;
                    carrito = []; ActualizarInsigniaBus();
                } else throw new Error(j.message);
            } catch(error) {
                MostrarToast('danger', error.message || 'Interrupción de servidor durante guardado.');
                b.disabled = false; b.innerHTML = 'Someter Auditoría a Pago <i class="fas fa-arrow-circle-up ml-2"></i>';
            } 
        });
    }
}
