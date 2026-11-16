<?php
ob_start();
session_start();

require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/navegacion.php';
$pdo = conectar();

requerirRol(['administrador', 'supervisor', 'vendedor']);

$esAdmin = esAdmin();
$estaLogueado = estaLogueado();
$rolActual = $_SESSION['usuario']['rol'] ?? null;
$mensaje = "";

if (isset($_SESSION['mensaje_flash'])) {
    $mensaje = $_SESSION['mensaje_flash'];
    unset($_SESSION['mensaje_flash']);
}

// 1. Listar ventas recientes
$ventasRecientes = [];
try {
    $sql = "SELECT v.id_ventas, v.fecha_hora, v.total_venta,
                   c.nombre AS cliente_nombre, c.apellidos AS cliente_apellido,
                   f.nombre AS func_nombre
            FROM Ventas v
            LEFT JOIN Clientes c ON v.id_cliente = c.id_cliente
            LEFT JOIN funcionarios f ON v.id_funcionario = f.id_funcionario
            ORDER BY v.id_ventas DESC
            LIMIT 50";
    $ventasRecientes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $ventasRecientes = [];
}

// 2. Cargar productos de la venta seleccionada
$ventaSeleccionada = isset($_GET['venta']) ? intval($_GET['venta']) : 0;
$productosVenta = [];
if ($ventaSeleccionada > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT d.id_detalle, d.id_producto, d.cantidad, d.precio_unitario, d.subtotal,
                   p.nombre, p.codigo,
                   (SELECT COALESCE(SUM(dv.cantidad), 0) FROM Devoluciones dv 
                    WHERE dv.id_venta = d.id_ventas AND dv.id_producto = d.id_producto) AS ya_devuelto
            FROM Detalle_Ventas d
            INNER JOIN Productos p ON d.id_producto = p.id_producto
            WHERE d.id_ventas = ?
        ");
        $stmt->execute([$ventaSeleccionada]);
        $productosVenta = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $productosVenta = [];
    }
}

