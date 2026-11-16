document.addEventListener('DOMContentLoaded', () => {
    const btnLimpiar = document.getElementById('btn-limpiar');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', () => {
            window.location.href = 'supervisar_ventas.php';
        });
    }
});

function exportarPDF() {
   
    const originalTitle = document.title;
    const fechaActual = new Date().toISOString().slice(0, 10);
    document.title = `Reporte_Ventas_MarketPlus_${fechaActual}`;

    // Disparar diálogo nativo de impresión / Guardar como PDF
    window.print();

    // Restaurar título original
    document.title = originalTitle;
}