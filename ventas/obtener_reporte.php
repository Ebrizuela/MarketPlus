<?php

require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/ventas_lib.php';
require_once __DIR__ . '/ventas_exportadores.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/navegacion.php';

header('X-Content-Type-Options: nosniff');

$formato = $_GET['formato'] ?? '';
if (!is_string($formato) || !in_array($formato, ['', 'excel', 'pdf'], true)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Formato de reporte no válido.');
}

$bufferPdf = $formato === 'pdf';
if ($bufferPdf) {
    ini_set('display_errors', '0');
    ob_start();
}

try {
    $filtros = leerFiltrosReporte($_GET);
    $reportes = consultarReporteVentas(conectar(), $filtros);

    if ($formato === 'excel') {
        $contenido = generarReporteExcel($reportes, calcularResumenReporte($reportes));
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="Reporte_Ventas_' . date('Y-m-d_H-i') . '.xls"');
        header('Cache-Control: private, no-store, max-age=0');
        echo "\xEF\xBB\xBF" . $contenido;
        exit;
    }

    if ($formato === 'pdf') {
        date_default_timezone_set('America/Santiago');
        $resumen = calcularResumenReporte($reportes);
        $pdf = PdfReporteVentas::generar(
            $reportes,
            $resumen['total'],
            $resumen['promedio'],
            (float) $resumen['unidades'],
            $filtros
        );
        if ($bufferPdf && ob_get_level() > 0) {
            ob_end_clean();
            $bufferPdf = false;
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="reporte_ventas.pdf"');
        header('Content-Length: ' . strlen($pdf));
        header('Cache-Control: private, no-store, max-age=0');
        echo $pdf;
        exit;
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'success',
        'data' => $reportes,
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (InvalidArgumentException $error) {
    if ($bufferPdf && ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code(400);
    if ($formato === '') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'error',
            'message' => $error->getMessage(),
        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    } else {
        header('Content-Type: text/plain; charset=utf-8');
        echo $error->getMessage();
    }
} catch (Throwable $error) {
    if ($bufferPdf && ob_get_level() > 0) {
        ob_end_clean();
    }
    error_log('Error al generar el reporte de ventas: ' . $error->getMessage());
    http_response_code(500);
    if ($formato === '') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'error',
            'message' => 'No fue posible consultar el reporte de ventas.',
        ], JSON_UNESCAPED_UNICODE);
    } else {
        header('Content-Type: text/plain; charset=utf-8');
        echo 'No fue posible generar el reporte de ventas.';
    }
}
