<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/autorizacion_ventas.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function responderNoAutorizado(): void
{
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Sesión no autorizada'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$usuarioSesion = obtenerUsuarioSesionVentas();
if ($usuarioSesion === null) {
    responderNoAutorizado();
}

try {
    require_once __DIR__ . '/../funciones/conexion.php';
    $pdo = conectar();
    if (!funcionarioTieneAccesoVentas(
        $pdo,
        $usuarioSesion['id_funcionario'],
        $usuarioSesion['rol']
    )) {
        responderNoAutorizado();
    }

    $stmt = $pdo->query(
        'SELECT id_producto, nombre, precio, stock
         FROM Productos
         WHERE stock > 0 AND activo = 1
         ORDER BY nombre'
    );
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $json = json_encode($productos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    if ($json === false) {
        throw new RuntimeException('No fue posible serializar la lista de productos.');
    } else {
        echo $json;
    }

} catch (PDOException $e) {
    http_response_code(500);
    error_log('Error al cargar productos: ' . $e->getMessage());
    echo json_encode(['error' => 'No fue posible cargar los productos.']);
} catch (Throwable $e) {
    http_response_code(500);
    error_log('Error al cargar productos: ' . $e->getMessage());
    echo json_encode(['error' => 'No fue posible cargar los productos.']);
}