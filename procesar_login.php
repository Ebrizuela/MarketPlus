<?php
require_once _DIR_ . '/includes/auth.php';
require_once _DIR_ . '/funciones/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$correo = $_POST['correo'] ?? '';
$password = $_POST['password'] ?? '';


if (!is_string($correo) || !is_string($password) || $correo === '' || $password === '') {
    header('Location: login.php?error=1');
    exit;
}

$correo = strtolower(trim($correo));
if ($correo === '') {
    header('Location: login.php?error=1');
    exit;
}

$pdo = conectar();
$columnaActivo = $pdo->query("SHOW COLUMNS FROM funcionarios LIKE 'activo'");
$filtroActivo = $columnaActivo->rowCount() > 0
    ? ' AND (f.activo = 1 OR f.activo IS NULL)'
    : '';
$stmt = $pdo->prepare(
    'SELECT f.id_funcionario, f.nombre, f.apellido, f.correo, f.rut, f.contrasena, f.id_rol
     FROM funcionarios f
     INNER JOIN Roles r ON f.id_rol = r.id_rol
     WHERE LOWER(TRIM(f.correo)) = ?' . $filtroActivo
);
$stmt->execute([$correo]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header('Location: login.php?error=1');
    exit;
}

$claveGuardada = (string) $usuario['contrasena'];
$claveValida = password_verify($password, $claveGuardada);

if (!$claveValida && hash_equals($claveGuardada, $password)) {
    $claveValida = true;
    $hashActualizado = $pdo->prepare(
        'UPDATE funcionarios SET contrasena = ? WHERE id_funcionario = ?'
    );
    $hashActualizado->execute([
        password_hash($password, PASSWORD_BCRYPT),
        $usuario['id_funcionario'],
    ]);
}

if (!$claveValida) {
    header('Location: login.php?error=1');
    exit;
}

$rol = mapearRol($usuario['id_rol']);
$destinos = [
    'administrador' => 'administrador/index.php',
    'supervisor' => 'Supervisor/index.php',
    'vendedor' => 'vendedor/dashboard.php',
    'Auditor' => 'auditor/validar_token.php'
];

if (!isset($destinos[$rol])) {
    header('Location: login.php?error=1');
    exit;
}

iniciarSesionUsuario($usuario, $rol);
header('Location: ' . $destinos[$rol]);
exit;