<?php
require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../funciones/recuperacion_funciones.php';

try {
    $pdo = conectar();
} catch (PDOException $e) {
    error_log('Error de conexión al restablecer contraseña: ' . $e->getMessage());
    http_response_code(503);
    exit('No se pudo conectar con la base de datos. Inténtalo más tarde.');
}

$tokenEntrada = filter_input(INPUT_GET, 'token', FILTER_DEFAULT)
    ?? filter_input(INPUT_POST, 'token', FILTER_DEFAULT);
$token = is_string($tokenEntrada) ? $tokenEntrada : '';
$mensaje = '';
$tipo_mensaje = '';
$token_valido = false;
$datos_token = null;

// Validar token y expiración de 30 minutos a través de la capa de funciones
try {
    $validacion = obtenerTokenValido($pdo, $token);
} catch (PDOException $e) {
    error_log('Error validando token de recuperación: ' . $e->getMessage());
    http_response_code(503);
    exit('No se pudo validar el enlace. Inténtalo más tarde.');
}

if (!$validacion['valido']) {
    $mensaje = $validacion['error'];
    $tipo_mensaje = 'danger';
} else {
    $token_valido = true;
    $datos_token = $validacion['datos'];
}

// Procesar el cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valido) {
    $clave1 = $_POST['clave1'] ?? '';
    $clave2 = $_POST['clave2'] ?? '';

    if ($clave1 !== $clave2) {
        $mensaje = 'Las contraseñas no coinciden.';
        $tipo_mensaje = 'danger';
    } elseif (!validarFortalezaClave($clave1)) {
        $mensaje = 'La contraseña debe tener entre 12 y 72 bytes, con al menos 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial.';
        $tipo_mensaje = 'warning';
    } else {
        $actualizado = actualizarClaveUsuario($pdo, $datos_token['correo'], $datos_token['id_recuperacion'], $clave1);

        if ($actualizado) {
            $mensaje = '¡Contraseña restablecida exitosamente! Ya puedes iniciar sesión con tu nueva clave.';
            $tipo_mensaje = 'success';
            $token_valido = false; // Desactivar el formulario
        } else {
            $mensaje = 'Ocurrió un error al actualizar la contraseña en el sistema.';
            $tipo_mensaje = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlus - Definir Nueva Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/estilos.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/marketplus-theme.css?v=1">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card card-custom p-3 bg-white">
                <div class="card-body">
                    <h3 class="card-title text-center mb-1 text-primary fw-bold">MarketPlus</h3>
                    <p class="text-center text-muted mb-4 small">Restablecimiento Seguro de Contraseña</p>

                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?= $tipo_mensaje; ?> small" role="alert">
                            <?= htmlspecialchars($mensaje); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($token_valido): ?>
                        <form method="POST" action="" id="formRestablecer" novalidate>
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">

                            <div class="mb-3">
                                <label for="clave1" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" id="clave1" name="clave1" required autocomplete="new-password">
                                
                                <div class="mt-2">
                                    <span id="req-longitud" class="badge-requisito requisito-invalido">12+ caracteres</span>
                                    <span id="req-mayus" class="badge-requisito requisito-invalido">Mayúscula</span>
                                    <span id="req-minus" class="badge-requisito requisito-invalido">Minúscula</span>
                                    <span id="req-numero" class="badge-requisito requisito-invalido">Número</span>
                                    <span id="req-simbolo" class="badge-requisito requisito-invalido">Símbolo</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="clave2" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="clave2" name="clave2" required autocomplete="new-password">
                                <div id="feedbackCoincidencia"></div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Guardar nueva contraseña</button>
                        </form>
                    <?php else: ?>
                        <div class="text-center mt-3">
                            <a href="solicitar_recuperacion.php" class="btn btn-outline-secondary btn-sm">Solicitar un nuevo enlace</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../js/recuperacion.js"></script>
</body>
</html>