// 3. Procesar devolución
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_devolucion'])) {
    $id_venta    = intval($_POST['id_venta'] ?? 0);
    $id_producto = intval($_POST['id_producto'] ?? 0);
    $cantidad    = intval($_POST['cantidad'] ?? 0);
    $motivo      = trim($_POST['motivo'] ?? '');

    if ($id_venta <= 0 || $id_producto <= 0 || $cantidad <= 0 || empty($motivo)) {
        $mensaje = "<div class='alert error'>Todos los campos son obligatorios.</div>";
    } else {
        try {
            $stmtVal = $pdo->prepare("SELECT cantidad FROM Detalle_Ventas WHERE id_ventas = ? AND id_producto = ?");
            $stmtVal->execute([$id_venta, $id_producto]);
            $vendido = (int)$stmtVal->fetchColumn();

            $stmtDev = $pdo->prepare("SELECT COALESCE(SUM(cantidad), 0) FROM Devoluciones WHERE id_venta = ? AND id_producto = ?");
            $stmtDev->execute([$id_venta, $id_producto]);
            $yaDevuelto = (int)$stmtDev->fetchColumn();

            $disponibleDevolver = $vendido - $yaDevuelto;

            if ($disponibleDevolver <= 0) {
                $mensaje = "<div class='alert error'>Este producto ya fue devuelto completamente en esta venta.</div>";
            } elseif ($cantidad > $disponibleDevolver) {
                $mensaje = "<div class='alert error'>Solo puedes devolver {$disponibleDevolver} unidad(es).</div>";
            } else {
                $pdo->beginTransaction();

                $hoy = date('Y-m-d');
                $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM Devoluciones WHERE DATE(fecha) = ?");
                $stmtCount->execute([$hoy]);
                $numDia = (int)$stmtCount->fetchColumn() + 1;
                $folio = 'DEV-' . date('Ymd') . '-' . str_pad($numDia, 4, '0', STR_PAD_LEFT);

                $stmtIns = $pdo->prepare("
                    INSERT INTO Devoluciones (folio, id_venta, id_producto, cantidad, motivo, fecha, id_funcionario)
                    VALUES (?, ?, ?, ?, ?, NOW(), ?)
                ");
                $idFuncionario = $_SESSION['usuario']['id'] ?? null;
                $stmtIns->execute([$folio, $id_venta, $id_producto, $cantidad, $motivo, $idFuncionario]);

                $stmtUpd = $pdo->prepare("UPDATE Productos SET stock = stock + ? WHERE id_producto = ?");
                $stmtUpd->execute([$cantidad, $id_producto]);

                $pdo->commit();

                $_SESSION['mensaje_flash'] = "<div class='alert success'>Devolución registrada con éxito. Folio: <strong>{$folio}</strong>. Stock actualizado.</div>";
                header("Location: " . BASE_URL . "/supervisor/devoluciones.php");
                exit();
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $mensaje = "<div class='alert error'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Devoluciones - MarketPlus</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/estilos.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/navegacion.css">
    <link rel="stylesheet" href="../css/marketplus-theme.css?v=1">
</head>
<body>

<?php renderNavegacion(null, BASE_URL . '/index.php'); ?>
<div class="container">

    <?php echo $mensaje; ?>

    <div class="header">
        <div>
            <h2 style="margin:0; color: #2563eb;">MarketPlus - Devoluciones</h2>
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
                    <a href="<?php echo BASE_URL; ?>/supervisor/devoluciones.php">Gestor de Devoluciones</a>
                    <a href="<?php echo BASE_URL; ?>/supervisor/historial_devoluciones.php">Historial de Devoluciones</a>
                    <div class="menu-divider"></div>
                    <a href="<?php echo BASE_URL; ?>/includes/logout.php" class="item-logout">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </div>

    <!-- PASO 1 -->
    <div class="card">
        <h3>Paso 1: Seleccionar la venta</h3>
        <p style="color:#64748b; font-size:13px;">Elige la venta de la cual deseas devolver un producto.</p>

        <form action="" method="GET">
            <div style="margin-bottom: 15px;">
                <label style="display:block; font-weight:600; color:#334155; margin-bottom:6px;">Venta:</label>
                <select name="venta" onchange="this.form.submit()" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px;">
                    <option value="">-- Seleccione una venta --</option>
                    <?php foreach ($ventasRecientes as $v): ?>
                        <option value="<?php echo $v['id_ventas']; ?>" <?php echo ($ventaSeleccionada == $v['id_ventas']) ? 'selected' : ''; ?>>
                            Venta #<?php echo $v['id_ventas']; ?> 
                            - <?php echo date('d/m/Y H:i', strtotime($v['fecha_hora'])); ?>
                            - $<?php echo number_format($v['total_venta'], 0, ',', '.'); ?>
                            <?php if (!empty($v['cliente_nombre'])): ?>
                                - Cliente: <?php echo htmlspecialchars($v['cliente_nombre'] . ' ' . $v['cliente_apellido']); ?>
                            <?php elseif (!empty($v['func_nombre'])): ?>
                                - Funcionario: <?php echo htmlspecialchars($v['func_nombre']); ?>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <!-- PASO 2 -->
    <?php if ($ventaSeleccionada > 0 && !empty($productosVenta)): ?>
    <div class="card">
        <h3>Paso 2: Seleccionar producto y motivo</h3>
        <p style="color:#64748b; font-size:13px;">Venta #<?php echo $ventaSeleccionada; ?>.</p>

        <form action="" method="POST">
            <input type="hidden" name="id_venta" value="<?php echo $ventaSeleccionada; ?>">

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display:block; font-weight:600; color:#334155; margin-bottom:6px;">Producto a devolver:</label>
                    <select name="id_producto" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px;">
                        <option value="">-- Seleccione un producto --</option>
                        <?php foreach ($productosVenta as $p): 
                            $disponibles = $p['cantidad'] - $p['ya_devuelto'];
                        ?>
                            <option value="<?php echo $p['id_producto']; ?>" <?php echo ($disponibles <= 0) ? 'disabled' : ''; ?>>
                                <?php echo htmlspecialchars($p['nombre']); ?> 
                                (Código: <?php echo htmlspecialchars($p['codigo'] ?? 'N/A'); ?>)
                                - Vendidos: <?php echo $p['cantidad']; ?>
                                - Disponibles: <?php echo $disponibles; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-weight:600; color:#334155; margin-bottom:6px;">Cantidad a devolver:</label>
                    <input type="number" name="cantidad" value="1" min="1" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px;">
                </div>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display:block; font-weight:600; color:#334155; margin-bottom:6px;">Motivo de la devolución (obligatorio):</label>
                <textarea name="motivo" rows="3" placeholder="Ej: Producto defectuoso, talla incorrecta..." required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px;"></textarea>
            </div>

            <button type="submit" name="registrar_devolucion" class="btn">Registrar Devolución</button>
        </form>
    </div>
    <?php elseif ($ventaSeleccionada > 0): ?>
    <div class="card">
        <p style="text-align: center; color: #64748b;">No se encontraron productos en esta venta.</p>
    </div>
    <?php endif; ?>

    <!-- VENTAS RECIENTES -->
    <div class="card">
        <h3>Ventas Recientes</h3>
        <p style="color:#64748b; font-size:13px;">Vista general de las últimas ventas registradas.</p>

        <?php if (empty($ventasRecientes)): ?>
            <p style="text-align: center; color: #64748b; padding: 20px;">No hay ventas registradas aún.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Venta #</th>
                        <th>Fecha</th>
                        <th>Cliente / Funcionario</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventasRecientes as $v): ?>
                        <tr>
                            <td><strong>#<?php echo $v['id_ventas']; ?></strong></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($v['fecha_hora'])); ?></td>
                            <td>
                                <?php 
                                if (!empty($v['cliente_nombre'])) {
                                    echo htmlspecialchars($v['cliente_nombre'] . ' ' . $v['cliente_apellido']);
                                } elseif (!empty($v['func_nombre'])) {
                                    echo 'Funcionario: ' . htmlspecialchars($v['func_nombre']);
                                } else {
                                    echo '<em style="color:#94a3b8;">Sin asignar</em>';
                                }
                                ?>
                            </td>
                            <td>$<?php echo number_format($v['total_venta'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

</body>
</html>