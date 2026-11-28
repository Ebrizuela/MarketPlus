document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('tablaProductos');
    if (!searchInput || !table) return;

    const rows = table.querySelectorAll('tbody tr');

    searchInput.addEventListener('input', () => {
        const term = searchInput.value.toLowerCase().trim();

        rows.forEach(row => {
            if (row.querySelector('.empty-state')) return;

            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    });
});