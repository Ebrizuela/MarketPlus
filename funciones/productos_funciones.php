<?php

function codigoProductoExiste($pdo, $codigo) {
    $codigo = trim((string) $codigo);
    if ($codigo === '') {
        return false;
    }
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM Productos WHERE LOWER(codigo) = LOWER(?)');
    $stmt->execute([$codigo]);
    return (int) $stmt->fetchColumn() > 0;
}


function asegurarIndiceUnicoCodigoProducto($pdo) {
    static $verificado = false;
    if ($verificado) {
        return;
    }
    $verificado = true;

    try {
        $stmt = $pdo->query("SHOW INDEX FROM Productos WHERE Key_name = 'uq_productos_codigo'");
        if ($stmt && $stmt->rowCount() > 0) {
            return;
        }
        $pdo->exec('ALTER TABLE Productos ADD UNIQUE INDEX uq_productos_codigo (codigo)');
    } catch (PDOException $e) {
        error_log('No se pudo crear el índice único de Productos.codigo: ' . $e->getMessage());
    }
}


 
function auditarIntentoProducto($pdo, $codigo, $resultado, $detalle = null) {
    try {
        $stmt = $pdo->prepare('INSERT INTO auditoria (usuario_id, accion, detalles, ip, user_agent)
                               VALUES (:usuario_id, :accion, :detalles, :ip, :user_agent)');
        $stmt->execute([
            ':usuario_id' => $_SESSION['usuario']['id'] ?? null,
            ':accion' => 'crear_producto',
            ':detalles' => json_encode([
                'codigo' => $codigo,
                'resultado' => $resultado,
                'detalle' => $detalle,
            ], JSON_UNESCAPED_UNICODE),
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ':user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : null,
        ]);
    } catch (Throwable $e) {
        error_log('No se pudo registrar la auditoría de producto: ' . $e->getMessage());
    }
}
