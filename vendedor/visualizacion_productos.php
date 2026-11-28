<?php
require_once '../funciones/conexion.php'; 

$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

$sql = "SELECT 
            p.id_producto, 
            p.nombre AS producto_nombre, 
            p.descripcion, 
            p.precio, 
            p.stock, 
            c.nombre AS categoria_nombre
        FROM Productos p
        INNER JOIN Categorias c ON p.id_categorias = c.id_categorias";

$params = [];

$pdo = conectar();

if (!empty($busqueda)) {
    if (ctype_digit($busqueda)) {
        $sql .= " WHERE (p.id_producto = :id_exacto OR p.nombre LIKE :busqueda OR c.nombre LIKE :busqueda)";
        $params['id_exacto'] = (int)$busqueda;
        $params['busqueda']  = "%{$busqueda}%";
    } else {
        $sql .= " WHERE (p.nombre LIKE :busqueda OR c.nombre LIKE :busqueda)";
        $params['busqueda']  = "%{$busqueda}%";
    }
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consulta de Productos</title>
    <link rel="stylesheet" href="../css/visualizacion_productos.css">
    <script src="../js/visualizacion_productos.js" defer></script>
</head>
<body>

    <div class="container">
        
        <div class="header">
            <div class="header-content">
                <h1>Consulta de Productos</h1>
                <p>Panel de consulta rápida para vendedores</p>
            </div>
            <a href="/vendedor/index.php" class="btn-back">← Volver</a>
        </div>

        <!-- Buscador -->
        <div class="search-card">
            <form method="GET" class="search-form">
                <input
                    type="text" 
                    name="busqueda" 
                    id="searchInput"
                    class="search-input"
                    placeholder="Buscar por código, nombre o categoría..." 
                    value="<?= htmlspecialchars($busqueda) ?>"
                >
                <button type="submit" class="btn-search">Buscar</button>
                <?php if (!empty($busqueda)): ?>
                    <a href="index.php" class="btn-clear">Limpiar</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-card">
            <table id="tablaProductos">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Stock Disponible</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($productos) > 0): ?>
                        <?php foreach ($productos as $producto): ?>
                            <?php
                                // Lógica visual de stock
                                $stock = $producto['stock'];
                                if ($stock == 0) {
                                    $stockClass = 'stock-out';
                                    $stockText = 'Agotado (0)';
                                } elseif ($stock <= 5) {
                                    $stockClass = 'stock-low';
                                    $stockText = "Bajo ({$stock})";
                                } else {
                                    $stockClass = 'stock-ok';
                                    $stockText = "Disponibles ({$stock})";
                                }
                            ?>
                            <tr>
                                <td>
                                    <span class="badge-codigo">#<?= htmlspecialchars($producto['id_producto']) ?></span>
                                </td>
                                <td>
                                    <span class="text-nombre"><?= htmlspecialchars($producto['producto_nombre']) ?></span>
                                </td>
                                <td>
                                    <span class="badge-categoria"><?= htmlspecialchars($producto['categoria_nombre']) ?></span>
                                </td>
                                <td>
                                    <div class="text-descripcion"><?= htmlspecialchars($producto['descripcion'] ?: 'Sin descripción') ?></div>
                                </td>
                                <td>
                                    <span class="badge-precio">$<?= number_format($producto['precio'], 0, ',', '.') ?></span>
                                </td>
                                <td>
                                    <span class="stock-tag <?= $stockClass ?>">
                                        <?= $stockText ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                No se encontraron productos que coincidan con la búsqueda.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>