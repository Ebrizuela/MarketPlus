<?php
require_once '../includes/verificar_sesion.php';
session_start();
require_once 'funciones/conexion.php'; // Se mantiene tu estructura exacta

// 1. VALIDACIÓN DE ADMINISTRADOR (Criterio de aceptación)
// Basado en tu sistema de JWT por cookies
if (!isset($_COOKIE['jwt_token'])) {
    header("Location: login.php");
    exit;
}

$mensaje = '';
$error = '';

// Obtener el ID del producto desde la URL
$id_producto = $_GET['id'] ?? null;

if (!$id_producto) {
    die("Error: No se especificó el producto a editar. Debes enviar el ID por la URL (ej: actualizar_producto.php?id=1).");
}

// 2. PROCESAR LA ACTUALIZACIÓN
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = (int)$_POST['precio'];
    $stock = (int)$_POST['stock'];
    $id_categorias = (int)$_POST['id_categorias'];

    // Validar que no se duplique el nombre/código en otro producto (Criterio de aceptación)
    $stmt_val = $pdo->prepare("SELECT id_producto FROM Productos WHERE nombre = ? AND id_producto != ?");
    $stmt_val->execute([$nombre, $id_producto]);
    
    if ($stmt_val->rowCount() > 0) {
        $error = "El nombre o código del producto ya está registrado en otro ítem.";
    } else {
        // Obtener datos antiguos para la trazabilidad
        $stmt_old = $pdo->prepare("SELECT precio, stock FROM Productos WHERE id_producto = ?");
        $stmt_old->execute([$id_producto]);
        $producto_antiguo = $stmt_old->fetch(PDO::FETCH_ASSOC);

        // Ejecutar actualización
        $stmt_update = $pdo->prepare("UPDATE Productos SET id_categorias = ?, nombre = ?, descripcion = ?, precio = ?, stock = ? WHERE id_producto = ?");
        
        if ($stmt_update->execute([$id_categorias, $nombre, $descripcion, $precio, $stock, $id_producto])) {
            $mensaje = "Producto actualizado correctamente.";

            // Guardar historial de cambios en archivo plano (Criterio de aceptación)
            $fecha_cambio = date('Y-m-d H:i:s');
            $log_entry = "[$fecha_cambio] ADMIN editó Producto ID: $id_producto | " .
                         "Antes: [Precio: {$producto_antiguo['precio']}, Stock: {$producto_antiguo['stock']}] " .
                         "-> Después: [Precio: $precio, Stock: $stock]" . PHP_EOL;
            
            // Crea un archivo llamado historial_productos.txt en la misma carpeta si no existe
            file_put_contents('historial_productos.txt', $log_entry, FILE_APPEND);
        } else {
            $error = "Ocurrió un error al intentar guardar los cambios.";
        }
    }
}

// 3. CARGAR DATOS ACTUALES DEL PRODUCTO PARA EL FORMULARIO
$stmt_prod = $pdo->prepare("SELECT * FROM Productos WHERE id_producto = ?");
$stmt_prod->execute([$id_producto]);
$producto = $stmt_prod->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    die("Error: El producto solicitado no existe en la base de datos.");
}

// Cargar categorías disponibles
$stmt_cat = $pdo->query("SELECT id_categorias, nombre FROM Categorias");
$categorias = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Producto - MarketPlus</title>
    <!-- Vincula aquí tu archivo CSS externo, tal como lo solicitaste -->
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<main class="contenedor-principal">
    <section class="tarjeta-formulario">
        <h2>Actualización de Producto (HU-005)</h2>
        
        <?php if ($mensaje): ?>
            <div class="alerta-exito"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alerta-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="actualizar_producto.php?id=<?= htmlspecialchars($producto['id_producto']) ?>">
            
            <div class="grupo-input">
                <label for="id_prod">ID Producto (Solo lectura)</label>
                <input type="text" id="id_prod" value="<?= htmlspecialchars($producto['id_producto']) ?>" readonly disabled>
            </div>

            <div class="grupo-input">
                <label for="nombre">Nombre / Código del Producto</label>
                <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required>
            </div>

            <div class="grupo-input">
                <label for="id_categorias">Categoría</label>
                <select id="id_categorias" name="id_categorias" required>
                    <option value="">Seleccione una categoría</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['id_categorias']) ?>" <?= ($cat['id_categorias'] == $producto['id_categorias']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grupo-input">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="4" required><?= htmlspecialchars($producto['descripcion']) ?></textarea>
            </div>

            <div class="grupo-input">
                <label for="precio">Precio Final ($)</label>
                <input type="number" id="precio" name="precio" value="<?= htmlspecialchars($producto['precio']) ?>" required min="0">
            </div>

            <div class="grupo-input">
                <label for="stock">Stock Disponible</label>
                <input type="number" id="stock" name="stock" value="<?= htmlspecialchars($producto['stock']) ?>" required min="0">
            </div>

            <div class="acciones-formulario">
                <button type="submit" class="btn-primario">Actualizar Producto</button>
                <a href="modulo_admin.php" class="btn-secundario">Cancelar y Volver</a>
            </div>
            
        </form>
    </section>
</main>

</body>
</html>