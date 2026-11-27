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
        'status' => 'error',
        'message' => 'Sesión no autorizada.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function responder(array $respuesta, int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function campoClienteValido($valor): ?string
{
    if (!is_string($valor)) {
        return null;
    }

    $valor = trim($valor);
    $coincidencias = [];
    $longitud = preg_match_all('/./us', $valor, $coincidencias);
    if ($valor === '' || $longitud === false || $longitud > 45 || preg_match('/[\x00-\x1F\x7F]/u', $valor)) {
        return null;
    }

    return $valor;
}

$idFuncionario = filter_var(
    $_SESSION['id_funcionario'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
$rol = is_string($_SESSION['rol'] ?? null) ? strtolower(trim($_SESSION['rol'])) : null;
if (
    $idFuncionario === false
    || $idFuncionario === null
    || !is_string($rol)
    || !in_array($rol, ['administrador', 'vendedor'], true)
) {
    responderNoAutorizado();
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
if (
    !is_string($tokenSesion)
    || $tokenSesion === ''
    || !is_string($tokenSolicitud)
    || !hash_equals($tokenSesion, $tokenSolicitud)
) {
    responder(['status' => 'error', 'message' => 'La sesión expiró o la solicitud no es válida. Recargue la página.'], 403);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
    responder(['status' => 'error', 'message' => 'La solicitud contiene JSON inválido.'], 400);
}

$rut = $data['rut'] ?? null;
$nombre = campoClienteValido($data['nombre'] ?? null);
$apellidos = campoClienteValido($data['apellidos'] ?? null);
$correo = campoClienteValido($data['correo'] ?? null);
$telefono = campoClienteValido($data['telefono'] ?? null);
require_once '../funciones/validaciones.php';
if (!is_string($rut) || !validarRut($rut)) {
    responder(['status' => 'error', 'message' => 'Ingrese un RUT válido, incluido su dígito verificador.'], 422);
}
if ($nombre === null || $apellidos === null || $correo === null || $telefono === null) {
    responder(['status' => 'error', 'message' => 'Complete todos los campos con valores válidos de hasta 45 caracteres.'], 422);
}
if (filter_var($correo, FILTER_VALIDATE_EMAIL) === false) {
    responder(['status' => 'error', 'message' => 'Ingrese un correo electrónico válido.'], 422);
}

$rutNormalizado = normalizarRut($rut);
$rutCanonico = substr($rutNormalizado, 0, -1) . '-' . substr($rutNormalizado, -1);
$rutNormalizadoDb = "UPPER(REPLACE(REPLACE(REPLACE(rut, '.', ''), '-', ''), ' ', ''))";

require_once '../funciones/conexion.php';
require_once '../funciones/autorizacion_ventas.php';

try {
    $pdo = conectar();
    if (!funcionarioTieneAccesoVentas($pdo, (int) $idFuncionario, $rol)) {
        responderNoAutorizado();
    }

    $pdo->beginTransaction();
    $stmtExistente = $pdo->prepare(
        "SELECT id_cliente
         FROM Clientes
         WHERE {$rutNormalizadoDb} = :rut
         LIMIT 2
         FOR UPDATE"
    );
    $stmtExistente->execute([':rut' => $rutNormalizado]);
    $clientes = $stmtExistente->fetchAll(PDO::FETCH_COLUMN);
    if (count($clientes) > 0) {
        $pdo->rollBack();
        responder([
            'status' => 'error',
            'message' => count($clientes) > 1
                ? 'Este RUT ya está asociado a registros duplicados. Solicite la revisión de los datos.'
                : 'Ya existe un cliente con este RUT. Verifíquelo para seleccionarlo.'
        ], 409);
    }

    $claveAleatoria = bin2hex(random_bytes(32));
    $hashClave = password_hash($claveAleatoria, PASSWORD_DEFAULT);
    if (!is_string($hashClave)) {
        throw new RuntimeException('No fue posible proteger la credencial del cliente.');
    }

    $stmtCrear = $pdo->prepare(
        'INSERT INTO Clientes (nombre, apellidos, rut, correo, telefono, clave)
         VALUES (:nombre, :apellidos, :rut, :correo, :telefono, :clave)'
    );
    $stmtCrear->execute([
        ':nombre' => $nombre,
        ':apellidos' => $apellidos,
        ':rut' => $rutCanonico,
        ':correo' => $correo,
        ':telefono' => $telefono,
        ':clave' => $hashClave
    ]);
    $idCliente = (int) $pdo->lastInsertId();
    $pdo->commit();

    responder([
        'status' => 'success',
        'cliente' => [
            'id_cliente' => $idCliente,
            'nombre' => $nombre,
            'apellidos' => $apellidos
        ]
    ], 201);
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if (($e->errorInfo[1] ?? null) === 1062) {
        responder(['status' => 'error', 'message' => 'Ya existe un cliente con este RUT. Verifíquelo para seleccionarlo.'], 409);
    }
    error_log('Error al crear cliente durante una venta: ' . $e->getMessage());
    responder(['status' => 'error', 'message' => 'No fue posible crear el cliente. Intente nuevamente.'], 500);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Error al crear cliente durante una venta: ' . $e->getMessage());
    responder(['status' => 'error', 'message' => 'No fue posible crear el cliente. Intente nuevamente.'], 500);
}
