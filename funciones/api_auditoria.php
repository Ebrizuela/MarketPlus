<?php
declare(strict_types=1);

require_once __DIR__ . '/conexiones.php';
require_once __DIR__ . '/../auditor/middleware.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');

function apiAuditoriaResponder(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function apiAuditoriaFechaValida(string $fecha): bool
{
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
    return $parsed !== false && $parsed->format('Y-m-d') === $fecha;
}

function apiAuditoriaParametro(string $nombre): string
{
    $valor = $_GET[$nombre] ?? '';
    if (!is_string($valor) && !is_int($valor)) {
        apiAuditoriaResponder(400, ['error' => 'Parámetro inválido: ' . $nombre]);
    }
    return trim((string)$valor);
}

try {
    $pdo = conectar();

    if (!auditorEstaAutorizado($pdo)) {
        auditorRegistrarRechazo($pdo, 'Intento de consulta API sin rol Auditor o segundo factor válido.');
        apiAuditoriaResponder(403, ['error' => 'Acceso denegado. Se requiere rol Auditor y 2FA válido.']);
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        header('Allow: GET');
        apiAuditoriaResponder(405, ['error' => 'Método no permitido.']);
    }

    if (isset($_GET['id'])) {
        $id = filter_var(apiAuditoriaParametro('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            apiAuditoriaResponder(400, ['error' => 'Identificador de evento inválido.']);
        }

        $stmt = $pdo->prepare(
            'SELECT id_evento, modulo, tipo_evento, descripcion, id_registro_afectado,
                    datos_previos, datos_nuevos, id_usuario_responsable, nombre_usuario,
                    ip_origen, fecha_evento
             FROM auditoria_eventos
             WHERE id_evento = :id'
        );
        $stmt->execute([':id' => $id]);
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$evento) {
            apiAuditoriaResponder(404, ['error' => 'Evento no encontrado.']);
        }

        foreach (['datos_previos', 'datos_nuevos'] as $campo) {
            $evento[$campo] = $evento[$campo] === null
                ? null
                : json_decode((string)$evento[$campo], true, 512, JSON_THROW_ON_ERROR);
        }
        apiAuditoriaResponder(200, ['evento' => $evento]);
    }

    $fechaInicio = apiAuditoriaParametro('fecha_inicio');
    $fechaFin = apiAuditoriaParametro('fecha_fin');
    if ($fechaInicio !== '' && !apiAuditoriaFechaValida($fechaInicio)) {
        apiAuditoriaResponder(400, ['error' => 'La fecha de inicio no es válida.']);
    }
    if ($fechaFin !== '' && !apiAuditoriaFechaValida($fechaFin)) {
        apiAuditoriaResponder(400, ['error' => 'La fecha de término no es válida.']);
    }
    if ($fechaInicio !== '' && $fechaFin !== '' && $fechaInicio > $fechaFin) {
        apiAuditoriaResponder(400, ['error' => 'La fecha de inicio no puede superar la fecha de término.']);
    }

    $page = filter_var(apiAuditoriaParametro('page') ?: '1', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $perPage = filter_var(apiAuditoriaParametro('per_page') ?: '20', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100]]);
    if ($page === false || $perPage === false) {
        apiAuditoriaResponder(400, ['error' => 'Paginación inválida.']);
    }

    $where = [];
    $params = [];
    if ($fechaInicio !== '') {
        $where[] = 'fecha_evento >= :fecha_inicio';
        $params[':fecha_inicio'] = $fechaInicio . ' 00:00:00';
    }
    if ($fechaFin !== '') {
        $where[] = 'fecha_evento < DATE_ADD(:fecha_fin, INTERVAL 1 DAY)';
        $params[':fecha_fin'] = $fechaFin . ' 00:00:00';
    }
    foreach (['modulo', 'tipo_evento'] as $filter) {
        $value = apiAuditoriaParametro($filter);
        if ($value !== '') {
            $where[] = $filter . ' = :' . $filter;
            $params[':' . $filter] = auditoriaLimitarTexto($value, 80);
        }
    }
    $usuario = apiAuditoriaParametro('usuario');
    if ($usuario !== '') {
        $where[] = '(nombre_usuario LIKE :usuario OR CAST(id_usuario_responsable AS CHAR) = :usuario_id)';
        $params[':usuario'] = '%' . auditoriaLimitarTexto($usuario, 150) . '%';
        $params[':usuario_id'] = $usuario;
    }
    $registro = apiAuditoriaParametro('id_registro_afectado');
    if ($registro !== '') {
        $where[] = 'id_registro_afectado = :id_registro_afectado';
        $params[':id_registro_afectado'] = auditoriaLimitarTexto($registro, 100);
    }

    $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);
    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM auditoria_eventos' . $whereSql);
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;
    $stmt = $pdo->prepare(
        'SELECT id_evento, modulo, tipo_evento, descripcion, id_registro_afectado,
                id_usuario_responsable, nombre_usuario, ip_origen, fecha_evento
         FROM auditoria_eventos' . $whereSql . '
         ORDER BY fecha_evento DESC, id_evento DESC
         LIMIT :limite OFFSET :desplazamiento'
    );
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limite', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':desplazamiento', $offset, PDO::PARAM_INT);
    $stmt->execute();

    apiAuditoriaResponder(200, [
        'eventos' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'paginacion' => [
            'pagina' => $page,
            'por_pagina' => $perPage,
            'total' => $total,
            'total_paginas' => $totalPages,
        ],
    ]);
} catch (Throwable $exception) {
    error_log('Error en API de auditoría: ' . $exception->getMessage());
    apiAuditoriaResponder(500, ['error' => 'No fue posible consultar la auditoría.']);
}
