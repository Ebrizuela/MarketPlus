document.addEventListener('DOMContentLoaded', () => {
    const formFiltros = document.querySelector('[data-report-endpoint]');
    const contenedorGrafico = document.getElementById('contenedorGrafico');
    const canvasGrafico = document.getElementById('graficoVentas');
    const cuerpoTabla = document.getElementById('detalleVentas');
    const mensajeError = document.getElementById('reporteError');
    const exportarExcel = document.getElementById('exportarExcel');
    const exportarPdf = document.getElementById('exportarPdf');
    const formatoNumero = new Intl.NumberFormat('es-CL', {
        maximumFractionDigits: 0
    });

    function obtenerTendencias(reportes) {
        const ventasPorFecha = new Map();

        reportes.forEach((reporte) => {
            const fecha = String(reporte.fecha_hora).slice(0, 10);
            const subtotal = Number(reporte.subtotal) || 0;
            ventasPorFecha.set(fecha, (ventasPorFecha.get(fecha) || 0) + subtotal);
        });

        return Array.from(ventasPorFecha.entries())
            .sort(([fechaA], [fechaB]) => fechaA.localeCompare(fechaB));
    }

    const datosIniciales = canvasGrafico
        ? {
            labels: JSON.parse(canvasGrafico.dataset.labels || '[]'),
            values: JSON.parse(canvasGrafico.dataset.values || '[]')
        }
        : { labels: [], values: [] };

    const grafico = canvasGrafico && typeof Chart !== 'undefined'
        ? new Chart(canvasGrafico.getContext('2d'), {
            type: 'line',
            data: {
                labels: datosIniciales.labels,
                datasets: [{
                    label: 'Ventas ($)',
                    data: datosIniciales.values,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => `$${formatoNumero.format(value)}`
                        }
                    }
                }
            }
        })
        : null;

    function actualizarGrafico(reportes) {
        if (!grafico) {
            return;
        }

        const tendencias = obtenerTendencias(reportes);
        grafico.data.labels = tendencias.map(([fecha]) => fecha);
        grafico.data.datasets[0].data = tendencias.map(([, total]) => total);
        contenedorGrafico.hidden = tendencias.length === 0;
        grafico.update();

        if (tendencias.length > 0) {
            grafico.resize();
        }
    }

    function crearCelda(fila, contenido) {
        const celda = document.createElement('td');
        celda.textContent = contenido;
        fila.appendChild(celda);
    }

    function actualizarTabla(reportes) {
        cuerpoTabla.replaceChildren();

        if (reportes.length === 0) {
            const fila = document.createElement('tr');
            const celda = document.createElement('td');
            celda.colSpan = 7;
            celda.className = 'text-center py-4';
            celda.textContent = 'No se encontraron datos.';
            fila.appendChild(celda);
            cuerpoTabla.appendChild(fila);
            return;
        }

        reportes.forEach((reporte) => {
            const fila = document.createElement('tr');
            crearCelda(fila, String(reporte.id_ventas ?? ''));
            crearCelda(fila, String(reporte.fecha_hora ?? ''));
            crearCelda(fila, String(reporte.cliente ?? ''));
            crearCelda(fila, String(reporte.producto ?? ''));
            crearCelda(fila, formatoNumero.format(Number(reporte.cantidad) || 0));
            crearCelda(fila, `$${formatoNumero.format(Number(reporte.precio_unitario) || 0)}`);
            crearCelda(fila, `$${formatoNumero.format(Number(reporte.subtotal) || 0)}`);
            cuerpoTabla.appendChild(fila);
        });
    }

    function actualizarMetricas(reportes) {
        const totalVentas = reportes.reduce(
            (total, reporte) => total + (Number(reporte.subtotal) || 0),
            0
        );
        const totalUnidades = reportes.reduce(
            (total, reporte) => total + (Number(reporte.cantidad) || 0),
            0
        );
        const promedio = reportes.length > 0 ? totalVentas / reportes.length : 0;

        document.getElementById('ventasTotales').textContent =
            `$${formatoNumero.format(totalVentas)}`;
        document.getElementById('promedioVenta').textContent =
            `$${formatoNumero.format(promedio)}`;
        document.getElementById('unidadesVendidas').textContent =
            formatoNumero.format(totalUnidades);
    }

    function actualizarEnlacesExportacion() {
        const parametros = new URLSearchParams(new FormData(formFiltros));

        [exportarExcel, exportarPdf].forEach((enlace) => {
            if (!enlace) {
                return;
            }
            const url = new URL(enlace.href, window.location.href);
            url.search = parametros.toString();
            enlace.href = url.toString();
        });
    }

    if (!formFiltros) {
        return;
    }

    formFiltros.addEventListener('input', actualizarEnlacesExportacion);
    formFiltros.addEventListener('change', actualizarEnlacesExportacion);

    formFiltros.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        mensajeError.hidden = true;
        mensajeError.textContent = '';
        actualizarEnlacesExportacion();

        const botonEnviar = formFiltros.querySelector('[type="submit"]');
        const estabaDeshabilitado = botonEnviar ? botonEnviar.disabled : false;
        if (botonEnviar) {
            botonEnviar.disabled = true;
        }

        try {
            const parametros = new URLSearchParams(new FormData(formFiltros));
            const endpoint = new URL(formFiltros.dataset.reportEndpoint, window.location.href);
            endpoint.search = parametros.toString();

            const respuesta = await fetch(endpoint, {
                headers: { Accept: 'application/json' }
            });
            const resultado = await respuesta.json();

            if (!respuesta.ok || resultado.status !== 'success' || !Array.isArray(resultado.data)) {
                throw new Error(resultado.message || 'No fue posible obtener el reporte.');
            }

            actualizarMetricas(resultado.data);
            actualizarTabla(resultado.data);
            actualizarGrafico(resultado.data);
        } catch (error) {
            console.error('Error al actualizar el reporte:', error);
            mensajeError.textContent = error.message || 'Ocurrió un error al consultar el reporte.';
            mensajeError.hidden = false;
        } finally {
            if (botonEnviar) {
                botonEnviar.disabled = estabaDeshabilitado;
            }
        }
    });
});
