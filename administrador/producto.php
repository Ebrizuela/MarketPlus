<?php
ob_start();
session_start();

require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../includes/auth.php';

// 🔒 Solo administradores
requerirRol(['administrador']);

try {
    $pdo = conectar();
} catch (PDOException $e) {
    error_log('Error de conexión en el detalle del producto: ' . $e->getMessage());
    http_response_code(503);
    exit('No se pudo conectar con la base de datos para cargar el producto.');
}

$esAdmin = esAdmin();
$rolActual = $_SESSION['usuario']['rol'] ?? null;

$idProducto = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$producto = null;

if ($idProducto > 0) {
    try {
        $stmt = $pdo->prepare("SELECT p.*, c.nombre AS categoria 
                               FROM Productos p 
                               INNER JOIN Categorias c ON p.id_categorias = c.id_categorias 
                               WHERE p.id_producto = ?");
        $stmt->execute([$idProducto]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error al consultar el producto ' . $idProducto . ': ' . $e->getMessage());
        http_response_code(503);
        exit('No se pudo cargar el producto. Comprueba la conexión a la base de datos.');
    }
}

if (!$producto) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

// Resolver imagen
$imgSrc = 'https://placehold.co/400x400?text=Sin+Imagen';
$coincidencias = glob(__DIR__ . "/../img/{$idProducto}.*");
if (!empty($coincidencias) && file_exists($coincidencias[0])) {
    $imgSrc = BASE_URL . '/img/' . basename($coincidencias[0]);
} elseif (!empty($producto['imagen']) && file_exists(__DIR__ . '/../' . $producto['imagen'])) {
    $imgSrc = BASE_URL . '/' . $producto['imagen'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($producto['nombre']); ?> - MarketPlus</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/estilos.producto.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="container">

    <div class="header">
        <div>
            <h2 style="margin:0; color: #2563eb;">MarketPlus - Admin</h2>
        </div>

        <div style="display: flex; gap: 15px; align-items: center;">
            <?php if ($esAdmin): ?>
                <span style="background: #10b981; color: white; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 13px;">Modo Admin</span>
            <?php endif; ?>

            <div class="menu-hamburguesa-container">
                <button class="btn-hamburguesa" aria-label="Abrir menú">☰ Menú</button>
                <div class="menu-desplegable">
                    <a href="<?php echo BASE_URL; ?>/index.php">Catálogo Principal</a>
                    <a href="<?php echo BASE_URL; ?>/public/carrito.php">Ver Carrito</a>
                    <a href="<?php echo BASE_URL; ?>/supervisor/devoluciones.php">Gestor de Devoluciones</a>
                    <div class="menu-divider"></div>
                    <a href="<?php echo BASE_URL; ?>/includes/logout.php" class="item-logout">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-bottom: 15px;">
        <a href="<?php echo BASE_URL; ?>/index.php" style="color: #64748b; text-decoration: none; font-size: 14px;">← Volver al Catálogo</a>
    </div>

    <div class="card" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        
        <div style="background: #f8fafc; display: flex; justify-content: center; align-items: center; min-height: 350px; border-radius: 8px; border: 1px solid #e2e8f0; padding: 10px;">
            <img src="<?php echo htmlspecialchars($imgSrc); ?>" 
                 alt="<?php echo htmlspecialchars($producto['nombre']); ?>" 
                 style="max-height: 320px; max-width: 100%; object-fit: contain;"
                 onerror="this.src='https://placehold.co/400x400?text=Sin+Imagen';">
        </div>

        <div>
            <span class="categoria-tag"><?php echo htmlspecialchars($producto['categoria']); ?></span>
            
            <?php if (!empty($producto['codigo'])): ?>
                <div style="color: #64748b; font-size: 12px; margin: 8px 0;">SKU: <?php echo htmlspecialchars($producto['codigo']); ?></div>
            <?php endif; ?>

            <h1 style="margin: 10px 0; font-size: 24px; color: #1e293b;"><?php echo htmlspecialchars($producto['nombre']); ?></h1>
            
            <div style="font-size: 26px; font-weight: 800; color: #2563eb; margin-bottom: 20px;">
                $ <?php echo number_format((float)$producto['precio'], 0, ',', '.'); ?>
            </div>

            <div style="margin-bottom: 20px;">
                <strong>Stock disponible:</strong> <?php echo (int)$producto['stock']; ?> unidades
            </div>

            <div style="margin-bottom: 20px;">
                <strong>Descripción:</strong>
                <p style="color: #475569; margin-top: 8px;"><?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
            </div>

            <a href="<?php echo BASE_URL; ?>/index.php" class="btn">Volver al Catálogo</a>
        </div>
    </div>

</div>

</body>
</html>