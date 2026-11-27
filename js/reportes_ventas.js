document.addEventListener('DOMContentLoaded', () => {
    const formFiltros = document.querySelector('[data-report-endpoint]');
    const contenedorGrafico = document.getElementById('contenedorGrafico');
    const canvasGrafico = document.getElementById('graficoVentas');
    const cuerpoTabla = document.getElementById('detalleVentas');
    const mensajeError = document.getElementById('reporteError');
    const enlacesExportacion = [
        document.getElementById('exportarExcel'),
        document.getElementById('exportarPdf')
    ];
    const formatoNumero = new Intl.NumberFormat('es-CL', {
        maximumFractionDigits: 0
    });

    if (!formFiltros || !cuerpoTabla || !mensajeError) {
        return;
    }

    const tablaDataTable = typeof DataTable !== 'undefined'
        ? new DataTable('#tablaVentas', {
            columnDefs: [{ targets: 0, type: 'num' }],
            order: [[0, 'desc']],
            language: {
                emptyTable: 'No se encontraron datos.',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ transacciones',
                infoEmpty: 'Mostrando 0 transacciones',
                lengthMenu: 'Mostrar _MENU_ transacciones',
                search: 'Buscar:',
                zeroRecords: 'No se encontraron coincidencias',
                paginate: {
                    first: 'Primera',
                    last: 'Última',
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            }
        })
        : null;

    function obtenerTendencias(reportes) {
        const ventasPorFecha = new Map();
        reportes.forEach((reporte) => {
            const fecha = String(reporte.fecha_hora).slice(0, 10);
            ventasPorFecha.set(
                fecha,
                (ventasPorFecha.get(fecha) || 0) + (Number(reporte.total_venta) || 0)
            );
        });
        return Array.from(ventasPorFecha.entries())
            .sort(([fechaA], [fechaB]) => fechaA.localeCompare(fechaB));
    }

    let grafico = null;
    if (canvasGrafico && typeof Chart !== 'undefined') {
        try {
            grafico = new Chart(canvasGrafico.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: JSON.parse(canvasGrafico.dataset.labels || '[]'),
                    datasets: [{
                        label: 'Ventas ($)',
                        data: JSON.parse(canvasGrafico.dataset.values || '[]'),
                        borderColor: '#2457e6',
                        backgroundColor: 'rgba(36, 87, 230, 0.1)',
                        borderWidth: 1,
                        borderRadius: 6,
                        maxBarThickness: 48
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
            });
        } catch (error) {
            console.error('Error al inicializar el gráfico de ventas:', error);
        }
    }

    function actualizarGrafico(reportes) {
        if (!grafico) {
            return;
        }

        const tendencias = obtenerTendencias(reportes);
        grafico.data.labels = tendencias.map(([fecha]) => fecha);
        grafico.data.datasets[0].data = tendencias.map(([, total]) => total);
        if (contenedorGrafico) {
            contenedorGrafico.hidden = tendencias.length === 0;
        }
        grafico.update();
    }

    function crearFila(reporte) {
        const fila = document.createElement('tr');
        const valores = [
            reporte.id_ventas,
            reporte.fecha_hora,
            reporte.vendedor,
            reporte.cliente,
            reporte.productos,
            formatoNumero.format(Number(reporte.cantidad) || 0),
            `$${formatoNumero.format(Number(reporte.total_venta) || 0)}`
        ];

        valores.forEach((valor, indice) => {
            const celda = document.createElement('td');
            if (indice === 0) {
                celda.dataset.order = String(Number(reporte.id_ventas) || 0);
                const insigniaId = document.createElement('span');
                insigniaId.className = 'sale-id-badge';
                insigniaId.textContent = String(valor ?? '');
                celda.appendChild(insigniaId);
            } else if (indice === 5) {
                celda.dataset.order = String(Number(reporte.cantidad) || 0);
                celda.textContent = String(valor ?? '');
            } else if (indice === 6) {
                celda.dataset.order = String(Number(reporte.total_venta) || 0);
                celda.textContent = String(valor ?? '');
            } else {
                celda.textContent = String(valor ?? '');
            }
            fila.appendChild(celda);
        });

        return fila;
    }

    function actualizarTabla(reportes) {
        if (tablaDataTable) {
            tablaDataTable.clear();
            reportes.forEach((reporte) => tablaDataTable.row.add(crearFila(reporte)));
            tablaDataTable.draw();
            return;
        }

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

        reportes.forEach((reporte) => cuerpoTabla.appendChild(crearFila(reporte)));
    }

    function actualizarMetricas(reportes) {
        const totalVentas = reportes.reduce(
            (total, reporte) => total + (Number(reporte.total_venta) || 0),
            0
        );
        const totalUnidades = reportes.reduce(
            (total, reporte) => total + (Number(reporte.cantidad) || 0),
            0
        );
        const transacciones = reportes.length;
        const promedio = transacciones > 0 ? totalVentas / transacciones : 0;

        document.getElementById('ventasTotales').textContent =
            `$${formatoNumero.format(totalVentas)}`;
        document.getElementById('cantidadTransacciones').textContent =
            formatoNumero.format(transacciones);
        document.getElementById('promedioVenta').textContent =
            `$${formatoNumero.format(promedio)}`;
        document.getElementById('unidadesVendidas').textContent =
            formatoNumero.format(totalUnidades);
        document.getElementById('totalUnidadesTabla').textContent =
            formatoNumero.format(totalUnidades);
        document.getElementById('totalVentasTabla').textContent =
            `$${formatoNumero.format(totalVentas)}`;
    }

    function actualizarEnlacesExportacion() {
        const filtros = new URLSearchParams(new FormData(formFiltros));
        enlacesExportacion.forEach((enlace) => {
            if (!enlace) {
                return;
            }
            const url = new URL(enlace.href, window.location.href);
            const parametros = new URLSearchParams(filtros);
            parametros.set('formato', enlace.dataset.exportFormat);
            url.search = parametros.toString();
            enlace.href = url.toString();
        });
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
