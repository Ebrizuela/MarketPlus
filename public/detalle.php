<?php
ob_start();
session_start();

require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/imagenes_productos.php';
require_once __DIR__ . '/../funciones/navegacion.php';
$pdo = conectar();

// ✅ Si llega por POST, guarda el ID en sesión y redirige (URL limpia)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_producto'])) {
    $_SESSION['producto_id'] = (int)$_POST['id_producto'];
    header("Location: " . BASE_URL . "/public/detalle.php");
    exit();
}

// ✅ Recuperar el ID desde la sesión (fallback: GET por si acaso)
$id_producto = $_SESSION['producto_id'] ?? (isset($_GET['id']) ? intval($_GET['id']) : 0);

if ($id_producto <= 0) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM Productos WHERE id_producto = ?");
$stmt->execute([$id_producto]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto || (array_key_exists('estado', $producto) && (int) $producto['estado'] !== 1)) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$esAdmin = esAdmin();
$estaLogueado = estaLogueado();
$rolActual = $_SESSION['usuario']['rol'] ?? null;
$mensaje = "";

if (isset($_SESSION['mensaje_flash'])) {
    $mensaje = $_SESSION['mensaje_flash'];
    unset($_SESSION['mensaje_flash']);
}

// AGREGAR AL CARRITO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_carrito'])) {
    $cantidadDeseada = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;
    if ($cantidadDeseada < 1) $cantidadDeseada = 1;

    if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];

    $cantidadEnCarrito = isset($_SESSION['carrito'][$id_producto]) ? $_SESSION['carrito'][$id_producto]['cantidad'] : 0;
    $totalPedida = $cantidadEnCarrito + $cantidadDeseada;

    if ($totalPedida > $producto['stock']) {
        $disponiblesParaAgregar = $producto['stock'] - $cantidadEnCarrito;
        
        if ($disponiblesParaAgregar <= 0) {
            $mensaje = "<div class='alert error'>Ya tienes el máximo disponible (" . $producto['stock'] . " unidades) en tu carrito.</div>";
        } else {
            $mensaje = "<div class='alert error'>No puedes añadir esa cantidad. Solo puedes agregar {$disponiblesParaAgregar} unidad(es) más.</div>";
        }
    } else {
        if (isset($_SESSION['carrito'][$id_producto])) {
            $_SESSION['carrito'][$id_producto]['cantidad'] += $cantidadDeseada;
        } else {
            $_SESSION['carrito'][$id_producto] = [
                'id_producto' => $producto['id_producto'],
                'nombre'      => $producto['nombre'],
                'precio'      => $producto['precio'],
                'cantidad'    => $cantidadDeseada
            ];
        }

        $_SESSION['mensaje_flash'] = "<div class='alert success'>¡Producto añadido al carrito correctamente!</div>";
        // ✅ Redirige sin parámetros (URL limpia)
        header("Location: " . BASE_URL . "/public/detalle.php");
        exit();
    }
}

$enCarritoActual = isset($_SESSION['carrito'][$id_producto]) ? $_SESSION['carrito'][$id_producto]['cantidad'] : 0;
$maxPermitidoInput = max(0, $producto['stock'] - $enCarritoActual);

// Categoría simplificada
$nombresSimples = [
    'Tecnología' => 'Tecnología',
    'Software y Licencias' => 'Software',
    'Línea Blanca y Electrodomésticos' => 'Electrodomésticos',
    'Herramientas y Ferretería' => 'Ferretería',
    'Muebles y Oficina' => 'Oficina',
    'Seguridad y Vigilancia' => 'Seguridad',
    'Insumos de Aseo y Limpieza' => 'Aseo y Limpieza'
];
$catMostrar = $nombresSimples[$producto['categoria'] ?? ''] ?? ($producto['categoria'] ?? 'PRODUCTO');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($producto['nombre']); ?> - MarketPlus</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/estilos.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/navegacion.css">
    <link rel="stylesheet" href="../css/marketplus-theme.css?v=1">
</head>
<body>

<?php if ($esAdmin): ?>
    <?php renderNavegacion(null, BASE_URL . '/index.php', BASE_URL . '/administrador/Productos_Inventory.php'); ?>
