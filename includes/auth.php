<?php
/**
 * includes/auth.php
 * Funciones de autenticación y control de acceso por rol
 */

if (session_status() === PHP_SESSION_NONE) {
    $https = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (!defined('BASE_URL')) {
    $directoriosAplicacion = [
        'auth', 'administrador', 'supervisor', 'ventas', 'vendedor',
        'public', 'productos', 'clientes', 'includes', 'funciones', 'css',
    ];
    $rutaScript = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $segmentos = array_values(array_filter(explode('/', trim($rutaScript, '/')), 'strlen'));
    if ($segmentos && in_array(strtolower(end($segmentos)), $directoriosAplicacion, true)) {
        array_pop($segmentos);
    }
    define('BASE_URL', $segmentos ? '/' . implode('/', $segmentos) : '');
}

/**
 * Verifica si hay un usuario logueado
 */
function estaLogueado() {
    return isset($_SESSION['usuario']);
}

/**
 * Verifica si el usuario logueado es administrador
 */
function esAdmin() {
    return isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'administrador';
}

/**
 * Verifica si el usuario logueado es supervisor (o vendedor, que tratamos igual)
 */
function esSupervisor() {
    return isset($_SESSION['usuario']['rol']) 
        && in_array($_SESSION['usuario']['rol'], ['supervisor', 'vendedor']);
}

/**
 * Verifica si el usuario tiene uno de los roles permitidos.
 * Si no, redirige a la página de login.
 */
function requerirRol($rolesPermitidos = []) {
    if (!estaLogueado()) {
        header("Location: " . BASE_URL . "/login.php");
        exit();
    }

    $rolActual = $_SESSION['usuario']['rol'] ?? '';
    if (!in_array($rolActual, $rolesPermitidos, true)) {
        $_SESSION['mensaje_flash'] = "<div class='alert error'>No tienes permisos para acceder a esa sección.</div>";
        header("Location: " . BASE_URL . "/index.php");
        exit();
    }
}

/**
 * Mapea el id_rol de la BD al nombre del rol.
 */
function mapearRol($idRol) {
    switch ((int)$idRol) {
        case 1: return 'administrador';
        case 2: return 'supervisor';
        case 3: return 'vendedor';
        default: return 'desconocido';
    }
}

/**
 * Guarda la sesión del usuario tras un login exitoso.
 */
function iniciarSesionUsuario($usuario, $rol) {
    session_regenerate_id(true);
    unset($_SESSION['usuario_admin']);
    $_SESSION['usuario'] = [
        'id'       => $usuario['id_funcionario'] ?? $usuario['id_cliente'] ?? null,
        'nombre'   => trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')),
        'correo'   => $usuario['correo'] ?? $usuario['email'] ?? '',
        'rut'      => $usuario['rut'] ?? '',
        'rol'      => $rol
    ];

    // ✅ Compatibilidad con código antiguo
    if ($rol === 'administrador') {
        $_SESSION['usuario_admin'] = $usuario;
    }
}

/**
 * Cierra la sesión actual.
 */
function cerrarSesion() {
    setcookie('jwt_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
            || (string) ($_SERVER['SERVER_PORT'] ?? '') === '443',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . "/login.php");
    exit();
}