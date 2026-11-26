<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../funciones/conexion.php';

try {
    // Capturar filtros GET
    $fecha_inicio = $_GET['fecha_inicio'] ?? '';
    $fecha_fin    = $_GET['fecha_fin'] ?? '';
    $id_cliente   = !empty($_GET['id_cliente']) ? $_GET['id_cliente'] : null;
    $id_producto  = !empty($_GET['id_producto']) ? $_GET['id_producto'] : null;

    // Construcción dinámica de la consulta SQL
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
                dv.subtotal
            FROM Ventas v
            INNER JOIN Clientes c ON v.id_cliente = c.id_cliente
            INNER JOIN Detalle_Ventas dv ON v.id_ventas = dv.id_ventas
            INNER JOIN Productos p ON dv.id_producto = p.id_producto
            WHERE $whereSql
            ORDER BY v.fecha_hora DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $excelCss = file_get_contents(__DIR__ . '/../css/reportes_ventas.css');
    if ($excelCss === false) {
        throw new RuntimeException('No se pudo cargar la hoja de estilos de Excel.');
    }

    // Encabezados HTTP para forzar la descarga de Excel
    $filename = "Reporte_Ventas_" . date('Y-m-d_H-i') . ".xls";
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
    <title>Reporte de Ventas</title>
    <style><?= $excelCss ?></style>
</head>
<body class="excel-report">
    <h2 class="excel-title" style="margin: 0 0 8px; color: #198754; font-family: Arial, sans-serif; font-size: 20pt;">Reporte de Ventas</h2>
    <p class="excel-generated" style="margin: 0 0 18px; color: #555555; font-family: Arial, sans-serif; font-size: 10pt;"><strong>Fecha de generación:</strong> <?= date('d/m/Y H:i:s') ?></p>
    
    <table class="excel-table" border="1" cellpadding="7" cellspacing="0" style="width: 100%; border-collapse: collapse; table-layout: fixed; font-family: Arial, sans-serif; font-size: 10pt;">
        <colgroup>
            <col width="8%">
            <col width="14%">
            <col width="18%">
            <col width="28%">
            <col width="8%">
            <col width="12%">
            <col width="12%">
        </colgroup>
        <thead>
            <tr height="34">
                <th class="centro" bgcolor="#198754" style="border: 1px solid #146c43; background-color: #198754; color: #ffffff; font-weight: bold; text-align: center; vertical-align: middle;">ID Venta</th>
                <th class="centro" bgcolor="#198754" style="border: 1px solid #146c43; background-color: #198754; color: #ffffff; font-weight: bold; text-align: center; vertical-align: middle;">Fecha y Hora</th>
                <th bgcolor="#198754" style="border: 1px solid #146c43; background-color: #198754; color: #ffffff; font-weight: bold; text-align: left; vertical-align: middle;">Cliente</th>
                <th bgcolor="#198754" style="border: 1px solid #146c43; background-color: #198754; color: #ffffff; font-weight: bold; text-align: left; vertical-align: middle;">Producto</th>
                <th class="centro" bgcolor="#198754" style="border: 1px solid #146c43; background-color: #198754; color: #ffffff; font-weight: bold; text-align: center; vertical-align: middle;">Cantidad</th>
                <th class="monto" bgcolor="#198754" style="border: 1px solid #146c43; background-color: #198754; color: #ffffff; font-weight: bold; text-align: right; vertical-align: middle;">Precio Unitario ($)</th>
                <th class="monto" bgcolor="#198754" style="border: 1px solid #146c43; background-color: #198754; color: #ffffff; font-weight: bold; text-align: right; vertical-align: middle;">Subtotal ($)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total_general = 0;
            $total_cantidad = 0;
            $indice = 0;

            if (empty($reportes)):
            ?>
                <tr>
                    <td colspan="7" class="centro" style="border: 1px solid #d5dce2; padding: 10px; text-align: center;">No se encontraron registros con los filtros seleccionados.</td>
                </tr>
            <?php
            else:
                foreach ($reportes as $r):
                    $total_general += $r['subtotal'];
                    $total_cantidad += $r['cantidad'];
                    $colorFila = ($indice++ % 2 === 0) ? '#f3f7f5' : '#ffffff';
            ?>
                <tr bgcolor="<?= $colorFila ?>">
                    <td class="centro" style="border: 1px solid #d5dce2; padding: 7px; text-align: center; vertical-align: middle;"><?= $r['id_ventas'] ?></td>
                    <td class="centro" style="border: 1px solid #d5dce2; padding: 7px; text-align: center; vertical-align: middle;"><?= date('d-m-Y H:i', strtotime($r['fecha_hora'])) ?></td>
                    <td style="border: 1px solid #d5dce2; padding: 7px; vertical-align: middle;"><?= htmlspecialchars($r['cliente']) ?></td>
                    <td style="border: 1px solid #d5dce2; padding: 7px; vertical-align: middle;"><?= htmlspecialchars($r['producto']) ?></td>
                    <td class="centro" style="border: 1px solid #d5dce2; padding: 7px; text-align: center; vertical-align: middle;"><?= $r['cantidad'] ?></td>
                    <td class="monto" style="border: 1px solid #d5dce2; padding: 7px; text-align: right; vertical-align: middle;">$<?= number_format($r['precio_unitario'], 0, ',', '.') ?></td>
                    <td class="monto" style="border: 1px solid #d5dce2; padding: 7px; text-align: right; vertical-align: middle;">$<?= number_format($r['subtotal'], 0, ',', '.') ?></td>
                </tr>
            <?php
                endforeach;
            ?>
                <tr class="total-row">
                    <td colspan="4" class="monto" bgcolor="#e9ecef" style="border: 1px solid #d5dce2; border-top: 2px solid #198754; padding: 9px 7px; background-color: #e9ecef; font-weight: bold; text-align: right;">TOTALES:</td>
                    <td class="centro" bgcolor="#e9ecef" style="border: 1px solid #d5dce2; border-top: 2px solid #198754; padding: 9px 7px; background-color: #e9ecef; font-weight: bold; text-align: center;"><?= $total_cantidad ?></td>
                    <td bgcolor="#e9ecef" style="border: 1px solid #d5dce2; border-top: 2px solid #198754; background-color: #e9ecef;"></td>
                    <td class="monto" bgcolor="#e9ecef" style="border: 1px solid #d5dce2; border-top: 2px solid #198754; padding: 9px 7px; background-color: #e9ecef; color: #146c43; font-weight: bold; text-align: right;">$<?= number_format($total_general, 0, ',', '.') ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
<?php
} catch (Throwable $e) {
    die("Error al generar el archivo Excel: " . $e->getMessage());
}
?>