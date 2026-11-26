<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/reporte_ventas_lib.php';
requerirRol(['administrador', 'supervisor']);

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
$escapar = static function ($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisión de Ventas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/reportes_ventas.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js" defer></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js" defer></script>
</head>
<body class="reporte-ventas-body">
<main class="container my-4 reporte-ventas">
    <header class="sales-header">
        <div>
            <p class="page-eyebrow">PANEL DE CONTROL</p>
            <h1 class="page-title">Supervisión de Ventas</h1>
            <p class="page-subtitle">Revisa el rendimiento y detalle de las ventas.</p>
        </div>
        <div class="sales-actions">
            <a id="exportarExcel" data-export-format="excel" href="obtener_reporte.php?<?= $escapar($parametrosExcel) ?>" class="btn rounded-pill export-button export-button-primary">Exportar Excel</a>
            <a id="exportarPdf" data-export-format="pdf" href="obtener_reporte.php?<?= $escapar($parametrosPdf) ?>" class="btn rounded-pill export-button export-button-secondary">Exportar PDF</a>
        </div>
    </header>

    <section class="card filter-panel mb-4" aria-labelledby="filtrosTitulo">
        <div class="card-body">
            <div class="section-heading">
                <h2 id="filtrosTitulo" class="section-title">Filtros</h2>
                <p class="section-description">Combina criterios para acotar el reporte.</p>
            </div>
            <form method="GET" action="reporte_ventas.php" class="row row-cols-1 row-cols-md-2 row-cols-xl-5 g-3" data-report-endpoint="obtener_reporte.php">
                <div class="col">
                    <label class="form-label" for="filtroVendedor">Vendedor</label>
                    <select id="filtroVendedor" name="vendedor" class="form-select">
                        <option value="">Todos los vendedores</option>
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
                    <button type="submit" class="btn rounded-pill filter-button">Filtrar</button>
                    <a href="reporte_ventas.php" class="btn rounded-pill clear-button">Limpiar</a>
                </div>
            </form>
        </div>
    </section>

    <div id="reporteError" class="alert alert-danger report-error" role="alert" aria-live="polite" hidden></div>

    <section class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4" aria-label="Resumen de ventas">
        <div class="col">
            <article class="card kpi-card kpi-card-primary h-100">
                <div class="card-body">
                    <div class="kpi-heading">
                        <h2 class="kpi-label">Monto total</h2>
                        <span class="kpi-highlight">Total acumulado</span>
                    </div>
                    <p id="ventasTotales" class="kpi-value kpi-value-primary">$<?= number_format($resumen['total'], 0, ',', '.') ?></p>
                    <p class="kpi-footnote">Ingresos según los filtros activos</p>
                </div>
            </article>
        </div>
        <div class="col">
            <article class="card kpi-card h-100">
                <div class="card-body">
                    <h2 class="kpi-label">Transacciones</h2>
                    <p id="cantidadTransacciones" class="kpi-value"><?= $resumen['transacciones'] ?></p>
                    <p class="kpi-footnote">Ventas registradas</p>
                </div>
            </article>
        </div>
        <div class="col">
            <article class="card kpi-card h-100">
                <div class="card-body">
                    <h2 class="kpi-label">Promedio por venta</h2>
                    <p id="promedioVenta" class="kpi-value">$<?= number_format($resumen['promedio'], 0, ',', '.') ?></p>
                    <p class="kpi-footnote">Monto promedio por transacción</p>
                </div>
            </article>
        </div>
        <div class="col">
            <article class="card kpi-card h-100">
                <div class="card-body">
                    <h2 class="kpi-label">Unidades vendidas</h2>
                    <p id="unidadesVendidas" class="kpi-value"><?= $resumen['unidades'] ?></p>
                    <p class="kpi-footnote">Unidades en las ventas filtradas</p>
                </div>
            </article>
        </div>
    </section>

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
                                <td data-order="<?= $escapar($reporte['id_ventas']) ?>"><?= $escapar($reporte['id_ventas']) ?></td>
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
