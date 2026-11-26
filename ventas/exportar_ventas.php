<?php
/**
 * MarketPlus - Módulo de Exportación Oficial de Reportes de Ventas
 * Soporta exportación en PDF, Excel (.xls) y HTML descargable autónomo.
 * Aplica estilos CSS institucionales centralizados y pie de página corporativo.
 */

ob_start();
ini_set('display_errors', '0');
date_default_timezone_set('America/Santiago');

// Definicion segura de BASE_URL para compatibilidad en Docker o servidores web
if (!defined('BASE_URL')) {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $basePath = ($scriptDir === '/' || $scriptDir === '\\') ? '' : rtrim(str_replace('\\', '/', $scriptDir), '/');
    define('BASE_URL', preg_replace('#/(ventas|productos|administrador|supervisor|auth|public|includes|funciones).*$#i', '', $basePath));
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../funciones/plantilla_reportes.php';

requerirRol(['administrador', 'supervisor']);

$usuario = obtenerUsuarioResponsable();

// 1. Captura y validación estricta de parámetros y filtros
$validarFechaYmd = static function (string $valor): string {
    if ($valor === '') {
        return '';
    }
    $dt = DateTime::createFromFormat('!Y-m-d', $valor);
    return ($dt !== false && $dt->format('Y-m-d') === $valor) ? $valor : '';
};

$formato       = strtolower(trim((string) ($_GET['formato'] ?? 'html')));
$fechaInicio   = $validarFechaYmd(trim((string) ($_GET['fecha_inicio'] ?? '')));
$fechaFin      = $validarFechaYmd(trim((string) ($_GET['fecha_fin'] ?? '')));
$idFuncionario = trim((string) ($_GET['id_funcionario'] ?? ''));
$idMedioPago   = trim((string) ($_GET['id_medio_pago'] ?? ''));
$idCategoria   = trim((string) ($_GET['id_categoria'] ?? ''));
$busqueda      = trim((string) ($_GET['busqueda'] ?? ''));
$modoDescarga  = isset($_GET['descargar']) ? (bool) $_GET['descargar'] : true;

try {
    $pdo = obtenerConexionBd();
    if (!$pdo) {
        throw new RuntimeException("No fue posible establecer conexión con la base de datos.");
    }

    // 2. Construcción de consulta dinámica con filtros seguros
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

        $stmtF = $pdo->prepare("SELECT CONCAT(nombre, ' ', apellido) FROM funcionarios WHERE id_funcionario = ?");
        $stmtF->execute([(int) $idFuncionario]);
        $nombreF = $stmtF->fetchColumn();
        if ($nombreF) {
            $filtrosTexto['Vendedor'] = $nombreF;
        }
    }

    if ($idMedioPago !== '' && ctype_digit((string) $idMedioPago)) {
        $sql .= " AND v.id_medio_pago = :id_medio_pago";
        $params[':id_medio_pago'] = (int) $idMedioPago;

        $stmtM = $pdo->prepare("SELECT nombre_medio FROM Medio_pago WHERE id_medio_pago = ?");
        $stmtM->execute([(int) $idMedioPago]);
        $nombreM = $stmtM->fetchColumn();
        if ($nombreM) {
            $filtrosTexto['Medio de Pago'] = $nombreM;
        }
    }

    if ($idCategoria !== '' && ctype_digit((string) $idCategoria)) {
        $sql .= " AND v.id_ventas IN (
                    SELECT dv2.id_ventas 
                    FROM Detalle_Ventas dv2 
                    INNER JOIN Productos p2 ON dv2.id_producto = p2.id_producto 
                    WHERE p2.id_categorias = :id_categoria
                 )";
        $params[':id_categoria'] = (int) $idCategoria;

        $stmtC = $pdo->prepare("SELECT nombre FROM Categorias WHERE id_categorias = ?");
        $stmtC->execute([(int) $idCategoria]);
        $nombreC = $stmtC->fetchColumn();
        if ($nombreC) {
            $filtrosTexto['Categoría'] = $nombreC;
        }
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
        $filtrosTexto['Búsqueda'] = $busqueda;
    }

    $sql .= " ORDER BY v.fecha_hora DESC, v.id_ventas DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular estadísticas para el encabezado y métricas
    $metricas = calcularMetricasReporte($ventas);

    // Registro de auditoría
    try {
        $stmtLog = $pdo->prepare("INSERT INTO logs_seguridad (usuario, accion, detalle, ip_origen) VALUES (?, ?, ?, ?)");
        $stmtLog->execute([
            $usuario['nombre'],
            'Exportación de Reporte de Ventas',
            "Formato: " . strtoupper($formato) . ", Registros: " . count($ventas),
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);
    } catch (\Throwable $e) {
        // No bloquear la exportación
    }

} catch (\Throwable $e) {
    error_log("[MarketPlus - Módulo Reportes] Error al consultar datos para exportar: " . $e->getMessage());
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Error en Reporte</title><link rel="stylesheet" href="../css/marketplus.css"></head><body style="padding:40px;text-align:center;font-family:sans-serif;">';
    echo '<h2 style="color:#dc2626;">Aviso del Sistema</h2>';
    echo '<p style="color:#475569;font-size:14px;">No fue posible generar el reporte de ventas solicitado en este momento.<br>El incidente ha sido registrado para su revisión. Por favor, intente nuevamente más tarde.</p>';
    echo '<a href="reportes_ventas.php" class="btn btn-primary" style="display:inline-block;margin-top:15px;padding:8px 16px;background:#1f5eff;color:#fff;text-decoration:none;border-radius:4px;">Volver al Módulo de Reportes</a>';
    echo '</body></html>';
    exit();
}

