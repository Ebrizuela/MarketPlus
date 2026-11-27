<?php
final class PdfReporteVentas
{
    private const PAGE_WIDTH = 842.0;
    private const PAGE_HEIGHT = 595.0;
    private const LEFT_MARGIN = 36.0;
    private const TABLE_WIDTH = 770.0;
    private const ROW_LINE_HEIGHT = 11.0;

    public static function generar(
        array $reportes,
        float $total,
        float $promedio,
        float $unidades,
        array $filtros
    ): string {
        $columnWidths = [44.0, 94.0, 135.0, 135.0, 190.0, 52.0, 120.0];
        $headers = ['ID Venta', 'Fecha y hora', 'Vendedor', 'Cliente', 'Productos', 'Unidades', 'Total venta'];
        $pageNumber = 0;
        $content = '';
        $streams = [];
        $y = 0.0;
        $bottom = 558.0;

        $startPage = static function (int $number) use (
            &$pageNumber,
            &$content,
            &$streams,
            &$y,
            $columnWidths,
            $headers,
            $total,
            $promedio,
            $unidades,
            $filtros
        ): void {
            if ($pageNumber > 0) {
                $streams[] = $content;
            }
            $pageNumber = $number;
            $content = '';
            self::texto($content, 36, 27, 'Reporte de Ventas', 18, true);
            self::texto($content, 36, 49, 'Generado: ' . date('d/m/Y H:i:s') . '    Pagina ' . $number, 9);

            if ($number === 1) {
                self::texto($content, 36, 72, 'Ventas totales: $' . self::formatearMonto($total), 10, true);
                self::texto($content, 280, 72, 'Promedio por venta: $' . self::formatearMonto($promedio), 10, true);
                self::texto($content, 570, 72, 'Unidades vendidas: ' . self::formatearMonto($unidades), 10, true);
                self::texto($content, 36, 96, 'Filtros: ' . self::describirFiltros($filtros), 8);
                $y = 119.0;
            } else {
                self::texto($content, 36, 68, 'Continuacion del reporte', 9);
                $y = 85.0;
            }

            self::dibujarEncabezadoTabla($content, $y, $columnWidths, $headers);
            $y += 30.0;
        };

        $startPage(1);

        if ($reportes === []) {
            self::rectangulo($content, self::LEFT_MARGIN, $y, self::TABLE_WIDTH, 24, [0.96, 0.97, 0.98]);
            self::texto($content, self::LEFT_MARGIN + 5, $y + 7, 'No se encontraron ventas para los filtros seleccionados.', 9);
            self::bordeFila($content, $y, 24, $columnWidths);
            $y += 24;
        } else {
            foreach ($reportes as $indice => $reporte) {
                $valores = [
                    (string) $reporte['id_ventas'],
                    date('d/m/Y H:i', strtotime((string) $reporte['fecha_hora'])),
                    (string) $reporte['vendedor'],
                    (string) $reporte['cliente'],
                    (string) $reporte['productos'],
                    self::formatearMonto((float) $reporte['cantidad']),
                    '$' . self::formatearMonto((float) $reporte['total_venta']),
                ];
                $lineasPorCelda = [];

                foreach ($valores as $columna => $valor) {
                    $maxCaracteres = max(3, (int) floor(($columnWidths[$columna] - 8) / (8.5 * 0.52)));
                    $lineasPorCelda[] = self::ajustarTexto($valor, $maxCaracteres);
                }

                $lineasTotales = max(array_map('count', $lineasPorCelda));
                $desplazamiento = 0;

                while ($desplazamiento < $lineasTotales) {
                    $capacidad = (int) floor(($bottom - $y - 6) / self::ROW_LINE_HEIGHT);
                    if ($capacidad < 1) {
                        $startPage($pageNumber + 1);
                        $capacidad = (int) floor(($bottom - $y - 6) / self::ROW_LINE_HEIGHT);
                    }

                    $lineasFragmento = min($lineasTotales - $desplazamiento, $capacidad);
                    $altoFila = ($lineasFragmento * self::ROW_LINE_HEIGHT) + 6;
                    $fondo = ($indice % 2 === 0) ? [1.0, 1.0, 1.0] : [0.96, 0.97, 0.98];
                    self::rectangulo($content, self::LEFT_MARGIN, $y, self::TABLE_WIDTH, $altoFila, $fondo);

                    $x = self::LEFT_MARGIN;
                    foreach ($columnWidths as $columna => $ancho) {
                        $lineasCelda = array_slice($lineasPorCelda[$columna], $desplazamiento, $lineasFragmento);
                        foreach ($lineasCelda as $linea => $texto) {
                            self::texto($content, $x + 4, $y + 3 + ($linea * self::ROW_LINE_HEIGHT), $texto, 8.5);
                        }
                        $x += $ancho;
                    }

                    self::bordeFila($content, $y, $altoFila, $columnWidths);
                    $y += $altoFila;
                    $desplazamiento += $lineasFragmento;

                    if ($desplazamiento < $lineasTotales) {
                        $startPage($pageNumber + 1);
                    }
                }
            }
        }

        if ($y + 22 > $bottom) {
            $startPage($pageNumber + 1);
        }
        self::dibujarTotales($content, $y, $columnWidths, $unidades, $total);
        $streams[] = $content;

        return self::serializar($streams);
    }

