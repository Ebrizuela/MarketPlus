let listaProductos = [];
let detalleVenta = [];
let controladorBusqueda = null;
let rutBuscado = '';

const formatoMoneda = new Intl.NumberFormat('es-CL', {
    maximumFractionDigits: 0
});

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('rut_cliente').addEventListener('input', validarRutEnCampo);
    document.getElementById('rut_cliente').addEventListener('blur', buscarClienteSiValido);
    document.getElementById('verificar_cliente').addEventListener('click', validarYBuscarCliente);
    document.getElementById('crear_cliente').addEventListener('click', crearCliente);
    document.getElementById('agregar_producto').addEventListener('click', agregarProducto);
    document.getElementById('finalizar_venta').addEventListener('click', finalizarVenta);
    document.getElementById('select_producto').addEventListener('change', actualizarLimiteCantidad);
    document.getElementById('tabla_detalle').addEventListener('change', actualizarCantidad);
    document.getElementById('tabla_detalle').addEventListener('click', manejarAccionDetalle);

    cargarProductos();
});

function mostrarToast(mensaje, tipo = 'info', duracion = 4500) {
    const contenedor = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    toast.setAttribute('role', tipo === 'error' ? 'alert' : 'status');
    toast.textContent = mensaje;
    contenedor.appendChild(toast);
    window.setTimeout(() => toast.remove(), duracion);
}

function normalizarRut(rut) {
    return rut.trim().replace(/[.-]/g, '').toUpperCase();
}

function validarRut(rut) {
    const valor = rut.trim();
    const formatoValido = /^(?:\d{7,8}|\d{1,2}(?:\.\d{3}){2})-[\dkK]$/.test(valor);
    if (!formatoValido) {
        return false;
    }

    const normalizado = normalizarRut(valor);
    const digitos = normalizado.slice(0, -1);
    let suma = 0;
    let multiplicador = 2;

    for (let indice = digitos.length - 1; indice >= 0; indice -= 1) {
        suma += Number(digitos[indice]) * multiplicador;
        multiplicador = multiplicador === 7 ? 2 : multiplicador + 1;
    }

    const resultado = 11 - (suma % 11);
    const digitoEsperado = resultado === 11
        ? '0'
        : (resultado === 10 ? 'K' : String(resultado));

    return normalizado.slice(-1) === digitoEsperado;
}

function actualizarFeedbackRut(mensaje, tipo = '') {
    const feedback = document.getElementById('rut-feedback');
    feedback.className = `feedback-text${tipo ? ` ${tipo}` : ''}`;
    feedback.textContent = mensaje;
}

function limpiarClienteSeleccionado() {
    document.getElementById('id_cliente').value = '';
    document.getElementById('nombre_cliente').value = '';
    rutBuscado = '';
}

function validarRutEnCampo() {
    const rut = document.getElementById('rut_cliente').value.trim();
    limpiarClienteSeleccionado();
    if (!rut) {
        actualizarFeedbackRut('');
        document.getElementById('formulario_nuevo_cliente').hidden = true;
        if (controladorBusqueda) {
            controladorBusqueda.abort();
        }
        return;
    }

    const valido = validarRut(rut);
    actualizarFeedbackRut(
        valido ? 'Formato y dígito verificador correctos.' : 'Formato o dígito verificador inválido.',
        valido ? 'success' : 'error'
    );
    document.getElementById('formulario_nuevo_cliente').hidden = true;
    if (controladorBusqueda) {
        controladorBusqueda.abort();
    }
}

function buscarClienteSiValido() {
    const rut = document.getElementById('rut_cliente').value.trim();
    if (rut && validarRut(rut)) {
        buscarCliente(rut);
    }
}

function validarYBuscarCliente() {
    const rut = document.getElementById('rut_cliente').value.trim();
    if (!rut || !validarRut(rut)) {
        limpiarClienteSeleccionado();
        actualizarFeedbackRut('Ingrese un RUT válido, incluido su dígito verificador.', 'error');
        return;
    }
    buscarCliente(rut);
}

async function buscarCliente(rut) {
    if (controladorBusqueda) {
        controladorBusqueda.abort();
    }
    controladorBusqueda = new AbortController();
    const rutNormalizado = normalizarRut(rut);
    actualizarFeedbackRut('Buscando cliente…');

    try {
        const respuesta = await fetch(`buscar_cliente.php?rut=${encodeURIComponent(rut)}`, {
            signal: controladorBusqueda.signal,
            headers: { Accept: 'application/json' }
        });
        const data = await respuesta.json();
        if (document.getElementById('rut_cliente').value.trim() !== rut) {
            return;
        }

        if (respuesta.ok && data.status === 'success') {
            document.getElementById('nombre_cliente').value =
                `${data.cliente.nombre} ${data.cliente.apellidos}`.trim();
            document.getElementById('id_cliente').value = data.cliente.id_cliente;
            rutBuscado = rutNormalizado;
            document.getElementById('formulario_nuevo_cliente').hidden = true;
            actualizarFeedbackRut('RUT válido y cliente encontrado.', 'success');
        } else if (data.status === 'not_found') {
            document.getElementById('formulario_nuevo_cliente').hidden = false;
            actualizarFeedbackRut('RUT válido; puede registrar un cliente nuevo.', 'success');
        } else {
            limpiarClienteSeleccionado();
            document.getElementById('formulario_nuevo_cliente').hidden = true;
            actualizarFeedbackRut(data.message || 'No fue posible verificar el cliente.', 'error');
        }
    } catch (error) {
        if (error.name !== 'AbortError') {
            limpiarClienteSeleccionado();
            document.getElementById('formulario_nuevo_cliente').hidden = true;
            actualizarFeedbackRut('No fue posible consultar el cliente. Intente nuevamente.', 'error');
        }
    }
}

