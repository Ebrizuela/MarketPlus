<?php
/**
 * MarketPlus - Módulo de Reportes de Ventas
 * Pantalla principal interactiva para consulta, filtrado y exportación formal de ventas.
 * Aplica estilos CSS institucionales centralizados y pie de página corporativo oficial.
 */

// Definicion segura de BASE_URL para compatibilidad en Docker o servidores web
if (!defined('BASE_URL')) {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $basePath = ($scriptDir === '/' || $scriptDir === '\\') ? '' : rtrim(str_replace('\\', '/', $scriptDir), '/');
    define('BASE_URL', preg_replace('#/(ventas|productos|administrador|supervisor|auth|public|includes|funciones).*$#i', '', $basePath));
}

date_default_timezone_set('America/Santiago');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../funciones/plantilla_reportes.php';

requerirRol(['administrador', 'supervisor']);

$usuario = obtenerUsuarioResponsable();

// 1. Captura y validación estricta de filtros
$validarFechaYmd = static function (string $valor): string {
    if ($valor === '') {
        return '';
    }
    $dt = DateTime::createFromFormat('!Y-m-d', $valor);
    return ($dt !== false && $dt->format('Y-m-d') === $valor) ? $valor : '';
};

$fechaInicio   = $validarFechaYmd(trim((string) ($_GET['fecha_inicio'] ?? '')));
$fechaFin      = $validarFechaYmd(trim((string) ($_GET['fecha_fin'] ?? '')));
$idFuncionario = trim((string) ($_GET['id_funcionario'] ?? ''));
$idMedioPago   = trim((string) ($_GET['id_medio_pago'] ?? ''));
$idCategoria   = trim((string) ($_GET['id_categoria'] ?? ''));
$busqueda      = trim((string) ($_GET['busqueda'] ?? ''));

$ventas = [];
$funcionariosList = [];
$mediosPagoList = [];
$categoriasList = [];
$errorMsg = null;

try {
    $pdo = obtenerConexionBd();
    if (!$pdo) {
        throw new RuntimeException("No fue posible establecer conexión con la base de datos.");
    }

    // Cargar listas desplegables para filtros
    $funcionariosList = $pdo->query("SELECT id_funcionario, CONCAT(nombre, ' ', apellido) as nombre_completo, id_rol FROM funcionarios ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
    $mediosPagoList   = $pdo->query("SELECT id_medio_pago, nombre_medio FROM Medio_pago ORDER BY id_medio_pago ASC")->fetchAll(PDO::FETCH_ASSOC);
    $categoriasList   = $pdo->query("SELECT id_categorias, nombre FROM Categorias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

    // Consulta de ventas con filtros
    $sql = "SELECT 
                v.id_ventas,
                v.total_venta,
                v.fecha_hora,
                v.ip_origen,
                COALESCE(CONCAT(c.nombre, ' ', c.apellidos), 'Cliente Web / Mostrador') AS cliente_nombre,
                COALESCE(c.rut, 'Sin RUT') AS cliente_rut,
                COALESCE(CONCAT(f.nombre, ' ', f.apellido), 'Autoservicio / Sistema') AS funcionario_nombre,
                COALESCE(mp.nombre_medio, 'No especificado') AS medio_pago,
                (SELECT GROUP_CONCAT(CONCAT(dv.cantidad, 'x ', p.nombre) SEPARATOR '; ')
                 FROM Detalle_Ventas dv
                 INNER JOIN Productos p ON dv.id_producto = p.id_producto
                 WHERE dv.id_ventas = v.id_ventas) AS productos_resumen,
                (SELECT SUM(dv.cantidad)
                 FROM Detalle_Ventas dv
                 WHERE dv.id_ventas = v.id_ventas) AS total_unidades
            FROM Ventas v
            LEFT JOIN Clientes c ON v.id_cliente = c.id_cliente
            LEFT JOIN funcionarios f ON v.id_funcionario = f.id_funcionario
            LEFT JOIN Medio_pago mp ON v.id_medio_pago = mp.id_medio_pago
            WHERE 1=1";

    $params = [];
    $filtrosTexto = [];

    if ($fechaInicio !== '') {
        $sql .= " AND DATE(v.fecha_hora) >= :fecha_inicio";
        $params[':fecha_inicio'] = $fechaInicio;
        $filtrosTexto['Desde'] = date('d/m/Y', strtotime($fechaInicio));
    }

    if ($fechaFin !== '') {
        $sql .= " AND DATE(v.fecha_hora) <= :fecha_fin";
        $params[':fecha_fin'] = $fechaFin;
        $filtrosTexto['Hasta'] = date('d/m/Y', strtotime($fechaFin));
    }

    if ($idFuncionario !== '' && ctype_digit((string) $idFuncionario)) {
        $sql .= " AND v.id_funcionario = :id_funcionario";
        $params[':id_funcionario'] = (int) $idFuncionario;
    }

    if ($idMedioPago !== '' && ctype_digit((string) $idMedioPago)) {
        $sql .= " AND v.id_medio_pago = :id_medio_pago";
        $params[':id_medio_pago'] = (int) $idMedioPago;
    }

    if ($idCategoria !== '' && ctype_digit((string) $idCategoria)) {
        $sql .= " AND v.id_ventas IN (
                    SELECT dv2.id_ventas 
                    FROM Detalle_Ventas dv2 
                    INNER JOIN Productos p2 ON dv2.id_producto = p2.id_producto 
                    WHERE p2.id_categorias = :id_categoria
                 )";
        $params[':id_categoria'] = (int) $idCategoria;
    }

    if ($busqueda !== '') {
        $sql .= " AND (
            c.nombre LIKE :busq1 OR 
            c.apellidos LIKE :busq2 OR 
            c.rut LIKE :busq3 OR 
            v.id_ventas = :busq4 OR
            v.id_ventas IN (
                SELECT dv3.id_ventas 
                FROM Detalle_Ventas dv3 
                INNER JOIN Productos p3 ON dv3.id_producto = p3.id_producto 
                WHERE p3.nombre LIKE :busq5
            )
        )";
        $busquedaLimpia = ltrim($busqueda, '#');
        $params[':busq1'] = "%$busqueda%";
        $params[':busq2'] = "%$busqueda%";
        $params[':busq3'] = "%$busqueda%";
        $params[':busq4'] = ctype_digit($busquedaLimpia) ? (int) $busquedaLimpia : 0;
        $params[':busq5'] = "%$busqueda%";
    }

    $sql .= " ORDER BY v.fecha_hora DESC, v.id_ventas DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $metricas = calcularMetricasReporte($ventas);

} catch (\Throwable $e) {
    error_log("[MarketPlus - Módulo Reportes] Error al cargar panel de reportes: " . $e->getMessage());
    $errorMsg = "No fue posible conectar con el origen de datos. Por favor revise el servicio.";
    $metricas = [
        'total_monto' => 0,
        'cantidad_ventas' => 0,
        'ticket_promedio' => 0,
        'por_medio_pago' => []
    ];
}

