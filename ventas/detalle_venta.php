<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../funciones/navegacion.php';

requerirRol(['administrador', 'supervisor', 'vendedor']);

$idVenta = (string) ($_GET['id'] ?? '');
$venta = null;
$detalles = [];
$error = null;

if (!ctype_digit($idVenta) || (int) $idVenta < 1) {
    http_response_code(400);
    $error = 'El identificador de venta no es válido.';
} else {
    try {
        $pdo = conectar();
        $sqlVenta = "SELECT v.id_ventas, v.fecha_hora, v.total_venta,
                            COALESCE(NULLIF(CONCAT_WS(' ', c.nombre, c.apellidos), ''), 'Cliente general') AS cliente_nombre,
                            COALESCE(NULLIF(CONCAT_WS(' ', f.nombre, f.apellido), ''), 'No identificado') AS funcionario_nombre
                     FROM Ventas v
                     LEFT JOIN Clientes c ON v.id_cliente = c.id_cliente
                     LEFT JOIN funcionarios f ON v.id_funcionario = f.id_funcionario
                     WHERE v.id_ventas = :id_venta";
        $parametrosVenta = [':id_venta' => (int) $idVenta];
        if ($_SESSION['usuario']['rol'] === 'vendedor') {
            $sqlVenta .= ' AND v.id_funcionario = :id_funcionario';
            $parametrosVenta[':id_funcionario'] = (int) $_SESSION['usuario']['id'];
        }

        $stmtVenta = $pdo->prepare($sqlVenta);
        $stmtVenta->execute($parametrosVenta);
        $venta = $stmtVenta->fetch();

        if (!$venta) {
            http_response_code(404);
            $error = 'La venta no existe o no tienes permiso para consultarla.';
        } else {
            $stmtDetalle = $pdo->prepare(
                "SELECT COALESCE(p.nombre, 'Producto no disponible') AS producto_nombre,
                        dv.cantidad, dv.precio_unitario, dv.subtotal
                 FROM Detalle_Ventas dv
                 LEFT JOIN Productos p ON dv.id_producto = p.id_producto
                 WHERE dv.id_ventas = :id_venta
                 ORDER BY p.nombre"
            );
            $stmtDetalle->execute([':id_venta' => (int) $idVenta]);
            $detalles = $stmtDetalle->fetchAll();
        }
    } catch (PDOException $e) {
        error_log('Error al consultar detalle de venta: ' . $e->getMessage());
        http_response_code(503);
        $error = 'No fue posible cargar el detalle de la venta. Comprueba la conexión a la base de datos.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle Venta #<?php echo htmlspecialchars($idVenta, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="../css/navegacion.css">
    <style>
        body { margin: 0; background: #f2f5f7; font-family: Arial, sans-serif; }
        .content { max-width: 1000px; margin: 24px auto; padding: 0 20px; }
        .table-wrap { overflow-x: auto; background: #fff; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border-bottom: 1px solid #cbd5e0; padding: 10px; text-align: left; }
        th { background-color: #edf2f7; }
        .alert { padding: 12px; border-radius: 8px; background: #fee2e2; color: #991b1b; }
    </style>
    <link rel="stylesheet" href="../css/marketplus-theme.css?v=1">
</head>
<body>

    <?php renderNavegacion('consultar_ventas.php', BASE_URL . '/index.php'); ?>

    <main class="content">
        <h1>Detalle de la venta #<?= htmlspecialchars($idVenta, ENT_QUOTES, 'UTF-8') ?></h1>

        <?php if ($error !== null): ?>
            <p class="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php elseif ($venta): ?>
            <p>
                Fecha: <?= htmlspecialchars($venta['fecha_hora'], ENT_QUOTES, 'UTF-8') ?> |
                Cliente: <?= htmlspecialchars($venta['cliente_nombre'], ENT_QUOTES, 'UTF-8') ?> |
                Atendido por: <?= htmlspecialchars($venta['funcionario_nombre'], ENT_QUOTES, 'UTF-8') ?> |
                Total: $<?= number_format((float) $venta['total_venta'], 2, ',', '.') ?>
            </p>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Producto</th><th>Cantidad</th><th>Precio unitario</th><th>Subtotal</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($detalles): ?>
                            <?php foreach ($detalles as $detalle): ?>
                                <tr>
                                    <td><?= htmlspecialchars($detalle['producto_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= (int) $detalle['cantidad'] ?></td>
                                    <td>$<?= number_format((float) $detalle['precio_unitario'], 2, ',', '.') ?></td>
                                    <td>$<?= number_format((float) $detalle['subtotal'], 2, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4">Esta venta no tiene productos en el detalle.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>