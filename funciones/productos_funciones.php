<?php

function asegurarIndiceUnicoCodigoProducto(PDO $pdo): void {
    try {
        $stmt = $pdo->query("SHOW INDEX FROM Productos WHERE Key_name = 'uq_productos_codigo'");
        if ($stmt && $stmt->rowCount() > 0) {
            return;
        }
        $pdo->exec("ALTER TABLE Productos ADD UNIQUE KEY uq_productos_codigo (codigo)");
    } catch (PDOException $e) {
       
        error_log('No se pudo asegurar el índice único uq_productos_codigo: ' . $e->getMessage());
    }
}

function codigoProductoExiste(PDO $pdo, string $codigo, ?int $excluirId = null): bool {
    $codigo = trim($codigo);
    if ($codigo === '') {
        return false;
    }

    try {
        $sql = "SELECT COUNT(*) FROM Productos WHERE LOWER(codigo) = LOWER(?)";
        $parametros = [$codigo];
        if ($excluirId !== null) {
            $sql .= " AND id_producto <> ?";
            $parametros[] = $excluirId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametros);
        return (int)$stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log('Error al validar la unicidad del código de producto: ' . $e->getMessage());
        return false;
    }
}

function auditarIntentoProducto(PDO $pdo, string $codigo, string $resultado, string $detalle = ''): void {
    try {
        $usuarioId = $_SESSION['usuario']['id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

        $detalles = json_encode([
            'codigo'    => $codigo,
            'resultado' => $resultado,
            'detalle'   => $detalle,
        ], JSON_UNESCAPED_UNICODE);

        $stmt = $pdo->prepare("INSERT INTO auditoria (usuario_id, accion, detalles, ip, user_agent, fecha)
                               VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$usuarioId, 'producto_creacion', $detalles, $ip, $userAgent]);
    } catch (PDOException $e) {
        error_log('No se pudo auditar el intento de creación de producto: ' . $e->getMessage());
    }
}