async function crearCliente() {
    const rut = document.getElementById('rut_cliente').value.trim();
    const campos = {
        nombre: document.getElementById('nuevo_nombre').value.trim(),
        apellidos: document.getElementById('nuevo_apellidos').value.trim(),
        correo: document.getElementById('nuevo_correo').value.trim(),
        telefono: document.getElementById('nuevo_telefono').value.trim()
    };
    const boton = document.getElementById('crear_cliente');

    if (!validarRut(rut)) {
        actualizarFeedbackRut('Ingrese un RUT válido antes de crear el cliente.', 'error');
        return;
    }
    if (Object.values(campos).some((valor) => !valor)) {
        mostrarToast('Complete nombre, apellidos, correo y teléfono para crear el cliente.', 'error');
        return;
    }

    boton.disabled = true;
    const campoRut = document.getElementById('rut_cliente');
    campoRut.disabled = true;
    try {
        const respuesta = await fetch('crear_cliente.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ rut, ...campos })
        });
        const data = await respuesta.json();
        if (!respuesta.ok || data.status !== 'success') {
            throw new Error(data.message || 'No fue posible crear el cliente.');
        }

        document.getElementById('id_cliente').value = data.cliente.id_cliente;
        document.getElementById('nombre_cliente').value =
            `${data.cliente.nombre} ${data.cliente.apellidos}`.trim();
        rutBuscado = normalizarRut(rut);
        document.getElementById('formulario_nuevo_cliente').hidden = true;
        actualizarFeedbackRut('Cliente creado y asociado correctamente.', 'success');
        mostrarToast('Cliente creado correctamente. Puede continuar con la venta.', 'success');
    } catch (error) {
        mostrarToast(error.message || 'Error de conexión al crear el cliente.', 'error');
    } finally {
        campoRut.disabled = false;
        boton.disabled = false;
    }
}

async function cargarProductos() {
    const select = document.getElementById('select_producto');
    try {
        const respuesta = await fetch('../productos/obtener_productos.php', {
            headers: { Accept: 'application/json' }
        });
        const data = await respuesta.json();
        if (!respuesta.ok || !Array.isArray(data)) {
            throw new Error(data.error || 'Respuesta inválida al cargar productos.');
        }

        listaProductos = data;
        select.replaceChildren(new Option('-- Seleccione un producto --', ''));
        data.forEach((producto) => {
            const opcion = new Option(
                `${producto.nombre} - $${formatoMoneda.format(Number(producto.precio))} (Stock: ${producto.stock})`,
                producto.id_producto
            );
            select.add(opcion);
        });
        actualizarLimiteCantidad();
    } catch (error) {
        mostrarToast('No fue posible cargar los productos. Recargue la página e intente nuevamente.', 'error');
    }
}

function actualizarLimiteCantidad() {
    const idProducto = document.getElementById('select_producto').value;
    const producto = listaProductos.find((item) => String(item.id_producto) === idProducto);
    const campoCantidad = document.getElementById('cantidad_producto');
    campoCantidad.max = producto ? String(producto.stock) : '';
    if (producto && Number(campoCantidad.value) > Number(producto.stock)) {
        campoCantidad.value = String(producto.stock);
    }
}

function agregarProducto() {
    const idProducto = document.getElementById('select_producto').value;
    const campoCantidad = document.getElementById('cantidad_producto');
    const cantidad = Number(campoCantidad.value);
    const producto = listaProductos.find((item) => String(item.id_producto) === idProducto);

    if (!producto || !Number.isSafeInteger(cantidad) || cantidad <= 0) {
        mostrarToast('Seleccione un producto y una cantidad entera mayor que cero.', 'error');
        return;
    }
    if (cantidad > Number(producto.stock)) {
        mostrarToast(`Stock insuficiente. Disponible: ${producto.stock}.`, 'error');
        return;
    }

    const precio = Number(producto.precio);
    if (!Number.isFinite(precio) || precio <= 0) {
        mostrarToast('El producto tiene un precio inválido y no puede agregarse.', 'error');
        return;
    }

    const existente = detalleVenta.find((item) => String(item.id_producto) === String(idProducto));
    if (existente) {
        if (existente.cantidad + cantidad > Number(producto.stock)) {
            mostrarToast(`La cantidad total supera el stock disponible (${producto.stock}).`, 'error');
            return;
        }
        existente.cantidad += cantidad;
    } else {
        detalleVenta.push({
            id_producto: producto.id_producto,
            nombre: producto.nombre,
            precio_unitario: precio,
            stock: Number(producto.stock),
            cantidad
        });
    }

    renderizarDetalle();
}

