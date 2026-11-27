<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function responderNoAutorizado(): void
{
    session_unset();
    session_destroy();
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Sesión no autorizada'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$idFuncionarioSesion = filter_var(
    $_SESSION['id_funcionario'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
$rolSesion = $_SESSION['rol'] ?? null;
if (
    $idFuncionarioSesion === false
    || $idFuncionarioSesion === null
    || !is_string($rolSesion)
    || !in_array($rolSesion, ['Administrador', 'Vendedor'], true)
) {
    responderNoAutorizado();
}
$idFuncionarioSesion = (int) $idFuncionarioSesion;
$rolSesion = (string) $rolSesion;

try {
    require_once '../funciones/conexion.php';
    require_once '../funciones/autorizacion_ventas.php';
    $pdo = conectar();
    if (!funcionarioTieneAccesoVentas($pdo, $idFuncionarioSesion, $rolSesion)) {
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