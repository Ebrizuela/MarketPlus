<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/conexion.php';

requerirRol(['administrador']);

if (empty($_SESSION['csrf_actualizar_producto'])) {
    $_SESSION['csrf_actualizar_producto'] = bin2hex(random_bytes(32));
}

try {
    $pdo = conectar();
} catch (PDOException $e) {
    error_log('Error de conexión en actualización de productos: ' . $e->getMessage());
    http_response_code(503);
    exit('No se pudo conectar con la base de datos para actualizar productos.');
}

$idProducto = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$editar = isset($_GET['id']);
$errores = [];
$producto = null;
$productos = [];
$categorias = [];

if ($editar && (!$idProducto || $idProducto < 1)) {
    http_response_code(400);
    exit('El producto seleccionado no es válido.');
}

try {
    $categorias = $pdo->query('SELECT id_categorias, nombre FROM Categorias ORDER BY nombre')->fetchAll();

    if ($editar) {
        $stmtProducto = $pdo->prepare(
            'SELECT id_producto, id_categorias, nombre, descripcion, precio, stock
             FROM Productos
             WHERE id_producto = ?'
        );
        $stmtProducto->execute([$idProducto]);
        $producto = $stmtProducto->fetch();

        if (!$producto) {
            http_response_code(404);
            exit('El producto solicitado no existe.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrf = $_POST['csrf'] ?? '';
            if (!is_string($csrf) || !hash_equals($_SESSION['csrf_actualizar_producto'], $csrf)) {
                http_response_code(419);
                exit('El formulario expiró. Recarga la página e inténtalo de nuevo.');
            }

            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $precioIngresado = $_POST['precio'] ?? '';
            $stockIngresado = $_POST['stock'] ?? '';
            $categoriaIngresada = $_POST['id_categorias'] ?? '';
            $precio = filter_var($precioIngresado, FILTER_VALIDATE_INT);
            $stock = filter_var($stockIngresado, FILTER_VALIDATE_INT);
            $idCategoria = filter_var($categoriaIngresada, FILTER_VALIDATE_INT);

            if ($nombre === '') {
                $errores[] = 'El nombre del producto es obligatorio.';
            }
            if ($descripcion === '') {
                $errores[] = 'La descripción del producto es obligatoria.';
            }
            if ($precio === false || $precio < 0) {
                $errores[] = 'Ingresa un precio válido mayor o igual a cero.';
            }
            if ($stock === false || $stock < 0) {
                $errores[] = 'Ingresa un stock válido mayor o igual a cero.';
            }
            if (!$idCategoria || $idCategoria < 1) {
                $errores[] = 'Selecciona una categoría válida.';
            } else {
                $stmtCategoria = $pdo->prepare('SELECT COUNT(*) FROM Categorias WHERE id_categorias = ?');
                $stmtCategoria->execute([$idCategoria]);
                if ((int) $stmtCategoria->fetchColumn() === 0) {
                    $errores[] = 'La categoría seleccionada ya no existe.';
                }
            }

            if ($nombre !== '') {
                $stmtDuplicado = $pdo->prepare(
                    'SELECT id_producto
                     FROM Productos
                     WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?)) AND id_producto <> ?
                     LIMIT 1'
                );
                $stmtDuplicado->execute([$nombre, $idProducto]);
                if ($stmtDuplicado->fetchColumn()) {
                    $errores[] = 'Ya existe otro producto con ese nombre.';
                }
            }

            if (!$errores) {
                try {
                    $stmtActualizar = $pdo->prepare(
                        'UPDATE Productos
                         SET id_categorias = ?, nombre = ?, descripcion = ?, precio = ?, stock = ?
                         WHERE id_producto = ?'
                    );
                    $stmtActualizar->execute([$idCategoria, $nombre, $descripcion, $precio, $stock, $idProducto]);
                    header('Location: ' . BASE_URL . '/productos/actualizar_producto.php?id=' . $idProducto . '&actualizado=1');
                    exit;
                } catch (PDOException $e) {
                    error_log('Error al actualizar producto ' . $idProducto . ': ' . $e->getMessage());
                    $errores[] = 'No se pudo guardar el producto. Revisa los datos e inténtalo nuevamente.';
                }
            }

            $producto['nombre'] = $nombre;
            $producto['descripcion'] = $descripcion;
            $producto['precio'] = $precioIngresado;
            $producto['stock'] = $stockIngresado;
            $producto['id_categorias'] = $categoriaIngresada;
        }
    } else {
        $productos = $pdo->query(
            'SELECT p.id_producto, p.nombre, p.precio, p.stock, c.nombre AS categoria
             FROM Productos p
             INNER JOIN Categorias c ON p.id_categorias = c.id_categorias
             ORDER BY p.nombre'
        )->fetchAll();
    }
} catch (PDOException $e) {
    error_log('Error al cargar productos o categorías para edición: ' . $e->getMessage());
    http_response_code(503);
    exit('No se pudieron cargar los productos o las categorías. Comprueba la conexión a la base de datos.');
}