    private static function describirFiltros(array $filtros): string
    {
        $partes = [];
        if ($filtros['fecha_desde'] !== '') {
            $partes[] = 'desde ' . $filtros['fecha_desde'];
        }
        if ($filtros['fecha_hasta'] !== '') {
            $partes[] = 'hasta ' . $filtros['fecha_hasta'];
        }
        if ($filtros['vendedor'] !== '') {
            $partes[] = 'vendedor ' . $filtros['vendedor'];
        }
        if ($filtros['cliente'] !== '') {
            $partes[] = 'cliente ' . $filtros['cliente'];
        }
        if ($filtros['producto'] !== '') {
            $partes[] = 'producto ID ' . $filtros['producto'];
        }

        return $partes === [] ? 'todos los registros' : implode(', ', $partes);
    }

    private static function dibujarEncabezadoTabla(
        string &$content,
        float $y,
        array $columnWidths,
        array $headers
    ): void {
        self::rectangulo($content, self::LEFT_MARGIN, $y, self::TABLE_WIDTH, 30, [0.12, 0.40, 0.31]);
        $x = self::LEFT_MARGIN;

        foreach ($headers as $indice => $header) {
            $maxCaracteres = max(3, (int) floor(($columnWidths[$indice] - 8) / (8 * 0.52)));
            foreach (self::ajustarTexto($header, $maxCaracteres) as $linea => $texto) {
                self::texto($content, $x + 4, $y + 5 + ($linea * 9), $texto, 8, true, [1.0, 1.0, 1.0]);
            }
            $x += $columnWidths[$indice];
        }

        self::bordeFila($content, $y, 30, $columnWidths);
    }

    private static function dibujarTotales(
        string &$content,
        float $y,
        array $columnWidths,
        float $unidades,
        float $total
    ): void {
        $alto = 22.0;
        self::rectangulo($content, self::LEFT_MARGIN, $y, self::TABLE_WIDTH, $alto, [0.90, 0.92, 0.94]);
        self::texto($content, self::LEFT_MARGIN + 5, $y + 6, 'TOTALES', 9, true);

        $xCantidad = self::LEFT_MARGIN + array_sum(array_slice($columnWidths, 0, 5));
        self::texto($content, $xCantidad + 4, $y + 6, self::formatearMonto($unidades), 9, true);
        $xSubtotal = self::LEFT_MARGIN + array_sum(array_slice($columnWidths, 0, 6));
        self::texto($content, $xSubtotal + 4, $y + 6, '$' . self::formatearMonto($total), 9, true);
        self::bordeFila($content, $y, $alto, $columnWidths);
    }

