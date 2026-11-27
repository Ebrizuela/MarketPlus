<?php
if (file_exists('../funciones/conexion.php')) {
    require_once '../funciones/conexion.php';
} elseif (file_exists('../conexion.php')) {
    require_once '../conexion.php';
} else {
    require_once 'conexion.php';
}

$sql = "SELECT 
            p.id_producto, 
            p.nombre AS producto, 
            p.descripcion, 
            p.precio, 
            p.stock, 
            c.nombre AS categoria 
        FROM Productos p
        LEFT JOIN Categorias c ON p.id_categorias = c.id_categorias
        ORDER BY p.id_producto ASC";

$pdo = conectar();
try {
    $stmt = $pdo->query($sql);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar productos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisión - Productos</title>
    <link rel="stylesheet" href="../css/supervisor_productos.css?v=20261123">
</head>
<body class="supervisor-page">
    <div class="container">
        <a href="../Supervisor/index.php" class="btn-back" aria-label="Volver al dashboard">
            <span aria-hidden="true">&larr;</span> Volver
        </a>
        <header class="main-header">
            <div>
                <h1>Control de Inventario y Precios</h1>
                <p class="subtitle">Módulo de Supervisión de Productos</p>
            </div>
            <div class="user-role">
                <span>Rol: <strong>Supervisor</strong></span>
            </div>
        </header>

        <section class="toolbar">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Filtrar por producto o categoría...">
            </div>
            <div class="actions">
                <a href="exportar_reporte.php" class="btn btn-export">
                    ⬇ Generar Reporte Stock/Precios
                </a>
            </div>
        </section>

        <main class="table-container">
            <table class="data-table" id="tablaProductos">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Descripción</th>
                        <th>Precio Unitario</th>
                        <th>Stock Actual</th>
                        <th>Estado Disponibilidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($productos)): ?>
                        <?php foreach ($productos as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['id_producto'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><strong><?= htmlspecialchars($item['producto'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><?= htmlspecialchars($item['categoria'] ?? 'Sin Categoría', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($item['descripcion'] ?? 'Sin descripción', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>$<?= number_format($item['precio'], 0, ',', '.') ?></td>
                                <td><?= htmlspecialchars($item['stock'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if ($item['stock'] > 10): ?>
                                        <span class="badge badge-success">Disponible</span>
                                    <?php elseif ($item['stock'] > 0): ?>
                                        <span class="badge badge-warning">Stock Crítico</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Agotado / Inactivo</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-msg">No existen productos registrados en la base de datos.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

    <script src="/js/supervisor_productos.js?v=20261123"></script>
</body>
</html>