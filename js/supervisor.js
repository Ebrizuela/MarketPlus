document.addEventListener('DOMContentLoaded', () => {
    const formFiltros = document.getElementById('form-filtros');
    const etiquetaMontoTotal = document.getElementById('etiqueta-monto-total');

    if (formFiltros && etiquetaMontoTotal) {
        const actualizarEtiqueta = () => {
            const hayFiltros = Array.from(
                formFiltros.querySelectorAll('select, input[type="date"]')
            ).some(control => control.value.trim() !== '');

            etiquetaMontoTotal.textContent = hayFiltros
                ? 'Monto Total Filtrado'
                : 'Monto Total';
        };

        actualizarEtiqueta();
        formFiltros.addEventListener('submit', actualizarEtiqueta);
    }

    const $ = window.jQuery;

    if ($ && $.fn.DataTable && $('#tabla-ventas').length) {
        $('#tabla-ventas').DataTable({
            order: [[0, 'asc']],
            pageLength: 25,
            language: {
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ filas',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ filas',
                infoEmpty: 'Sin registros',
                zeroRecords: 'No se encontraron resultados',
                paginate: {
                    first: 'Primero',
                    last: 'Último',
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            }
        });
    }
});

function exportarPDF() {
   
    const originalTitle = document.title;
    const fechaActual = new Date().toISOString().slice(0, 10);
    document.title = `Reporte_Ventas_MarketPlus_${fechaActual}`;

    
    window.print();

    document.title = originalTitle;
}