    private static function bordeFila(string &$content, float $y, float $alto, array $columnWidths): void
    {
        $x = self::LEFT_MARGIN;
        self::linea($content, $x, $y, $x + self::TABLE_WIDTH, $y);
        self::linea($content, $x, $y + $alto, $x + self::TABLE_WIDTH, $y + $alto);
        self::linea($content, $x, $y, $x, $y + $alto);

        foreach ($columnWidths as $ancho) {
            $x += $ancho;
            self::linea($content, $x, $y, $x, $y + $alto);
        }
    }

    private static function ajustarTexto(string $texto, int $limite): array
    {
        $texto = self::aWindows1252($texto);
        $palabras = preg_split('/\s+/', trim($texto)) ?: [];
        $lineas = [];
        $linea = '';

        foreach ($palabras as $palabra) {
            while (strlen($palabra) > $limite) {
                if ($linea !== '') {
                    $lineas[] = $linea;
                    $linea = '';
                }
                $lineas[] = substr($palabra, 0, $limite);
                $palabra = substr($palabra, $limite);
            }

            $candidata = $linea === '' ? $palabra : $linea . ' ' . $palabra;
            if (strlen($candidata) > $limite) {
                $lineas[] = $linea;
                $linea = $palabra;
            } else {
                $linea = $candidata;
            }
        }

        if ($linea !== '' || $lineas === []) {
            $lineas[] = $linea;
        }

        return $lineas;
    }

    private static function aWindows1252(string $texto): string
    {
        $texto = str_replace(["\r", "\n", "\t"], ' ', $texto);
        if (preg_match('//u', $texto) !== 1) {
            return $texto;
        }
        if (function_exists('iconv')) {
            $convertido = iconv('UTF-8', 'Windows-1252//TRANSLIT', $texto);
            if ($convertido !== false) {
                return $convertido;
            }
        }

        return preg_replace('/[^\x20-\x7E]/', '?', $texto) ?? '';
    }

    private static function formatearMonto(float $monto): string
    {
        return number_format($monto, 0, ',', '.');
    }

