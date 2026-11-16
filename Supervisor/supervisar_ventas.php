<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../funciones/navegacion.php';

requerirRol(['administrador', 'supervisor']);
$pdo = conectar();

// Cargar listas para los filtros
$vendedores =$pdo->query("SELECT id_funcionario, CONCAT(nombre, ' ', apellido) AS nombre_completo FROM funcionarios ORDER BY nombre ASC")->fetchAll();
$clientes   =$pdo->query("SELECT id_cliente, nombre FROM Clientes ORDER BY nombre ASC")->fetchAll();
$productos  =$pdo->query("SELECT id_producto, nombre FROM Productos ORDER BY nombre ASC")->fetchAll();

// Capturar filtros
$f_vendedor =$_GET['vendedor'] ?? '';
$f_cliente  =$_GET['cliente'] ?? '';
$f_producto =$_GET['producto'] ?? '';
$f_desde    =$_GET['fecha_desde'] ?? '';
$f_hasta    =$_GET['fecha_hasta'] ?? '';

$sql = "SELECT 
            v.id_ventas,
            v.fecha_hora,
            CONCAT(f.nombre, ' ', f.apellido) AS vendedor,
            c.nombre AS cliente,
            p.nombre AS producto,
            dv.cantidad,
            dv.precio_unitario,
            dv.subtotal
        FROM Ventas v
        INNER JOIN funcionarios f ON v.id_funcionario = f.id_funcionario
        INNER JOIN Clientes c ON v.id_cliente = c.id_cliente
        INNER JOIN Detalle_Ventas dv ON v.id_ventas = dv.id_ventas
        INNER JOIN Productos p ON dv.id_producto = p.id_producto
        WHERE 1=1";

$params = [];

if (!empty($f_vendedor)) {$sql .= " AND f.id_funcionario = :vendedor";
    $params[':vendedor'] =$f_vendedor;
}
if (!empty($f_cliente)) {$sql .= " AND c.id_cliente = :cliente";
    $params[':cliente'] =$f_cliente;
}
if (!empty($f_producto)) {$sql .= " AND p.id_producto = :producto";
    $params[':producto'] =$f_producto;
}
if (!empty($f_desde)) {$sql .= " AND DATE(v.fecha_hora) >= :desde";
    $params[':desde'] =$f_desde;
}
if (!empty($f_hasta)) {$sql .= " AND DATE(v.fecha_hora) <= :hasta";
    $params[':hasta'] =$f_hasta;
}

$sql .= " ORDER BY v.fecha_hora DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ventas =$stmt->fetchAll();

$total_registros = count($ventas);
$suma_monto_total = array_sum(array_column($ventas, 'subtotal'));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisión General de Ventas - MarketPlus</title>
    
  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/supervisor.css?v=1.0">
    <link rel="stylesheet" href="../css/navegacion.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="../css/marketplus-theme.css?v=1">
