<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../funciones/navegacion.php';
requerirRol(['administrador', 'supervisor']);

$f_vendedor = $_GET['vendedor'] ?? '';
$f_cliente  = $_GET['cliente'] ?? '';
$f_producto = $_GET['producto'] ?? '';
$f_desde    = $_GET['fecha_desde'] ?? '';
$f_hasta    = $_GET['fecha_hasta'] ?? '';


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

if (!empty($f_vendedor)) { $sql .= " AND CONCAT(f.nombre, ' ', f.apellido) = :vendedor"; $params[':vendedor'] = $f_vendedor; }
if (!empty($f_cliente))  { $sql .= " AND c.id_cliente = :cliente"; $params[':cliente'] = $f_cliente; }
if (!empty($f_producto)) { $sql .= " AND p.id_producto = :producto"; $params[':producto'] = $f_producto; }
if (!empty($f_desde))    { $sql .= " AND DATE(v.fecha_hora) >= :desde"; $params[':desde'] = $f_desde; }
if (!empty($f_hasta))    { $sql .= " AND DATE(v.fecha_hora) <= :hasta"; $params[':hasta'] = $f_hasta; }

$sql .= " ORDER BY v.fecha_hora DESC";
$pdo = conectar();
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$detalles = $stmt->fetchAll();

$ventas = [];
foreach ($detalles as $detalle) {
    $id_venta = $detalle['id_ventas'];
    if (!isset($ventas[$id_venta])) {
        $ventas[$id_venta] = [
            'id_ventas' => $detalle['id_ventas'],
            'fecha_hora' => $detalle['fecha_hora'],
            'vendedor' => $detalle['vendedor'],
            'cliente' => $detalle['cliente'],
            'detalles' => [],
        ];
    }
    $ventas[$id_venta]['detalles'][] = $detalle;
}

$suma_monto_total = array_sum(array_column($detalles, 'subtotal'));
$suma_cantidad    = array_sum(array_column($detalles, 'cantidad'));

$filename = "Reporte_Ventas_MarketPlus_" . date('Ymd_His') . ".xls";


header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF"; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
  
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
                <?php foreach ($ventas as $index => $venta): ?>
                    <?php foreach ($venta['detalles'] as $indice => $row): ?>
                        <tr class="<?= ($index + $indice) % 2 === 0 ? '' : 'bg-cebra' ?>">
                            <?php if ($indice === 0): ?>
                                <td class="text-center bold" rowspan="<?= count($venta['detalles']) ?>"><span class="badge-id">#<?= $venta['id_ventas'] ?></span></td>
                                <td class="text-center" rowspan="<?= count($venta['detalles']) ?>"><?= date('d/m/Y H:i', strtotime($venta['fecha_hora'])) ?></td>
                                <td rowspan="<?= count($venta['detalles']) ?>"><?= htmlspecialchars($venta['vendedor']) ?></td>
                                <td rowspan="<?= count($venta['detalles']) ?>"><?= htmlspecialchars($venta['cliente']) ?></td>
                            <?php endif; ?>
                            <td><?= htmlspecialchars($row['producto']) ?></td>
                            <td class="text-center bold"><?= $row['cantidad'] ?></td>
                            <td class="text-right">$<?= number_format($row['precio_unitario'], 0, ',', '.') ?></td>
                            <td class="text-right bold">$<?= number_format($row['subtotal'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
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