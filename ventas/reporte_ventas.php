<?php
require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/ventas_lib.php';


try {
    $pdo = conectar();
    $filtros = leerFiltrosReporte($_GET);
    $opciones = obtenerOpcionesReporte($pdo);
    $reportes = consultarReporteVentas($pdo, $filtros);
    $resumen = calcularResumenReporte($reportes);
} catch (InvalidArgumentException $error) {
    http_response_code(400);
    exit(htmlspecialchars($error->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
} catch (Throwable $error) {
    error_log('Error al cargar el reporte de ventas: ' . $error->getMessage());
    http_response_code(500);
    exit('No fue posible cargar el reporte de ventas.');
}

$tendencias = [];
foreach ($reportes as $reporte) {
    $fecha = substr((string) $reporte['fecha_hora'], 0, 10);
    $tendencias[$fecha] = ($tendencias[$fecha] ?? 0) + (float) $reporte['total_venta'];
}
ksort($tendencias);
$parametrosExcel = http_build_query($filtros + ['formato' => 'excel']);
$parametrosPdf = http_build_query($filtros + ['formato' => 'pdf']);
$fechaActual = date('d/m/Y H:i');
$escapar = static function ($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/reportes_ventas.css?v=4">
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" defer></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js" defer></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js" defer></script>
</head>
<body class="reporte-ventas-body">
<main class="container my-4 reporte-ventas">
    <header class="sales-header">
        <div>
            <p class="page-eyebrow">MARKETPLUS · ANALÍTICA</p>
            <h1 class="page-title">Reporte de Ventas</h1>
            <p class="page-subtitle">Consulta, filtra y revisa el rendimiento de tus ventas.</p>
        </div>
        <div class="sales-header-actions">
            <div class="sales-actions">
                <a href="../Supervisor/Dashboard_Supervisor.php" class="btn rounded-pill export-button export-button-secondary dashboard-button">
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 8H2m0 0 5-5m-5 5 5 5"/></svg>
                    Volver al dashboard
                </a>
                <a id="exportarExcel" data-export-format="excel" href="obtener_reporte.php?<?= $escapar($parametrosExcel) ?>" class="btn rounded-pill export-button export-button-secondary">
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 1h6l3 3v10H4a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2Zm5 1v3h3M5 8l4 4m0-4-4 4"/></svg>
                    Exportar a Excel
                </a>
                <a id="exportarPdf" data-export-format="pdf" href="obtener_reporte.php?<?= $escapar($parametrosPdf) ?>" class="btn rounded-pill export-button export-button-primary">
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 1h6l3 3v10H4a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2Zm5 1v3h3M5 8h6M5 10h6M5 12h4"/></svg>
                    Exportar a PDF
                </a>
            </div>
            <p class="report-timestamp">
                <svg aria-hidden="true" width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 4v4l2.5 1.5"/></svg>
                <?= $escapar($fechaActual) ?>
            </p>
        </div>
    </header>

    <section class="card filter-panel mb-4" aria-labelledby="filtrosTitulo">
        <div class="card-body">
            <div class="section-heading">
                <span class="section-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 12h16M4 18h16M9 4v4m6 2v4m-7 2v4"/></svg>
                </span>
                <div>
                    <h2 id="filtrosTitulo" class="section-title">Filtros</h2>
                    <p class="section-description">Afina los resultados por vendedor, cliente, producto o fecha.</p>
                </div>
            </div>
            <form method="GET" action="reporte_ventas.php" class="row row-cols-1 row-cols-md-2 row-cols-xl-5 g-3" data-report-endpoint="obtener_reporte.php">
                <div class="col">
                    <label class="form-label" for="filtroVendedor">Vendedor</label>
                    <select id="filtroVendedor" name="vendedor" class="form-select">
                        <option value="">-- Todos --</option>
                        <?php foreach ($opciones['vendedores'] as $vendedor): ?>
                            <option value="<?= $escapar($vendedor) ?>" <?= $filtros['vendedor'] === $vendedor ? 'selected' : '' ?>>
                                <?= $escapar($vendedor) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col">
                    <label class="form-label" for="fechaDesde">Fecha desde</label>
                    <input id="fechaDesde" type="date" name="fecha_desde" class="form-control" value="<?= $escapar($filtros['fecha_desde']) ?>">
                </div>
                <div class="col">
                    <label class="form-label" for="fechaHasta">Fecha hasta</label>
                    <input id="fechaHasta" type="date" name="fecha_hasta" class="form-control" value="<?= $escapar($filtros['fecha_hasta']) ?>">
                </div>
                <div class="col">
                    <label class="form-label" for="filtroCliente">Cliente</label>
                    <select id="filtroCliente" name="cliente" class="form-select">
                        <option value="">Todos los clientes</option>
                        <?php foreach ($opciones['clientes'] as $cliente): ?>
                            <option value="<?= $escapar($cliente) ?>" <?= $filtros['cliente'] === $cliente ? 'selected' : '' ?>>
                                <?= $escapar($cliente) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col">
                    <label class="form-label" for="filtroProducto">Producto</label>
                    <select id="filtroProducto" name="producto" class="form-select">
                        <option value="">Todos los productos</option>
                        <?php foreach ($opciones['productos'] as $producto): ?>
                            <option value="<?= $escapar($producto['id_producto']) ?>" <?= $filtros['producto'] === (string) $producto['id_producto'] ? 'selected' : '' ?>>
                                <?= $escapar($producto['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 filter-actions">
                    <button type="submit" class="btn rounded-pill filter-button">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M2 3h12l-4.5 5.2V13l-3-1.5V8.2L2 3Z"/></svg>
                        Filtrar
                    </button>
                    <a href="reporte_ventas.php" class="btn rounded-pill clear-button">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7a5 5 0 1 1 1.2 4.5M3 3v4h4"/></svg>
                        Limpiar
                    </a>
                </div>
            </form>
        </div>
    </section>

    <section class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4" aria-label="Resumen de ventas">
        <div class="col">
            <article class="card kpi-card kpi-card-primary h-100">
                <div class="card-body">
                    <div class="kpi-copy">
                        <h2 class="kpi-label">Monto total</h2>
                        <p id="ventasTotales" class="kpi-value kpi-value-primary">$<?= number_format($resumen['total'], 0, ',', '.') ?></p>
                        <p class="kpi-footnote">Ingresos según los filtros activos</p>
                    </div>
                    <span class="kpi-icon kpi-icon-primary" aria-hidden="true">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2v3m0 14v3m7-10H5m14 0a7 7 0 1 0-7 7m7-7a7 7 0 0 1-7 7"/></svg>
                    </span>
                </div>
            </article>
        </div>
        <div class="col">
            <article class="card kpi-card h-100">
                <div class="card-body">
                    <div class="kpi-copy">
                        <h2 class="kpi-label">Transacciones</h2>
                        <p id="cantidadTransacciones" class="kpi-value"><?= $resumen['transacciones'] ?></p>
                        <p class="kpi-footnote">Ventas registradas</p>
                    </div>
                    <span class="kpi-icon" aria-hidden="true">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h12v18l-2-1.5L14 21l-2-1.5L10 21l-2-1.5L6 21V3Zm3 5h6M9 12h6M9 16h4"/></svg>
                    </span>
                </div>
            </article>
        </div>
        <div class="col">
            <article class="card kpi-card h-100">
                <div class="card-body">
                    <div class="kpi-copy">
                        <h2 class="kpi-label">Promedio por venta</h2>
                        <p id="promedioVenta" class="kpi-value">$<?= number_format($resumen['promedio'], 0, ',', '.') ?></p>
                        <p class="kpi-footnote">Monto promedio por transacción</p>
                    </div>
                    <span class="kpi-icon" aria-hidden="true">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V5m0 14h16M8 15l4-4 3 2 5-6"/></svg>
                    </span>
                </div>
            </article>
        </div>
        <div class="col">
            <article class="card kpi-card h-100">
                <div class="card-body">
                    <div class="kpi-copy">
                        <h2 class="kpi-label">Unidades vendidas</h2>
                        <p id="unidadesVendidas" class="kpi-value"><?= $resumen['unidades'] ?></p>
                        <p class="kpi-footnote">Unidades en las ventas filtradas</p>
                    </div>
                    <span class="kpi-icon" aria-hidden="true">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 9 5-9 5-9-5 9-5Zm-9 9 9 5 9-5M3 16l9 5 9-5"/></svg>
                    </span>
                </div>
            </article>
        </div>
    </section>

    <div id="reporteError" class="alert alert-danger report-error" role="alert" aria-live="polite" hidden></div>

    <section id="contenedorGrafico" class="card report-card mb-4" <?= $tendencias === [] ? 'hidden' : '' ?>>
        <div class="card-header report-card-header">
            <h2 class="section-title">Tendencia de ventas</h2>
            <p class="section-description">Monto vendido por fecha</p>
        </div>
        <div class="card-body chart-body">
            <canvas id="graficoVentas"
                    data-labels='<?= json_encode(array_keys($tendencias), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?>'
                    data-values='<?= json_encode(array_values($tendencias), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?>'>
            </canvas>
        </div>
    </section>

    <section class="card report-card table-card" aria-labelledby="detalleVentasTitulo">
        <div class="card-header report-card-header">
            <div>
                <h2 id="detalleVentasTitulo" class="section-title">Detalle de Ventas</h2>
                <p class="section-description">Transacciones incluidas en el reporte.</p>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tablaVentas" class="table table-hover align-middle mb-0 sales-table">
                    <thead>
                        <tr>
                            <th scope="col">ID Venta</th>
                            <th scope="col">Fecha y Hora</th>
                            <th scope="col">Vendedor</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Productos</th>
                            <th scope="col">Unidades</th>
                            <th scope="col">Total Venta</th>
                        </tr>
                    </thead>
                    <tbody id="detalleVentas">
                        <?php foreach ($reportes as $reporte): ?>
                            <tr>
                                <td data-order="<?= $escapar($reporte['id_ventas']) ?>"><span class="sale-id-badge"><?= $escapar($reporte['id_ventas']) ?></span></td>
                                <td><?= $escapar($reporte['fecha_hora']) ?></td>
                                <td><?= $escapar($reporte['vendedor']) ?></td>
                                <td><?= $escapar($reporte['cliente']) ?></td>
                                <td><?= $escapar($reporte['productos']) ?></td>
                                <td data-order="<?= $escapar($reporte['cantidad']) ?>"><?= $escapar($reporte['cantidad']) ?></td>
                                <td data-order="<?= $escapar($reporte['total_venta']) ?>">$<?= number_format((float) $reporte['total_venta'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="table-total-label">Total</td>
                            <td id="totalUnidadesTabla" class="table-total-units"><?= $resumen['unidades'] ?></td>
                            <td id="totalVentasTabla" class="table-total-amount">$<?= number_format($resumen['total'], 0, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </section>

</main>

<script src="../js/reportes_ventas.js" defer></script>
</body>
</html>
