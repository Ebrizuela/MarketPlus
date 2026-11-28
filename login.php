<?php
require_once __DIR__ . '/funciones/sesion_segura.php';
iniciarSesionSegura();

if (isset($_SESSION['usuario']['rol'])) {
    $destinos = [
        'administrador' => 'administrador/index.php',
        'supervisor' => 'Supervisor/index.php',
        'vendedor' => 'vendedor/dashboard.php',
    ];
    $rol = $_SESSION['usuario']['rol'];
    if (isset($destinos[$rol]) && verificarInactividadSesion()) {
        header('Location: ' . $destinos[$rol]);
        exit;
    }
}
require_once __DIR__ . '/funciones/conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - MarketPlus</title>
    <link rel="stylesheet" href="css/consultar_ventas.css">
</head>
<body>
<div class="login-card">
    <div class="logo">MarketPlus</div>

    <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
        <div class="error-message">Credenciales incorrectas</div>
    <?php elseif (isset($_GET['expirada']) && $_GET['expirada'] == 1): ?>
        <div class="error-message">Tu sesión expiró por inactividad. Inicia sesión nuevamente.</div>
    <?php endif; ?>

    <form action="procesar_login.php" method="POST" autocomplete="off">
        <div class="form-group">
            <label for="correo">Correo electrónico</label>
            <input type="email" id="correo" name="correo" required placeholder="ejemplo@marketplus.cl">
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required placeholder="Ingresa tu contraseña">
        </div>
        <button type="submit" class="btn-submit">Iniciar Sesión</button>

        <div class="forgot-password">
            <a href="administrador/solicitar_recuperacion.php">¿Olvidaste tu contraseña?</a>
        </div>
        <a href="index.php" class="btn-back">Volver al Inicio</a>
    </form>
</div>
</body>
</html>