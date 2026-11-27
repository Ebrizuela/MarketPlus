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
$rolSesion = is_string($_SESSION['rol'] ?? null) ? strtolower(trim($_SESSION['rol'])) : null;
if (
    $idFuncionarioSesion === false
    || $idFuncionarioSesion === null
    || !is_string($rolSesion)
    || !in_array($rolSesion, ['administrador', 'vendedor'], true)
) {
    responderNoAutorizado();
}
$idFuncionarioSesion = (int) $idFuncionarioSesion;
$rolSesion = (string) $rolSesion;

function responder(array $respuesta, int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function enteroPositivo($valor): ?int
{
    if (!is_int($valor) && !is_string($valor)) {
        return null;
    }

    $entero = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $entero === false ? null : $entero;
}

function formatearCentavos(int $centavos): string
{
    return intdiv($centavos, 100) . '.' . str_pad((string) ($centavos % 100), 2, '0', STR_PAD_LEFT);
}

class ErrorValidacionVenta extends RuntimeException
{
    public function __construct(string $mensaje, int $codigoHttp)
    {
        parent::__construct($mensaje, $codigoHttp);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(['status' => 'error', 'message' => 'Método no permitido.'], 405);
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== 0) {
    responder(['status' => 'error', 'message' => 'El contenido de la solicitud debe ser JSON.'], 415);
}

$tokenSesion = $_SESSION['csrf_token'] ?? '';
$tokenSolicitud = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!is_string($tokenSesion) || $tokenSesion === '' || !is_string($tokenSolicitud) || !hash_equals($tokenSesion, $tokenSolicitud)) {
    responder(['status' => 'error', 'message' => 'La sesión expiró o la solicitud no es válida. Recargue la página.'], 403);
}

$idFuncionario = $idFuncionarioSesion;

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
    responder(['status' => 'error', 'message' => 'La solicitud contiene JSON inválido.'], 400);
}

$rut = $data['rut'] ?? '';
$idCliente = enteroPositivo($data['id_cliente'] ?? null);
$idMedioPago = enteroPositivo($data['id_medio_pago'] ?? null);
$productos = $data['productos'] ?? null;

require_once '../funciones/validaciones.php';
require_once '../funciones/autorizacion_ventas.php';
if (!is_string($rut) || trim($rut) === '' || !validarRut($rut)) {
    responder(['status' => 'error', 'message' => 'El RUT es obligatorio y debe tener un dígito verificador válido.'], 422);
}
if ($idCliente === null || $idMedioPago === null) {
    responder(['status' => 'error', 'message' => 'Seleccione un cliente y un medio de pago válidos.'], 422);
}
if (!is_array($productos) || count($productos) === 0) {
    responder(['status' => 'error', 'message' => 'La venta debe incluir al menos un producto.'], 422);
}

$productosSolicitados = [];
foreach ($productos as $producto) {
    if (!is_array($producto)) {
        responder(['status' => 'error', 'message' => 'La lista contiene un producto inválido.'], 422);
    }

    $idProducto = enteroPositivo($producto['id_producto'] ?? null);
    $cantidad = enteroPositivo($producto['cantidad'] ?? null);
    if ($idProducto === null || $cantidad === null || isset($productosSolicitados[$idProducto])) {
        responder(['status' => 'error', 'message' => 'Cada producto debe ser válido, tener cantidad positiva y aparecer una sola vez.'], 422);
    }
    $productosSolicitados[$idProducto] = $cantidad;
}

ksort($productosSolicitados, SORT_NUMERIC);
$ipOrigen = $_SERVER['REMOTE_ADDR'] ?? '';
if (!filter_var($ipOrigen, FILTER_VALIDATE_IP)) {
    $ipOrigen = 'desconocida';
}
$rutNormalizado = normalizarRut($rut);
$rutNormalizadoDb = "UPPER(REPLACE(REPLACE(REPLACE(rut, '.', ''), '-', ''), ' ', ''))";

require_once '../funciones/conexion.php';

