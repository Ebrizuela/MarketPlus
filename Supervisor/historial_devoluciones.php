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

$devoluciones = [];
try {
    $sql = "SELECT d.id_devolucion, d.folio, d.cantidad, d.motivo, d.fecha,
                   p.nombre AS producto, p.codigo,
                   v.id_ventas,
                   f.nombre AS func_nombre, f.apellido AS func_apellido
            FROM Devoluciones d
            LEFT JOIN Productos p ON d.id_producto = p.id_producto
            LEFT JOIN Ventas v ON d.id_venta = v.id_ventas
            LEFT JOIN funcionarios f ON d.id_funcionario = f.id_funcionario
            ORDER BY d.fecha DESC";
    $stmt = $pdo->query($sql);
    if ($stmt) {
        $devoluciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $devoluciones = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Devoluciones - MarketPlus</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/estilos.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/navegacion.css">
    <link rel="stylesheet" href="../css/marketplus-theme.css?v=1">
</head>
<body>

<?php renderNavegacion(null, BASE_URL . '/index.php'); ?>
<div class="container">

    <div class="header">
        <div>
            <h2 style="margin:0; color: #2563eb;">MarketPlus - Historial</h2>
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
                    <a href="<?php echo BASE_URL; ?>/supervisor/devoluciones.php">Registrar Devolución</a>
                    <a href="<?php echo BASE_URL; ?>/supervisor/historial_devoluciones.php">Historial de Devoluciones</a>
                    <div class="menu-divider"></div> 
                    <a href="<?php echo BASE_URL; ?>/includes/logout.php" class="item-logout">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-bottom: 20px;">Historial General de Devoluciones</h3>

        <?php if (empty($devoluciones)): ?>
            <p style="text-align: center; color: var(--text-muted); padding: 20px 0;">No hay devoluciones registradas en el sistema.</p>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                        <th style="padding: 12px 10px;">Folio</th>
                        <th style="padding: 12px 10px;">Venta</th>
                        <th style="padding: 12px 10px;">Producto</th>
                        <th style="padding: 12px 10px;">Cantidad</th>
                        <th style="padding: 12px 10px;">Motivo</th>
                        <th style="padding: 12px 10px;">Registró</th>
                        <th style="padding: 12px 10px;">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($devoluciones as $d): ?>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 10px; font-weight: 700; color: #2563eb;"><?php echo htmlspecialchars($d['folio']); ?></td>
                        <td style="padding: 10px;">#<?php echo (int)$d['id_ventas']; ?></td>
                        <td style="padding: 10px;">
                            <?php echo htmlspecialchars($d['producto'] ?? 'Producto eliminado'); ?>
                            <?php if (!empty($d['codigo'])): ?>
                                <br><small style="color:#94a3b8;">Código: <?php echo htmlspecialchars($d['codigo']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 10px;"><?php echo (int)$d['cantidad']; ?></td>
                        <td style="padding: 10px;"><?php echo htmlspecialchars($d['motivo']); ?></td>
                        <td style="padding: 10px;">
                            <?php 
                            if (!empty($d['func_nombre'])) {
                                echo htmlspecialchars($d['func_nombre'] . ' ' . $d['func_apellido']);
                            } else {
                                echo '<em style="color:#94a3b8;">N/A</em>';
                            }
                            ?>
                        </td>
                        <td style="padding: 10px; color: var(--text-muted); font-size: 13px;"><?php echo date('d/m/Y H:i', strtotime($d['fecha'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

</body>
</html>