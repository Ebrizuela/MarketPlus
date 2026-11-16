<?php
require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../funciones/recuperacion_funciones.php';

try {
    $pdo = conectar();
} catch (PDOException $e) {
    error_log('Error de conexión al solicitar recuperación de contraseña: ' . $e->getMessage());
    http_response_code(503);
    exit('No se pudo conectar con la base de datos. Inténtalo más tarde.');
}

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = filter_input(INPUT_POST, 'correo', FILTER_VALIDATE_EMAIL);

    if (!$correo) {
        $mensaje = 'Por favor, ingresa un correo electrónico válido.';
        $tipo_mensaje = 'danger';
    } else {
        $correo = strtolower(trim($correo));
        try {
            $stmtFunc = $pdo->prepare("SELECT correo FROM funcionarios WHERE correo = :correo");
            $stmtFunc->execute(['correo' => $correo]);
            $existeEnFunc = $stmtFunc->fetch();

            $stmtCli = $pdo->prepare("SELECT correo FROM Clientes WHERE correo = :correo");
            $stmtCli->execute(['correo' => $correo]);
            $existeEnCli = $stmtCli->fetch();

            $cuentaEncontrada = (bool) ($existeEnFunc || $existeEnCli);
            error_log('[recuperacion] cuenta_encontrada=' . ($cuentaEncontrada ? 'si' : 'no'));

            if ($cuentaEncontrada) {
                try {
                    generarTokenRecuperacion($pdo, $correo);
                    error_log('[recuperacion] envio_aceptado_por_transporte=si');
                } catch (Throwable $e) {
                    error_log('[recuperacion] solicitud_fallida=' . $e->getMessage());
                }
            }

            $mensaje = 'Si el correo electrónico está registrado, recibirás un enlace seguro de recuperación válido durante 30 minutos. Si no llega, verifica la dirección o contacta al administrador.';
            $tipo_mensaje = 'success';
        } catch (PDOException $e) {
            error_log('Error consultando cuenta para recuperación: ' . $e->getMessage());
            http_response_code(503);
            $mensaje = 'No se pudo completar la solicitud. Inténtalo más tarde.';
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
    <title>MarketPlus - Recuperar Contraseña</title>
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
                    <p class="text-center text-muted mb-4 small">Recuperación de Contraseña</p>

                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?= $tipo_mensaje; ?> small" role="alert">
                            <?= htmlspecialchars($mensaje); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo electrónico registrado</label>
                            <input type="email" class="form-control" id="correo" name="correo" placeholder="ejemplo@marketplus.cl" required autocomplete="email">
                            <div class="form-text">Ingresa la dirección con la que accedes al sistema.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Enviar enlace de recuperación</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>