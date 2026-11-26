<?php

function obtenerUsuarioSesionVentas(): ?array
{
    $usuario = $_SESSION['usuario'] ?? null;
    if (!is_array($usuario)) {
        return null;
    }

    $idFuncionario = filter_var(
        $usuario['id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );
    $rol = $usuario['rol'] ?? null;
    if (
        $idFuncionario === false
        || $idFuncionario === null
        || !is_string($rol)
    ) {
        return null;
    }

    $rol = strtolower(trim($rol));
    if (!in_array($rol, ['administrador', 'vendedor'], true)) {
        return null;
    }

    $nombre = $usuario['nombre'] ?? '';

    return [
        'id_funcionario' => (int) $idFuncionario,
        'nombre' => is_string($nombre) ? trim($nombre) : '',
        'rol' => $rol
    ];
}

function funcionarioTieneAccesoVentas(PDO $pdo, int $idFuncionario, string $rol): bool
{
    $rol = strtolower(trim($rol));
    if (!in_array($rol, ['administrador', 'vendedor'], true)) {
        return false;
    }

    $stmt = $pdo->prepare(
        "SELECT f.id_funcionario
         FROM funcionarios f
         INNER JOIN Roles r ON r.id_rol = f.id_rol
         WHERE f.id_funcionario = :id
           AND f.activo = 1
           AND r.estado = 'Activo'
           AND LOWER(TRIM(r.nombre)) = :rol
           AND LOWER(TRIM(r.nombre)) IN ('administrador', 'vendedor')
         LIMIT 1"
    );
    $stmt->execute([
        ':id' => $idFuncionario,
        ':rol' => $rol
    ]);

    return $stmt->fetchColumn() !== false;
}