    private static function texto(
        string &$content,
        float $x,
        float $top,
        string $text,
        float $size,
        bool $bold = false,
        array $color = [0.13, 0.16, 0.20]
    ): void {
        $encoded = self::aWindows1252($text);
        $encoded = preg_replace('/[\x00-\x1F\x7F]/', ' ', $encoded) ?? '';
        $encoded = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encoded);
        $font = $bold ? 'F2' : 'F1';
        $baseline = self::PAGE_HEIGHT - $top - $size;
        $content .= sprintf(
            "BT /%s %s Tf %s %s %s rg 1 0 0 1 %s %s Tm (%s) Tj ET\n",
            $font,
            self::numero($size),
            self::numero($color[0]),
            self::numero($color[1]),
            self::numero($color[2]),
            self::numero($x),
            self::numero($baseline),
            $encoded
        );
    }

    private static function rectangulo(
        string &$content,
        float $x,
        float $top,
        float $width,
        float $height,
        array $color
    ): void {
        $bottom = self::PAGE_HEIGHT - $top - $height;
        $content .= sprintf(
            "q %s %s %s rg %s %s %s %s re f Q\n",
            self::numero($color[0]),
            self::numero($color[1]),
            self::numero($color[2]),
            self::numero($x),
            self::numero($bottom),
            self::numero($width),
            self::numero($height)
        );
    }

    private static function linea(string &$content, float $x1, float $top1, float $x2, float $top2): void
    {
        $content .= sprintf(
            "0.78 0.80 0.82 RG 0.5 w %s %s m %s %s l S\n",
            self::numero($x1),
            self::numero(self::PAGE_HEIGHT - $top1),
            self::numero($x2),
            self::numero(self::PAGE_HEIGHT - $top2)
        );
    }

    private static function numero(float $value): string
    {
        return number_format($value, 3, '.', '');
    }

    private static function serializar(array $streams): string
    {
        $objects = [null];
        $fontRegularId = self::agregarObjeto($objects, '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>');
        $fontBoldId = self::agregarObjeto($objects, '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>');
        $pagesId = self::agregarObjeto($objects, '');
        $pageIds = [];

        foreach ($streams as $stream) {
            $streamId = self::agregarObjeto(
                $objects,
                '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . "endstream"
            );
            $pageIds[] = self::agregarObjeto(
                $objects,
                '<< /Type /Page /Parent ' . $pagesId . ' 0 R /MediaBox [0 0 ' .
                self::numero(self::PAGE_WIDTH) . ' ' . self::numero(self::PAGE_HEIGHT) .
                '] /Resources << /Font << /F1 ' . $fontRegularId . ' 0 R /F2 ' . $fontBoldId .
                ' 0 R >> >> /Contents ' . $streamId . ' 0 R >>'
            );
        }

        $kids = implode(' ', array_map(static function (int $id): string {
            return $id . ' 0 R';
        }, $pageIds));
        $objects[$pagesId] = '<< /Type /Pages /Kids [' . $kids . '] /Count ' . count($pageIds) . ' >>';
        $catalogId = self::agregarObjeto($objects, '<< /Type /Catalog /Pages ' . $pagesId . ' 0 R >>');

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];
        for ($id = 1, $count = count($objects); $id < $count; $id++) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $objects[$id] . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . count($objects) . "\n0000000000 65535 f \n";
        for ($id = 1, $count = count($objects); $id < $count; $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id]);
        }
        $pdf .= "trailer\n<< /Size " . count($objects) . ' /Root ' . $catalogId . " 0 R >>\n";
        $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF\n";

        return $pdf;
    }

    private static function agregarObjeto(array &$objects, string $object): int
    {
        $objects[] = $object;
        return count($objects) - 1;
    }
}

function generarReporteExcel(array $reportes, array $resumen): string
{
    $excelCss = file_get_contents(__DIR__ . '/../css/reportes_ventas.css');
    if ($excelCss === false) {
        throw new RuntimeException('No se pudo cargar la hoja de estilos de Excel.');
    }

    $escapar = static function ($valor): string {
        return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    };

    ob_start();
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style><?= $excelCss ?></style>
</head>
<body class="excel-report">
    <h2 class="excel-title">Reporte de Ventas</h2>
    <p class="excel-generated"><strong>Fecha de generación:</strong> <?= date('d/m/Y H:i:s') ?></p>
    <table class="excel-table" border="1" cellpadding="7" cellspacing="0">
        <thead>
            <tr>
                <th>ID Venta</th>
                <th>Fecha y Hora</th>
                <th>Vendedor</th>
                <th>Cliente</th>
                <th>Productos</th>
                <th>Unidades</th>
                <th>Total Venta ($)</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($reportes === []): ?>
                <tr>
                    <td colspan="7" class="centro">No se encontraron registros con los filtros seleccionados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($reportes as $reporte): ?>
                    <tr>
                        <td class="centro"><?= $escapar($reporte['id_ventas']) ?></td>
                        <td class="centro"><?= $escapar($reporte['fecha_hora']) ?></td>
                        <td><?= $escapar($reporte['vendedor']) ?></td>
                        <td><?= $escapar($reporte['cliente']) ?></td>
                        <td><?= $escapar($reporte['productos']) ?></td>
                        <td class="centro"><?= $escapar($reporte['cantidad']) ?></td>
                        <td class="monto">$<?= number_format((float) $reporte['total_venta'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="5" class="monto">TOTALES:</td>
                    <td class="centro"><?= $resumen['unidades'] ?></td>
                    <td class="monto">$<?= number_format($resumen['total'], 0, ',', '.') ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
    <?php
    $contenido = ob_get_clean();
    if ($contenido === false) {
        throw new RuntimeException('No fue posible generar el archivo Excel.');
    }

    return $contenido;
}
