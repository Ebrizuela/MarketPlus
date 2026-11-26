<?php
ob_start();
session_start();
    
require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/navegacion.php';

// 🔒 Solo administradores
requerirRol(['administrador']);

$errorProductos = null;
try {
    $pdo = conectar();
} catch (PDOException $e) {
    error_log('Error de conexión en inventario: ' . $e->getMessage());
    http_response_code(503);
    exit('No se pudo conectar con la base de datos para cargar el inventario.');
}

$esAdmin = esAdmin();
$rolActual = $_SESSION['usuario']['rol'] ?? null;

$productos = [];
try {
    $stmt = $pdo->query("SELECT p.*, c.nombre AS categoria 
                         FROM Productos p 
                         INNER JOIN Categorias c ON p.id_categorias = c.id_categorias 
                         ORDER BY p.id_producto DESC");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error al cargar productos del inventario: ' . $e->getMessage());
    $errorProductos = 'No se pudo cargar el inventario. Comprueba la conexión a la base de datos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlus - Inventario</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/estilos.css?v=1">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/marketplus-theme.css?v=1">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/navegacion.css">
</head>
<body>

<?php renderNavegacion(null, BASE_URL . '/index.php'); ?>

<div class="container">

    <div class="header">
        <div>
            <h2 style="margin:0; color: #2563eb;">MarketPlus - Inventario</h2>
        </div>

        <div style="display: flex; gap: 15px; align-items: center;">
            <span style="background: #10b981; color: white; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 13px;">Modo Admin</span>

            <div class="menu-hamburguesa-container">
                <button class="btn-hamburguesa" aria-label="Abrir menú">☰ Menú</button>
                <div class="menu-desplegable">
                    <a href="<?php echo BASE_URL; ?>/index.php">Catálogo Principal</a>
                    <a href="<?php echo BASE_URL; ?>/public/carrito.php">Ver Carrito</a>
                    <a href="<?php echo BASE_URL; ?>/administrador/categorias.php">Gestión de Categorías</a>
                    <a href="<?php echo BASE_URL; ?>/administrador/inventario.php">Control de Inventario</a>
                    <a href="<?php echo BASE_URL; ?>/supervisor/devoluciones.php">Gestor de Devoluciones</a>
                    <div class="menu-divider"></div>
                    <a href="<?php echo BASE_URL; ?>/includes/logout.php" class="item-logout">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-bottom: 20px;">Control de Inventario y Stock</h3>

        <?php if ($errorProductos !== null): ?>
            <p class="alert error"><?php echo htmlspecialchars($errorProductos, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php elseif (empty($productos)): ?>
            <p style="text-align: center; color: var(--text-muted); padding: 20px 0;">No hay productos registrados.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p): 
                        $stockBajo = ((int)$p['stock'] < 5);
                    ?>
                        <tr>
                            <td><strong>#<?php echo $p['id_producto']; ?></strong></td>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($p['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($p['categoria']); ?></td>
                            <td>$ <?php echo number_format((float)$p['precio'], 0, ',', '.'); ?></td>
                            <td>
                                <span style="font-weight: 700; color: <?php echo $stockBajo ? 'var(--danger)' : 'var(--text-dark)'; ?>;">
                                    <?php echo (int)$p['stock']; ?> unidades
                                </span>
                                <?php if ($stockBajo): ?>
                                    <span style="background:#fee2e2; color:#991b1b; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:700; margin-left:8px;">Stock bajo</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

</body>
</html>