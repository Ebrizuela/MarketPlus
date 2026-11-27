<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../funciones/navegacion.php';

requerirRol(['administrador', 'supervisor', 'vendedor']);

$ventas = [];
$error = null;

try {
    $pdo = conectar();
    $sql = "SELECT
                v.id_ventas,
                v.total_venta,
                v.fecha_hora,
                COALESCE(NULLIF(CONCAT_WS(' ', c.nombre, c.apellidos), ''), 'Cliente general') AS cliente_nombre,
                COALESCE(NULLIF(CONCAT_WS(' ', f.nombre, f.apellido), ''), 'No identificado') AS funcionario_nombre,
                COALESCE(mp.nombre_medio, 'No registrado') AS medio_pago
            FROM Ventas v
            LEFT JOIN Clientes c ON v.id_cliente = c.id_cliente
            LEFT JOIN funcionarios f ON v.id_funcionario = f.id_funcionario
            LEFT JOIN Medio_pago mp ON v.id_medio_pago = mp.id_medio_pago";

    $parametros = [];
    if ($_SESSION['usuario']['rol'] === 'vendedor') {
        $sql .= ' WHERE v.id_funcionario = :id_funcionario';
        $parametros[':id_funcionario'] = (int) $_SESSION['usuario']['id'];
    }

    $sql .= ' ORDER BY v.fecha_hora DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    $ventas = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Error al consultar ventas: ' . $e->getMessage());
    $error = 'No fue posible cargar las ventas. Comprueba la conexión a la base de datos.';
    http_response_code(503);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Ventas - MarketPlus</title>
    <link rel="stylesheet" href="../css/navegacion.css">
    <link rel="stylesheet" href="../css/consultar_ventas.css">
    <link rel="stylesheet" href="../css/marketplus-theme.css?v=1">
</head>
<body>
    <?php renderNavegacion(null, BASE_URL . '/index.php'); ?>
    <main class="content">
        <h1>Listado de ventas</h1>
        <?php if ($_SESSION['usuario']['rol'] === 'vendedor'): ?>
            <p>
                <a class="mp-btn mp-btn-primary" href="../vendedor/visualizacion_productos.php">Visualización de Productos</a>
            </p>
        <?php endif; ?>
        <?php if ($error !== null): ?>
            <p class="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p> 
        <?php endif; ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID Venta</th>
                        <th>Fecha / Hora</th>
                        <th>Cliente</th>
                        <th>Atendido por</th>
                        <th>Medio de pago</th>
                        <th>Total</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($ventas): ?>
                        <?php foreach ($ventas as $venta): ?>
                            <tr>
                                <td>#<?= (int) $venta['id_ventas'] ?></td>
                                <td><?= htmlspecialchars($venta['fecha_hora'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($venta['cliente_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($venta['funcionario_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($venta['medio_pago'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>$<?= number_format((float) $venta['total_venta'], 2, ',', '.') ?></td>
                                <td>
                                    <a class="mp-btn mp-btn-outline" href="detalle_venta.php?id=<?= (int) $venta['id_ventas'] ?>">Ver detalle</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php elseif ($error === null): ?>
                        <tr><td colspan="7">No hay ventas para mostrar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
