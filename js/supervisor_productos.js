document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('tablaProductos');
    const rows = table.querySelectorAll('tbody tr');

    if (searchInput) {
        searchInput.addEventListener('keyup', (e) => {
            const term = e.target.value.toLowerCase().trim();

            rows.forEach(row => {
                if (row.querySelector('.empty-msg')) return;

                const text = row.textContent.toLowerCase();
                
                if (text.includes(term)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});