$nombreArchivoBase = 'Reporte_Ventas_MarketPlus_' . date('Ymd_His');

// =========================================================================
// RENDERIZADO SEGÚN FORMATO
// =========================================================================

// Armar filas de la tabla de ventas
$filasHtml = '';
$totalUnidadesGlobal = 0;

if (count($ventas) > 0) {
    foreach ($ventas as $v) {
        $medioClass = strtolower(preg_replace('/[^a-z0-9]+/i', '', $v['medio_pago'] ?? ''));
        $unidades = (int) ($v['total_unidades'] ?? 1);
        $totalUnidadesGlobal += $unidades;

        $productosLista = !empty($v['productos_resumen']) 
            ? htmlspecialchars($v['productos_resumen']) 
            : '<span style="color:#94a3b8;font-style:italic;">Sin ítems detallados</span>';

        $filasHtml .= '<tr>';
        $filasHtml .= '<td class="text-center col-folio"><span class="badge-folio">#' . str_pad($v['id_ventas'], 5, '0', STR_PAD_LEFT) . '</span></td>';
        $filasHtml .= '<td class="col-fecha">' . date('d/m/Y H:i', strtotime($v['fecha_hora'])) . '</td>';
        $filasHtml .= '<td class="col-cliente"><strong>' . htmlspecialchars($v['cliente_nombre']) . '</strong><br><small style="color:#64748b;">RUT: ' . htmlspecialchars($v['cliente_rut']) . '</small></td>';
        $filasHtml .= '<td class="col-vendedor">' . htmlspecialchars($v['funcionario_nombre']) . '</td>';
        $filasHtml .= '<td class="col-pago"><span class="badge-payment ' . htmlspecialchars($medioClass) . '">' . htmlspecialchars($v['medio_pago']) . '</span></td>';
        $filasHtml .= '<td class="col-detalle" style="font-size:11px;">' . $productosLista . '</td>';
        $filasHtml .= '<td class="text-center col-cantidad">' . number_format($unidades, 0, ',', '.') . ' un.</td>';
        $filasHtml .= '<td class="text-right col-total" style="font-weight:700;color:#173fbf;">$' . number_format($v['total_venta'], 0, ',', '.') . '</td>';
        $filasHtml .= '</tr>';
    }
} else {
    $filasHtml = '<tr><td colspan="8" class="text-center" style="padding:25px;color:#64748b;">No se encontraron registros de ventas para los filtros seleccionados.</td></tr>';
}

