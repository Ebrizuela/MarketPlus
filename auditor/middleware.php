<?php
declare(strict_types=1);

require_once __DIR__ . '/../funciones/auditoria_helper.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function auditorObtenerIdentidad(PDO $pdo): ?array
{
    $usuario = $_SESSION['usuario'] ?? [];
    $id = (is_array($usuario) ? ($usuario['id'] ?? null) : null)
        ?? $_SESSION['id_funcionario']
        ?? $_SESSION['id_usuario']
        ?? null;
    if (!is_numeric($id) || (int)$id < 1) {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT f.id_funcionario, f.nombre, f.apellido, f.correo, r.nombre AS rol
         FROM funcionarios AS f
         INNER JOIN Roles AS r ON r.id_rol = f.id_rol
         WHERE f.id_funcionario = :id AND f.activo = 1
         LIMIT 1'
    );
    $stmt->execute([':id' => (int)$id]);
    $identity = $stmt->fetch(PDO::FETCH_ASSOC);

    return $identity ?: null;
}

function auditorTieneSesion(): bool
{
    $usuario = $_SESSION['usuario'] ?? [];
    $id = (is_array($usuario) ? ($usuario['id'] ?? null) : null)
        ?? $_SESSION['id_funcionario']
        ?? $_SESSION['id_usuario']
        ?? null;

    return is_numeric($id) && (int)$id > 0;
}

function auditorRolDeSesion(): ?string
{
    $usuario = $_SESSION['usuario'] ?? [];
    $rol = is_array($usuario) ? ($usuario['rol'] ?? null) : null;
    $rol = $rol ?? $_SESSION['rol'] ?? $_SESSION['role'] ?? null;

    return is_string($rol) && $rol !== '' ? $rol : null;
}

function auditorRegistrarRechazo(PDO $pdo, string $motivo): void
{
    $identity = null;
    try {
        $identity = auditorObtenerIdentidad($pdo);
    } catch (Throwable $exception) {
        error_log('No se pudo consultar la identidad al registrar rechazo de auditoría: ' . $exception->getMessage());
    }

    registrarEventoAuditoria(
        $pdo,
        'Seguridad',
        'acceso_denegado',
        $motivo,
        null,
        null,
        ['correo' => $identity['correo'] ?? auditorSesionValor('correo')]
    );

    $correo = $identity['correo']
        ?? auditorSesionValor('correo')
        ?? auditorSesionValor('email')
        ?? $_SESSION['email']
        ?? 'Sin sesión';
    $rol = $identity['rol']
        ?? auditorRolDeSesion()
        ?? $_SESSION['role']
        ?? 'Desconocido';
    $stmt = $pdo->prepare(
        'INSERT INTO Logs_Seguridad (correo_intento, rol_identificado, estado)
         VALUES (:correo, :rol, :estado)'
    );
    $stmt->execute([
        ':correo' => auditoriaLimitarTexto((string)$correo, 100),
        ':rol' => auditoriaLimitarTexto((string)$rol, 50),
        ':estado' => 'Fallido',
    ]);
}

function auditorSesionValor(string $clave): mixed
{
    $usuario = $_SESSION['usuario'] ?? [];
    $valor = is_array($usuario) ? ($usuario[$clave] ?? null) : null;

    return $valor ?? $_SESSION[$clave] ?? null;
}

function auditorRequerirRol(PDO $pdo): ?array
{
    if (!auditorTieneSesion()) {
        header('Location: ../login.php');
        exit;
    }

    $identity = auditorObtenerIdentidad($pdo);
    if ($identity === null) {
        header('Location: ../login.php');
        exit;
    }

    $rolSesion = auditorRolDeSesion();
    if (
        strcasecmp((string)$identity['rol'], 'Auditor') !== 0
        || ($rolSesion !== null && strcasecmp($rolSesion, 'Auditor') !== 0)
    ) {
        auditorRegistrarRechazo($pdo, 'Acceso denegado al módulo de auditoría por rol no autorizado.');
        header('Location: ../login.php');
        exit;
    }

    return $identity;
}

function auditorTieneSegundoFactorValido(): bool
{
    if (($_SESSION['auditor_2fa_validado'] ?? false) !== true) {
        return false;
    }

    $validadoEn = $_SESSION['auditor_2fa_validado_en'] ?? null;
    return is_int($validadoEn) && $validadoEn >= time() - 1800;
}

function auditorEstaAutorizado(PDO $pdo): bool
{
    $identity = auditorObtenerIdentidad($pdo);
    $authorized = $identity !== null
        && strcasecmp((string)$identity['rol'], 'Auditor') === 0
        && (auditorRolDeSesion() === null || strcasecmp((string)auditorRolDeSesion(), 'Auditor') === 0)
        && auditorTieneSegundoFactorValido();
    if (!$authorized) {
        unset($_SESSION['auditor_2fa_validado'], $_SESSION['auditor_2fa_validado_en']);
    }

    return $authorized;
}

if (!auditorTieneSesion()) {
    header('Location: ../login.php');
    exit;
}
