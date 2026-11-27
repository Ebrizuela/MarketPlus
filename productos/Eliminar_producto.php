<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../funciones/imagenes_productos.php';

requerirRol(['administrador']);

if (empty($_SESSION['csrf_eliminar_producto'])) {
    $_SESSION['csrf_eliminar_producto'] = bin2hex(random_bytes(32));
}

$mensaje = '';
$errorAccion = '';
$eliminado = false;

try {
    $pdo = conectar();
    $columnasEstado = [];
    foreach (['estado', 'activo'] as $columna) {
        $checkColumna = $pdo->query("SHOW COLUMNS FROM Productos LIKE " . $pdo->quote($columna));
        if ($checkColumna->fetch(PDO::FETCH_ASSOC) !== false) {
            $columnasEstado[] = $columna;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_producto'])) {
        $csrf = $_POST['csrf'] ?? '';
        if (!is_string($csrf) || !hash_equals($_SESSION['csrf_eliminar_producto'], $csrf)) {
            http_response_code(419);
            exit('El formulario expiró. Recarga la página e inténtalo de nuevo.');
        }

        $id = filter_var($_POST['id_producto'], FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            http_response_code(400);
            exit('El producto seleccionado no es válido.');
        }

        if (!$columnasEstado) {
            http_response_code(409);
            $errorAccion = 'No se puede dar de baja el producto porque la tabla no tiene una columna de estado.';
        } else {
            try {
                $pdo->beginTransaction();
                $buscarProducto = $pdo->prepare(
                    'SELECT id_producto, nombre FROM Productos WHERE id_producto = :id FOR UPDATE'
                );
                $buscarProducto->execute([':id' => $id]);
                $producto = $buscarProducto->fetch(PDO::FETCH_ASSOC);

                if (!$producto) {
                    $pdo->rollBack();
                    http_response_code(404);
                    $errorAccion = 'El producto no existe o ya fue eliminado.';
                } else {
                    $asignacionesEstado = array_map(
                        static fn(string $columna): string => $columna . ' = 0',
                        $columnasEstado
                    );
                    $condicionesActivo = array_map(
                        static fn(string $columna): string => $columna . ' = 1',
                        $columnasEstado
                    );

                    $actualizar = $pdo->prepare(
                        'UPDATE Productos SET ' . implode(', ', $asignacionesEstado)
                        . ' WHERE id_producto = :id AND ' . implode(' AND ', $condicionesActivo)
                    );
                    $actualizar->execute([':id' => $id]);

                    if ($actualizar->rowCount() === 0) {
                        $pdo->rollBack();
                        http_response_code(409);
                        $errorAccion = 'El producto ya fue dado de baja.';
                    } else {
                        $usuario = $_SESSION['usuario'] ?? [];
                        $usuarioId = filter_var($usuario['id'] ?? null, FILTER_VALIDATE_INT);
                        $correoAdministrador = trim((string) ($usuario['correo'] ?? ''));
                        $nombreAdministrador = trim((string) ($usuario['nombre'] ?? ''));
                        $realizadoPor = $correoAdministrador !== ''
                            ? $correoAdministrador
                            : ($nombreAdministrador !== '' ? $nombreAdministrador : 'Administrador');

                        $ventasHistoricas = $pdo->prepare(
                            'SELECT COUNT(*) FROM Detalle_Ventas WHERE id_producto = :id'
                        );
                        $ventasHistoricas->execute([':id' => $id]);
                        $cantidadVentasHistoricas = (int) $ventasHistoricas->fetchColumn();

                        $detalles = json_encode([
                            'producto_id' => (int) $producto['id_producto'],
                            'producto' => $producto['nombre'],
                            'realizado_por' => $realizadoPor,
                            'historial_ventas_conservado' => true,
                            'ventas_historicas_conservadas' => $cantidadVentasHistoricas,
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

                        $auditoria = $pdo->prepare(
                            'INSERT INTO auditoria (usuario_id, accion, detalles, ip, user_agent, fecha)
                             VALUES (:usuario_id, :accion, :detalles, :ip, :user_agent, NOW())'
                        );
                        $auditoria->execute([
                            ':usuario_id' => $usuarioId === false ? null : $usuarioId,
                            ':accion' => 'eliminar_producto',
                            ':detalles' => $detalles,
                            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                            ':user_agent' => isset($_SERVER['HTTP_USER_AGENT'])
                                ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255)
                                : null,
                        ]);
                        $pdo->commit();
                        $eliminado = true;
                    }
                }
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $e;
            }
        }

        if ($eliminado) {
            header('Location: ' . BASE_URL . '/productos/Eliminar_producto.php?msg=eliminado');
            exit;
        }
        if ($errorAccion === '') {
            http_response_code(404);
            $errorAccion = 'El producto no existe o ya fue eliminado.';
        }
    }

    if (($_GET['msg'] ?? '') === 'eliminado') {
        $mensaje = 'Producto dado de baja del catálogo; el historial de ventas se conservó.';
    }

    $sqlProductos = 'SELECT * FROM Productos';
    if ($columnasEstado) {
        $condicionesActivo = [];
        foreach ($columnasEstado as $columna) {
            $condicionesActivo[] = $columna . ' = 1';
        }
        $sqlProductos .= ' WHERE ' . implode(' AND ', $condicionesActivo);
    }
    $productos = $pdo->query($sqlProductos . ' ORDER BY id_producto ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error al cargar o eliminar productos: ' . $e->getMessage());
    http_response_code(503);
    exit('No se pudieron cargar o eliminar los productos. Revisa la configuración de la base de datos.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlus - Catálogo</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/css/estilos.css?v=1">
    <link rel="stylesheet" href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/css/marketplus-theme.css?v=1">
    <style>
        .mensaje-eliminacion {
            background: #dcfce7;
            color: #15803d;
            padding: 15px;
            text-align: center;
            font-weight: bold;
            border-radius: 12px;
            margin: 20px auto;
            max-width: 1200px;
            border: 1px solid #bbf7d0;
        }
        .contenedor-productos {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .tarjeta-producto {
            background: #fff;
            border-radius: 15px;
            border: 1px solid #eee;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .tarjeta-producto:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        }
        .contenedor-img {
            cursor: pointer;
            background: #fafafa;
        }
        .contenedor-img img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            padding: 10px;
            display: block;
        }
        .producto-vacio {
            grid-column: 1 / -1;
            padding: 28px;
            text-align: center;
            color: #64748b;
            background: #fff;
            border-radius: 12px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(4px);
            justify-content: center; align-items: center;
        }
        .modal-contenido {
            background: #fff;
            width: 90%;
            max-width: 500px;
            border-radius: 20px;
            padding: 40px;
            position: relative;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        #m-img {
            max-width: 100%;
            max-height: 300px;
            object-fit: contain;
            margin-bottom: 20px;
            filter: drop-shadow(0 10px 10px rgba(0,0,0,0.05));
        }
        #m-titulo { font-size: 24px; color: #1e293b; margin: 10px 0; }
        .modal-precio { font-size: 28px; font-weight: bold; color: #2563eb; margin-bottom: 15px; }
        #m-desc { color: #64748b; line-height: 1.6; font-size: 15px; }
    </style>
</head>
<body>

    <header class="header" style="max-width: 1200px; margin: 0 auto 15px;">
        <div style="font-size: 22px; font-weight: bold; letter-spacing: 0.5px;">MarketPlus - Eliminar productos</div>
        <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/administrador/dashboard_administrador.php" style="text-decoration: none;">Volver al dashboard</a>
    </header>

    <?php if ($mensaje !== ''): ?>
        <div class="mensaje-eliminacion" role="status"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($errorAccion !== ''): ?>
        <div class="mensaje-eliminacion" role="alert" style="background:#fee2e2; color:#991b1b; border-color:#fecaca;">
            <?= htmlspecialchars($errorAccion, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <main class="contenedor-productos">
        <?php if (!$productos): ?>
            <p class="producto-vacio">No hay productos activos para mostrar.</p>
        <?php endif; ?>
        <?php foreach ($productos as $prod):
            $id = (int) $prod['id_producto'];
            $nombre = (string) $prod['nombre'];
            $nombreSeguro = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
            $precio = number_format((float) $prod['precio'], 0, ',', '.');
            $desc = (string) ($prod['descripcion'] ?? 'Sin descripción.');
            $imagen = obtenerImagenProducto($prod, dirname(__DIR__));
            $descSeguro = htmlspecialchars($desc, ENT_QUOTES, 'UTF-8');
        ?>
        <article class="tarjeta-producto">
            <div class="contenedor-img"
                 data-titulo="<?= $nombreSeguro ?>"
                 data-desc="<?= $descSeguro ?>"
                 data-precio="$ <?= $precio ?>"
                 onclick="mostrarModal(this)">
                <?php if ($imagen !== null): ?>
                    <img src="<?= htmlspecialchars($imagen, ENT_QUOTES, 'UTF-8') ?>" alt="<?= $nombreSeguro ?>">
                <?php else: ?>
                    <div class="producto-vacio">Imagen no disponible</div>
                <?php endif; ?>
            </div>
            <div style="padding: 15px;">
                <h3 style="margin: 0; font-size: 16px; height: 40px; overflow: hidden;"><?= $nombreSeguro ?></h3>
                <p style="font-size: 18px; font-weight: bold; color: #2563eb;">$ <?= $precio ?></p>
                <form method="POST" onsubmit="return confirm(<?= htmlspecialchars(json_encode('¿Deseas dar de baja ' . $nombre . ' del catálogo? Sus ventas históricas se conservarán.', JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>);">
                    <input type="hidden" name="id_producto" value="<?= $id ?>">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_eliminar_producto'], ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" style="width: 100%; background: #ef4444; color: white; border: none; padding: 10px; border-radius: 8px; cursor: pointer; font-weight: bold;">Dar de baja</button>
                </form>
            </div>
        </article>
        <?php endforeach; ?>
    </main>

    <div id="modal-desc" class="modal" onclick="cerrarModalAfuera(event)">
        <div class="modal-contenido">
            <button onclick="cerrarModal()" style="position: absolute; top: 15px; right: 20px; border: none; background: none; font-size: 28px; cursor: pointer; color: #94a3b8;">&times;</button>
            <img id="m-img" src="" alt="Producto">
            <h2 id="m-titulo"></h2>
            <div id="m-precio" class="modal-precio"></div>
            <p id="m-desc"></p>
        </div>
    </div>

    <script>
        function mostrarModal(elemento) {
            const imgActual = elemento.querySelector('img');
            const imagenModal = document.getElementById('m-img');
            if (imgActual) {
                imagenModal.src = imgActual.src;
                imagenModal.hidden = false;
            } else {
                imagenModal.hidden = true;
            }
            document.getElementById('m-titulo').textContent = elemento.getAttribute('data-titulo');
            document.getElementById('m-desc').textContent = elemento.getAttribute('data-desc');
            document.getElementById('m-precio').textContent = elemento.getAttribute('data-precio');
            document.getElementById('modal-desc').style.display = 'flex';
        }
        function cerrarModal() { document.getElementById('modal-desc').style.display = 'none'; }
        function cerrarModalAfuera(e) { if (e.target.id === 'modal-desc') cerrarModal(); }
    </script>
</body>
</html>
