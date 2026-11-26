<?php
session_start();
if (isset($_SESSION['id_funcionario'])) {
    if ($_SESSION['id_rol'] == 1) {
        header("Location: administrador/dashboard_administrador.php");
        exit;
    } elseif ($_SESSION['id_rol'] == 2) {
        header("Location: supervisor/Dashboard_Supervisor.php");
        exit;
    } elseif ($_SESSION['id_rol'] == 3) {
        header("Location: ventas/consultar_ventas.php");
        exit;
    }
}
require_once 'funciones/conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - MarketPlus</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f2f5f7; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); width: 100%; max-width: 360px; }
        .logo { text-align: center; color: #0071ce; font-size: 28px; font-weight: bold; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #4a4a4a; font-weight: 500; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; }
        .btn-submit { width: 100%; padding: 14px; background-color: #0071ce; color: white; border: none; border-radius: 25px; font-weight: bold; cursor: pointer; }
        .btn-back { display: block; width: 100%; padding: 12px; margin-top: 15px; background-color: #e9ecef; color: #4a4a4a; text-align: center; text-decoration: none; border-radius: 25px; font-weight: bold; box-sizing: border-box; font-size: 14px; }
        .btn-back:hover { background-color: #dde2e6; }
        .error-message { color: #d32f2f; background-color: #fde0e0; padding: 10px; border-radius: 8px; font-size: 14px; margin-bottom: 20px; text-align: center; }
        .forgot-password { text-align: center; margin-top: 15px; }
        .forgot-password a { color: #0071ce; text-decoration: none; font-size: 14px; font-weight: 500; }
        .forgot-password a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="logo">MarketPlus</div>
    <?php if(isset($_GET['error']) && $_GET['error'] == 1): ?>
        <div class="error-message">Credenciales incorrectas</div>
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