</head>
<body class="py-4">
    <?php renderNavegacion(null, BASE_URL . '/index.php'); ?>

    <div class="container-fluid px-4" id="area-reporte">
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark m-0"><i class="fa-solid fa-chart-line text-primary me-2"></i>Supervisión General de Ventas</h3>
                <p class="text-muted small mb-0">Consulta, filtra y exporta los registros de transacciones en tiempo real.</p>
            </div>
            <div class="text-end text-muted small">
                <span><i class="fa-regular fa-calendar me-1"></i><?= date('d/m/Y H:i') ?></span>
            </div>
        </div>

        <!-- Tarjetas KPI -->
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="card card-kpi bg-white p-3">
                    <div class="d-flex align-items-center">
                        <div class="kpi-icon bg-primary bg-opacity-10 text-primary me-3">
                            <i class="fa-solid fa-sack-dollar"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Monto Total Filtrado</span>
                            <h4 class="fw-bold text-dark mb-0">$<?= number_format($suma_monto_total, 0, ',', '.') ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card card-kpi bg-white p-3">
                    <div class="d-flex align-items-center">
                        <div class="kpi-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Transacciones</span>
                            <h4 class="fw-bold text-dark mb-0"><?= $total_registros ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros (No se incluyen en el PDF) -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 no-print">
            <div class="card-body p-4">
                <form id="form-filtros" method="GET" action="supervisar_ventas.php">
                    <div class="row g-3">
                        <div class="col-md-4 col-lg-2">
                            <label class="form-label small fw-bold text-muted">VENDEDOR</label>
                            <select name="vendedor" class="form-select form-select-sm">
                                <option value="">-- Todos --</option>
                                <?php foreach ($vendedores as$v): ?>
                                    <option value="<?= $v['id_funcionario'] ?>" <?= $f_vendedor ==$v['id_funcionario'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($v['nombre_completo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4 col-lg-2">
                            <label class="form-label small fw-bold text-muted">CLIENTE</label>
                            <select name="cliente" class="form-select form-select-sm">
                                <option value="">-- Todos --</option>
                                <?php foreach ($clientes as$c): ?>
                                    <option value="<?= $c['id_cliente'] ?>" <?= $f_cliente ==$c['id_cliente'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4 col-lg-2">
                            <label class="form-label small fw-bold text-muted">PRODUCTO</label>
                            <select name="producto" class="form-select form-select-sm">
                                <option value="">-- Todos --</option>
                                <?php foreach ($productos as$p): ?>
                                    <option value="<?= $p['id_producto'] ?>" <?= $f_producto ==$p['id_producto'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <label class="form-label small fw-bold text-muted">DESDE</label>
                            <input type="date" name="fecha_desde" class="form-select form-select-sm" value="<?= htmlspecialchars($f_desde) ?>">
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <label class="form-label small fw-bold text-muted">HASTA</label>
                            <input type="date" name="fecha_hasta" class="form-select form-select-sm" value="<?= htmlspecialchars($f_hasta) ?>">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4 flex-wrap">
                        <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold">
                            <i class="fa-solid fa-filter me-1"></i> Aplicar Filtros
                        </button>
                        <a href="supervisar_ventas.php" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">
                            <i class="fa-solid fa-rotate-left me-1"></i> Limpiar
                        </a>
                        <a href="exportar_ventas.php?<?= http_build_query($_GET) ?>" class="btn btn-success btn-sm px-3 fw-semibold">
                            <i class="fa-solid fa-file-excel me-1"></i> Exportar a Excel
                        </a>
                        <button type="button" onclick="exportarPDF()" class="btn btn-danger btn-sm px-3 fw-semibold">
                            <i class="fa-solid fa-file-pdf me-1"></i> Exportar a PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Datos -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center">ID Venta</th>
                        <th>Fecha y Hora</th>
                        <th>Vendedor</th>
                        <th>Cliente</th>
                        <th>Producto</th>
                        <th class="text-center">Cant.</th>
                        <th class="text-end">P. Unitario</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ventas)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fa-solid fa-folder-open fa-2x mb-2 d-block"></i>
                                No se encontraron registros de ventas con los filtros aplicados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ventas as$row): ?>
                            <tr>
                                <td class="text-center"><span class="badge-id">#<?= $row['id_ventas'] ?></span></td>
                                <td><i class="fa-regular fa-clock text-muted me-1"></i><?= date('d/m/Y H:i', strtotime($row['fecha_hora'])) ?></td>
                                <td><?= htmlspecialchars($row['vendedor']) ?></td>
                                <td><?= htmlspecialchars($row['cliente']) ?></td>
                                <td><?= htmlspecialchars($row['producto']) ?></td>
                                <td class="text-center fw-semibold"><?= $row['cantidad'] ?></td>
                                <td class="text-end">$<?= number_format($row['precio_unitario'], 0, ',', '.') ?></td>
                                <td class="text-end fw-bold text-dark">$<?= number_format($row['subtotal'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <script>
        function exportarPDF() {
            const elementosNoImprimir = document.querySelectorAll('.no-print');
            elementosNoImprimir.forEach(el => el.style.display = 'none');

            const elemento = document.getElementById('area-reporte');
            const fecha = new Date().toISOString().slice(0, 10);

            const opciones = {
                margin:       [10, 10, 10, 10],
                filename:     `Reporte_Ventas_MarketPlus_${fecha}.pdf`,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, logging: false, useCORS: true },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
            };

            html2pdf().set(opciones).from(elemento).save().then(() => {
                elementosNoImprimir.forEach(el => el.style.display = '');
            }).catch(err => {
                console.error('Error al generar PDF:', err);
                elementosNoImprimir.forEach(el => el.style.display = '');
                window.print();
            });
        }
    </script>
</body>
</html>