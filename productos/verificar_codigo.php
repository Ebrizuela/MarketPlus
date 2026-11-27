<?php

require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/productos_funciones.php';

header('Content-Type: application/json; charset=utf-8');


if (!estaLogueado() || !esAdmin()) {
    http_response_code(403);
    echo json_encode(['existe' => null, 'mensaje' => 'No tienes permisos para validar códigos de producto.']);
    exit();
}

try {
    $pdo = conectar();
} catch (PDOException $e) {
    http_response_code(503);
    echo json_encode(['existe' => null, 'mensaje' => 'No se pudo conectar con la base de datos.']);
    exit();
}

$codigo = trim($_GET['codigo'] ?? '');
$existe = codigoProductoExiste($pdo, $codigo);

echo json_encode([
    'existe'  => $existe,
    'mensaje' => $existe ? 'El código ingresado ya existe. Ingrese un código único.' : '',
]);
