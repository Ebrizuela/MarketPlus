<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../funciones/navegacion.php';

requerirRol(['administrador']);

$resumen = ['ventas_hoy' => 0, 'ventas_mes' => 0, 'total_ventas' => 0, 'cantidad_ventas' => 0, 'clientes' => 0, 'productos' => 0];
$ventasRecientes = [];
$error = null;
try {
    $pdo = conectar();
    $resumen = $pdo->query(
        "SELECT
            COALESCE(SUM(CASE WHEN DATE(fecha_hora) = CURDATE() THEN total_venta ELSE 0 END), 0) AS ventas_hoy,
            COALESCE(SUM(CASE WHEN YEAR(fecha_hora) = YEAR(CURDATE()) AND MONTH(fecha_hora) = MONTH(CURDATE()) THEN total_venta ELSE 0 END), 0) AS ventas_mes,
            COALESCE(SUM(total_venta), 0) AS total_ventas,
            COUNT(*) AS cantidad_ventas
         FROM Ventas"
    )->fetch();
    $resumen['clientes'] = (int) $pdo->query('SELECT COUNT(*) FROM Clientes')->fetchColumn();
    $resumen['productos'] = (int) $pdo->query('SELECT COUNT(*) FROM Productos')->fetchColumn();
    $ventasRecientes = $pdo->query(
        "SELECT DATE(fecha_hora) AS fecha, COUNT(*) AS cantidad,
                COALESCE(SUM(total_venta), 0) AS total
         FROM Ventas
         WHERE fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
         GROUP BY DATE(fecha_hora)
         ORDER BY fecha DESC"
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Error al generar reportes del administrador: ' . $e->getMessage());
    http_response_code(503);
    $error = 'No fue posible generar el reporte. Comprueba la conexión a la base de datos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - MarketPlus</title>
    <link rel="stylesheet" href="../css/navegacion.css">
    <style>
        body { margin: 0; background: #f2f5f7; color: #1e293b; font-family: Arial, sans-serif; }
        main { max-width: 1100px; margin: 24px auto; padding: 0 20px; }
        .metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin: 20px 0; }
        .metric, .table-wrap { padding: 18px; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; }
        .metric span { display: block; color: #64748b; margin-bottom: 8px; }
        .metric strong { font-size: 22px; }
        .table-wrap { overflow-x: auto; padding: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 11px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        th { background: #edf2f7; }
        .message { padding: 12px; border-radius: 8px; background: #fee2e2; color: #991b1b; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; margin: 18px 0; }
    </style>
    <link rel="stylesheet" href="../css/marketplus-theme.css?v=1">
</head>
<body>
    <?php renderNavegacion(null, BASE_URL . '/index.php'); ?>
    <main>
        <h1>Reportes administrativos</h1>
        <p>Resumen de ventas y actividad comercial.</p>
        <?php if ($error !== null): ?>
            <p class="message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php else: ?>
            <section class="metrics" aria-label="Resumen de ventas">
                <div class="metric"><span>Ventas de hoy</span><strong>$<?= number_format((float) $resumen['ventas_hoy'], 2, ',', '.') ?></strong></div>
                <div class="metric"><span>Ventas del mes</span><strong>$<?= number_format((float) $resumen['ventas_mes'], 2, ',', '.') ?></strong></div>
                <div class="metric"><span>Ventas acumuladas</span><strong>$<?= number_format((float) $resumen['total_ventas'], 2, ',', '.') ?></strong></div>
                <div class="metric"><span>Transacciones</span><strong><?= (int) $resumen['cantidad_ventas'] ?></strong></div>
                <div class="metric"><span>Clientes registrados</span><strong><?= (int) $resumen['clientes'] ?></strong></div>
                <div class="metric"><span>Productos registrados</span><strong><?= (int) $resumen['productos'] ?></strong></div>
            </section>
            <h2>Ventas de los últimos 7 días</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Fecha</th><th>Transacciones</th><th>Total vendido</th></tr></thead>
                    <tbody>
                        <?php if ($ventasRecientes): ?>
                            <?php foreach ($ventasRecientes as $dia): ?>
                                <tr>
                                    <td><?= htmlspecialchars($dia['fecha'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= (int) $dia['cantidad'] ?></td>
                                    <td>$<?= number_format((float) $dia['total'], 2, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3">No hay ventas registradas en este período.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <div class="actions">
            <a class="mp-btn mp-btn-outline" href="../ventas/consultar_ventas.php">Consultar ventas</a>
            <a class="mp-btn mp-btn-outline" href="inventario.php">Revisar inventario</a>
        </div>
    </main>
</body>
</html>
