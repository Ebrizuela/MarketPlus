<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../funciones/navegacion.php';

requerirRol(['administrador']);

$clientes = [];
$error = null;
try {
    $pdo = conectar();
    $clientes = $pdo->query(
        "SELECT c.id_cliente, c.nombre, c.apellidos, c.correo,
                COUNT(v.id_ventas) AS cantidad_ventas,
                COALESCE(SUM(v.total_venta), 0) AS total_compras
         FROM Clientes c
         LEFT JOIN Ventas v ON v.id_cliente = c.id_cliente
         GROUP BY c.id_cliente, c.nombre, c.apellidos, c.correo
         ORDER BY c.nombre, c.apellidos"
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Error al cargar clientes del administrador: ' . $e->getMessage());
    http_response_code(503);
    $error = 'No fue posible cargar los clientes. Comprueba la conexión a la base de datos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - MarketPlus</title>
    <link rel="stylesheet" href="../css/navegacion.css">
    <style>
        body { margin: 0; background: #f2f5f7; color: #1e293b; font-family: Arial, sans-serif; }
        main { max-width: 1200px; margin: 24px auto; padding: 0 20px; }
        .table-wrap { overflow-x: auto; background: #fff; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 11px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        th { background: #edf2f7; }
        .message { padding: 12px; border-radius: 8px; background: #fee2e2; color: #991b1b; }
    </style>
    <link rel="stylesheet" href="../css/marketplus-theme.css?v=1">
</head>
<body>
    <?php renderNavegacion(null, BASE_URL . '/index.php'); ?>
    <main>
        <h1>Clientes registrados</h1>
        <?php if ($error !== null): ?>
            <p class="message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Cliente</th><th>Correo</th><th>Ventas</th><th>Total comprado</th></tr>
                </thead>
                <tbody>
                    <?php if ($clientes): ?>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td><?= htmlspecialchars(trim($cliente['nombre'] . ' ' . ($cliente['apellidos'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($cliente['correo'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= (int) $cliente['cantidad_ventas'] ?></td>
                                <td>$<?= number_format((float) $cliente['total_compras'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php elseif ($error === null): ?>
                        <tr><td colspan="4">No hay clientes registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
