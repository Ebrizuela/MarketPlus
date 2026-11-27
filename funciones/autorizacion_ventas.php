<?php

function funcionarioTieneAccesoVentas(PDO $pdo, int $idFuncionario, string $rol): bool
{
    $stmt = $pdo->prepare(
        "SELECT f.id_funcionario
         FROM funcionarios f
         INNER JOIN Roles r ON r.id_rol = f.id_rol
         WHERE f.id_funcionario = :id
           AND f.activo = 1
           AND r.estado = 'Activo'
           AND r.nombre = :rol
           AND r.nombre IN ('Administrador', 'Vendedor')
         LIMIT 1"
    );
    $stmt->execute([
        ':id' => $idFuncionario,
        ':rol' => $rol
    ]);

    return $stmt->fetchColumn() !== false;
}
