<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/autorizacion_ventas.php';

$usuarioSesion = obtenerUsuarioSesionVentas();
if ($usuarioSesion === null) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

require_once __DIR__ . '/../funciones/validaciones.php';
require_once __DIR__ . '/../funciones/conexion.php';

function escapar(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function mostrarError(int $codigo, string $mensaje): void
{
    http_response_code($codigo);
    exit($mensaje);
}

$idVenta = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($idVenta === false || $idVenta === null) {
    mostrarError(400, 'Debe proporcionar un ID de venta válido.');
}

try {
    $pdo = conectar();
    if (!funcionarioTieneAccesoVentas(
        $pdo,
        $usuarioSesion['id_funcionario'],
        $usuarioSesion['rol']
    )) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }

    $stmtVenta = $pdo->prepare(
        'SELECT v.id_ventas, v.total_venta, v.fecha_hora, v.id_funcionario,
                c.rut, c.nombre AS cliente_nombre, c.apellidos AS cliente_apellidos,
                m.nombre_medio,
                COALESCE(NULLIF(CONCAT_WS(\' \', f.nombre, f.apellido), \'\'), \'No disponible\') AS vendedor
         FROM Ventas v
         LEFT JOIN Clientes c ON v.id_cliente = c.id_cliente
         LEFT JOIN Medio_pago m ON v.id_medio_pago = m.id_medio_pago
         LEFT JOIN funcionarios f ON v.id_funcionario = f.id_funcionario
         WHERE v.id_ventas = :id'
    );
    $stmtVenta->execute([':id' => $idVenta]);
    $venta = $stmtVenta->fetch(PDO::FETCH_ASSOC);
    if (!$venta) {
        mostrarError(404, 'La venta solicitada no existe.');
    }

    $stmtDetalle = $pdo->prepare(
        'SELECT p.nombre AS producto_nombre, d.precio_unitario,
                SUM(d.cantidad) AS cantidad, SUM(d.subtotal) AS subtotal
         FROM Detalle_Ventas d
         INNER JOIN Productos p ON d.id_producto = p.id_producto
         WHERE d.id_ventas = :id
         GROUP BY d.id_producto, p.nombre, d.precio_unitario
         ORDER BY p.nombre'
    );
    $stmtDetalle->execute([':id' => $idVenta]);
    $detalles = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log('Error al consultar boleta de venta: ' . $e->getMessage());
    mostrarError(500, 'No fue posible cargar la boleta. Intente nuevamente.');
}

$rutCliente = is_string($venta['rut'] ?? null) && validarRut($venta['rut'])
    ? $venta['rut']
    : 'No disponible (RUT inválido)';
$fechaHora = DateTime::createFromFormat('!Y-m-d H:i:s', (string) $venta['fecha_hora']);
$fechaHoraMostrar = $fechaHora ? $fechaHora->format('d/m/Y H:i:s') : (string) $venta['fecha_hora'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta N° <?= escapar((string) $venta['id_ventas']) ?></title>
    <link rel="stylesheet" href="../css/boleta.css">
</head>
<body>

<div class="boleta-container">
    <div class="boleta-card">
        <div class="stamp-paid">Pagado</div>

        <div class="header-brand">
            <div>
                <h1 class="brand-title">MarketPlus</h1>
                <p class="brand-sub">Comprobante de Venta Electrónico</p>
            </div>
            <div class="folio-badge">
                <span class="label">Folio Venta</span>
                <span class="number">#<?= escapar(str_pad((string) $venta['id_ventas'], 6, '0', STR_PAD_LEFT)) ?></span>
            </div>
        </div>

        <div class="info-section">
            <div class="info-block">
                <div><strong>Fecha:</strong> <?= escapar($fechaHoraMostrar) ?></div>
                <div><strong>Pago:</strong> <?= escapar((string) ($venta['nombre_medio'] ?? 'No disponible')) ?></div>
                <div><strong>Vendedor:</strong> <?= escapar((string) $venta['vendedor']) ?></div>
            </div>
            <div class="info-block">
                <div><strong>RUT:</strong> <?= escapar($rutCliente) ?></div>
                <div><strong>Cliente:</strong> <?= escapar(trim(($venta['cliente_nombre'] ?? '') . ' ' . ($venta['cliente_apellidos'] ?? '')) ?: 'No disponible') ?></div>
            </div>
        </div>

        <table class="tabla-productos">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-center">Cant.</th>
                    <th class="text-right">Precio U.</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($detalles === []): ?>
                    <tr><td colspan="4">Esta venta no tiene productos registrados.</td></tr>
                <?php else: ?>
                    <?php foreach ($detalles as $item): ?>
                    <tr>
                        <td><?= escapar((string) $item['producto_nombre']) ?></td>
                        <td class="text-center"><?= escapar((string) $item['cantidad']) ?></td>
                        <td class="text-right">$<?= number_format((float) $item['precio_unitario'], 0, ',', '.') ?></td>
                        <td class="text-right">$<?= number_format((float) $item['subtotal'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="summary-container">
            <div class="summary-box">
                <span class="label">Total Pagado</span>
                <span class="amount">$<?= number_format((float) $venta['total_venta'], 0, ',', '.') ?> CLP</span>
            </div>
        </div>

        <div class="footer-note">
            <p>¡Gracias por su compra en MarketPlus!</p>
        </div>
    </div>

    <div class="actions">
        <a href="registrar_venta.php" class="btn btn-back">← Nueva Venta</a>
        <?php if (($_SESSION['usuario']['rol'] ?? '') === 'administrador'): ?>
            <a href="../administrador/index.php" class="btn btn-back">Volver al Panel Administrador</a>
        <?php endif; ?>
        <button type="button" onclick="window.print()" class="btn btn-print">Imprimir / Guardar PDF</button>
    </div>
</div>

</body>
</html>