$escapar = static function ($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editar ? 'Editar producto' : 'Actualizar producto' ?> - MarketPlus</title>
    <link rel="stylesheet" href="<?= $escapar(BASE_URL) ?>/css/estilos.css?v=1">
    <link rel="stylesheet" href="<?= $escapar(BASE_URL) ?>/css/marketplus-theme.css?v=1">
    <style>
        .pagina-actualizar { max-width: 1000px; margin: 0 auto; }
        .acciones-producto { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 20px; }
        .mensaje-error { padding: 12px 16px; margin-bottom: 12px; border-radius: 9px; background: #fee2e2; color: #991b1b; }
        .mensaje-ok { padding: 12px 16px; margin-bottom: 20px; border-radius: 9px; background: #dcfce7; color: #166534; }
        .tabla-productos { overflow-x: auto; }
        .tabla-productos table { min-width: 600px; }
    </style>
</head>
<body>
<main class="container pagina-actualizar">
    <header class="header">
        <h1><?= $editar ? 'Editar producto' : 'Actualizar producto' ?></h1>
        <a class="btn" href="<?= $escapar(BASE_URL) ?>/administrador/dashboard_administrador.php">Volver al dashboard</a>
    </header>

    <?php if (!$editar): ?>
        <section class="card">
            <h2>Selecciona un producto</h2>
            <?php if (!$productos): ?>
                <p>No hay productos registrados para actualizar.</p>
            <?php else: ?>
                <div class="tabla-productos">
                    <table>
                        <thead>
                            <tr><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Acción</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $item): ?>
                                <tr>
                                    <td><?= $escapar($item['nombre']) ?></td>
                                    <td><?= $escapar($item['categoria']) ?></td>
                                    <td>$ <?= number_format((float) $item['precio'], 0, ',', '.') ?></td>
                                    <td><?= (int) $item['stock'] ?></td>
                                    <td><a class="btn" href="<?= $escapar(BASE_URL) ?>/productos/actualizar_producto.php?id=<?= (int) $item['id_producto'] ?>">Editar</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    <?php else: ?>
        <?php if (($_GET['actualizado'] ?? '') === '1'): ?>
            <div class="mensaje-ok" role="status">El producto se actualizó correctamente.</div>
        <?php endif; ?>

        <?php foreach ($errores as $error): ?>
            <div class="mensaje-error" role="alert"><?= $escapar($error) ?></div>
        <?php endforeach; ?>

        <section class="card">
            <h2><?= $escapar($producto['nombre']) ?></h2>
            <form method="POST" action="<?= $escapar(BASE_URL) ?>/productos/actualizar_producto.php?id=<?= (int) $idProducto ?>">
                <input type="hidden" name="csrf" value="<?= $escapar($_SESSION['csrf_actualizar_producto']) ?>">
                <div class="field-col-12">
                    <label for="nombre">Nombre del producto</label>
                    <input type="text" id="nombre" name="nombre" value="<?= $escapar($producto['nombre']) ?>" maxlength="150" required>
                </div>
                <div class="field-col-12">
                    <label for="id_categorias">Categoría</label>
                    <select id="id_categorias" name="id_categorias" required>
                        <option value="">Selecciona una categoría</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= (int) $categoria['id_categorias'] ?>" <?= (int) $categoria['id_categorias'] === (int) $producto['id_categorias'] ? 'selected' : '' ?>>
                                <?= $escapar($categoria['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field-col-12">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="4" required><?= $escapar($producto['descripcion']) ?></textarea>
                </div>
                <div class="field-col-6">
                    <label for="precio">Precio</label>
                    <input type="number" id="precio" name="precio" value="<?= $escapar($producto['precio']) ?>" min="0" step="1" required>
                </div>
                <div class="field-col-6">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" value="<?= $escapar($producto['stock']) ?>" min="0" step="1" required>
                </div>
                <div class="acciones-producto">
                    <button class="btn" type="submit">Guardar cambios</button>
                    <a class="btn" href="<?= $escapar(BASE_URL) ?>/productos/actualizar_producto.php">Volver a productos</a>
                </div>
            </form>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