$tablaHtml = '
<table class="report-table">
    <thead>
        <tr>
            <th class="text-center col-folio">Folio</th>
            <th class="col-fecha">Fecha / Hora</th>
            <th class="col-cliente">Cliente y RUT</th>
            <th class="col-vendedor">Atendido por</th>
            <th class="col-pago">Medio de Pago</th>
            <th class="col-detalle">Detalle de Productos</th>
            <th class="text-center col-cantidad">Cantidad</th>
            <th class="text-right col-total">Total Venta</th>
        </tr>
    </thead>
    <tbody>
        ' . $filasHtml . '
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" class="text-right">TOTALES CONSOLIDADOS (' . count($ventas) . ' transacciones):</td>
            <td class="text-center col-cantidad">' . number_format($totalUnidadesGlobal, 0, ',', '.') . ' un.</td>
            <td class="text-right col-total">$' . number_format($metricas['total_monto'], 0, ',', '.') . '</td>
        </tr>
    </tfoot>
</table>';

// -------------------------------------------------------------------------
// FORMATO EXCEL (.xls basado en HTML estructurado con estilos MSO corporativos)
// -------------------------------------------------------------------------
if ($formato === 'excel' || $formato === 'xls') {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    $nombreArchivo = $nombreArchivoBase . '.xls';

    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Inyección de BOM UTF-8 para garantizar que Microsoft Excel en Windows respete tildes y caracteres especiales en español
    echo "\xEF\xBB\xBF";

    // Estilos optimizados específicamente para que Microsoft Excel aplique colores corporativos y bordes
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta charset="UTF-8">';
    echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Reporte de Ventas</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
    echo '<style>
        body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; color: #1e293b; }
        .titulo-corp { font-size: 16pt; font-weight: bold; color: #1f5eff; }
        .subtitulo-corp { font-size: 10pt; color: #475569; }
        .header-filtro { background-color: #f1f5f9; font-weight: bold; color: #0f172a; }
        th { background-color: #1f5eff; color: #ffffff; font-weight: bold; font-size: 11pt; border: 1px solid #173fbf; text-align: left; padding: 6px; }
        th.num { text-align: right; }
        th.center { text-align: center; }
        td { border: 0.5pt solid #cbd5e1; padding: 5px; font-size: 10pt; vertical-align: middle; }
        td.moneda { mso-number-format:"\$\#\,\#\#0"; text-align: right; font-weight: bold; }
        td.fecha { mso-number-format:"yyyy-mm-dd hh\:mm"; text-align: center; }
        td.folio { mso-number-format:"00000"; text-align: center; color: #1f5eff; font-weight: bold; }
        td.center { text-align: center; }
        tr.even { background-color: #f8fafc; }
        tr.total-row td { background-color: #eaf0ff; font-weight: bold; color: #173fbf; border-top: 2pt solid #1f5eff; border-bottom: 2pt solid #1f5eff; }
        .footer-celda { background-color: #f8fafc; border-top: 2pt solid #14b8a6; font-size: 9pt; color: #64748b; }
    </style>';
    echo '</head><body>';

    echo '<table border="0" cellpadding="4" cellspacing="0">';
    // Encabezado institucional en Excel
    echo '<tr><td colspan="9" class="titulo-corp">MarketPlus - REPORTE DE VENTAS</td></tr>';
    echo '<tr><td colspan="9" class="subtitulo-corp">RUT: 76.123.456-7 | MarketPlus | Cauquenes</td></tr>';
    echo '<tr><td colspan="9" class="subtitulo-corp"><strong>Fecha de Emisión:</strong> ' . date('d/m/Y H:i:s') . ' | <strong>Responsable:</strong> ' . htmlspecialchars($usuario['nombre']) . ' (' . htmlspecialchars($usuario['rol']) . ')</td></tr>';

    if (!empty($filtrosTexto)) {
        $filtrosStr = [];
        foreach ($filtrosTexto as $k => $v) {
            $filtrosStr[] = "$k: $v";
        }
        echo '<tr><td colspan="9" class="header-filtro"><strong>Filtros aplicados:</strong> ' . htmlspecialchars(implode(' | ', $filtrosStr)) . '</td></tr>';
    }
    echo '<tr><td colspan="9"></td></tr>'; // Fila vacía

    // Cabecera de la tabla con consistencia plena (incluye Detalle de Productos)
    echo '<tr>
            <th class="center">Folio</th>
            <th class="center">Fecha / Hora</th>
            <th>Cliente</th>
            <th>RUT Cliente</th>
            <th>Atendido por</th>
            <th>Medio de Pago</th>
            <th>Detalle de Productos</th>
            <th class="center">Unidades</th>
            <th class="num">Total Venta</th>
          </tr>';

    $i = 0;
    foreach ($ventas as $v) {
        $clase = ($i % 2 === 1) ? 'class="even"' : '';
        $unidades = (int) ($v['total_unidades'] ?? 1);
        $resumenProd = !empty($v['productos_resumen']) ? htmlspecialchars($v['productos_resumen']) : 'Venta consolidada';

        echo "<tr $clase>";
        echo '<td class="folio">' . (int)$v['id_ventas'] . '</td>';
        echo '<td class="fecha">' . htmlspecialchars($v['fecha_hora']) . '</td>';
        echo '<td>' . htmlspecialchars($v['cliente_nombre']) . '</td>';
        echo '<td>' . htmlspecialchars($v['cliente_rut']) . '</td>';
        echo '<td>' . htmlspecialchars($v['funcionario_nombre']) . '</td>';
        echo '<td>' . htmlspecialchars($v['medio_pago']) . '</td>';
        echo '<td>' . $resumenProd . '</td>';
        echo '<td class="center">' . $unidades . '</td>';
        echo '<td class="moneda">' . (float)$v['total_venta'] . '</td>';
        echo '</tr>';
        $i++;
    }

    // Fila de Totales
    echo '<tr class="total-row">
            <td colspan="7" style="text-align:right;">TOTALES CONSOLIDADOS (' . count($ventas) . ' ventas):</td>
            <td class="center">' . $totalUnidadesGlobal . '</td>
            <td class="moneda">' . (float)$metricas['total_monto'] . '</td>
          </tr>';

    echo '<tr><td colspan="9"></td></tr>'; // Fila vacía

    // Pie de Página en Excel
    echo '<tr><td colspan="9" class="footer-celda"><strong>MarketPlus</strong> &bull; Sistema de Gestión de Ventas</td></tr>';
    echo '<tr><td colspan="9" class="footer-celda">Generado automáticamente el ' . date('d/m/Y H:i:s') . ' por ' . htmlspecialchars($usuario['nombre']) . ' (' . htmlspecialchars($usuario['rol']) . ')</td></tr>';
    echo '</table>';

    echo '</body></html>';
    exit();
}

// -------------------------------------------------------------------------
// FORMATO PDF (Generación nativa en PHP puro de archivo .pdf binario válido)
// -------------------------------------------------------------------------
if ($formato === 'pdf') {
    $pdfAWinAnsi = static function (string $texto): string {
        $limpio = preg_replace('/\s+/u', ' ', trim($texto)) ?? '';
        if (function_exists('iconv')) {
            $conv = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $limpio);
            if ($conv !== false) {
                $limpio = $conv;
            }
        } elseif (function_exists('mb_convert_encoding')) {
            $limpio = @mb_convert_encoding($limpio, 'ISO-8859-1', 'UTF-8');
        }
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $limpio);
    };

    $truncarTexto = static function (string $texto, int $maxChars): string {
        $texto = preg_replace('/\s+/u', ' ', trim($texto)) ?? '';
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            return mb_strlen($texto, 'UTF-8') > $maxChars
                ? mb_substr($texto, 0, $maxChars - 3, 'UTF-8') . '...'
                : $texto;
        }
        return strlen($texto) > $maxChars ? substr($texto, 0, $maxChars - 3) . '...' : $texto;
    };

    $pageW = 842.0; // A4 horizontal (landscape)
    $pageH = 595.0;
    $marginL = 32.0;
    $tableW = 778.0;
    $colWidths = [46.0, 82.0, 125.0, 95.0, 74.0, 240.0, 40.0, 76.0];
    $colHeaders = ['Folio', 'Fecha / Hora', 'Cliente', 'Vendedor', 'Medio Pago', 'Detalle de Productos', 'Cant.', 'Total'];
    $colAligns = ['C', 'L', 'L', 'L', 'L', 'L', 'C', 'R'];

    $drawRect = static function (string &$buf, float $x, float $yTop, float $w, float $h, array $rgb) use ($pageH): void {
        $yPdf = $pageH - $yTop - $h;
        $buf .= sprintf("%.3F %.3F %.3F rg %.2F %.2F %.2F %.2F re f\n", $rgb[0], $rgb[1], $rgb[2], $x, $yPdf, $w, $h);
    };

    $drawBorder = static function (string &$buf, float $x, float $yTop, float $w, float $h, array $rgb = [0.85, 0.89, 0.94], float $lw = 0.6) use ($pageH): void {
        $yPdf = $pageH - $yTop - $h;
        $buf .= sprintf("%.2F w %.3F %.3F %.3F RG %.2F %.2F %.2F %.2F re S\n", $lw, $rgb[0], $rgb[1], $rgb[2], $x, $yPdf, $w, $h);
    };

    $drawText = static function (string &$buf, float $x, float $yTop, string $text, float $size = 8.5, bool $bold = false, array $rgb = [0.06, 0.09, 0.16]) use ($pageH, $pdfAWinAnsi): void {
        $font = $bold ? 'F2' : 'F1';
        $yPdf = $pageH - $yTop - $size;
        $buf .= sprintf("BT /%s %.2F Tf %.3F %.3F %.3F rg 1 0 0 1 %.2F %.2F Tm (%s) Tj ET\n", $font, $size, $rgb[0], $rgb[1], $rgb[2], $x, $yPdf, $pdfAWinAnsi($text));
    };

    $drawCellText = static function (string &$buf, float $x, float $yTop, float $w, string $text, float $size, bool $bold, string $align, array $rgb = [0.06, 0.09, 0.16]) use ($drawText): void {
        $approxCharW = $size * ($bold ? 0.54 : 0.49);
        $textLen = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
        $textW = $textLen * $approxCharW;
        if ($align === 'R') {
            $tx = max($x + 4, $x + $w - $textW - 5);
        } elseif ($align === 'C') {
            $tx = max($x + 3, $x + ($w - $textW) / 2);
        } else {
            $tx = $x + 5;
        }
        $drawText($buf, $tx, $yTop, $text, $size, $bold, $rgb);
    };

    $pageStreams = [];
    $stream = '';
    $y = 0.0;
    $pageNum = 0;

    $startPage = static function () use (
        &$pageStreams, &$stream, &$y, &$pageNum,
        $marginL, $tableW, $colWidths, $colHeaders, $colAligns,
        $drawRect, $drawBorder, $drawText, $drawCellText,
        $usuario, $filtrosTexto, $metricas, $totalUnidadesGlobal, $truncarTexto
    ): void {
        if ($pageNum > 0) {
            $pageStreams[] = $stream;
        }
        $pageNum++;
        $stream = '';

        // Encabezado oficial MarketPlus (#1f5eff)
        $etiquetaPanel = (strtolower((string) ($usuario['rol'] ?? '')) === 'supervisor') ? 'Panel de Supervision' : 'Panel Administrativo';
        $drawRect($stream, $marginL, 24, $tableW, 44, [0.122, 0.369, 1.0]);
        $drawText($stream, $marginL + 14, 33, 'MarketPlus - Reporte Consolidado de Ventas', 15, true, [1.0, 1.0, 1.0]);
        $drawText($stream, $marginL + 14, 52, $etiquetaPanel . '  |  Emitido: ' . date('d/m/Y H:i:s') . '  |  Responsable: ' . ($usuario['nombre'] ?? 'Administrador') . ' (' . ($usuario['rol'] ?? 'Administrador') . ')', 8.5, false, [0.92, 0.95, 1.0]);

        if ($pageNum === 1) {
            // Filtros aplicados
            $partesFiltro = [];
            foreach ($filtrosTexto as $k => $val) {
                $partesFiltro[] = is_string($k) ? ($k . ': ' . $val) : (string) $val;
            }
            $filtrosLinea = !empty($partesFiltro) ? implode('  |  ', $partesFiltro) : 'Todas las ventas registradas (sin filtros)';
            $drawRect($stream, $marginL, 74, $tableW, 18, [0.953, 0.969, 1.0]);
            $drawBorder($stream, $marginL, 74, $tableW, 18);
            $drawText($stream, $marginL + 8, 79, 'Filtros: ' . $truncarTexto($filtrosLinea, 125), 8, false, [0.20, 0.25, 0.33]);

            // Tarjetas KPI (4 columnas)
            $kpiW = ($tableW - 24) / 4;
            $kpis = [
                ['Total Ventas', number_format((float) ($metricas['cantidad_ventas'] ?? 0), 0, ',', '.'), 'Transacciones registradas'],
                ['Recaudacion Total', '$' . number_format((float) ($metricas['total_monto'] ?? 0), 0, ',', '.'), 'Monto acumulado CLP'],
                ['Ticket Promedio', '$' . number_format((float) ($metricas['ticket_promedio'] ?? 0), 0, ',', '.'), 'Promedio por venta'],
                ['Productos Vendidos', number_format((float) $totalUnidadesGlobal, 0, ',', '.'), 'Unidades despachadas'],
            ];
            foreach ($kpis as $idx => $kpi) {
                $kx = $marginL + $idx * ($kpiW + 8);
                $drawRect($stream, $kx, 98, $kpiW, 42, [1.0, 1.0, 1.0]);
                $drawBorder($stream, $kx, 98, $kpiW, 42);
                $drawRect($stream, $kx, 98, 4, 42, $idx === 1 ? [0.086, 0.639, 0.290] : ($idx === 2 ? [0.078, 0.722, 0.651] : [0.122, 0.369, 1.0]));
                $drawText($stream, $kx + 11, 103, strtoupper($kpi[0]), 7.2, true, [0.39, 0.45, 0.55]);
                $drawText($stream, $kx + 11, 114, $kpi[1], 13, true, [0.06, 0.09, 0.16]);
                $drawText($stream, $kx + 11, 129, $kpi[2], 7.0, false, [0.45, 0.52, 0.61]);
            }
            $y = 148.0;
        } else {
            $y = 78.0;
        }

        // Encabezado de tabla (#eaf0ff)
        $drawRect($stream, $marginL, $y, $tableW, 22, [0.918, 0.941, 1.0]);
        $drawBorder($stream, $marginL, $y, $tableW, 22, [0.75, 0.82, 0.92], 0.8);
        $cx = $marginL;
        foreach ($colHeaders as $i => $hText) {
            $drawCellText($stream, $cx, $y + 7, $colWidths[$i], $hText, 8.2, true, $colAligns[$i], [0.06, 0.09, 0.16]);
            $cx += $colWidths[$i];
        }
        $y += 22.0;
    };

    $startPage();

    $rowH = 19.0;
    $maxY = 532.0;

    if (empty($ventas)) {
        $drawRect($stream, $marginL, $y, $tableW, 26, [0.98, 0.99, 1.0]);
        $drawBorder($stream, $marginL, $y, $tableW, 26);
        $drawText($stream, $marginL + 12, $y + 9, 'No se encontraron registros de ventas para los filtros seleccionados.', 9, false, [0.39, 0.45, 0.55]);
        $y += 26.0;
    } else {
        foreach ($ventas as $idx => $v) {
            if ($y + $rowH > $maxY) {
                $startPage();
            }
            if ($idx % 2 === 1) {
                $drawRect($stream, $marginL, $y, $tableW, $rowH, [0.973, 0.980, 1.0]);
            }
            $drawBorder($stream, $marginL, $y, $tableW, $rowH, [0.89, 0.91, 0.94], 0.4);

            $fechaFmt = !empty($v['fecha_hora']) ? date('d/m/Y H:i', strtotime((string) $v['fecha_hora'])) : '-';
            $detalleTxt = !empty($v['productos_resumen']) ? (string) $v['productos_resumen'] : 'Sin detalle';
            $unidadesVenta = (int) ($v['total_unidades'] ?? 1);
            $celdas = [
                '#' . (int) $v['id_ventas'],
                $fechaFmt,
                $truncarTexto((string) ($v['cliente_nombre'] ?? 'Cliente'), 24),
                $truncarTexto((string) ($v['funcionario_nombre'] ?? 'Sistema'), 18),
                $truncarTexto((string) ($v['medio_pago'] ?? '-'), 14),
                $truncarTexto($detalleTxt, 48),
                (string) $unidadesVenta,
                '$' . number_format((float) $v['total_venta'], 0, ',', '.'),
            ];

            $cx = $marginL;
            foreach ($celdas as $i => $val) {
                $drawCellText($stream, $cx, $y + 5.5, $colWidths[$i], $val, 7.8, $i === 0 || $i === 7, $colAligns[$i]);
                $cx += $colWidths[$i];
            }
            $y += $rowH;
        }

        // Fila de Totales
        if ($y + 22 > $maxY) {
            $startPage();
        }
        $drawRect($stream, $marginL, $y, $tableW, 22, [0.918, 0.941, 1.0]);
        $drawBorder($stream, $marginL, $y, $tableW, 22, [0.122, 0.369, 1.0], 0.9);
        $xCant = $marginL + array_sum(array_slice($colWidths, 0, 6));
        $xTotal = $xCant + $colWidths[6];
        $drawCellText($stream, $marginL, $y + 7, $xCant - $marginL - 6, 'TOTALES CONSOLIDADOS (' . count($ventas) . ' ventas)', 8.5, true, 'R', [0.09, 0.25, 0.75]);
        $drawCellText($stream, $xCant, $y + 7, $colWidths[6], (string) $totalUnidadesGlobal, 8.5, true, 'C', [0.09, 0.25, 0.75]);
        $drawCellText($stream, $xTotal, $y + 7, $colWidths[7], '$' . number_format((float) ($metricas['total_monto'] ?? 0), 0, ',', '.'), 8.8, true, 'R', [0.09, 0.25, 0.75]);
    }

    $pageStreams[] = $stream;
    $totalPages = count($pageStreams);

    // Pie de pagina en todas las paginas
    foreach ($pageStreams as $idx => &$pStream) {
        $drawRect($pStream, $marginL, 554, $tableW, 1.2, [0.85, 0.89, 0.94]);
        $drawText($pStream, $marginL, 562, 'MarketPlus  |  Documento generado automaticamente el ' . date('d/m/Y H:i:s'), 7.8, false, [0.39, 0.45, 0.55]);
        $drawCellText($pStream, $marginL, 562, $tableW, 'Pagina ' . ($idx + 1) . ' de ' . $totalPages, 8.0, true, 'R', [0.20, 0.25, 0.33]);
    }
    unset($pStream);

    // Ensamblado binario PDF 1.4
    $objects = [];
    $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
    $objects[3] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";
    $objects[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>";

    $kidsRefs = [];
    $nextObjId = 5;
    foreach ($pageStreams as $pStream) {
        $contentId = $nextObjId++;
        $pageId = $nextObjId++;
        $objects[$contentId] = "<< /Length " . strlen($pStream) . " >>\nstream\n" . $pStream . "endstream";
        $objects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents {$contentId} 0 R >>";
        $kidsRefs[] = "{$pageId} 0 R";
    }
    $objects[2] = "<< /Type /Pages /Kids [" . implode(' ', $kidsRefs) . "] /Count " . count($kidsRefs) . " >>";
    ksort($objects);

    $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
    $offsets = [0 => 0];
    foreach ($objects as $id => $objBody) {
        $offsets[$id] = strlen($pdf);
        $pdf .= "{$id} 0 obj\n{$objBody}\nendobj\n";
    }
    $xrefPos = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    for ($i = 1; $i <= count($objects); $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }
    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF";

    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    $nombreArchivoPdf = $nombreArchivoBase . '.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: ' . ($modoDescarga ? 'attachment' : 'inline') . '; filename="' . $nombreArchivoPdf . '"');
    header('Content-Length: ' . strlen($pdf));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, max-age=0, must-revalidate');
    echo $pdf;
    exit();
}

// -------------------------------------------------------------------------
// FORMATO HTML DESCARGABLE (Con CSS corporativo incrustado y pie de página)
// -------------------------------------------------------------------------
while (ob_get_level() > 0) {
    ob_end_clean();
}
$nombreArchivoHtml = $nombreArchivoBase . '.html';

if ($modoDescarga) {
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $nombreArchivoHtml . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
}

echo generarHtmlReporteCompleto(
    'Reporte Consolidado de Ventas',
    $tablaHtml,
    $filtrosTexto,
    $usuario,
    $metricas,
    false
);
exit();
