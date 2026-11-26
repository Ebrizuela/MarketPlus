<?php
date_default_timezone_set('America/Santiago');
/**
 * MarketPlus - Plantilla Única y Centralizada para Reportes y Exportaciones
 * Cumple con las reglas de negocio de la HU Reportes de Ventas:
 * - Carga centralizada de estilos CSS oficiales desde una plantilla única.
 * - Manejo seguro de errores con registro en logs y mensaje genérico al usuario.
 * - Inclusión de pie de página corporativo estandarizado con razón social, RUT, fecha/hora, usuario y páginas.
 */

if (file_exists(__DIR__ . '/conexion.php')) {
    try {
        @include_once __DIR__ . '/conexion.php';
    } catch (\Throwable $e) {
        // Silencioso, obtenerConexionBd() resolverá la conexión
    }
}

/**
 * Obtiene la conexión PDO de forma segura y autónoma sin depender de modificaciones externas.
 */
function obtenerConexionBd(): ?PDO
{
    global $pdo;
    if (isset($pdo) && ($pdo instanceof PDO)) {
        return $pdo;
    }
    if (!function_exists('conectar') && file_exists(__DIR__ . '/conexion.php')) {
        require_once __DIR__ . '/conexion.php';
    }
    if (function_exists('conectar')) {
        try {
            $conn = conectar();
            if ($conn instanceof PDO) return $conn;
        } catch (\Throwable $e) {}
    }

    $db   = getenv('DB_NAME') ?: '';
    $user = getenv('DB_USER') ?: '';
    $pass = getenv('DB_PASSWORD') ?: '';
    $host = getenv('DB_HOST') ?: 'localhost';
    $charset = 'utf8mb4';

    if ($db !== '' && $user !== '') {
        try {
            return new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT            => 2
            ]);
        } catch (\Throwable $e) {
            return null;
        }
    }

    return null;
}

/**
 * Obtiene el contenido CSS corporativo oficial para reportes desde css/reportes.css.
 * En caso de error, registra el evento en los logs y retorna un CSS de contingencia corporativo
 * sin exponer trazas técnicas al usuario.
 *
 * @param string|null $errorReportado Variable por referencia para capturar avisos amigables
 * @return string Hojas de estilo CSS en texto plano para incrustar inline o en etiquetas <style>
 */