try {
    $pdo = conectar();
    if (!funcionarioTieneAccesoVentas($pdo, $idFuncionario, $rolSesion)) {
        responderNoAutorizado();
    }

    $pdo->beginTransaction();

    $stmtCliente = $pdo->prepare(
        "SELECT id_cliente
         FROM Clientes
         WHERE {$rutNormalizadoDb} = :rut
         LIMIT 2
         FOR UPDATE"
    );
    $stmtCliente->execute([':rut' => $rutNormalizado]);
    $clientes = $stmtCliente->fetchAll(PDO::FETCH_COLUMN);
    if (count($clientes) > 1) {
        throw new ErrorValidacionVenta('Este RUT tiene registros duplicados. Solicite la revisión de los datos antes de vender.', 409);
    }
    if (count($clientes) !== 1 || (int) $clientes[0] !== $idCliente) {
        throw new ErrorValidacionVenta('El cliente no corresponde al RUT validado. Verifíquelo nuevamente.', 422);
    }

    $stmtPago = $pdo->prepare('SELECT id_medio_pago FROM Medio_pago WHERE id_medio_pago = :id');
    $stmtPago->execute([':id' => $idMedioPago]);
    if (!$stmtPago->fetchColumn()) {
        throw new ErrorValidacionVenta('El medio de pago seleccionado no es válido.', 422);
    }

    $stmtProducto = $pdo->prepare(
        'SELECT id_producto, nombre, precio, stock
         FROM Productos
         WHERE id_producto = :id AND activo = 1
         FOR UPDATE'
    );
    $lineas = [];
    $totalCentavos = 0;
    $maximoCentavos = 9999999999;

    foreach ($productosSolicitados as $idProducto => $cantidad) {
        $stmtProducto->execute([':id' => $idProducto]);
        $producto = $stmtProducto->fetch(PDO::FETCH_ASSOC);
        if (!$producto) {
            throw new ErrorValidacionVenta('Uno de los productos no está disponible. Actualice la página e intente nuevamente.', 409);
        }

        $precio = filter_var($producto['precio'], FILTER_VALIDATE_INT);
        $stock = filter_var($producto['stock'], FILTER_VALIDATE_INT);
        if ($precio === false || $precio <= 0 || $stock === false || $stock < 0) {
            throw new ErrorValidacionVenta('Un producto tiene precio o stock inválido. No se registró la venta.', 409);
        }
        if ($cantidad > $stock) {
            throw new ErrorValidacionVenta('Stock insuficiente para ' . $producto['nombre'] . '. Disponible: ' . $stock . '.', 409);
        }

        $precioCentavos = $precio * 100;
        if ($precioCentavos <= 0 || $cantidad > intdiv($maximoCentavos - $totalCentavos, $precioCentavos)) {
            throw new ErrorValidacionVenta('El total de la venta excede el máximo permitido.', 422);
        }
        $subtotalCentavos = $precioCentavos * $cantidad;
        $totalCentavos += $subtotalCentavos;
        $lineas[] = [
            'id_producto' => (int) $producto['id_producto'],
            'nombre' => (string) $producto['nombre'],
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'subtotal_centavos' => $subtotalCentavos
        ];
    }

    $stmtVenta = $pdo->prepare(
        'INSERT INTO Ventas
         (id_funcionario, id_cliente, id_medio_pago, total_venta, fecha_hora, ip_origen, logs_seguridad)
         VALUES (:funcionario, :cliente, :medio_pago, :total, NOW(), :ip, :log)'
    );
    $stmtVenta->execute([
        ':funcionario' => $idFuncionario,
        ':cliente' => $idCliente,
        ':medio_pago' => $idMedioPago,
        ':total' => formatearCentavos($totalCentavos),
        ':ip' => $ipOrigen,
        ':log' => 'Venta registrada por funcionario ' . $idFuncionario . ' desde IP ' . $ipOrigen
    ]);
    $idVenta = (int) $pdo->lastInsertId();

    $stmtDetalle = $pdo->prepare(
        'INSERT INTO Detalle_Ventas (id_ventas, id_producto, cantidad, precio_unitario, subtotal)
         VALUES (:venta, :producto, :cantidad, :precio, :subtotal)'
    );
    $stmtActualizarStock = $pdo->prepare(
        'UPDATE Productos
         SET stock = stock - :cantidad_restar
         WHERE id_producto = :producto AND activo = 1 AND stock >= :cantidad_minima'
    );

    foreach ($lineas as $linea) {
        $stmtDetalle->execute([
            ':venta' => $idVenta,
            ':producto' => $linea['id_producto'],
            ':cantidad' => $linea['cantidad'],
            ':precio' => number_format($linea['precio_unitario'], 2, '.', ''),
            ':subtotal' => formatearCentavos($linea['subtotal_centavos'])
        ]);
        $stmtActualizarStock->execute([
            ':cantidad_restar' => $linea['cantidad'],
            ':producto' => $linea['id_producto'],
            ':cantidad_minima' => $linea['cantidad']
        ]);
        if ($stmtActualizarStock->rowCount() !== 1) {
            throw new RuntimeException('El stock cambió durante el registro de la venta.');
        }
    }

    $detallesAuditoria = [
        'modulo' => 'ventas',
        'id_venta' => $idVenta,
        'id_cliente' => $idCliente,
        'id_medio_pago' => $idMedioPago,
        'total' => formatearCentavos($totalCentavos),
        'productos' => array_map(
            static function (array $linea): array {
                return [
                    'id_producto' => $linea['id_producto'],
                    'nombre' => $linea['nombre'],
                    'cantidad' => $linea['cantidad'],
                    'precio_unitario' => $linea['precio_unitario'],
                    'subtotal' => formatearCentavos($linea['subtotal_centavos'])
                ];
            },
            $lineas
        )
    ];
    $detallesAuditoriaJson = json_encode($detallesAuditoria, JSON_INVALID_UTF8_SUBSTITUTE);
    if ($detallesAuditoriaJson === false) {
        throw new RuntimeException('No fue posible preparar la auditoría de la venta.');
    }

    $stmtAuditoria = $pdo->prepare(
        'INSERT INTO auditoria (usuario_id, accion, detalles, ip, fecha)
         VALUES (:usuario, :accion, :detalles, :ip, NOW())'
    );
    $stmtAuditoria->execute([
        ':usuario' => $idFuncionario,
        ':accion' => 'registrar_venta',
        ':detalles' => $detallesAuditoriaJson,
        ':ip' => $ipOrigen
    ]);

    $pdo->commit();
    responder([
        'status' => 'success',
        'message' => 'Venta registrada con éxito.',
        'id_ventas' => $idVenta
    ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Error al registrar venta: ' . $e->getMessage());
    if ($e instanceof ErrorValidacionVenta) {
        responder(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
    }
    responder(['status' => 'error', 'message' => 'No fue posible registrar la venta. No se aplicaron cambios.'], 500);
}
