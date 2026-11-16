<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/conexion.php';

requerirRol(['administrador']);

$pdo = null;
try {
    $pdo = conectar();
} catch (PDOException $e) {
    error_log('Error de conexión en el dashboard de administrador: ' . $e->getMessage());
    $errorConexion = 'No se pudo conectar con la base de datos.';
}

$usuarioActual = $_SESSION['usuario'];
// Initialización de variables
$ventasDelDia = 0.00;
$stockBajoAlerta = [];
$logsSeguridad = [];
$clientesRegistrados = 0;

// Consultas dinámicas a la base de datos
if ($pdo instanceof PDO) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS logs_seguridad (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario VARCHAR(100) NOT NULL,
            accion VARCHAR(255) NOT NULL,
            fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            detalle VARCHAR(255) NULL,
            ip_origen VARCHAR(45) NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->prepare("INSERT INTO logs_seguridad (usuario, accion, detalle, ip_origen) VALUES (?, 'Acceso al Home del Administrador', 'Ingreso al panel principal', ?)")
            ->execute([$usuarioActual['nombre_completo'] ?? $usuarioActual['nombre'] ?? 'Administrador', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);

        // 1. Obtener la suma total de ventas del día actual
        $stmtVentas = $pdo->query("SELECT COALESCE(SUM(CAST(total_venta AS DECIMAL(10,2))), 0) FROM Ventas WHERE DATE(fecha_hora) = CURDATE()");
        $ventasDelDia = (float) $stmtVentas->fetchColumn();

        // 2. Obtener productos con stock bajo el umbral operativo de 10 unidades
        $stmtStock = $pdo->query("SELECT nombre AS producto, CAST(stock AS UNSIGNED) AS stock, 10 AS minimo FROM Productos WHERE CAST(stock AS UNSIGNED) <= 10 ORDER BY stock ASC LIMIT 10");
        $stockBajoAlerta = $stmtStock->fetchAll(PDO::FETCH_ASSOC);

        // 3. Obtener las últimas 5 actividades registradas en logs
        $stmtLogs = $pdo->query("SELECT usuario, accion, fecha, detalle FROM logs_seguridad ORDER BY fecha DESC LIMIT 5");
        $logsSeguridad = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

        $clientesRegistrados = (int) $pdo->query("SELECT COUNT(*) FROM Clientes")->fetchColumn();
    } catch (PDOException $e) {
        error_log('Error al cargar datos del dashboard de administrador: ' . $e->getMessage());
        $errorConexion = 'No se pudieron cargar todos los datos del dashboard.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Panel Principal Administrador</title>
    <!-- Framework CSS Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/marketplus.css">
    <link rel="stylesheet" href="../css/marketplus-theme.css?v=1">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard_administrador.php"><i class="bi bi-shield-lock"></i> MarketPlus</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard_administrador.php"><i class="bi bi-house-door"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php"><i class="bi bi-shop"></i> Catálogo</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="registro.php"><i class="bi bi-people"></i> 1. Usuarios/Roles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Productos_Inventory.php"><i class="bi bi-box-seam"></i> 2. Productos e Inventario</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../ventas/consultar_ventas.php"><i class="bi bi-cart-check"></i> 3. Ventas registradas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Clientes.php"><i class="bi bi-person-badge"></i> 4. Clientes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Reportes.php"><i class="bi bi-graph-up"></i> 5. Reportes</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center text-white me-3 gap-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person-circle fs-5 me-2"></i>
                        <div>
                            <small class="d-block text-white-50">Administrador</small>
                            <span><?= htmlspecialchars($_SESSION['usuario']['nombre_completo'] ?? $_SESSION['usuario']['nombre']) ?></span>
                        </div>
                    </div>
                    <a class="btn btn-outline-light btn-sm btn-logout" href="../includes/logout.php" onclick="return confirm('¿Seguro que deseas cerrar sesión?');">
                        <i class="bi bi-box-arrow-right"></i> Cerrar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Dashboard Principal -->
    <div class="container my-4">
        
        <?php if (isset($errorConexion)): ?>
            <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Aviso de Base de Datos:</strong> No se pudo conectar a la base de datos (<code><?= htmlspecialchars($errorConexion) ?></code>). Verifique que los contenedores estén corriendo con <code>docker compose up -d</code>.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="dashboard-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <p class="text-uppercase text-primary fw-semibold mb-1 small">Panel administrativo</p>
                <h2 class="mb-0">Dashboard Principal</h2>
            </div>
            <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill">Rol: <?= htmlspecialchars($_SESSION['usuario']['rol']) ?></span>
        </div>

        <!-- Información del Administrador -->
        <div class="row mb-4">
            <div class="col-12 col-md-6">
                <div class="card panel-card">
                    <div class="card-header d-flex align-items-center">
                        <i class="bi bi-person-badge text-primary me-2"></i> Información del Administrador
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <h6 class="text-muted small mb-1">Nombre Completo</h6>
                                <p class="mb-0"><strong><?= htmlspecialchars($_SESSION['usuario']['nombre_completo'] ?? $_SESSION['usuario']['nombre']) ?></strong></p>
                            </div>
                            <div class="col-sm-6">
                                <h6 class="text-muted small mb-1">RUT</h6>
                                <p class="mb-0"><strong><?= htmlspecialchars($_SESSION['usuario']['rut'] ?? 'N/A') ?></strong></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <h6 class="text-muted small mb-1">Correo Electrónico</h6>
                                <p class="mb-0"><strong><?= htmlspecialchars($_SESSION['usuario']['correo'] ?? 'N/A') ?></strong></p>
                            </div>
                            <div class="col-sm-6">
                                <h6 class="text-muted small mb-1">Rol del Sistema</h6>
                                <p class="mb-0"><span class="badge bg-success"><?= htmlspecialchars($_SESSION['usuario']['rol']) ?></span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acceso Rápido -->
        <h5 class="mb-3 text-muted">Módulos de Gestión Rápida</h5>
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <a href="registro.php" class="quick-card card text-center p-2">
                    <div class="card-body">
                        <i class="bi bi-people fs-1 text-primary"></i>
                        <h6 class="card-title text-dark mt-2 mb-0">Usuarios y Roles</h6>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <a href="eliminar_supervisor.php" class="quick-card card text-center p-2">
                    <div class="card-body">
                        <i class="bi bi-person-x fs-1 text-danger"></i>
                        <h6 class="card-title text-dark mt-2 mb-0">Eliminar supervisor</h6>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <a href="producto.php" class="quick-card card text-center p-2">
                    <div class="card-body">
                        <i class="bi bi-box-seam fs-1 text-success"></i>
                        <h6 class="card-title text-dark mt-2 mb-0">Productos e Inventario</h6>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <a href="../productos/Eliminar_producto.php" class="quick-card card text-center p-2">
                    <div class="card-body">
                        <i class="bi bi-trash fs-1 text-danger"></i>
                        <h6 class="card-title text-dark mt-2 mb-0">Eliminar productos</h6>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <a href="../administrador/actualizar_producto.php" class="quick-card card text-center p-2">
                    <div class="card-body">
                        <i class="bi bi-pencil-square fs-1 text-primary"></i>
                        <h6 class="card-title text-dark mt-2 mb-0">Actualizar producto</h6>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <a href="Clientes.php" class="quick-card card text-center p-2">
                    <div class="card-body">
                        <i class="bi bi-person-badge fs-1 text-info"></i>
                        <h6 class="card-title text-dark mt-2 mb-0">Clientes</h6>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <a href="../ventas/consultar_ventas.php" class="quick-card card text-center p-2">
                    <div class="card-body">
                        <i class="bi bi-receipt fs-1 text-primary"></i>
                        <h6 class="card-title text-dark mt-2 mb-0">Ventas registradas</h6>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <a href="inventario.php" class="quick-card card text-center p-2">
                    <div class="card-body">
                        <i class="bi bi-clipboard-data fs-1 text-success"></i>
                        <h6 class="card-title text-dark mt-2 mb-0">Control de inventario</h6>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <a href="../supervisor/devoluciones.php" class="quick-card card text-center p-2">
                    <div class="card-body">
                        <i class="bi bi-arrow-return-left fs-1 text-warning"></i>
                        <h6 class="card-title text-dark mt-2 mb-0">Gestión de devoluciones</h6>
                    </div>
                </a>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-6" id="usuarios">
                <div class="card panel-card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-people text-primary me-2"></i>Usuarios y Roles</span>
                        <span class="badge bg-primary">Administradores</span>
                    </div>
                    <div class="card-body">
                        <?php if ($pdo instanceof PDO): ?>
                            <?php
                            try {
                                $columnaActivo = $pdo->query("SHOW COLUMNS FROM funcionarios LIKE 'activo'");
                                $filtroActivo = $columnaActivo->rowCount() > 0
                                    ? ' WHERE (f.activo = 1 OR f.activo IS NULL)'
                                    : '';
                                $usuarios = $pdo->query("SELECT f.nombre, f.apellido, r.nombre AS rol FROM funcionarios f LEFT JOIN Roles r ON f.id_rol = r.id_rol" . $filtroActivo . " ORDER BY f.nombre LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                                <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Nombre</th><th>Rol</th></tr></thead><tbody>
                                    <?php foreach ($usuarios as $usuario): ?><tr><td><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></td><td><?= htmlspecialchars($usuario['rol'] ?? 'Sin rol') ?></td></tr><?php endforeach; ?>
                                </tbody></table></div>
                            <?php
                            } catch (PDOException $e) {
                                echo '<p class="text-muted mb-0">No se pudieron cargar los usuarios.</p>';
                            }
                            ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6" id="productos">
                <div class="card panel-card h-100">
                    <div class="card-header"><i class="bi bi-box-seam text-success me-2"></i>Productos e Inventario</div>
                    <div class="card-body">
                        <?php if (!empty($stockBajoAlerta)): ?>
                            <p class="mb-2">Productos con stock agotado:</p>
                            <ul class="mb-0"><?php foreach ($stockBajoAlerta as $item): ?><li><?= htmlspecialchars($item['producto']) ?>: <?= htmlspecialchars($item['stock']) ?> unidades</li><?php endforeach; ?></ul>
                        <?php else: ?><p class="text-muted mb-0">No hay productos con stock agotado.</p><?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4" id="pos">
                <div class="card panel-card h-100"><div class="card-header"><i class="bi bi-cart-check text-warning me-2"></i>Punto de Venta</div><div class="card-body"><p class="mb-0">Ventas registradas hoy: <strong><?= number_format($ventasDelDia, 2) ?></strong></p></div></div>
            </div>
            <div class="col-12 col-lg-4" id="clientes">
                <div class="card panel-card h-100"><div class="card-header"><i class="bi bi-person-badge text-info me-2"></i>Clientes</div><div class="card-body"><p class="mb-0">Clientes registrados: <strong><?= $clientesRegistrados ?></strong></p></div></div>
            </div>
            <div class="col-12 col-lg-4" id="reportes">
                <div class="card panel-card h-100"><div class="card-header"><i class="bi bi-bar-chart-line text-danger me-2"></i>Reportes</div><div class="card-body"><p class="mb-0">Consulta el resumen de ventas y alertas de inventario en esta pantalla.</p></div></div>
            </div>
        </div>

        <hr class="my-4">

        <!-- Métricas y Resumen Operativo -->
        <h5 class="mb-3 text-muted">Resumen Operativo</h5>
        <div class="row g-4">
            
            <!-- Ventas del Día -->
            <div class="col-12 col-md-4">
                <div class="card stat-card text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-white-50">Ventas del Día</h6>
                                <h2 class="card-title mb-0">$<?= number_format($ventasDelDia, 2) ?></h2>
                            </div>
                            <i class="bi bi-cash-stack fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Bajo Alerta -->
            <div class="col-12 col-md-8">
                <div class="card panel-card h-100">
                    <div class="card-header d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i> Stock Bajo Alerta
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Stock Actual</th>
                                        <th>Stock Mínimo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($stockBajoAlerta)): ?>
                                        <?php foreach ($stockBajoAlerta as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['producto']) ?></td>
                                                <td><span class="badge badge-soft"><?= htmlspecialchars($item['stock']) ?> unidades</span></td>
                                                <td><?= htmlspecialchars($item['minimo']) ?> unidades</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">No hay productos en alerta de stock.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logs de Seguridad -->
            <div class="col-12">
                <div class="card panel-card">
                    <div class="card-header d-flex align-items-center">
                        <i class="bi bi-journal-text text-secondary me-2"></i> Últimas Actividades (Logs de Seguridad)
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if (!empty($logsSeguridad)): ?>
                                <?php foreach ($logsSeguridad as $log): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bi bi-clock-history me-2 text-muted"></i>
                                            <strong><?= htmlspecialchars($log['usuario']) ?>:</strong> <?= htmlspecialchars($log['accion']) ?>
                                        </div>
                                        <small class="text-muted"><?= htmlspecialchars($log['fecha']) ?></small>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="list-group-item text-center text-muted py-3">No hay registros de actividad recientes.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Script de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>