<?php
ob_start();
session_start();

require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/navegacion.php';
$pdo = conectar();

requerirRol(['administrador', 'supervisor', 'vendedor']);

$esAdmin = esAdmin();
$rolActual = $_SESSION['usuario']['rol'] ?? null;

$ventas = [];
try {
    $sql = "SELECT v.id_ventas, v.fecha_hora, v.total_venta,
                   c.nombre AS cliente_nombre, c.apellidos AS cliente_apellido,
                   f.nombre AS func_nombre, f.apellido AS func_apellido,
                   mp.nombre AS medio_pago_nombre
            FROM Ventas v
            LEFT JOIN Clientes c ON v.id_cliente = c.id_cliente
            LEFT JOIN funcionarios f ON v.id_funcionario = f.id_funcionario
            LEFT JOIN Medio_pago mp ON v.id_medio_pago = mp.id_medio_pago
            ORDER BY v.id_ventas DESC
            LIMIT 100";
    $stmt = $pdo->query($sql);
    if ($stmt) {
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Fallback sin JOIN con Medio_pago si no existe
    try {
        $sql = "SELECT v.id_ventas, v.fecha_hora, v.total_venta,
                       c.nombre AS cliente_nombre, c.apellidos AS cliente_apellido,
                       f.nombre AS func_nombre, f.apellido AS func_apellido
                FROM Ventas v
                LEFT JOIN Clientes c ON v.id_cliente = c.id_cliente
                LEFT JOIN funcionarios f ON v.id_funcionario = f.id_funcionario
                ORDER BY v.id_ventas DESC
                LIMIT 100";
        $ventas = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        $ventas = [];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Ventas - MarketPlus</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/estilos.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/navegacion.css">
    <link rel="stylesheet" href="../css/marketplus-theme.css?v=1">
</head>
<body>

<?php renderNavegacion(null, BASE_URL . '/index.php'); ?>
<div class="container">

    <div class="header">
        <div>
            <h2 style="margin:0; color: #2563eb;">MarketPlus - Ventas</h2>
        </div>

        <div style="display: flex; gap: 15px; align-items: center;">
            <?php if ($esAdmin): ?>
                <span style="background: #10b981; color: white; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 13px;">Modo Admin</span>
            <?php else: ?>
                <span style="background: #3b82f6; color: white; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 13px;">Modo <?php echo htmlspecialchars(ucfirst($rolActual)); ?></span>
            <?php endif; ?>

            <div class="menu-hamburguesa-container">
                <button class="btn-hamburguesa" aria-label="Abrir menú">☰ Menú</button>
                <div class="menu-desplegable">
                    <a href="<?php echo BASE_URL; ?>/index.php">Catálogo Principal</a>
                    <a href="<?php echo BASE_URL; ?>/public/carrito.php">Ver Carrito</a>
                    <a href="<?php echo BASE_URL; ?>/supervisor/devoluciones.php">Gestor de Devoluciones</a>
                    <a href="<?php echo BASE_URL; ?>/supervisor/historial_devoluciones.php">Historial de Devoluciones</a>
                    <a href="<?php echo BASE_URL; ?>/supervisor/historial_ventas.php">Historial de Ventas</a>
                    <div class="menu-divider"></div>
                    <a href="<?php echo BASE_URL; ?>/includes/logout.php" class="item-logout">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <h3>Historial de Ventas Realizadas</h3>

        <?php if (empty($ventas)): ?>
            <p style="text-align: center; color: var(--text-muted); padding: 20px 0;">No hay ventas registradas aún.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>N° Venta</th>
                        <th>Fecha</th>
                        <th>Cliente / Funcionario</th>
                        <th>Medio de pago</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventas as $v): ?>
                        <tr>
                            <td><strong>#<?php echo $v['id_ventas']; ?></strong></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($v['fecha_hora'])); ?></td>
                            <td>
                                <?php 
                                if (!empty($v['cliente_nombre'])) {
                                    echo htmlspecialchars($v['cliente_nombre'] . ' ' . $v['cliente_apellido']);
                                } elseif (!empty($v['func_nombre'])) {
                                    echo 'Funcionario: ' . htmlspecialchars($v['func_nombre'] . ' ' . $v['func_apellido']);
                                } else {
                                    echo '<em style="color:#94a3b8;">Sin asignar</em>';
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($v['medio_pago_nombre'] ?? 'Efectivo'); ?></td>
                            <td><strong>$ <?php echo number_format($v['total_venta'], 0, ',', '.'); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

</body>
</html>