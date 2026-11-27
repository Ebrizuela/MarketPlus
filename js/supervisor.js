$(function () {
    const tabla = $('#tabla-ventas');

    if (tabla.length && $.fn.DataTable) {
        tabla.DataTable({
            language: {
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'No hay registros disponibles',
                infoFiltered: '(filtrado de _MAX_ registros)',
                zeroRecords: 'No se encontraron coincidencias',
                paginate: {
                    first: 'Primero',
                    previous: 'Anterior',
                    next: 'Siguiente',
                    last: 'Último'
                }
            },
            pageLength: 10,
            order: [[0, 'asc']],
            columnDefs: [
                { targets: [0, 5], className: 'text-center' },
                { targets: [6, 7], className: 'text-end' }
            ]
        });
    }
});