function obtenerCssReportes(?string &$errorReportado = null): string
{
    $rutaCss = __DIR__ . '/../css/reportes.css';

    try {
        if (!file_exists($rutaCss)) {
            throw new RuntimeException("El archivo de estilos '{$rutaCss}' no fue encontrado en el servidor.");
        }

        $css = file_get_contents($rutaCss);
        if ($css === false || trim($css) === '') {
            throw new RuntimeException("El archivo de estilos '{$rutaCss}' no pudo ser leído o está vacío.");
        }

        return $css;
    } catch (\Throwable $e) {
        // Regla de Negocio: Registrar evento en logs del servidor
        error_log("[MarketPlus - Módulo Reportes] Error al cargar estilos CSS corporativos: " . $e->getMessage());

        // Registro opcional en logs_seguridad si la conexión PDO está activa
        try {
            $pdo = obtenerConexionBd();
            if ($pdo instanceof PDO) {
                $stmt = $pdo->prepare("INSERT INTO logs_seguridad (usuario, accion, detalle, ip_origen) VALUES (?, 'Error de Estilos en Reporte', ?, ?)");
                $stmt->execute([
                    $_SESSION['usuario']['nombre'] ?? $_SESSION['nombre'] ?? 'Sistema',
                    'Fallo al cargar CSS corporativo: ' . substr($e->getMessage(), 0, 180),
                    $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                ]);
            }
        } catch (\Throwable $ex) {
            // Silencioso para no interferir en la exportación
        }

        // Regla de Negocio: Mensaje genérico para el usuario
        $errorReportado = "No fue posible cargar la versión extendida de los estilos corporativos. Se aplicó el formato estándar de contingencia.";

        // CSS corporativo de contingencia para garantizar que las tablas y el pie de página siempre tengan formato profesional
        return <<<CSS
            :root {
                --mp-primary: #1f5eff;
                --mp-accent: #14b8a6;
            }
            body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #1e293b; background: #ffffff; margin: 0; padding: 15px; }
            .report-header { border-bottom: 2px solid #1f5eff; padding-bottom: 12px; margin-bottom: 15px; }
            .report-brand-title { color: #1f5eff; font-size: 22px; font-weight: bold; margin: 0; }
            .report-brand-title span.accent { color: #14b8a6; }
            .report-company-info { font-size: 11px; color: #475569; margin-top: 4px; }
            table.report-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11px; }
            table.report-table th { background-color: #1f5eff; color: #ffffff; padding: 10px 8px; border-bottom: 3px solid #14b8a6; text-align: left; text-transform: uppercase; }
            table.report-table td { padding: 8px; border: 1px solid #e2e8f0; }
            table.report-table tr:nth-child(even) { background-color: #f8fafc; }
            table.report-table tfoot td { background-color: #eaf0ff; font-weight: bold; color: #173fbf; border-top: 2px solid #1f5eff; }
            .report-footer { border-top: 3px solid #14b8a6; background: #f8fafc; padding: 12px 15px; font-size: 10px; color: #64748b; margin-top: 20px; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .badge-payment { padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 10px; }
CSS;
    }
}

/**
 * Obtiene los datos del usuario actual responsable de la emisión del reporte.
 *
 * @return array
 */
function obtenerUsuarioResponsable(): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $nombre = 'Administrador Sistema';
    $rol = 'Administrador';
    $rut = '16.813.140-1';

    if (isset($_SESSION['usuario']) && is_array($_SESSION['usuario'])) {
        $nombre = $_SESSION['usuario']['nombre_completo'] ?? $_SESSION['usuario']['nombre'] ?? $nombre;
        $rol = $_SESSION['usuario']['rol'] ?? $rol;
        $rut = $_SESSION['usuario']['rut'] ?? $rut;
    } elseif (isset($_SESSION['nombre'])) {
        $nombre = $_SESSION['nombre'];
        $rol = ($_SESSION['id_rol'] ?? 1) == 2 ? 'Supervisor' : (($_SESSION['id_rol'] ?? 1) == 3 ? 'Vendedor' : 'Administrador');
    }

    return [
        'nombre' => $nombre,
        'rol'    => ucfirst(strtolower((string) $rol)),
        'rut'    => $rut
    ];
}

/**
 * Genera el Encabezado Corporativo Oficial de MarketPlus.
 *
 * @param string $titulo Título formal del reporte
 * @param array $filtrosTexto Arreglo con filtros descriptivos aplicados
 * @param array $usuario Datos del usuario emisor
 * @return string Bloque HTML del encabezado
 */
function renderizarEncabezadoCorporativo(string $titulo, array $filtrosTexto = [], ?array $usuario = null): string
{
    $usuario = $usuario ?? obtenerUsuarioResponsable();
    $fechaEmision = date('d/m/Y H:i:s');

    $html = '
    <div class="report-top-bar"></div>
    <header class="report-header">
        <div class="report-brand">
            <h1 class="report-brand-title">MarketPlus</h1>
            <div class="report-company-info">
                <strong>Razón Social:</strong> MarketPlus &bull; <strong>RUT:</strong> 76.123.456-7<br>
                <strong>Giro:</strong> Venta minorista de tecnología, ferretería e insumos para el hogar<br>
                <strong>Casa Matriz:</strong> Av. Monseñor Enrique Alvear 1234, Cauquenes &bull; <strong>Contacto:</strong> contacto@marketplus.cl
            </div>
        </div>
        <div class="report-meta-box">
            <div class="report-meta-title">' . htmlspecialchars($titulo) . '</div>
            <div class="report-meta-item"><strong>Fecha y Hora:</strong> ' . $fechaEmision . '</div>
            <div class="report-meta-item"><strong>Responsable:</strong> ' . htmlspecialchars($usuario['nombre']) . '</div>
            <div class="report-meta-item"><strong>Rol:</strong> <span class="report-footer-badge">' . htmlspecialchars($usuario['rol']) . '</span></div>
        </div>
    </header>';

    if (!empty($filtrosTexto)) {
        $html .= '<div class="report-filters-bar"><strong>Filtros aplicados:</strong> ';
        foreach ($filtrosTexto as $clave => $valor) {
            $html .= '<span class="filter-pill"><strong>' . htmlspecialchars($clave) . ':</strong> ' . htmlspecialchars($valor) . '</span> ';
        }
        $html .= '</div>';
    }

    return $html;
}

/**
 * Genera el Pie de Página Corporativo Oficial de MarketPlus.
 * Cumple con la Regla de Negocio:
 * - Nombre de la empresa (MarketPlus)
 * - Fecha y hora de generación
 * - Usuario responsable
 * - Número de página (en PDF o pantalla)
 *
 * @param array|null $usuario Datos del usuario responsable
 * @param bool $esPdf Indica si es para renderizado de PDF
 * @return string Bloque HTML del pie de página corporativo
 */
function renderizarPiePaginaCorporativo(?array $usuario = null, bool $esPdf = false): string
{
    $usuario = $usuario ?? obtenerUsuarioResponsable();
    $fechaHora = date('d/m/Y H:i:s');

    $seccionPagina = $esPdf 
        ? '<span class="page-number">&nbsp;</span>'
        : '<span>Documento Oficial &bull; Formato Digital</span>';

    return '
    <footer class="report-footer">
        <div class="report-footer-left">
            <div class="report-footer-company">MarketPlus &bull; RUT: 76.123.456-7</div>
            <div>Sistema de Gestión Comercial y Ventas &bull; Cauquenes, Región del Maule</div>
        </div>
        <div class="report-footer-center">
            <div>Documento oficial generado con hoja de estilo institucional.</div>
            <div>Uso interno y confidencial &bull; Certificación QA ISTQB</div>
        </div>
        <div class="report-footer-right">
            <div><strong>Generado:</strong> ' . $fechaHora . '</div>
            <div><strong>Emisor:</strong> ' . htmlspecialchars($usuario['nombre']) . ' (' . htmlspecialchars($usuario['rol']) . ')</div>
            <div>' . $seccionPagina . '</div>
        </div>
    </footer>';
}

/**
 * Calcula los totales y métricas de un conjunto de ventas.
 *
 * @param array $ventas Listado de ventas
 * @return array Totales estadísticos
 */
function calcularMetricasReporte(array $ventas): array
{
    $totalMonto = 0.0;
    $cantidadVentas = count($ventas);
    $mediosPago = [];

    foreach ($ventas as $v) {
        $monto = (float) ($v['total_venta'] ?? 0);
        $totalMonto += $monto;

        $medio = $v['medio_pago'] ?? 'Sin especificar';
        if (!isset($mediosPago[$medio])) {
            $mediosPago[$medio] = 0;
        }
        $mediosPago[$medio] += $monto;
    }

    $ticketPromedio = $cantidadVentas > 0 ? ($totalMonto / $cantidadVentas) : 0.0;

    return [
        'total_monto'      => $totalMonto,
        'cantidad_ventas'  => $cantidadVentas,
        'ticket_promedio'  => $ticketPromedio,
        'por_medio_pago'   => $mediosPago,
    ];
}

/**
 * Renderiza las tarjetas de resumen KPI para el reporte.
 *
 * @param array $metricas Resultados de calcularMetricasReporte
 * @return string HTML de las tarjetas KPI
 */
function renderizarTarjetasKpi(array $metricas): string
{
    return '
    <div class="report-summary-cards">
        <div class="kpi-card">
            <div class="kpi-label">Total Recaudado</div>
            <div class="kpi-value">$' . number_format($metricas['total_monto'], 0, ',', '.') . '</div>
            <div class="kpi-subtext">Ingresos brutos del periodo filtrado</div>
        </div>
        <div class="kpi-card accent">
            <div class="kpi-label">Transacciones</div>
            <div class="kpi-value">' . number_format($metricas['cantidad_ventas'], 0, ',', '.') . '</div>
            <div class="kpi-subtext">Ventas registradas y validadas</div>
        </div>
        <div class="kpi-card success">
            <div class="kpi-label">Ticket Promedio</div>
            <div class="kpi-value">$' . number_format($metricas['ticket_promedio'], 0, ',', '.') . '</div>
            <div class="kpi-subtext">Promedio por operación comercial</div>
        </div>
    </div>';
}

/**
 * Arma un documento HTML completo e independiente con estilos CSS inline
 * para descarga HTML autónoma o renderizado en motor PDF.
 *
 * @param string $titulo Título del reporte
 * @param string $tablaHtml Contenido de la tabla con cabecera y filas
 * @param array $filtrosTexto Filtros aplicados
 * @param array|null $usuario Usuario responsable
 * @param array|null $metricas Métricas calculadas
 * @param bool $esParaPdf Configuración especial para exportación a PDF
 * @return string Documento HTML válido y completo
 */
function generarHtmlReporteCompleto(
    string $titulo,
    string $tablaHtml,
    array $filtrosTexto = [],
    ?array $usuario = null,
    ?array $metricas = null,
    bool $esParaPdf = false
): string {
    $avisoEstilos = null;
    $css = obtenerCssReportes($avisoEstilos);
    $usuario = $usuario ?? obtenerUsuarioResponsable();
    $encabezadoHtml = renderizarEncabezadoCorporativo($titulo, $filtrosTexto, $usuario);
    $kpisHtml = ($metricas !== null) ? renderizarTarjetasKpi($metricas) : '';
    $piePaginaHtml = renderizarPiePaginaCorporativo($usuario, $esParaPdf);

    $scriptImpresion = !$esParaPdf ? '
    <div class="no-print report-actions-bar" style="margin-top: 15px; margin-bottom: 15px; display: flex; justify-content: flex-end; gap: 10px;">
        <button onclick="window.print();" class="btn-report btn-report-html" style="border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">
            🖨️ Imprimir / Guardar como PDF
        </button>
        <button onclick="window.close();" class="btn-report btn-report-secondary" style="border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">
            Cerrar Ventana
        </button>
    </div>' : '';

    $avisoHtml = ($avisoEstilos !== null && !$esParaPdf) 
        ? '<div style="background:#fff3cd; color:#856404; padding:10px 15px; border-radius:4px; margin-bottom:15px; font-size:12px;">' . htmlspecialchars($avisoEstilos) . '</div>' 
        : '';

    return '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($titulo) . ' - MarketPlus</title>
    <style>
' . $css . '
    </style>
</head>
<body class="report-body' . ($esParaPdf ? ' is-pdf' : '') . '">
    <div class="report-container">
        ' . $scriptImpresion . '
        ' . $avisoHtml . '
        ' . $encabezadoHtml . '
        ' . $kpisHtml . '
        <main class="report-content">
            <div class="table-responsive-report">
                ' . $tablaHtml . '
            </div>
        </main>
        ' . $piePaginaHtml . '
    </div>
</body>
</html>';
}
