<?php

if (!defined('ID_ROL_VENDEDOR')) {
    define('ID_ROL_VENDEDOR', 3);
}


function obtenerVendedores($pdo) {
    $sql = "SELECT f.id_funcionario, f.nombre, f.apellido, f.correo, f.id_rol, r.nombre AS nombre_rol
            FROM funcionarios f
            INNER JOIN Roles r ON f.id_rol = r.id_rol
            WHERE r.nombre = 'Vendedor'
            ORDER BY f.id_funcionario ASC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
