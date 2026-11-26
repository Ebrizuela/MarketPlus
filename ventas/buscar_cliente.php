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
$idFuncionarioSesion = $usuarioSesion['id_funcionario'];
$rolSesion = $usuarioSesion['rol'];

require_once __DIR__ . '/../funciones/validaciones.php';
require_once __DIR__ . '/../funciones/conexion.php';

function responder(array $respuesta, int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

$rut = filter_input(INPUT_GET, 'rut', FILTER_UNSAFE_RAW);
if (!is_string($rut) || trim($rut) === '' || !validarRut($rut)) {
    responder(['status' => 'error', 'message' => 'Ingrese un RUT válido, incluido su dígito verificador.'], 422);
}

$rutNormalizado = normalizarRut($rut);
$rutNormalizadoDb = "UPPER(REPLACE(REPLACE(REPLACE(rut, '.', ''), '-', ''), ' ', ''))";

try {
    $pdo = conectar();
    if (!funcionarioTieneAccesoVentas($pdo, $idFuncionarioSesion, $rolSesion)) {
        responderNoAutorizado();
    }

    $stmt = $pdo->prepare(
        "SELECT id_cliente, nombre, apellidos
         FROM Clientes
         WHERE {$rutNormalizadoDb} = :rut
         LIMIT 2"
    );
    $stmt->execute([':rut' => $rutNormalizado]);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($clientes) > 1) {
        responder([
            'status' => 'duplicate',
            'message' => 'Este RUT está asociado a más de un registro. Solicite la revisión de los datos antes de vender.'
        ], 409);
    }

    if (count($clientes) === 1) {
        responder(['status' => 'success', 'cliente' => $clientes[0]]);
    }

    responder(['status' => 'not_found', 'message' => 'No existe un cliente registrado con este RUT.']);
} catch (Throwable $e) {
    error_log('Error al buscar cliente por RUT: ' . $e->getMessage());
    responder(['status' => 'error', 'message' => 'No fue posible buscar al cliente. Intente nuevamente.'], 500);
}