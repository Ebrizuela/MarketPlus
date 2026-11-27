<?php
declare(strict_types=1);

require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/middleware.php';

$pdo = conectar();
$identity = auditorRequerirRol($pdo);

if (auditorTieneSegundoFactorValido()) {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['auditor_csrf'])) {
    $_SESSION['auditor_csrf'] = bin2hex(random_bytes(32));
}
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $csrf = (string)($_POST['csrf'] ?? '');
    $token = trim((string)($_POST['token'] ?? ''));
    if (!hash_equals((string)$_SESSION['auditor_csrf'], $csrf)) {
        $error = 'La solicitud expiró. Recarga la página e inténtalo nuevamente.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT auditor_token_hash
             FROM funcionarios
             WHERE id_funcionario = :id AND activo = 1
             LIMIT 1'
        );
        $stmt->execute([':id' => (int)$identity['id_funcionario']]);
        $tokenHash = $stmt->fetchColumn();
        $submittedHash = hash('sha256', $token);

        if (is_string($tokenHash) && $token !== '' && hash_equals($tokenHash, $submittedHash)) {
            session_regenerate_id(true);
            $_SESSION['auditor_2fa_validado'] = true;
            $_SESSION['auditor_2fa_validado_en'] = time();
            unset($_SESSION['auditor_csrf']);

            registrarEventoAuditoria($pdo, 'Autenticacion', '2FA_EXITOSO', 'Segundo factor de Auditor validado.');
            $log = $pdo->prepare(
                'INSERT INTO Logs_Seguridad (correo_intento, rol_identificado, estado)
                 VALUES (:correo, :rol, :estado)'
            );
            $log->execute([
                ':correo' => $identity['correo'],
                ':rol' => 'Auditor',
                ':estado' => 'Exitoso',
            ]);

            header('Location: index.php');
            exit;
        }

        auditorRegistrarRechazo($pdo, 'Token de segundo factor inválido para una sesión con rol Auditor.');
        $error = 'Token incorrecto. Verifica el código e inténtalo nuevamente.';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validación de acceso | MarketPlus</title>
    <link rel="stylesheet" href="../css/auditoria.css">
</head>
<body class="auditoria-login">
    <main class="token-card">
        <div class="token-mark" aria-hidden="true">MP</div>
        <p class="eyebrow">MarketPlus · Seguridad</p>
        <h1>Verificación de identidad</h1>
        <p class="token-intro">Ingresa el token de segundo factor para continuar al registro de auditoría.</p>
        <?php if ($error !== ''): ?>
            <p class="form-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form method="post" action="validar_token.php" autocomplete="off">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['auditor_csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <label for="token">Token de validación</label>
            <input id="token" name="token" type="password" inputmode="text" required maxlength="128"
                   autocomplete="one-time-code" autofocus>
            <button type="submit" class="button button-primary">Validar y continuar</button>
        </form>
        <p class="security-note">La verificación expira después de 30 minutos de sesión.</p>
    </main>
</body>
</html>
