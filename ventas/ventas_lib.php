<?php

function leerFiltrosReporte(array $origen): array
{
    $filtros = [];
    foreach (['vendedor', 'fecha_desde', 'fecha_hasta', 'cliente', 'producto'] as $nombre) {
        $valor = $origen[$nombre] ?? '';
        if (!is_string($valor)) {
            throw new InvalidArgumentException('Uno de los filtros no es valido.');
        }
        $filtros[$nombre] = trim($valor);
    }

    foreach (['fecha_desde', 'fecha_hasta'] as $nombre) {
        if ($filtros[$nombre] === '') {
            continue;
        }
        $fecha = DateTime::createFromFormat('!Y-m-d', $filtros[$nombre]);
        if ($fecha === false || $fecha->format('Y-m-d') !== $filtros[$nombre]) {
            throw new InvalidArgumentException('La fecha indicada no es valida.');
        }
    }

    if (
        $filtros['fecha_desde'] !== '' &&
        $filtros['fecha_hasta'] !== '' &&
        $filtros['fecha_desde'] > $filtros['fecha_hasta']
    ) {
        throw new InvalidArgumentException('La fecha desde no puede ser posterior a la fecha hasta.');
    }

    foreach (['vendedor', 'cliente'] as $nombre) {
        if (strlen($filtros[$nombre]) > 255) {
            throw new InvalidArgumentException('Uno de los filtros de nombre no es valido.');
        }
    }

    if ($filtros['producto'] !== '' && !ctype_digit($filtros['producto'])) {
        throw new InvalidArgumentException('El producto indicado no es valido.');
    }

    return $filtros;
}

function obtenerOpcionesReporte(PDO $pdo): array
{
    $clientes = $pdo->query(
        "SELECT DISTINCT TRIM(CONCAT_WS(' ', NULLIF(TRIM(nombre), ''), NULLIF(TRIM(apellidos), ''))) AS nombre
         FROM Clientes
         ORDER BY nombre"
    )->fetchAll(PDO::FETCH_COLUMN);

    $vendedores = $pdo->query(
        "SELECT DISTINCT TRIM(CONCAT_WS(' ', NULLIF(TRIM(f.nombre), ''), NULLIF(TRIM(f.apellido), ''))) AS nombre
         FROM funcionarios f
         INNER JOIN Roles r ON r.id_rol = f.id_rol
         WHERE LOWER(TRIM(r.nombre)) = 'vendedor'
         ORDER BY nombre"
    )->fetchAll(PDO::FETCH_COLUMN);

    $productos = $pdo->query(
        'SELECT id_producto, nombre FROM Productos ORDER BY nombre'
    )->fetchAll(PDO::FETCH_ASSOC);

    return [
        'clientes' => $clientes,
        'vendedores' => $vendedores,
        'productos' => $productos,
    ];
}

function consultarReporteVentas(PDO $pdo, array $filtros): array
{
    $clienteLimpio = "COALESCE(NULLIF(TRIM(CONCAT_WS(' ', NULLIF(TRIM(c.nombre), ''), NULLIF(TRIM(c.apellidos), ''))), ''), 'Cliente no asignado')";
    $vendedorLimpio = "COALESCE(NULLIF(TRIM(CONCAT_WS(' ', NULLIF(TRIM(f.nombre), ''), NULLIF(TRIM(f.apellido), ''))), ''), 'Sin vendedor')";

    $condiciones = [];
    $parametros = [];

    if ($filtros['vendedor'] !== '') {
        $condiciones[] = "$vendedorLimpio = :vendedor";
        $parametros[':vendedor'] = $filtros['vendedor'];
    }
    if ($filtros['fecha_desde'] !== '') {
        $condiciones[] = 'v.fecha_hora >= :fecha_desde';
        $parametros[':fecha_desde'] = $filtros['fecha_desde'] . ' 00:00:00';
    }
    if ($filtros['fecha_hasta'] !== '') {
        $condiciones[] = 'v.fecha_hora < DATE_ADD(:fecha_hasta, INTERVAL 1 DAY)';
        $parametros[':fecha_hasta'] = $filtros['fecha_hasta'];
    }
    if ($filtros['cliente'] !== '') {
        $condiciones[] = "$clienteLimpio = :cliente";
        $parametros[':cliente'] = $filtros['cliente'];
    }
    if ($filtros['producto'] !== '') {
        $condiciones[] = 'EXISTS (
            SELECT 1
            FROM Detalle_Ventas dv_filtro
            WHERE dv_filtro.id_ventas = v.id_ventas
              AND dv_filtro.id_producto = :producto
        )';
        $parametros[':producto'] = $filtros['producto'];
    }

    $sql = "SELECT
                v.id_ventas,
                v.fecha_hora,
                $vendedorLimpio AS vendedor,
                $clienteLimpio AS cliente,
                COALESCE(GROUP_CONCAT(DISTINCT p.nombre ORDER BY p.nombre SEPARATOR ', '), 'Sin productos') AS productos,
                COALESCE(SUM(dv.cantidad), 0) AS cantidad,
                v.total_venta
            FROM Ventas v
            LEFT JOIN funcionarios f ON f.id_funcionario = v.id_funcionario
            LEFT JOIN Clientes c ON c.id_cliente = v.id_cliente
            LEFT JOIN Detalle_Ventas dv ON dv.id_ventas = v.id_ventas
            LEFT JOIN Productos p ON p.id_producto = dv.id_producto";

    if ($condiciones !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $condiciones);
    }

    $sql .= ' GROUP BY v.id_ventas, v.fecha_hora, vendedor, cliente, v.total_venta
              ORDER BY v.fecha_hora DESC, v.id_ventas DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function calcularResumenReporte(array $reportes): array
{
    $total = 0.0;
    $unidades = 0;
    foreach ($reportes as $reporte) {
        $total += (float) $reporte['total_venta'];
        $unidades += (int) $reporte['cantidad'];
    }

    $transacciones = count($reportes);

    return [
        'total' => $total,
        'unidades' => $unidades,
        'transacciones' => $transacciones,
        'promedio' => $transacciones > 0 ? $total / $transacciones : 0.0,
    ];
}
