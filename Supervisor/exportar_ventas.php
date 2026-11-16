<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../funciones/navegacion.php';

requerirRol(['administrador', 'supervisor']);

if (($_GET['descargar'] ?? '') !== '1') {
    $parametrosExportacion = $_GET;
    unset($parametrosExportacion['descargar']);
    $parametrosExportacion['descargar'] = '1';
    $urlDescarga = BASE_URL . '/Supervisor/exportar_ventas.php?' . http_build_query($parametrosExportacion);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Exportar ventas - MarketPlus</title>
        <link rel="stylesheet" href="../css/estilos.css">
        <link rel="stylesheet" href="../css/navegacion.css">
        <style>
            .export-content { max-width: 760px; margin: 32px auto; padding: 0 20px; }
            .export-card { padding: 28px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06); }
            .export-card p { color: #64748b; }
            .export-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 20px; }
        </style>
        <link rel="stylesheet" href="../css/marketplus-theme.css?v=1">
</head>
    <body>
        <?php renderNavegacion(null, BASE_URL . '/index.php'); ?>
        <main class="export-content">
            <section class="export-card">
                <h1>Exportar ventas</h1>
                <p>Descarga el reporte de ventas en formato Excel. El archivo incluirá los filtros que seleccionaste en Supervisar ventas.</p>
                <div class="export-actions">
                    <a class="mp-btn mp-btn-primary" href="<?= htmlspecialchars($urlDescarga, ENT_QUOTES, 'UTF-8') ?>">Descargar Excel</a>
                    <a class="mp-btn mp-btn-outline" href="supervisar_ventas.php">Volver a supervisar ventas</a>
                </div>
            </section>
        </main>
    </body>
    </html>
    <?php
    exit;
}

$pdo = conectar();

// Capturar filtros
$f_vendedor = $_GET['vendedor'] ?? '';
$f_cliente  = $_GET['cliente'] ?? '';
$f_producto = $_GET['producto'] ?? '';
$f_desde    = $_GET['fecha_desde'] ?? '';
$f_hasta    = $_GET['fecha_hasta'] ?? '';

// Consulta SQL con filtros
$sql = "SELECT 
            v.id_ventas,
            v.fecha_hora,
            CONCAT(f.nombre, ' ', f.apellido) AS vendedor,
            c.nombre AS cliente,
            p.nombre AS producto,
            dv.cantidad,
            dv.precio_unitario,
            dv.subtotal
        FROM Ventas v
        INNER JOIN funcionarios f ON v.id_funcionario = f.id_funcionario
        INNER JOIN Clientes c ON v.id_cliente = c.id_cliente
        INNER JOIN Detalle_Ventas dv ON v.id_ventas = dv.id_ventas
        INNER JOIN Productos p ON dv.id_producto = p.id_producto
        WHERE 1=1";

$params = [];

if (!empty($f_vendedor)) { $sql .= " AND f.id_funcionario = :vendedor"; $params[':vendedor'] = $f_vendedor; }
if (!empty($f_cliente))  { $sql .= " AND c.id_cliente = :cliente"; $params[':cliente'] = $f_cliente; }
if (!empty($f_producto)) { $sql .= " AND p.id_producto = :producto"; $params[':producto'] = $f_producto; }
if (!empty($f_desde))    { $sql .= " AND DATE(v.fecha_hora) >= :desde"; $params[':desde'] = $f_desde; }
if (!empty($f_hasta))    { $sql .= " AND DATE(v.fecha_hora) <= :hasta"; $params[':hasta'] = $f_hasta; }

$sql .= " ORDER BY v.fecha_hora DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ventas = $stmt->fetchAll();

// Totales para el resumen inferior
$suma_monto_total = array_sum(array_column($ventas, 'subtotal'));
$suma_cantidad    = array_sum(array_column($ventas, 'cantidad'));

$filename = "Reporte_Ventas_MarketPlus_" . date('Ymd_His') . ".xls";

// Encabezados HTTP para la descarga en Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// UTF-8 BOM
echo "\xEF\xBB\xBF"; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        /* Carga dinámica del archivo CSS externo */
        <?= file_get_contents(__DIR__ . '/../css/supervisor.css'); ?>
    </style>
</head>
<body>

    <div class="titulo">REPORTE GENERAL DE VENTAS - MARKETPLUS</div>
    <div class="subtitulo">Generado el: <?= date('d/m/Y H:i') ?></div>

    <table>
        <thead>
            <tr>
                <th>ID VENTA</th>
                <th>FECHA Y HORA</th>
                <th>VENDEDOR</th>
                <th>CLIENTE</th>
                <th>PRODUCTO</th>
                <th>CANT.</th>
                <th>P. UNITARIO</th>
                <th>SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($ventas)): ?>
                <tr>
                    <td colspan="8" class="text-center">No hay registros de ventas para mostrar.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($ventas as $index => $row): ?>
                    <tr class="<?= $index % 2 === 0 ? '' : 'bg-cebra' ?>">
                        <td class="text-center bold"><span class="badge-id">#<?= $row['id_ventas'] ?></span></td>
                        <td class="text-center"><?= date('d/m/Y H:i', strtotime($row['fecha_hora'])) ?></td>
                        <td><?= htmlspecialchars($row['vendedor']) ?></td>
                        <td><?= htmlspecialchars($row['cliente']) ?></td>
                        <td><?= htmlspecialchars($row['producto']) ?></td>
                        <td class="text-center bold"><?= $row['cantidad'] ?></td>
                        <td class="text-right">$<?= number_format($row['precio_unitario'], 0, ',', '.') ?></td>
                        <td class="text-right bold">$<?= number_format($row['subtotal'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="fila-total">
                <td colspan="5" class="text-right bold">TOTAL GENERAL:</td>
                <td class="text-center bold"><?= $suma_cantidad ?></td>
                <td></td>
                <td class="text-right bold">$<?= number_format($suma_monto_total, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>

</body>
</html>