// Parámetros para enlaces de exportación
$queryStringExport = http_build_query([
    'fecha_inicio'   => $fechaInicio,
    'fecha_fin'      => $fechaFin,
    'id_funcionario' => $idFuncionario,
    'id_medio_pago'  => $idMedioPago,
    'id_categoria'   => $idCategoria,
    'busqueda'       => $busqueda,
]);

$esSupervisor = strtolower((string) ($usuario['rol'] ?? '')) === 'supervisor';
$urlDashboard = $esSupervisor ? '../Supervisor/index.php' : '../administrador/index.php';
$etiquetaPanel = $esSupervisor ? 'Panel de supervisión' : 'Panel administrativo';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de Ventas - MarketPlus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/marketplus.css">
    <link rel="stylesheet" href="../css/marketplus-theme.css?v=1">
    <style>
        .stat-card-teal {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        }
        .stat-card-indigo {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        }
        .badge-folio {
            font-family: monospace;
            font-weight: 700;
            background: #eef4ff;
            color: #173fbf;
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 0.82rem;
        }
        .badge-medio {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            white-space: nowrap;
        }
        .table-reportes th {
            white-space: nowrap;
            font-size: 0.84rem;
        }
        .table-reportes td {
            font-size: 0.87rem;
        }
        .table-reportes tfoot td {
            background: #eaf0ff;
            color: #0f172a;
            font-weight: 700;
            border-top: 2px solid #1f5eff;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: #ffffff !important; }
            .container { max-width: 100% !important; width: 100% !important; margin: 0 !important; padding: 0 !important; }
            .panel-card, .dashboard-header, .stat-card { box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-light">

    <!-- Navbar oficial MarketPlus -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark no-print">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="<?= htmlspecialchars($urlDashboard) ?>">
                <i class="bi bi-shield-lock"></i> MarketPlus
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuReportes">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuReportes">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars($urlDashboard) ?>">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">
                            <i class="bi bi-shop"></i> Catálogo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../productos/registrar_producto.php">
                            <i class="bi bi-box-seam"></i> Nuevo producto
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="consultar_ventas.php">
                            <i class="bi bi-cart-check"></i> Ventas registradas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="reportes_ventas.php">
                            <i class="bi bi-graph-up"></i> Reportes de ventas
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center text-white me-3 gap-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person-circle fs-5 me-2"></i>
                        <div>
                            <small class="d-block text-white-50"><?= htmlspecialchars($usuario['rol']) ?></small>
                            <span><?= htmlspecialchars($usuario['nombre']) ?></span>
                        </div>
                    </div>
                    <a class="btn btn-outline-light btn-sm btn-logout" href="../includes/logout.php" onclick="return confirm('¿Seguro que deseas cerrar sesión?');">
                        <i class="bi bi-box-arrow-right"></i> Cerrar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-4">

        <!-- Encabezado estilo Dashboard de MarketPlus -->
        <div class="dashboard-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <p class="text-uppercase text-primary fw-semibold mb-1 small"><?= htmlspecialchars($etiquetaPanel) ?></p>
                <h2 class="mb-0">Reportes de Ventas</h2>
                <small class="text-muted">Emisión: <?= date('d/m/Y H:i:s') ?> &bull; Responsable: <?= htmlspecialchars($usuario['nombre']) ?></small>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap no-print">
                <a href="<?= htmlspecialchars($urlDashboard) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Volver al Dashboard
                </a>
                <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill">Rol: <?= htmlspecialchars($usuario['rol']) ?></span>
            </div>
        </div>

        <?php if ($errorMsg): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Aviso del Sistema:</strong> <?= htmlspecialchars($errorMsg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>

        <!-- Tarjetas KPI estilo MarketPlus -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-4">
                <div class="card stat-card text-white h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 text-uppercase small mb-2">Total Recaudado</h6>
                            <h3 class="mb-1 fw-bold">$<?= number_format($metricas['total_monto'], 0, ',', '.') ?></h3>
                            <small class="text-white-50">Ingresos brutos del periodo</small>
                        </div>
                        <i class="bi bi-cash-stack fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card stat-card stat-card-teal text-white h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 text-uppercase small mb-2">Transacciones</h6>
                            <h3 class="mb-1 fw-bold"><?= number_format($metricas['cantidad_ventas'], 0, ',', '.') ?></h3>
                            <small class="text-white-50">Ventas registradas y validadas</small>
                        </div>
                        <i class="bi bi-receipt fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card stat-card stat-card-indigo text-white h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 text-uppercase small mb-2">Ticket Promedio</h6>
                            <h3 class="mb-1 fw-bold">$<?= number_format($metricas['ticket_promedio'], 0, ',', '.') ?></h3>
                            <small class="text-white-50">Promedio por operación</small>
                        </div>
                        <i class="bi bi-graph-up-arrow fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros y Exportación -->
        <div class="card panel-card mb-4 no-print">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-funnel text-primary me-2"></i>Filtros de Búsqueda</span>
                <small class="text-muted fw-normal">Combina filtros para generar un reporte específico</small>
            </div>
            <div class="card-body p-4">
                <form method="GET" action="reportes_ventas.php">
                    <div class="row g-3">
                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label small fw-semibold text-muted" for="fecha_inicio">
                                <i class="bi bi-calendar-event me-1"></i>Fecha Desde
                            </label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?= htmlspecialchars($fechaInicio) ?>" class="form-control">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label small fw-semibold text-muted" for="fecha_fin">
                                <i class="bi bi-calendar-check me-1"></i>Fecha Hasta
                            </label>
                            <input type="date" id="fecha_fin" name="fecha_fin" value="<?= htmlspecialchars($fechaFin) ?>" class="form-control">
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label small fw-semibold text-muted" for="id_funcionario">
                                <i class="bi bi-person-badge me-1"></i>Vendedor
                            </label>
                            <select id="id_funcionario" name="id_funcionario" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach ($funcionariosList as $f): ?>
                                    <option value="<?= $f['id_funcionario'] ?>" <?= ($idFuncionario == $f['id_funcionario']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($f['nombre_completo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label small fw-semibold text-muted" for="id_medio_pago">
                                <i class="bi bi-credit-card me-1"></i>Medio de Pago
                            </label>
                            <select id="id_medio_pago" name="id_medio_pago" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach ($mediosPagoList as $mp): ?>
                                    <option value="<?= $mp['id_medio_pago'] ?>" <?= ($idMedioPago == $mp['id_medio_pago']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($mp['nombre_medio']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label small fw-semibold text-muted" for="id_categoria">
                                <i class="bi bi-tags me-1"></i>Categoría
                            </label>
                            <select id="id_categoria" name="id_categoria" class="form-select">
                                <option value="">Todas</option>
                                <?php foreach ($categoriasList as $cat): ?>
                                    <option value="<?= $cat['id_categorias'] ?>" <?= ($idCategoria == $cat['id_categorias']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <label class="form-label small fw-semibold text-muted" for="busqueda">
                                <i class="bi bi-search me-1"></i>Búsqueda
                            </label>
                            <input type="text" id="busqueda" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Folio, Cliente o RUT" class="form-control">
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <!-- Acciones de exportación -->
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="exportar_ventas.php?formato=pdf&<?= $queryStringExport ?>"
                               class="btn btn-danger btn-sm px-3"
                               title="Descargar reporte en PDF">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Exportar a PDF
                            </a>
                            <a href="exportar_ventas.php?formato=excel&<?= $queryStringExport ?>"
                               class="btn btn-success btn-sm px-3"
                               title="Descargar reporte en Excel (.xls)">
                                <i class="bi bi-file-earmark-excel me-1"></i> Exportar a Excel
                            </a>
                            <a href="exportar_ventas.php?formato=html&<?= $queryStringExport ?>"
                               class="btn btn-outline-primary btn-sm px-3"
                               title="Descargar reporte en HTML">
                                <i class="bi bi-filetype-html me-1"></i> Exportar HTML
                            </a>
                            <button type="button" onclick="window.print();" class="btn btn-outline-secondary btn-sm px-3" title="Imprimir vista actual">
                                <i class="bi bi-printer me-1"></i> Imprimir
                            </button>
                        </div>

                        <!-- Acciones de filtrado -->
                        <div class="d-flex gap-2">
                            <a href="reportes_ventas.php" class="btn btn-outline-secondary btn-sm px-3">
                                <i class="bi bi-x-circle me-1"></i> Limpiar
                            </a>
                            <button type="submit" class="btn btn-primary btn-sm px-3">
                                <i class="bi bi-funnel me-1"></i> Aplicar Filtros
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Resultados -->
        <div class="card panel-card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-table text-primary me-2"></i>Detalle de Ventas Registradas</span>
                <span class="badge bg-primary rounded-pill"><?= count($ventas) ?> registros</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-reportes">
                        <thead>
                            <tr>
                                <th class="text-center">Folio</th>
                                <th>Fecha / Hora</th>
                                <th>Cliente y RUT</th>
                                <th>Atendido por</th>
                                <th>Medio de Pago</th>
                                <th>Detalle de Productos</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Total Venta</th>
                                <th class="text-center no-print">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $unidadesTotal = 0; ?>
                            <?php if (count($ventas) > 0): ?>
                                <?php foreach ($ventas as $v):
                                    $unidades = (int) ($v['total_unidades'] ?? 1);
                                    $unidadesTotal += $unidades;
                                ?>
                                    <tr>
                                        <td class="text-center">
                                            <span class="badge-folio">#<?= str_pad($v['id_ventas'], 5, '0', STR_PAD_LEFT) ?></span>
                                        </td>
                                        <td class="text-nowrap"><?= date('d/m/Y H:i', strtotime($v['fecha_hora'])) ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($v['cliente_nombre']) ?></strong><br>
                                            <small class="text-muted">RUT: <?= htmlspecialchars($v['cliente_rut']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($v['funcionario_nombre']) ?></td>
                                        <td>
                                            <span class="badge-medio"><?= htmlspecialchars($v['medio_pago']) ?></span>
                                        </td>
                                        <td class="small">
                                            <?= !empty($v['productos_resumen'])
                                                ? htmlspecialchars($v['productos_resumen'])
                                                : '<span class="text-muted fst-italic">Venta consolidada</span>' ?>
                                        </td>
                                        <td class="text-center text-nowrap"><?= number_format($unidades, 0, ',', '.') ?> un.</td>
                                        <td class="text-end fw-bold text-primary text-nowrap">
                                            $<?= number_format($v['total_venta'], 0, ',', '.') ?>
                                        </td>
                                        <td class="text-center no-print">
                                            <a href="detalle_venta.php?id=<?= (int) $v['id_ventas'] ?>" class="btn btn-outline-primary btn-sm py-0 px-2">
                                                Ver
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        No se encontraron ventas que coincidan con los filtros aplicados.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-end">TOTALES GENERALES (<?= count($ventas) ?> registros):</td>
                                <td class="text-center text-nowrap"><?= number_format($unidadesTotal, 0, ',', '.') ?> un.</td>
                                <td class="text-end text-nowrap">$<?= number_format($metricas['total_monto'], 0, ',', '.') ?></td>
                                <td class="no-print"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-muted small d-flex justify-content-between flex-wrap gap-2 py-3 px-4">
                <span><strong>MarketPlus</strong> &bull; Módulo de Reportes de Ventas</span>
                <span>Generado el <?= date('d/m/Y H:i:s') ?> por <?= htmlspecialchars($usuario['nombre']) ?> (<?= htmlspecialchars($usuario['rol']) ?>)</span>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