<?php endif; ?>
<div class="container">

    <?php echo $mensaje; ?>

    <div class="header">
        <div>
            <h2 style="margin:0; color: #2563eb;">MarketPlus</h2>
        </div>

        <div style="display: flex; gap: 15px; align-items: center;">
            <?php if ($esAdmin): ?>
                <span style="background: #10b981; color: white; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 13px;">Modo Admin</span>
            <?php elseif ($estaLogueado): ?>
                <span style="background: #3b82f6; color: white; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 13px;">
                    Modo <?php echo htmlspecialchars(ucfirst($rolActual)); ?>
                </span>
            <?php else: ?>
                <span style="background: #f1f5f9; color: #64748b; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 13px;">Invitado</span>
            <?php endif; ?>

            <div class="menu-hamburguesa-container">
                <button class="btn-hamburguesa" aria-label="Abrir menú">☰ Menú</button>
                <div class="menu-desplegable">
                    <a href="<?php echo BASE_URL; ?>/index.php">Catálogo Principal</a>
                    <a href="<?php echo BASE_URL; ?>/public/carrito.php">Ver Carrito</a>
                    <a href="<?php echo BASE_URL; ?>/supervisor/devoluciones.php">Gestor de Devoluciones</a>
                    <div class="menu-divider"></div>
                    <?php if ($estaLogueado): ?>
                        <a href="<?php echo BASE_URL; ?>/includes/logout.php" class="item-logout">Cerrar Sesión</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/login.php">Iniciar Sesión</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-bottom: 15px;">
        <a href="<?php echo BASE_URL; ?>/index.php" style="color: #64748b; text-decoration: none; font-size: 14px;">← Volver al Catálogo</a>
    </div>

    <div class="card" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        
        <div class="detalle-img-container">
            <?php 
                $imgSrc = obtenerImagenProducto($producto, dirname(__DIR__));
            ?>
            <?php if ($imgSrc !== null): ?>
                <img src="<?php echo htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8'); ?>"
                     alt="<?php echo htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                     onerror="this.replaceWith(Object.assign(document.createElement('div'), {className: 'product-img-empty', textContent: 'Imagen no disponible'}));">
            <?php else: ?>
                <div class="product-img-empty">Imagen no disponible</div>
            <?php endif; ?>
        </div>

        <div>
            <span class="categoria-tag"><?php echo htmlspecialchars($catMostrar); ?></span>
            <h1 style="margin: 10px 0; font-size: 24px; color: #1e293b;"><?php echo htmlspecialchars($producto['nombre']); ?></h1>
            <div style="font-size: 26px; font-weight: 800; color: #2563eb; margin-bottom: 20px;">
                $ <?php echo number_format($producto['precio'], 0, ',', '.'); ?>
            </div>

            <div style="margin-bottom: 20px;">
                <strong style="display: block; margin-bottom: 8px; color: #334155;">Descripción del producto:</strong>
                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; color: #475569; font-size: 14px; white-space: pre-line;">
                    <?php echo htmlspecialchars($producto['descripcion'] ?? 'Sin descripción disponible.'); ?>
                </div>
            </div>

            <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <span style="font-weight: 600; color: #334155;">Stock Disponible:</span>
                <span style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 12px; font-weight: 700; font-size: 13px;">
                    <?php echo $producto['stock']; ?> unidades
                </span>
            </div>

            <?php if ($maxPermitidoInput > 0): ?>
                <form action="" method="POST" style="display: flex; gap: 10px; align-items: flex-end;">
                    <div>
                        <label for="cantidad" style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px;">Cantidad:</label>
                        <input type="number" id="cantidad" name="cantidad" value="1" min="1" max="<?php echo $maxPermitidoInput; ?>" style="width: 70px; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 600; text-align: center;" required>
                    </div>
                    <button type="submit" name="agregar_carrito" class="btn">Añadir al Carrito</button>
                </form>
            <?php else: ?>
                <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; font-weight: 600; text-align: center;">
                    Has agregado todo el stock disponible a tu carrito.
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

</body>
</html>