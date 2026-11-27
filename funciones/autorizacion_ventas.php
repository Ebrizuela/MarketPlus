<?php

function funcionarioTieneAccesoVentas(PDO $pdo, int $idFuncionario, string $rol): bool
{
    $rol = strtolower(trim($rol));
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
