<?php
if (file_exists('../funciones/conexion.php')) {
    require_once '../funciones/conexion.php';
} elseif (file_exists('../conexion.php')) {
    require_once '../conexion.php';
} else {
    require_once 'conexion.php';
}

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename=Reporte_Stock_Precios_' . date('Y-m-d_H-i') . '.xls');
header('Pragma: no-cache');
header('Expires: 0');

echo "\xEF\xBB\xBF";

$sql = "SELECT 
            p.id_producto, 
            p.nombre AS producto, 
            c.nombre AS categoria, 
            p.precio, 
            p.stock 
        FROM Productos p
        LEFT JOIN Categorias c ON p.id_categorias = c.id_categorias
        ORDER BY p.id_producto ASC";

$pdo = conectar();

try {
    $stmt = $pdo->query($sql);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al generar el reporte: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/supervisor_productos.css">
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="6" class="title-header">REPORTE DE CONTROL DE INVENTARIO Y PRECIOS</th>
            </tr>
            <tr>
                <th colspan="6" class="subtitle-header">Generado el: <?= date('d/m/Y a las H:i') ?> | Sistema MarketPlus</th>
            </tr>
            <tr>
                <th style="width: 100px;">ID Producto</th>
                <th style="width: 380px;">Nombre del Producto</th>
                <th style="width: 220px;">Categoría</th>
                <th style="width: 150px;">Precio Unitario</th>
                <th style="width: 120px;">Stock Actual</th>
                <th style="width: 180px;">Estado Disponibilidad</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $item): ?>
                <?php 
                    if ($item['stock'] > 10) {
                        $estado = 'Disponible';
                        $classEstado = 'badge-disponible';
                    } elseif ($item['stock'] > 0) {
                        $estado = 'Stock Crítico';
                        $classEstado = 'badge-critico';
                    } else {
                        $estado = 'Agotado / Inactivo';
                        $classEstado = 'badge-agotado';
                    }
                ?>
                <tr>
                    <td class="text-center"><?= htmlspecialchars($item['id_producto'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-left"><?= htmlspecialchars($item['producto'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-left"><?= htmlspecialchars($item['categoria'] ?? 'Sin Categoría', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-right">$<?= number_format($item['precio'], 0, ',', '.') ?></td>
                    <td class="text-center"><?= htmlspecialchars($item['stock'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="<?= $classEstado ?>"><?= $estado ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>