<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/conexion.php';

requerirRol(['supervisor']);

$pdo = null;
try {
    $pdo = conectar();
} catch (PDOException $e) {
    error_log('Error de conexión en el dashboard de supervisor: ' . $e->getMessage());
    $errorDashboard = 'No se pudo conectar con la base de datos.';
}

$usuarioActual = $_SESSION['usuario'];
$ventasDelDia = 0.0;
$stockBajoAlerta = [];
$logsSeguridad = [];
$clientesRegistrados = 0;
$productosRegistrados = 0;
$usuarios = [];

if ($pdo) {
    try {
        $ventasDelDia = (float) $pdo->query(
            "SELECT COALESCE(SUM(total_venta), 0)
             FROM Ventas
             WHERE fecha_hora >= CURDATE()
               AND fecha_hora < CURDATE() + INTERVAL 1 DAY"
        )->fetchColumn();

        $stockBajoAlerta = $pdo->query(
            "SELECT nombre AS producto, stock, 10 AS stock_minimo
             FROM Productos
             WHERE stock <= 10
             ORDER BY stock ASC
             LIMIT 5"
        )->fetchAll();
        $clientesRegistrados = (int) $pdo->query("SELECT COUNT(*) FROM Clientes")->fetchColumn();
        $productosRegistrados = (int) $pdo->query("SELECT COUNT(*) FROM Productos")->fetchColumn();
        $usuarios = $pdo->query("SELECT f.nombre, f.apellido, r.nombre AS rol FROM funcionarios f LEFT JOIN Roles r ON f.id_rol = r.id_rol ORDER BY f.nombre, f.apellido LIMIT 20")->fetchAll();
    } catch (PDOException $e) {
        error_log('Error al cargar datos del dashboard de supervisor: ' . $e->getMessage());
        $errorDashboard = 'No se pudieron cargar todos los indicadores del dashboard.';
    }

    try {
        $usuarioLog = $_SESSION['usuario']['nombre'] ?? 'Supervisor';
        $ipOrigen = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $pdo->prepare(
            "INSERT INTO logs_seguridad (usuario, accion, fecha, detalle, ip_origen)
             VALUES (?, 'Acceso al Home del Supervisor', NOW(), 'Ingreso al panel principal', ?)"
        )->execute([$usuarioLog, $ipOrigen]);
    } catch (PDOException $e) {
        error_log('Error al registrar acceso al dashboard de supervisor: ' . $e->getMessage());
    }

    try {
        $logsSeguridad = $pdo->query(
            "SELECT usuario, accion, fecha, detalle
             FROM logs_seguridad
             ORDER BY fecha DESC
             LIMIT 5"
        )->fetchAll();
    } catch (PDOException $e) {
        error_log('Error al consultar actividad reciente del supervisor: ' . $e->getMessage());
        $errorDashboard = 'No se pudo cargar la actividad reciente.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Supervisor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/dashboard_supervisor.css?v=1.0">
    <link rel="stylesheet" href="../css/marketplus-theme.css?v=1">
</head>
<body class="supervisor-dashboard">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="../index.php"><i class="bi bi-shield-lock me-2"></i> MarketPlus</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuSupervisor">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menuSupervisor">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="index.php"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="../index.php"><i class="bi bi-shop me-1"></i>Catálogo</a></li>
                    <li class="nav-item"><a class="nav-link" href="#usuarios"><i class="bi bi-people me-1"></i>Usuarios/Roles</a></li>
                    <li class="nav-item"><a class="nav-link" href="#funciones"><i class="bi bi-grid me-1"></i>Funciones</a></li>
                    <li class="nav-item"><a class="nav-link" href="#productos"><i class="bi bi-box-seam me-1"></i>Productos</a></li>
                    <li class="nav-item"><a class="nav-link" href="../ventas/consultar_ventas.php"><i class="bi bi-cart-check me-1"></i>POS</a></li>
                    <li class="nav-item"><a class="nav-link" href="#clientes"><i class="bi bi-person-badge me-1"></i>Clientes</a></li>
                    <li class="nav-item"><a class="nav-link" href="supervisar_ventas.php"><i class="bi bi-graph-up me-1"></i>Supervisar ventas</a></li>
                </ul>
                <div class="d-flex align-items-center text-white gap-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person-circle fs-5 me-2"></i>
                        <span><?= htmlspecialchars($_SESSION['usuario']['nombre'] ?? 'Supervisor'); ?></span>
                    </div>
                    <a class="btn btn-outline-light btn-sm" href="../includes/logout.php" onclick="return confirm('¿Deseas cerrar sesión?');">
                        <i class="bi bi-box-arrow-right me-1"></i>Cerrar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <?php if (isset($errorDashboard)): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Advertencia:</strong> <?= htmlspecialchars($errorDashboard, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>

        <div class="header-panel d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <p class="text-uppercase text-primary fw-semibold mb-1 small">Panel principal</p>
                <h2 class="mb-0">Home Supervisor</h2>
            </div>
            <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill">Rol: <?= htmlspecialchars($_SESSION['usuario']['rol'] ?? 'Supervisor'); ?></span>
        </div>

        <section id="funciones" class="mb-4" aria-labelledby="funciones-titulo">
            <h5 id="funciones-titulo" class="mb-3 text-muted">Funciones de supervisión</h5>
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="../ventas/consultar_ventas.php" class="quick-card module-card card h-100">
                        <div class="card-body d-flex align-items-start gap-3">
                            <span class="module-icon"><i class="bi bi-receipt"></i></span>
                            <div><h6 class="card-title mb-1">Consultar ventas</h6><p>Ver ventas registradas y abrir sus detalles.</p></div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="supervisar_ventas.php" class="quick-card module-card card h-100">
                        <div class="card-body d-flex align-items-start gap-3">
                            <span class="module-icon"><i class="bi bi-graph-up"></i></span>
                            <div><h6 class="card-title mb-1">Supervisar ventas</h6><p>Filtrar transacciones y revisar productos vendidos.</p></div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="historial_ventas.php" class="quick-card module-card card h-100">
                        <div class="card-body d-flex align-items-start gap-3">
                            <span class="module-icon"><i class="bi bi-clock-history"></i></span>
                            <div><h6 class="card-title mb-1">Historial de ventas</h6><p>Consultar las ventas registradas anteriormente.</p></div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="devoluciones.php" class="quick-card module-card card h-100">
                        <div class="card-body d-flex align-items-start gap-3">
                            <span class="module-icon"><i class="bi bi-arrow-return-left"></i></span>
                            <div><h6 class="card-title mb-1">Gestionar devoluciones</h6><p>Registrar y revisar devoluciones de productos.</p></div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="historial_devoluciones.php" class="quick-card module-card card h-100">
                        <div class="card-body d-flex align-items-start gap-3">
                            <span class="module-icon"><i class="bi bi-journal-check"></i></span>
                            <div><h6 class="card-title mb-1">Historial de devoluciones</h6><p>Consultar devoluciones procesadas.</p></div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="exportar_ventas.php" class="quick-card module-card card h-100">
                        <div class="card-body d-flex align-items-start gap-3">
                            <span class="module-icon"><i class="bi bi-file-earmark-spreadsheet"></i></span>
                            <div><h6 class="card-title mb-1">Exportar ventas</h6><p>Descargar el reporte de ventas en Excel.</p></div>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <h5 class="mb-3 text-muted">Accesos directos</h5>
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <a href="#usuarios" class="quick-card card text-center p-2">
                    <div class="card-body">
                        <i class="bi bi-people fs-1 text-primary"></i>
                        <h6 class="card-title text-dark mt-2 mb-0">Usuarios y Roles</h6>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <a href="#productos" class="quick-card card text-center p-2">
                    <div class="card-body">
                        <i class="bi bi-box-seam fs-1 text-success"></i>
                        <h6 class="card-title text-dark mt-2 mb-0">Productos</h6>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <a href="#clientes" class="quick-card card text-center p-2">
                    <div class="card-body">
                        <i class="bi bi-person-badge fs-1 text-info"></i>
                        <h6 class="card-title text-dark mt-2 mb-0">Clientes</h6>
                    </div>
                </a>
            </div>
        </div>

        <hr class="my-4">

        <div class="row g-4 mb-4" id="usuarios">
            <div class="col-12">
                <div class="card panel-card">
                    <div class="card-header d-flex align-items-center">
                        <i class="bi bi-people text-primary me-2"></i> Usuarios y Roles
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Rol</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($usuarios)): ?>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <tr>
                                                <td><?= htmlspecialchars(trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? ''))) ?></td>
                                                <td><?= htmlspecialchars($usuario['rol'] ?? 'Sin rol') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-3">No se encontraron usuarios registrados.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mb-3 text-muted">Resumen operativo</h5>
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2 text-white-50">Ventas del día</h6>
                                <h3 class="mb-0">$<?= number_format($ventasDelDia, 0, ',', '.') ?></h3>
                            </div>
                            <i class="bi bi-cash-stack fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3" id="clientes">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2 text-white-50">Clientes</h6>
                                <h3 class="mb-0"><?= number_format($clientesRegistrados, 0, ',', '.') ?></h3>
                            </div>
                            <i class="bi bi-people fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3" id="productos">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2 text-white-50">Productos</h6>
                                <h3 class="mb-0"><?= number_format($productosRegistrados, 0, ',', '.') ?></h3>
                            </div>
                            <i class="bi bi-box-seam fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2 text-white-50">Stock bajo alerta</h6>
                                <h3 class="mb-0"><?= count($stockBajoAlerta) ?></h3>
                            </div>
                            <i class="bi bi-exclamation-triangle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-lg-7" id="inventario">
                <div class="card panel-card h-100">
                    <div class="card-header d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i> Stock bajo alerta
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Stock actual</th>
                                        <th>Stock mínimo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($stockBajoAlerta)): ?>
                                        <?php foreach ($stockBajoAlerta as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['producto'] ?? 'Sin nombre') ?></td>
                                                <td><span class="badge badge-soft"><?= htmlspecialchars($item['stock'] ?? 0) ?> unidades</span></td>
                                                <td><?= htmlspecialchars($item['stock_minimo'] ?? 10) ?> unidades</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">No hay productos con stock bajo alerta.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-5">
                <div class="card panel-card h-100">
                    <div class="card-header d-flex align-items-center">
                        <i class="bi bi-journal-text text-secondary me-2"></i> Últimas actividades (logs)
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if (!empty($logsSeguridad)): ?>
                                <?php foreach ($logsSeguridad as $log): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div>
                                            <strong><?= htmlspecialchars($log['usuario'] ?? 'Sistema') ?>:</strong>
                                            <?= htmlspecialchars($log['accion'] ?? 'Actividad') ?>
                                            <?php if (!empty($log['detalle'])): ?>
                                                <span class="text-muted">- <?= htmlspecialchars($log['detalle']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted"><?= htmlspecialchars($log['fecha'] ?? '') ?></small>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="list-group-item text-center text-muted py-3">No existen registros de seguridad recientes.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