function renderizarDetalle() {
    const tbody = document.getElementById('tabla_detalle');
    tbody.replaceChildren();

    if (detalleVenta.length === 0) {
        const fila = document.createElement('tr');
        const celda = document.createElement('td');
        celda.colSpan = 5;
        celda.className = 'tabla-empty';
        celda.textContent = 'No hay productos agregados a la venta.';
        fila.appendChild(celda);
        tbody.appendChild(fila);
        document.getElementById('total_general').textContent = '0';
        return;
    }

    let total = 0;
    detalleVenta.forEach((item, index) => {
        const subtotal = item.cantidad * item.precio_unitario;
        total += subtotal;
        const fila = document.createElement('tr');
        const nombre = document.createElement('td');
        nombre.textContent = item.nombre;

        const celdaCantidad = document.createElement('td');
        const campoCantidad = document.createElement('input');
        campoCantidad.type = 'number';
        campoCantidad.min = '1';
        campoCantidad.max = String(item.stock);
        campoCantidad.step = '1';
        campoCantidad.value = String(item.cantidad);
        campoCantidad.className = 'quantity-input';
        campoCantidad.dataset.index = String(index);
        campoCantidad.setAttribute('aria-label', `Cantidad de ${item.nombre}`);
        celdaCantidad.appendChild(campoCantidad);

        const precio = document.createElement('td');
        precio.textContent = `$${formatoMoneda.format(item.precio_unitario)}`;
        const celdaSubtotal = document.createElement('td');
        celdaSubtotal.textContent = `$${formatoMoneda.format(subtotal)}`;

        const celdaAccion = document.createElement('td');
        celdaAccion.className = 'cell-action';
        const botonEliminar = document.createElement('button');
        botonEliminar.type = 'button';
        botonEliminar.className = 'btn btn-danger';
        botonEliminar.dataset.action = 'eliminar';
        botonEliminar.dataset.index = String(index);
        botonEliminar.textContent = 'Eliminar';
        celdaAccion.appendChild(botonEliminar);

        fila.append(nombre, celdaCantidad, precio, celdaSubtotal, celdaAccion);
        tbody.appendChild(fila);
    });

    document.getElementById('total_general').textContent = formatoMoneda.format(total);
}

function actualizarCantidad(evento) {
    const campo = evento.target.closest('.quantity-input');
    if (!campo) {
        return;
    }
    const indice = Number(campo.dataset.index);
    const item = detalleVenta[indice];
    const cantidad = Number(campo.value);
    if (!item || !Number.isSafeInteger(cantidad) || cantidad <= 0 || cantidad > item.stock) {
        mostrarToast(`Ingrese una cantidad entre 1 y ${item ? item.stock : 0}.`, 'error');
        renderizarDetalle();
        return;
    }

    item.cantidad = cantidad;
    renderizarDetalle();
}

function manejarAccionDetalle(evento) {
    const boton = evento.target.closest('[data-action="eliminar"]');
    if (!boton) {
        return;
    }
    detalleVenta.splice(Number(boton.dataset.index), 1);
    renderizarDetalle();
}

async function finalizarVenta() {
    const rut = document.getElementById('rut_cliente').value.trim();
    const idCliente = document.getElementById('id_cliente').value;
    const idMedioPago = document.getElementById('id_medio_pago').value;
    const botonGuardar = document.getElementById('finalizar_venta');

    if (!validarRut(rut) || !idCliente || rutBuscado !== normalizarRut(rut)) {
        mostrarToast('Debe validar un RUT correcto y seleccionar el cliente correspondiente.', 'error');
        return;
    }
    if (!idMedioPago) {
        mostrarToast('Seleccione un medio de pago.', 'error');
        return;
    }
    if (detalleVenta.length === 0 || detalleVenta.some((item) =>
        !Number.isSafeInteger(item.cantidad) || item.cantidad <= 0 ||
        !Number.isFinite(item.precio_unitario) || item.precio_unitario <= 0
    )) {
        mostrarToast('Agregue al menos un producto con cantidad y precio válidos.', 'error');
        return;
    }

    botonGuardar.disabled = true;
    try {
        const respuesta = await fetch('guardar_venta.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                rut,
                id_cliente: idCliente,
                id_medio_pago: idMedioPago,
                productos: detalleVenta.map(({ id_producto, cantidad }) => ({ id_producto, cantidad }))
            })
        });
        const data = await respuesta.json();
        if (!respuesta.ok || data.status !== 'success') {
            throw new Error(data.message || 'No fue posible registrar la venta.');
        }

        mostrarToast('Venta registrada correctamente. Abriendo la boleta…', 'success', 1200);
        window.setTimeout(() => {
            window.location.href = `ver_venta.php?id=${encodeURIComponent(data.id_ventas)}`;
        }, 700);
    } catch (error) {
        mostrarToast(error.message || 'Error de conexión al registrar la venta.', 'error');
        botonGuardar.disabled = false;
    }
}
