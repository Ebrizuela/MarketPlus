<?php
require_once '../includes/verificar_sesion.php';
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

$sql = "SELECT v.id_venta, v.fecha, c.nombre AS cliente, v.productos_detalle, v.total 
        FROM Ventas v 
        JOIN Clientes c ON v.id_cliente = c.id_cliente 
        WHERE v.id_vendedor = :id_vendedor";

if ($busqueda_limpia !== '') {
    $sql .= " AND (v.id_venta = :id_exacto OR c.nombre LIKE :texto OR CAST(v.fecha AS CHAR) LIKE :texto)";
}

$sql .= " ORDER BY v.fecha DESC";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id_vendedor', $id_vendedor_actual, PDO::PARAM_INT);

if ($busqueda_limpia !== '') {
    $stmt->bindValue(':id_exacto', intval($busqueda_limpia), PDO::PARAM_INT);
    $stmt->bindValue(':texto', "%$busqueda_limpia%", PDO::PARAM_STR);
}

$stmt->execute();
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Ventas - MarketPlus</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f2f5f7; margin: 0; padding: 30px; }
        .container { max-width: 1050px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 25px; }
        .logo { color: #0071ce; font-size: 24px; font-weight: bold; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .nav-link { color: #0071ce; text-decoration: none; font-size: 14px; font-weight: bold; padding: 8px 12px; border-radius: 6px; background-color: #e6f0fa; }
        .nav-link:hover { background-color: #d0e4f7; }
        .btn-logout { background-color: #ffebee; color: #c62828; text-decoration: none; font-size: 14px; font-weight: bold; padding: 8px 12px; border-radius: 6px; }
        .btn-logout:hover { background-color: #ffcdd2; }
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
        <div class="logo">MarketPlus | Mis Ventas</div>
        <div class="user-info">
            <span style="color: #666; font-size: 14px;">Vendedor ID: <?= $id_vendedor_actual ?></span>
            <a href="../logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </div>

    <form method="GET" class="search-box">
        <input type="text" name="buscar" placeholder="Buscar por ID de Venta, Nombre de Cliente o Fecha (YYYY-MM-DD)" value="<?= htmlspecialchars($busqueda) ?>">
        <button type="submit" class="btn-search">Buscar</button>
        <?php if($busqueda !== ''): ?>
            <a href="<?= basename($_SERVER['PHP_SELF']) ?>" class="btn-clear">Limpiar</a>
        <?php endif; ?>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID Venta</th>
                <th>Fecha y Hora</th>
                <th>Cliente</th>
                <th>Productos</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($ventas) > 0): ?>
                <?php foreach ($ventas as $v): ?>
                <tr>
                    <td><strong>#<?= $v['id_venta'] ?></strong></td>
                    <td><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></td>
                    <td><?= htmlspecialchars($v['cliente']) ?></td>
                    <td><?= htmlspecialchars($v['productos_detalle']) ?></td>
                    <td class="total-badge">$<?= number_format($v['total'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="empty-state">No se encontraron ventas asociadas a los criterios de búsqueda.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>