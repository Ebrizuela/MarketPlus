<?php
function renderNavegacion($url_listado = null, $url_inicio = '../index.php', $urlVolver = null)
{
    $rol = $_SESSION['usuario']['rol'] ?? '';
    $urlDashboard = null;
    if ($rol === 'administrador') {
        $urlDashboard = BASE_URL . '/administrador/dashboard_administrador.php';
    } elseif ($rol === 'supervisor') {
        $urlDashboard = BASE_URL . '/Supervisor/Dashboard_Supervisor.php';
    }
    ?>
    <nav class="marketplus-nav-container" aria-label="Navegación bidireccional">
        <div class="mp-nav-group">
            <?php if ($url_listado !== null): ?>
                <a href="<?php echo htmlspecialchars($url_listado, ENT_QUOTES, 'UTF-8'); ?>" class="mp-btn mp-btn-secondary" title="Volver al listado de ventas">
                    &larr; Volver a ventas
                </a>
            <?php endif; ?>
            <?php if ($urlVolver !== null): ?>
                <a href="<?php echo htmlspecialchars($urlVolver, ENT_QUOTES, 'UTF-8'); ?>" class="mp-btn mp-btn-outline" title="Volver a Productos e inventario">
                    &larr; Volver
                </a>
            <?php elseif ($urlDashboard !== null): ?>
                <a href="<?php echo htmlspecialchars($urlDashboard, ENT_QUOTES, 'UTF-8'); ?>" class="mp-btn mp-btn-outline" title="Volver al dashboard">
                    &larr; Volver al dashboard
                </a>
            <?php endif; ?>
            <a href="<?php echo htmlspecialchars($url_inicio, ENT_QUOTES, 'UTF-8'); ?>" class="mp-btn mp-btn-primary" title="Ir al catálogo principal">
                &#8962; Inicio
            </a>
        </div>
    </nav>
    <?php
}
?>