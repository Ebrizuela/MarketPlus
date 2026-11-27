<?php
declare(strict_types=1);

/**
 * Registra un evento en la bitácora inmutable de auditoría.
 *
 * Ejemplos de integración (llamar después de confirmar cada operación):
 *
 * Ventas:
 * registrarEventoAuditoria($pdo, 'Ventas', 'creacion', 'Venta registrada', (string)$idVenta,
 *     null, ['carrito' => $carrito, 'medio_pago' => $medioPago, 'subtotal' => $subtotal,
 *            'impuestos' => $impuestos, 'total' => $total]);
 *
 * Devoluciones:
 * registrarEventoAuditoria($pdo, 'Devoluciones', 'creacion', 'Devolución registrada',
 *     (string)$idDevolucion, null,
 *     ['id_venta' => $idVenta, 'productos' => $productos, 'motivo' => $motivo]);
 *
 * Productos:
 * registrarEventoAuditoria($pdo, 'Productos', 'creacion', 'Producto creado',
 *     (string)$idProducto, null, $productoNuevo);
 * registrarEventoAuditoria($pdo, 'Productos', 'edicion', 'Producto actualizado',
 *     (string)$idProducto, $productoAnterior, $productoNuevo);
 * registrarEventoAuditoria($pdo, 'Productos', 'eliminacion', 'Producto eliminado',
 *     (string)$idProducto, $productoAnterior, null);
 *
 * Funcionarios/usuarios:
 * registrarEventoAuditoria($pdo, 'Funcionarios', 'creacion', 'Funcionario creado',
 *     (string)$idFuncionario, null, $datosFuncionario);
 * registrarEventoAuditoria($pdo, 'Funcionarios', 'cambio_rol', 'Rol actualizado',
 *     (string)$idFuncionario, ['id_rol' => $rolAnterior], ['id_rol' => $rolNuevo]);
 * registrarEventoAuditoria($pdo, 'Funcionarios', 'baja', 'Funcionario desactivado',
 *     (string)$idFuncionario, ['activo' => 1], ['activo' => 0]);
 *
 * Login/logout: establecer los valores de sesión después de autenticar y registrar
 * antes de destruir la sesión al salir.
 * registrarEventoAuditoria($pdo, 'Autenticacion', 'login', 'Inicio de sesión exitoso');
 * registrarEventoAuditoria($pdo, 'Autenticacion', 'logout', 'Cierre de sesión');
 *
 * No incluir contraseñas, hashes, tokens ni secretos dentro de los datos de auditoría.
 */
function auditoriaLimitarTexto(string $texto, int $limite): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($texto, 0, $limite, 'UTF-8');
    }

    if (preg_match('/^.{0,' . $limite . '}/us', $texto, $coincidencias) === 1) {
        return $coincidencias[0];
    }

    return substr($texto, 0, $limite);
}

function auditoriaSanitizarDatos(mixed $datos): mixed
{
    if (is_object($datos)) {
        return (object)auditoriaSanitizarDatos(get_object_vars($datos));
    }
    if (!is_array($datos)) {
        return $datos;
    }

    $sensibles = ['password', 'contrasena', 'clave', 'token', 'secret', 'secreto', 'hash'];
    $resultado = [];
    foreach ($datos as $clave => $valor) {
        if (is_string($clave)) {
            $claveNormalizada = strtolower($clave);
            $esSensible = false;
            foreach ($sensibles as $termino) {
                if (str_contains($claveNormalizada, $termino)) {
                    $esSensible = true;
                    break;
                }
            }
            $resultado[$clave] = $esSensible ? '[REDACTADO]' : auditoriaSanitizarDatos($valor);
        } else {
            $resultado[$clave] = auditoriaSanitizarDatos($valor);
        }
    }

    return $resultado;
}

function registrarEventoAuditoria(
    PDO $pdo,
    string $modulo,
    string $tipo_evento,
    string $descripcion,
    string|int|null $id_registro_afectado = null,
    mixed $datos_previos = null,
    mixed $datos_nuevos = null
): int {
    $idUsuario = $_SESSION['id_funcionario'] ?? $_SESSION['id_usuario'] ?? null;
    if (!is_numeric($idUsuario) || (int)$idUsuario < 1) {
        $idUsuario = null;
    } else {
        $idUsuario = (int)$idUsuario;
    }

    $nombreUsuario = $_SESSION['nombre_usuario']
        ?? $_SESSION['nombre']
        ?? $_SESSION['usuario']
        ?? 'Sin identificar';
    $nombreUsuario = is_string($nombreUsuario) && $nombreUsuario !== ''
        ? auditoriaLimitarTexto($nombreUsuario, 150)
        : 'Sin identificar';

    $ipOrigen = $_SERVER['REMOTE_ADDR'] ?? null;
    if (!is_string($ipOrigen) || filter_var($ipOrigen, FILTER_VALIDATE_IP) === false) {
        $ipOrigen = null;
    }

    $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;
    $previosJson = $datos_previos === null
        ? null
        : json_encode(auditoriaSanitizarDatos($datos_previos), $jsonFlags);
    $nuevosJson = $datos_nuevos === null
        ? null
        : json_encode(auditoriaSanitizarDatos($datos_nuevos), $jsonFlags);

    $stmt = $pdo->prepare(
        'INSERT INTO auditoria_eventos
            (modulo, tipo_evento, descripcion, id_registro_afectado, datos_previos,
             datos_nuevos, id_usuario_responsable, nombre_usuario, ip_origen)
         VALUES
            (:modulo, :tipo_evento, :descripcion, :id_registro_afectado, :datos_previos,
             :datos_nuevos, :id_usuario_responsable, :nombre_usuario, :ip_origen)'
    );
    $stmt->execute([
        ':modulo' => auditoriaLimitarTexto($modulo, 80),
        ':tipo_evento' => auditoriaLimitarTexto($tipo_evento, 80),
        ':descripcion' => $descripcion,
        ':id_registro_afectado' => $id_registro_afectado === null
            ? null
            : (string)$id_registro_afectado,
        ':datos_previos' => $previosJson,
        ':datos_nuevos' => $nuevosJson,
        ':id_usuario_responsable' => $idUsuario,
        ':nombre_usuario' => $nombreUsuario,
        ':ip_origen' => $ipOrigen,
    ]);

    return (int)$pdo->lastInsertId();
}
