<?php

require_once __DIR__ . '/../funciones/conexion.php';

$pdo = conectar();
$vendedores = $pdo->query("
    SELECT TRIM(CONCAT(TRIM(nombre), ' ', TRIM(COALESCE(apellido, '')))) AS nombre_completo
    FROM funcionarios
    WHERE nombre IS NOT NULL AND TRIM(nombre) <> ''
    GROUP BY TRIM(CONCAT(TRIM(nombre), ' ', TRIM(COALESCE(apellido, ''))))
    ORDER BY nombre_completo ASC
")->fetchAll(PDO::FETCH_COLUMN);

$clientes = $pdo->query("
    SELECT TRIM(CONCAT(TRIM(nombre), ' ', TRIM(COALESCE(apellidos, '')))) AS nombre_completo
    FROM Clientes
    WHERE nombre IS NOT NULL AND TRIM(nombre) <> ''
    GROUP BY TRIM(CONCAT(TRIM(nombre), ' ', TRIM(COALESCE(apellidos, ''))))
    ORDER BY nombre_completo ASC
")->fetchAll(PDO::FETCH_COLUMN);

$productos = $pdo->query("
    SELECT id_producto, nombre
    FROM Productos
    ORDER BY nombre ASC
")->fetchAll(PDO::FETCH_ASSOC);

$f_vendedor = $_GET['vendedor'] ?? '';
$f_cliente  = $_GET['cliente'] ?? '';
$f_producto = $_GET['producto'] ?? '';
$f_desde    = $_GET['fecha_desde'] ?? '';
$f_hasta    = $_GET['fecha_hasta'] ?? '';

$sql = "SELECT
            v.id_ventas,
    MAX(v.fecha_hora) AS fecha_hora,
    MAX(COALESCE(
                NULLIF(TRIM(CONCAT(TRIM(f.nombre), ' ', TRIM(COALESCE(f.apellido, '')))), ''),
                'Sin vendedor'
    )) AS vendedor,
    MAX(COALESCE(
                NULLIF(TRIM(CONCAT(TRIM(c.nombre), ' ', TRIM(COALESCE(c.apellidos, '')))), ''),
                'Sin cliente'
    )) AS cliente,
            GROUP_CONCAT(CONCAT(p.nombre, ' (x', dv.cantidad, ')') SEPARATOR '<br>') AS producto,
            SUM(dv.cantidad) AS cantidad,
            AVG(dv.precio_unitario) AS precio_unitario,
            SUM(dv.subtotal) AS subtotal
        FROM Ventas AS v
        LEFT JOIN funcionarios AS f ON f.id_funcionario = v.id_funcionario
        LEFT JOIN Clientes AS c ON c.id_cliente = v.id_cliente
        INNER JOIN Detalle_Ventas AS dv ON dv.id_ventas = v.id_ventas
        INNER JOIN Productos AS p ON p.id_producto = dv.id_producto
        WHERE 1 = 1";

$params = [];

if ($f_vendedor !== '') {
    $sql .= " AND TRIM(CONCAT(TRIM(f.nombre), ' ', TRIM(COALESCE(f.apellido, '')))) = :vendedor";
    $params[':vendedor'] = $f_vendedor;
}

if ($f_cliente !== '') {
    $sql .= " AND TRIM(CONCAT(TRIM(c.nombre), ' ', TRIM(COALESCE(c.apellidos, '')))) = :cliente";
    $params[':cliente'] = $f_cliente;
}

if ($f_producto !== '') {
    $sql .= " AND p.id_producto = :producto";
    $params[':producto'] = $f_producto;
}

if ($f_desde !== '') {
    $sql .= " AND DATE(v.fecha_hora) >= :desde";
    $params[':desde'] = $f_desde;
}

if ($f_hasta !== '') {
    $sql .= " AND DATE(v.fecha_hora) <= :hasta";
    $params[':hasta'] = $f_hasta;
}

$sql .= " GROUP BY v.id_ventas";

$sqlResumen = "SELECT
                   COUNT(DISTINCT id_ventas) AS total_registros,
                   COALESCE(SUM(subtotal), 0) AS suma_monto_total
               FROM ($sql) AS detalle_filtrado";

$stmtResumen = $pdo->prepare($sqlResumen);
$stmtResumen->execute($params);
$resumen = $stmtResumen->fetch(PDO::FETCH_ASSOC);

$total_registros = (int) $resumen['total_registros'];
$suma_monto_total = (float) $resumen['suma_monto_total'];

$sql .= " ORDER BY v.id_ventas ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisión General de Ventas - MarketPlus</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/supervisor.css?v=2.1">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body>
    <main class="container-fluid dashboard-container" id="area-reporte">
        <header class="dashboard-header">
            <div class="dashboard-heading">
                <span class="eyebrow">MARKETPLUS · ANALÍTICA</span>
                <h1>Supervisión General de Ventas</h1>
                <p>Consulta, filtra y revisa el rendimiento de tus ventas.</p>
            </div>
            <div class="header-actions no-print">
                <a href="../Supervisor/index.php" class="btn btn-export-excel rounded-pill">
                    <i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>Volver al dashboard
                </a>
                <a
                    href="exportar_ventas.php?<?= htmlspecialchars(http_build_query($_GET), ENT_QUOTES, 'UTF-8') ?>"
                    class="btn btn-export-excel rounded-pill">
                    <i class="fa-solid fa-file-excel me-2" aria-hidden="true"></i>Exportar a Excel
                </a>
                <button type="button" onclick="exportarPDF()" class="btn btn-primary-executive rounded-pill">
                    <i class="fa-solid fa-file-pdf me-2" aria-hidden="true"></i>Exportar a PDF
                </button>
                <div class="report-date">
                    <i class="fa-regular fa-calendar me-2" aria-hidden="true"></i><?= date('d/m/Y H:i') ?>
                </div>
            </div>
        </header>

        <section class="kpi-grid mb-4" aria-label="Resumen de ventas">
            <article class="card-moderna kpi-card kpi-total">
                <div class="kpi-copy">
                    <span id="etiqueta-monto-total" class="kpi-label">Monto Total</span>
                    <h2>$<?= number_format($suma_monto_total, 0, ',', '.') ?></h2>
                    <span class="kpi-caption">Ingresos registrados</span>
                </div>
                <div class="kpi-icon kpi-icon-light">
                    <i class="fa-solid fa-sack-dollar" aria-hidden="true"></i>
                </div>
            </article>

            <article class="card-moderna kpi-card kpi-transacciones">
                <div class="kpi-copy">
                    <span class="kpi-label">Transacciones</span>
                    <h2><?= $total_registros ?></h2>
                    <span class="kpi-caption">Ventas registradas</span>
                </div>
                <div class="kpi-icon">
                    <i class="fa-solid fa-receipt" aria-hidden="true"></i>
                </div>
            </article>
        </section>

        <section class="card-moderna filters-card mb-4 no-print" aria-label="Filtros de ventas">
            <div class="section-heading">
                <div class="section-icon">
                    <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                </div>
                <div>
                    <h2>Filtros</h2>
                    <p>Afina los resultados por vendedor, cliente, producto o fecha.</p>
                </div>
            </div>
            <div class="filters-content">
                <form id="form-filtros" method="GET" action="supervisar_ventas.php">
                    <div class="row g-3 filter-fields">
                        <div class="col-12 col-md-6 col-xl">
                            <label for="filtro-vendedor" class="form-label">Vendedor</label>
                            <select id="filtro-vendedor" name="vendedor" class="form-select">
                                <option value="">-- Todos --</option>
                                <?php foreach ($vendedores as $vendedor): ?>
                                    <option
                                        value="<?= htmlspecialchars($vendedor, ENT_QUOTES, 'UTF-8') ?>"
                                        <?= $f_vendedor === $vendedor ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($vendedor, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl">
                            <label for="filtro-cliente" class="form-label">Cliente</label>
                            <select id="filtro-cliente" name="cliente" class="form-select">
                                <option value="">-- Todos --</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option
                                        value="<?= htmlspecialchars($cliente, ENT_QUOTES, 'UTF-8') ?>"
                                        <?= $f_cliente === $cliente ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cliente, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl">
                            <label for="filtro-producto" class="form-label">Producto</label>
                            <select id="filtro-producto" name="producto" class="form-select">
                                <option value="">-- Todos --</option>
                                <?php foreach ($productos as $producto): ?>
                                    <option
                                        value="<?= htmlspecialchars((string) $producto['id_producto'], ENT_QUOTES, 'UTF-8') ?>"
                                        <?= (string) $f_producto === (string) $producto['id_producto'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl">
                            <label for="filtro-desde" class="form-label">Desde</label>
                            <input
                                id="filtro-desde"
                                type="date"
                                name="fecha_desde"
                                class="form-control"
                                value="<?= htmlspecialchars($f_desde, ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="col-12 col-md-6 col-xl">
                            <label for="filtro-hasta" class="form-label">Hasta</label>
                            <input
                                id="filtro-hasta"
                                type="date"
                                name="fecha_hasta"
                                class="form-control"
                                value="<?= htmlspecialchars($f_hasta, ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary-executive rounded-pill">
                            <i class="fa-solid fa-filter me-2" aria-hidden="true"></i>Aplicar filtros
                        </button>
                        <a href="supervisar_ventas.php" class="btn btn-light rounded-pill">
                            <i class="fa-solid fa-rotate-left me-2" aria-hidden="true"></i>Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </section>

        <section class="card-moderna table-card" aria-label="Tabla de ventas">
            <div class="section-heading table-heading">
                <div class="section-icon">
                    <i class="fa-solid fa-table-list" aria-hidden="true"></i>
                </div>
                <div>
                    <h2>Detalle de ventas</h2>
                    <p>Consulta las transacciones y sus productos.</p>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tabla-ventas" class="table table-hover align-middle mb-0">
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
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td class="text-center" data-order="<?= (int) $row['id_ventas'] ?>">
                                    <span class="badge-id">
                                        #<?= htmlspecialchars((string) $row['id_ventas'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($row['fecha_hora'])) ?></td>
                                <td><?= htmlspecialchars($row['vendedor'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['cliente'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= str_replace('&lt;br&gt;', '<br>', htmlspecialchars($row['producto'], ENT_QUOTES, 'UTF-8')) ?></td>
                                <td class="text-center fw-semibold">
                                    <?= htmlspecialchars((string) $row['cantidad'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="text-end">
                                    $<?= number_format((float) $row['precio_unitario'], 0, ',', '.') ?>
                                </td>
                                <td class="text-end fw-bold text-dark">
                                    $<?= number_format((float) $row['subtotal'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="7" class="text-end">Total General</td>
                            <td class="text-end">
                                $<?= number_format($suma_monto_total, 0, ',', '.') ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    </main>

    <script>
        function exportarPDF() {
            const elementosNoImprimir = document.querySelectorAll('.no-print');
            elementosNoImprimir.forEach(elemento => elemento.style.display = 'none');

            const areaReporte = document.getElementById('area-reporte');
            const fecha = new Date().toISOString().slice(0, 10);
            const opciones = {
                margin: [10, 10, 10, 10],
                filename: `Reporte_Ventas_MarketPlus_${fecha}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, logging: false, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
            };

            html2pdf().set(opciones).from(areaReporte).save().then(() => {
                elementosNoImprimir.forEach(elemento => elemento.style.display = '');
            }).catch(error => {
                console.error('Error al generar PDF:', error);
                elementosNoImprimir.forEach(elemento => elemento.style.display = '');
                window.print();
            });
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="../js/supervisor.js"></script>
</body>
</html>
