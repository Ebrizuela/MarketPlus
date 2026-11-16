<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../funciones/conexion.php';

try {
  
    $clientes = $pdo->query("SELECT id_cliente, CONCAT(nombre, ' ', apellidos) AS nombre_completo FROM Clientes")->fetchAll(PDO::FETCH_ASSOC);
    $productos = $pdo->query("SELECT id_producto, nombre FROM Productos")->fetchAll(PDO::FETCH_ASSOC);

    $fecha_inicio = $_GET['fecha_inicio'] ?? '';
    $fecha_fin    = $_GET['fecha_fin'] ?? '';
    $id_cliente   = !empty($_GET['id_cliente']) ? $_GET['id_cliente'] : null;
    $id_producto  = !empty($_GET['id_producto']) ? $_GET['id_producto'] : null;

    $where = ["1=1"];
    $params = [];

    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        $where[] = "DATE(v.fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin";
        $params[':fecha_inicio'] = $fecha_inicio;
        $params[':fecha_fin']    = $fecha_fin;
    }
    if (!empty($id_cliente)) {
        $where[] = "v.id_cliente = :id_cliente";
        $params[':id_cliente'] = $id_cliente;
    }
    if (!empty($id_producto)) {
        $where[] = "dv.id_producto = :id_producto";
        $params[':id_producto'] = $id_producto;
    }

    $whereSql = implode(" AND ", $where);

    $sql = "SELECT 
                v.id_ventas,
                v.fecha_hora,
                CONCAT(c.nombre, ' ', c.apellidos) AS cliente,
                p.nombre AS producto,
                dv.cantidad,
                dv.precio_unitario,
                dv.subtotal,
                v.total_venta
            FROM Ventas v
            INNER JOIN Clientes c ON v.id_cliente = c.id_cliente
            INNER JOIN Detalle_Ventas dv ON v.id_ventas = dv.id_ventas
            INNER JOIN Productos p ON dv.id_producto = p.id_producto
            WHERE $whereSql
            ORDER BY v.fecha_hora DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $total_ingresos = array_sum(array_column($reportes, 'subtotal'));
    $total_articulos = array_sum(array_column($reportes, 'cantidad'));
    $cantidad_registros = count($reportes);
    $promedio_venta = $cantidad_registros > 0 ? $total_ingresos / $cantidad_registros : 0;

    $tendencias = [];
    foreach ($reportes as $row) {
        $fecha = date('Y-m-d', strtotime($row['fecha_hora']));
        $tendencias[$fecha] = ($tendencias[$fecha] ?? 0) + $row['subtotal'];
    }
    ksort($tendencias);

} catch (Throwable $e) {
    die("Error general: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Ventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/reportes_ventas.css">
    

    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
</head>
<body class="reporte-ventas-body">
<div class="container my-4 reporte-ventas">
    <h2 class="mb-4">Panel de Reportes de Ventas</h2>


    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="reporte_ventas.php" class="row g-3" data-report-endpoint="obtener_reporte.php">
                <div class="col-md-3">
                    <label class="form-label">Fecha Inicio:</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fecha_inicio) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Fin:</label>
                    <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fecha_fin) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cliente:</label>
                    <select name="id_cliente" class="form-select">
                        <option value="">-- Todos los Clientes --</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id_cliente'] ?>" <?= $id_cliente == $c['id_cliente'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nombre_completo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Producto:</label>
                    <select name="id_producto" class="form-select">
                        <option value="">-- Todos los Productos --</option>
                        <?php foreach ($productos as $p): ?>
                            <option value="<?= $p['id_producto'] ?>" <?= $id_producto == $p['id_producto'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 text-end mt-3">
                    <button type="submit" class="btn btn-primary">Filtrar Reporte</button>
                    <a href="reporte_ventas.php" class="btn btn-secondary">Limpiar Filtros</a>
                </div>
            </form>
        </div>
    </div>

    <div id="reporteError" class="alert alert-danger" role="alert" aria-live="polite" hidden></div>


    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Ventas Totales</h5>
                    <h3 id="ventasTotales">$<?= number_format($total_ingresos, 0, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Promedio Venta</h5>
                    <h3 id="promedioVenta">$<?= number_format($promedio_venta, 0, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Unidades Vendidas</h5>
                    <h3 id="unidadesVendidas"><?= $total_articulos ?></h3>
                </div>
            </div>
        </div>
    </div>


    <div id="contenedorGrafico" class="card mb-4" <?= empty($tendencias) ? 'hidden' : '' ?>>
        <div class="card-header">Tendencia de Ventas por Fecha</div>
        <div class="card-body">
            <canvas id="graficoVentas" 
                    data-labels='<?= json_encode(array_keys($tendencias), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?>' 
                    data-values='<?= json_encode(array_values($tendencias), JSON_HEX_TAG | JSON_HEX_AMP) ?>'>
            </canvas>
        </div>
    </div>


    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Detalle de Ventas</span>
            <div>
                <a id="exportarExcel" href="exportar_excel.php?<?= http_build_query($_GET) ?>" class="btn btn-sm btn-outline-success">Exportar a Excel</a>
                <a id="exportarPdf" href="exportar_pdf.php?<?= htmlspecialchars(http_build_query($_GET), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-outline-danger">Exportar a PDF</a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID Venta</th>
                            <th>Fecha y Hora</th>
                            <th>Cliente</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="detalleVentas">
                        <?php if (empty($reportes)): ?>
                            <tr><td colspan="7" class="text-center py-4">No se encontraron datos.</td></tr>
                        <?php else: ?>
                            <?php foreach ($reportes as $r): ?>
                                <tr>
                                    <td><?= $r['id_ventas'] ?></td>
                                    <td><?= $r['fecha_hora'] ?></td>
                                    <td><?= htmlspecialchars($r['cliente']) ?></td>
                                    <td><?= htmlspecialchars($r['producto']) ?></td>
                                    <td><?= $r['cantidad'] ?></td>
                                    <td>$<?= number_format($r['precio_unitario'], 0, ',', '.') ?></td>
                                    <td>$<?= number_format($r['subtotal'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="../js/reportes_ventas.js" defer></script>
</body>
</html>