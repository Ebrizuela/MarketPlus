```php
<?php
session_start();
if (!isset($_SESSION['id_funcionario'])) {
    header("Location: ../login.php");
    exit;
}

require_once '../funciones/conexion.php';
$pdo = conectar();

$id_vendedor_actual = $_SESSION['id_funcionario'];
$busqueda = trim($_GET['buscar'] ?? '');
$busqueda_limpia = str_replace('#', '', $busqueda);

$sql = "SELECT dv.id_detalle, v.id_venta, v.fecha, p.nombre AS producto, dv.cantidad, dv.precio_unitario, dv.subtotal, c.nombre AS cliente 
        FROM Detalle_Ventas dv 
        JOIN Ventas v ON dv.id_venta = v.id_venta 
        JOIN Productos p ON dv.id_producto = p.id_producto 
        JOIN Clientes c ON v.id_cliente = c.id_cliente 
        WHERE v.id_vendedor = :id_vendedor";

if ($busqueda_limpia !== '') {
    $sql .= " AND (v.id_venta = :id_exacto OR c.nombre LIKE :texto OR p.nombre LIKE :texto)";
}

$sql .= " ORDER BY v.fecha DESC, dv.id_detalle ASC";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id_vendedor', $id_vendedor_actual, PDO::PARAM_INT);

if ($busqueda_limpia !== '') {
    $stmt->bindValue(':id_exacto', intval($busqueda_limpia), PDO::PARAM_INT);
    $stmt->bindValue(':texto', "%$busqueda_limpia%", PDO::PARAM_STR);
}

$stmt->execute();
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Ventas - MarketPlus</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f2f5f7; margin: 0; padding: 30px; }
        .container { max-width: 1050px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 25px; }
        .logo { color: #0071ce; font-size: 24px; font-weight: bold; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .nav-link { color: #0071ce; text-decoration: none; font-size: 14px; font-weight: bold; padding: 8px 12px; border-radius: 6px; background-color: #e6f0fa; }
        .nav-link:hover { background-color: #d0e4f7; }
        .search-box { display: flex; gap: 10px; margin-bottom: 25px; }
        .search-box input { flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; }
        .btn-search { background-color: #0071ce; color: white; border: none; padding: 12px 24px; border-radius: 20px; font-weight: bold; cursor: pointer; }
        .btn-clear { background-color: #e0e0e0; color: #333; border: none; padding: 12px 20px; border-radius: 20px; text-decoration: none; font-size: 14px; font-weight: bold; display: flex; align-items: center; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background-color: #f8f9fa; color: #4a4a4a; padding: 14px; border-bottom: 2px solid #e0e0e0; font-size: 14px; }
        td { padding: 14px; border-bottom: 1px solid #eeeeee; font-size: 14px; color: #333; }
        .total-badge { font-weight: bold; color: #0071ce; }
        .empty-state { text-align: center; padding: 30px; color: #777; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="logo">MarketPlus | Detalle de Ventas</div>
        <div class="user-info">
            <?php if(isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1): ?>
                <a href="../administrador/index.php" class="nav-link">Dashboard Admin</a>
            <?php endif; ?>
            <span style="color: #666; font-size: 14px;">Vendedor ID: <?= $id_vendedor_actual ?></span>
        </div>
    </div>

    <form method="GET" class="search-box">
        <input type="text" name="buscar" placeholder="Buscar por ID de Venta, Cliente o Producto..." value="<?= htmlspecialchars($busqueda) ?>">
        <button type="submit" class="btn-search">Buscar</button>
        <?php if($busqueda !== ''): ?>
            <a href="<?= basename($_SERVER['PHP_SELF']) ?>" class="btn-clear">Limpiar</a>
        <?php endif; ?>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID Detalle</th>
                <th>ID Venta</th>
                <th>Cliente</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($detalles) > 0): ?>
                <?php foreach ($detalles as $d): ?>
                <tr>
                    <td><strong>#<?= $d['id_detalle'] ?></strong></td>
                    <td>#<?= $d['id_venta'] ?></td>
                    <td><?= htmlspecialchars($d['cliente']) ?></td>
                    <td><?= htmlspecialchars($d['producto']) ?></td>
                    <td><?= $d['cantidad'] ?></td>
                    <td>$<?= number_format($d['precio_unitario'], 0, ',', '.') ?></td>
                    <td class="total-badge">$<?= number_format($d['subtotal'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="empty-state">No se encontraron detalles de ventas para los criterios ingresados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>

```