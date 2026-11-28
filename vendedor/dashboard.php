<?php
ob_start();

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/conexion.php';

requerirRol(['vendedor', 'administrador']);

$esAdmin = esAdmin();
$usuario = $_SESSION['usuario'];
$nombreUsuario = $usuario['nombre'] ?? 'Vendedor';
$rolActual = $usuario['rol'] ?? 'vendedor';

$ventasHoy = 0;
$ventasRecientes = [];
$errorVentas = null;
$pdo = null;
try {
    $pdo = conectar();
} catch (PDOException $e) {
    error_log('Error de conexión en el dashboard de vendedor: ' . $e->getMessage());
    $errorVentas = 'No se pudo conectar con la base de datos.';
}

if ($pdo instanceof PDO) {
    try {
        $filtroFuncionario = '';
        $parametrosVentas = [];
        if ($rolActual === 'vendedor') {
            $filtroFuncionario = ' AND v.id_funcionario = :id_funcionario';
            $parametrosVentas[':id_funcionario'] = (int) $usuario['id'];
        }

        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM Ventas v
             WHERE DATE(v.fecha_hora) = CURDATE()' . $filtroFuncionario
        );
        $stmt->execute($parametrosVentas);
        $ventasHoy = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare(
            "SELECT v.id_ventas, v.total_venta, v.fecha_hora,
                    COALESCE(NULLIF(CONCAT_WS(' ', c.nombre, c.apellidos), ''), 'Cliente general') AS cliente_nombre,
                    COALESCE(NULLIF(CONCAT_WS(' ', f.nombre, f.apellido), ''), 'No identificado') AS funcionario_nombre,
                    COALESCE(mp.nombre_medio, 'No registrado') AS medio_pago
             FROM Ventas v
             LEFT JOIN Clientes c ON v.id_cliente = c.id_cliente
             LEFT JOIN funcionarios f ON v.id_funcionario = f.id_funcionario
             LEFT JOIN Medio_pago mp ON v.id_medio_pago = mp.id_medio_pago
             WHERE 1 = 1" . $filtroFuncionario . '
             ORDER BY v.fecha_hora DESC
             LIMIT 5'
        );
        $stmt->execute($parametrosVentas);
        $ventasRecientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error al cargar las ventas del dashboard: ' . $e->getMessage());
        $errorVentas = 'No fue posible cargar la información de ventas. Intenta nuevamente más tarde.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Vendedor - MarketPlus</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/estilos.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/dashboard_vendedor.css?v=<?php echo time(); ?>">
</head>
<body class="dashboard-vendedor-page">

<div class="container dashboard-vendedor">

    <div class="header dashboard-vendedor__header">
        <div>
            <h2 class="dashboard-vendedor__title">MarketPlus - Panel de Vendedor</h2>
            <p class="dashboard-vendedor__greeting">¡Hola, <?php echo htmlspecialchars($nombreUsuario); ?>! ¿Qué haremos hoy?</p>
        </div>
        <div class="dashboard-vendedor__actions">
            <span class="dashboard-vendedor__role">
                Modo <?php echo htmlspecialchars(ucfirst($rolActual)); ?>
            </span>
            <a href="<?php echo BASE_URL; ?>/includes/logout.php" class="btn dashboard-vendedor__logout" onclick="return confirm('¿Estás seguro de que deseas cerrar sesión?');">Cerrar Sesión</a>
        </div>
    </div>

    <div class="card dashboard-vendedor__summary">
        <h3 class="dashboard-vendedor__summary-title">Resumen de Hoy</h3>
        <p class="dashboard-vendedor__summary-text">
            <?php if ($rolActual === 'administrador'): ?>
                Se han registrado <strong><?php echo $ventasHoy; ?> venta(s)</strong> en lo que va del día.
            <?php else: ?>
                Has registrado <strong><?php echo $ventasHoy; ?> venta(s)</strong> en lo que va del día.
            <?php endif; ?>
        </p>
    </div>

    <h3 class="dashboard-vendedor__section-title">Accesos Rápidos</h3>
    <div class="dashboard-vendedor__grid">
        <a href="<?php echo BASE_URL; ?>/index.php" class="card dashboard-vendedor__link-card">
            <div class="dashboard-vendedor__icon">🛒</div>
            <h4 class="dashboard-vendedor__card-title">Ir al Catálogo</h4>
            <p class="dashboard-vendedor__card-description">Ver productos disponibles y comenzar a vender.</p>
        </a>

        <a href="<?php echo BASE_URL; ?>/public/carrito.php" class="card dashboard-vendedor__link-card">
            <div class="dashboard-vendedor__icon">🛍️</div>
            <h4 class="dashboard-vendedor__card-title">Ver Carrito</h4>
            <p class="dashboard-vendedor__card-description">Revisar los productos seleccionados y finalizar la compra.</p>
        </a>

        <a href="<?php echo BASE_URL; ?>/ventas/consultar_ventas.php" class="card dashboard-vendedor__link-card">
            <div class="dashboard-vendedor__icon">📊</div>
            <h4 class="dashboard-vendedor__card-title">Historial de Ventas</h4>
            <p class="dashboard-vendedor__card-description">Revisar el listado de ventas realizadas.</p>
        </a>

        <a href="<?php echo BASE_URL; ?>/ventas/registrar_venta.php" class="card dashboard-vendedor__link-card">
            <div class="dashboard-vendedor__icon">🧾</div>
            <h4 class="dashboard-vendedor__card-title">Registrar nueva venta</h4>
            <p class="dashboard-vendedor__card-description">Ingresar una venta nueva al sistema.</p>
        </a>
    </div>

    <section class="dashboard-vendedor__sales" aria-labelledby="ventas-recientes-title">
        <div class="dashboard-vendedor__section-heading">
            <div>
                <h3 id="ventas-recientes-title" class="dashboard-vendedor__section-title">Ventas Recientes</h3>
                <p class="dashboard-vendedor__sales-caption">Tus últimas ventas registradas en el sistema.</p>
            </div>
            <a class="dashboard-vendedor__history-button" href="<?php echo BASE_URL; ?>/ventas/consultar_ventas.php">
                Ver historial completo
            </a>
        </div>

        <?php if ($errorVentas !== null): ?>
            <p class="dashboard-vendedor__sales-error" role="alert"><?php echo htmlspecialchars($errorVentas, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php elseif ($ventasRecientes): ?>
            <div class="dashboard-vendedor__table-scroll">
                <table class="dashboard-vendedor__table">
                    <thead>
                        <tr>
                            <th scope="col">N.º</th>
                            <th scope="col">Fecha y hora</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Vendedor</th>
                            <th scope="col">Medio de pago</th>
                            <th scope="col">Total</th>
                            <th scope="col">Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventasRecientes as $venta): ?>
                            <tr>
                                <td>#<?php echo (int) $venta['id_ventas']; ?></td>
                                <td><?php echo htmlspecialchars($venta['fecha_hora'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($venta['cliente_nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($venta['funcionario_nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($venta['medio_pago'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="dashboard-vendedor__sale-total">$<?php echo number_format((float) $venta['total_venta'], 2, ',', '.'); ?></td>
                                <td>
                                    <a class="dashboard-vendedor__detail-link" href="<?php echo BASE_URL; ?>/ventas/detalle_venta.php?id=<?php echo (int) $venta['id_ventas']; ?>">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="dashboard-vendedor__empty-sales">Todavía no hay ventas para mostrar.</p>
        <?php endif; ?>
    </section>

</div>

</body>
</html>