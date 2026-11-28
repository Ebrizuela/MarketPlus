<?php
ob_start();
session_start();

require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../includes/auth.php';
$pdo = conectar();
$columnaEstadoExiste = $pdo->query("SHOW COLUMNS FROM Productos LIKE 'estado'")->rowCount() > 0;

$esAdmin = esAdmin();
$estaLogueado = estaLogueado();
$rolActual = $_SESSION['usuario']['rol'] ?? null;
$carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];
$mensaje = "";

if (isset($_SESSION['mensaje_flash'])) {
    $mensaje = $_SESSION['mensaje_flash'];
    unset($_SESSION['mensaje_flash']);
}

// VACIAR CARRITO
if (isset($_POST['vaciar_carrito'])) { 
    unset($_SESSION['carrito']);
    $_SESSION['mensaje_flash'] = "<div class='alert success'>Se ha vaciado el carrito de compras.</div>";
    header("Location: " . BASE_URL . "/public/carrito.php");
    exit();
}

// FINALIZAR COMPRA
if (isset($_POST['finalizar_compra']) && !empty($carrito)) {
    try {
        $pdo->beginTransaction();
        $stockInsuficiente = false;
        $productoError = "";
        $productoNoDisponible = false;
        $totalVenta = 0;

        foreach ($carrito as $id_prod => $item) {
            $sqlProducto = "SELECT stock, nombre, precio FROM Productos WHERE id_producto = ?";
            if ($columnaEstadoExiste) {
                $sqlProducto .= " AND estado = 1";
            }
            $stmt = $pdo->prepare($sqlProducto . " FOR UPDATE");
            $stmt->execute([$id_prod]);
            $prodBd = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$prodBd) {
                $productoNoDisponible = true;
                $productoError = $item['nombre'];
                break;
            }
            if ($prodBd['stock'] < $item['cantidad']) {
                $stockInsuficiente = true;
                $productoError = $item['nombre'];
                break;
            }
            $totalVenta += $prodBd['precio'] * $item['cantidad'];
        }

        if ($stockInsuficiente) {
            $pdo->rollBack();
            $mensaje = "<div class='alert error'>Error: No hay suficiente stock para el producto '" . htmlspecialchars($productoError, ENT_QUOTES, 'UTF-8') . "'.</div>";
        } elseif ($productoNoDisponible) {
            $pdo->rollBack();
            $mensaje = "<div class='alert error'>El producto '" . htmlspecialchars($productoError, ENT_QUOTES, 'UTF-8') . "' ya no está disponible. Quita el producto del carrito antes de continuar.</div>";
        } else {
            $id_cliente     = null;
            $id_funcionario = null;

            if ($estaLogueado) {
                if (in_array($rolActual, ['administrador', 'supervisor', 'vendedor'])) {
                    $id_funcionario = $_SESSION['usuario']['id'] ?? null;
                } else {
                    $id_cliente = $_SESSION['usuario']['id'] ?? null;
                }
            }

            $id_medio_pago  = 1;
            $ip_origen      = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $logs_seguridad = 'Venta registrada desde el carrito web';

            $stmtVenta = $pdo->prepare("
                INSERT INTO Ventas 
                    (id_funcionario, id_cliente, id_medio_pago, total_venta, fecha_hora, ip_origen, logs_seguridad) 
                VALUES (?, ?, ?, ?, NOW(), ?, ?)
            ");
            $stmtVenta->execute([
                $id_funcionario, 
                $id_cliente, 
                $id_medio_pago, 
                $totalVenta, 
                $ip_origen, 
                $logs_seguridad
            ]);
            
            $idVenta = $pdo->lastInsertId();

            $stmtDetalle = $pdo->prepare("
                INSERT INTO Detalle_Ventas 
                    (id_ventas, id_producto, cantidad, precio_unitario, subtotal) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmtUpdate = $pdo->prepare("UPDATE Productos SET stock = stock - ? WHERE id_producto = ?");

            foreach ($carrito as $id_prod => $item) {
                $stmtProd = $pdo->prepare("SELECT precio FROM Productos WHERE id_producto = ?");
                $stmtProd->execute([$id_prod]);
                $precioUnitario = $stmtProd->fetchColumn();

                $subtotal = $precioUnitario * $item['cantidad'];

                $stmtDetalle->execute([$idVenta, $id_prod, $item['cantidad'], $precioUnitario, $subtotal]);
                $stmtUpdate->execute([$item['cantidad'], $id_prod]);
            }

            $pdo->commit();
            unset($_SESSION['carrito']);
            $_SESSION['mensaje_flash'] = "<div class='alert success'>¡Compra realizada con éxito! Venta #{$idVenta} registrada. El stock ha sido actualizado.</div>";
            header("Location: " . BASE_URL . "/public/carrito.php");
            exit();
        }

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $mensaje = "<div class='alert error'>Error al procesar la compra: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - MarketPlus</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/estilos.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/marketplus-theme.css?v=1">
</head>
<body>

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
                <button class="btn-hamburguesa" aria-label="Abrir menú">
                    ☰ Menú
                </button>
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

    <div class="card">
        <h3 style="margin-bottom: 20px;">Tu Carrito de Compras</h3>
        
        <?php if (empty($carrito)): ?>
            <p style="color: var(--text-muted); margin-bottom: 20px; text-align: center;">El carrito está vacío por el momento.</p>
            <div style="text-align: center;">
                <a href="<?php echo BASE_URL; ?>/index.php" class="btn" style="background: #000000;">Ir a Ver Productos</a>
            </div>
        <?php else: ?>
            <table style="width:100%; border-collapse: collapse; margin-bottom: 20px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                        <th style="padding: 12px 10px;">Producto</th>
                        <th style="padding: 12px 10px;">Precio</th>
                        <th style="padding: 12px 10px;">Cantidad</th>
                        <th style="padding: 12px 10px;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    foreach ($carrito as $item): 
                        $subtotal = $item['precio'] * $item['cantidad'];
                        $total += $subtotal;
                    ?>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 12px 10px; font-weight: 500;"><?php echo htmlspecialchars($item['nombre']); ?></td>
                        <td style="padding: 12px 10px;">$<?php echo number_format($item['precio'], 0, ',', '.'); ?></td>
                        <td style="padding: 12px 10px; font-weight: 600;"><?php echo $item['cantidad']; ?></td>
                        <td style="padding: 12px 10px; font-weight: 600;">$<?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="text-align: right; font-size: 20px; font-weight: 800; color: var(--text-dark); margin-bottom: 30px;">
                Total: $<?php echo number_format($total, 0, ',', '.'); ?>
            </div>

            <div style="display: flex; gap: 15px; justify-content: center; align-items: center; flex-wrap: wrap;">
                <a href="<?php echo BASE_URL; ?>/index.php" class="btn" style="background: #000000; padding: 12px 24px; font-size: 15px; text-decoration: none;">Seguir Comprando</a>
                
                <form action="" method="POST" style="margin: 0;">
                    <button type="submit" name="vaciar_carrito" class="btn" style="background: #ef4444; color: white; padding: 12px 24px; font-size: 15px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;" onclick="return confirm('¿Seguro que deseas vaciar el carrito?');">Vaciar Carrito</button>
                </form>

                <form action="" method="POST" style="margin: 0;">
                    <button type="submit" name="finalizar_compra" class="btn" style="background: #10b981; color: white; padding: 12px 24px; font-size: 15px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;" onclick="return confirm('¿Confirmar la compra de los productos?');">✓ Finalizar Compra</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>