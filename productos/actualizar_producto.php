<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../funciones/imagenes_productos.php';

requerirRol(['administrador']);

$conn = conectar();
$panel_destino = '../administrador/index.php';

$mensaje = '';
$error = '';
$producto = null;

if (!defined('BASE_URL')) {
    define('BASE_URL', '/');
}

$lista_productos = $conn->query("SELECT id_producto, codigo, nombre, precio, stock, imagen FROM Productos ORDER BY id_producto DESC")->fetchAll(PDO::FETCH_ASSOC);

$id_editar = trim($_GET['id'] ?? $_POST['id_producto_sel'] ?? '');
$busqueda_realizada = $id_editar !== '';

if ($busqueda_realizada) {
    $stmt = $conn->prepare("SELECT * FROM Productos WHERE id_producto = ? OR codigo = ?");
    $stmt->execute([$id_editar, $id_editar]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id_producto = $_POST['id_producto'] ?? ''; 
    $codigo = trim($_POST['codigo'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);

    if ($precio < 0 || $stock < 0) {
        $error = "El precio y el stock no pueden ser números negativos.";
    } else {
        try {
            $imagen_path = $_POST['imagen_actual'] ?? null;

            if (isset($_POST['eliminar_imagen']) && $_POST['eliminar_imagen'] == '1') {
                if (!empty($imagen_path) && (strpos($imagen_path, 'img/') === 0 || strpos($imagen_path, 'uploads/') === 0)) {
                    $archivo_fisico = __DIR__ . '/../' . $imagen_path;
                    if (file_exists($archivo_fisico)) {
                        @unlink($archivo_fisico);
                    }
                }
                $imagen_path = null;
            }

            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $directorio_subidas = __DIR__ . '/../img/';
                if (!is_dir($directorio_subidas)) {
                    mkdir($directorio_subidas, 0777, true);
                }

                if (!empty($_POST['imagen_actual']) && (strpos($_POST['imagen_actual'], 'img/') === 0 || strpos($_POST['imagen_actual'], 'uploads/') === 0)) {
                    $antiguo = __DIR__ . '/../' . $_POST['imagen_actual'];
                    if (file_exists($antiguo)) {
                        @unlink($antiguo);
                    }
                }

                $nombre_archivo = time() . '_' . basename($_FILES['imagen']['name']);
                $ruta_destino = $directorio_subidas . $nombre_archivo;
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
                    $imagen_path = 'img/' . $nombre_archivo;
                }
            }
            
            $stmtUp = $conn->prepare("UPDATE Productos SET codigo = ?, nombre = ?, descripcion = ?, precio = ?, stock = ?, imagen = ? WHERE id_producto = ?");
            $stmtUp->execute([$codigo, $nombre, $descripcion, $precio, $stock, $imagen_path, $id_producto]);

            $mensaje = "¡Producto actualizado correctamente en el sistema!";
       
            $stmt = $conn->prepare("SELECT * FROM Productos WHERE id_producto = ?");
            $stmt->execute([$id_producto]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_editar = $id_producto;
            $busqueda_realizada = true;
            
            $lista_productos = $conn->query("SELECT id_producto, codigo, nombre, precio, stock, imagen FROM Productos ORDER BY id_producto DESC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $error = "Error al actualizar: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlus - Actualizar Inventario</title>
    <link rel="stylesheet" href="../css/actualizar_productos.css">
</head>
<body>

    <header>
        <div class="logo-area">Market<span>Plus</span>Inventario</div>
        <div>
            <?php if ($producto): ?>
                <a href="actualizar_producto.php" style="background-color: #6c757d; color: white; padding: 8px 16px; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 700;">← Volver al Catálogo</a>
            <?php else: ?>
                <a href="<?= $panel_destino ?>" style="background-color: #dc3545; color: white; padding: 8px 16px; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 700;">Volver al Panel</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="pos-container" style="max-width: 1000px;">
        <h2>Actualización de Producto e Imagen</h2>
        <p class="subtext">Modifica los datos comerciales, código, stock y la imagen asociada.</p>

        <?php if (!empty($error)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: bold;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($mensaje)): ?>
            <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: bold;"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <form method="GET" style="margin-bottom: 25px; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #e5e7eb;">
            <label style="font-size: 13px; font-weight: 600; color: #444; display: block; margin-bottom: 5px;">SELECCIONAR PRODUCTO DEL CATÁLOGO:</label>
            <div style="display: flex; gap: 10px;">
                <select name="id" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; background: #fff; font-size: 13px;" required>
                    <option value="">-- Seleccione un producto (Muestra ID y Nombre) --</option>
                    <?php foreach ($lista_productos as $lp): ?>
                        <option value="<?= $lp['id_producto'] ?>" <?= ($id_editar == $lp['id_producto'] || $id_editar == $lp['codigo']) ? 'selected' : '' ?>>
                            [ID: #<?= $lp['id_producto'] ?>] - SKU: <?= htmlspecialchars($lp['codigo']) ?> - <?= htmlspecialchars($lp['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" style="background: #004080; color: white; border: none; padding: 8px 20px; border-radius: 4px; font-weight: bold; cursor: pointer;">Cargar</button>
            </div>
        </form>
     
        <?php if ($producto): ?>
            <form method="POST" enctype="multipart/form-data" style="display: grid; grid-template-columns: 1fr 320px; gap: 20px; align-items: start; margin-bottom: 30px;">
                <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">
                <input type="hidden" name="imagen_actual" value="<?= htmlspecialchars($producto['imagen'] ?? '') ?>">

                <div>
                    <div class="form-row" style="display: grid; grid-template-columns: 120px 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>ID SISTEMA:</label>
                            <input type="text" value="#<?= $producto['id_producto'] ?>" readonly style="background: #e9ecef; font-weight: bold; text-align: center; color: #495057; width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; cursor: not-allowed;">
                        </div>
                        <div class="form-group">
                            <label>CÓDIGO INTERNO / SKU:</label>
                            <input type="text" name="codigo" value="<?= htmlspecialchars($producto['codigo'] ?? '') ?>" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 15px;">
                        <label>NOMBRE DEL PRODUCTO:</label>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                    </div>

                    <div class="form-group" style="margin-top: 15px;">
                        <label>DESCRIPCIÓN:</label>
                        <textarea name="descripcion" rows="4" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;"><?= htmlspecialchars($producto['descripcion']) ?></textarea>
                    </div>

                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                        <div class="form-group">
                            <label>PRECIO ACTUAL ($):</label>
                            <input type="number" name="precio" value="<?= $producto['precio'] ?>" min="0" step="any" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                        </div>
                        <div class="form-group">
                            <label>STOCK DISPONIBLE:</label>
                            <input type="number" name="stock" value="<?= $producto['stock'] ?>" min="0" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                        </div>
                    </div>
                </div>
         
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #d1d5db; text-align: center;">
                    <label style="display: block; font-weight: bold; margin-bottom: 10px; color: #004080; text-align: left; font-size: 13px;">IMAGEN DEL PRODUCTO</label>
                    
                    <?php 
                    $img_prod = function_exists('obtenerImagenProducto') ? obtenerImagenProducto($producto, __DIR__ . '/../') : ($producto['imagen'] ?? null);
                    if ($img_prod): 
                    ?>
                        <div style="margin-bottom: 12px; background: #fff; padding: 5px; border-radius: 6px; border: 1px solid #ccc;">
                            <img src="../<?= htmlspecialchars(ltrim($img_prod, '/')) ?>" alt="Vista previa" style="width: 100%; height: 180px; object-fit: contain; display: block; margin: 0 auto;">
                        </div>
                        <div style="text-align: left; margin-bottom: 15px;">
                            <label style="font-size: 12px; color: #dc2626; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; background: #fee2e2; padding: 6px 10px; border-radius: 6px; border: 1px solid #fca5a5; width: 100%; box-sizing: border-box;">
                                <input type="checkbox" name="eliminar_imagen" value="1" style="width: 14px; height: 14px; accent-color: #dc2626; cursor: pointer;"> 
                                Eliminar imagen actual
                            </label>
                        </div>
                    <?php else: ?>
                        <div style="padding: 25px 10px; background: #e5e7eb; border-radius: 6px; margin-bottom: 12px; color: #4b5563; font-size: 12px;">
                            Sin imagen cargada
                        </div>
                    <?php endif; ?>
                    
                    <div style="text-align: left;">
                        <label style="font-size: 11px; font-weight: 600; color: #374151; display: block; margin-bottom: 4px;">Reemplazar / Subir:</label>
                        <input type="file" name="imagen" style="padding: 4px; background: #fff; border: 1px solid #ccc; border-radius: 4px; width: 100%; font-size: 11px; box-sizing: border-box;">
                    </div>
                </div>

                <div style="grid-column: 1 / -1; margin-top: 10px;">
    <button type="submit" name="actualizar" class="btn-submit" style="width: 100%; background: #004080; color: white; padding: 12px; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer;">
        Guardar Cambios y Actualizar
    </button>
</div>
            </form>
        <?php elseif ($busqueda_realizada): ?>
            <p style="color: #c82333; font-weight: bold; text-align: center; margin-bottom: 25px;">No se encontró ningún producto con el identificador seleccionado.</p>
        <?php endif; ?>

        <div style="margin-top: 30px; border-top: 2px solid #e5e7eb; padding-top: 20px;">
            <h3 style="color: #004080; margin-bottom: 20px; font-size: 18px;">Catálogo de Productos en Tarjetas</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px;">
                <?php foreach ($lista_productos as $lp): ?>
                    <div style="background: #fff; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <?php 
                            $img_card = function_exists('obtenerImagenProducto') ? obtenerImagenProducto($lp, __DIR__ . '/../') : ($lp['imagen'] ?? null);
                            if ($img_card): 
                            ?>
                                <div style="width: 100%; height: 160px; background: #ffffff; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 8px; box-sizing: border-box;">
                                    <img src="../<?= htmlspecialchars(ltrim($img_card, '/')) ?>" alt="Producto" style="max-width: 100%; max-height: 100%; object-fit: contain; display: block;">
                                </div>
                            <?php else: ?>
                                <div style="width: 100%; height: 160px; background: #e5e7eb; display: flex; align-items: center; justify-content: center; color: #6b7280; font-size: 12px; border-bottom: 1px solid #e5e7eb;">
                                    Sin imagen
                                </div>
                            <?php endif; ?>
                            <div style="padding: 15px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <span style="background: #004080; color: white; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">ID #<?= $lp['id_producto'] ?></span>
                                    <span style="font-size: 12px; color: #6b7280; font-weight: 600;">SKU: <?= htmlspecialchars($lp['codigo']) ?></span>
                                </div>
                                <h4 style="font-size: 15px; color: #111827; margin: 0 0 8px 0; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($lp['nombre']) ?>"><?= htmlspecialchars($lp['nombre']) ?></h4>
                                <div style="display: flex; justify-content: space-between; font-size: 13px; color: #374151; margin-bottom: 12px;">
                                    <span><b>Precio:</b> $<?= number_format($lp['precio'], 0, ',', '.') ?></span>
                                    <span><b>Stock:</b> <?= $lp['stock'] ?> un.</span>
                                </div>
                            </div>
                        </div>
                        <div style="padding: 0 15px 15px 15px;">
                            <a href="?id=<?= $lp['id_producto'] ?>" style="display: block; text-align: center; background: #004080; color: white; padding: 8px; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: bold;">Seleccionar para Editar</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

</body>
</html>