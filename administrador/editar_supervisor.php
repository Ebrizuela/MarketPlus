<?php
ini_set('display_errors', 1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../funciones/usuarios_funciones.php';
require_once __DIR__ . '/../funciones/navegacion.php';

requerirRol(['administrador']);
$pdo = conectar();

$mensaje = "";

$id_editar = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id_editar) {
    http_response_code(400);
    exit('Falta un identificador de supervisor válido.');
}

$funcionario = obtenerSupervisorPorId($pdo, $id_editar);

if (!$funcionario) {
    http_response_code(404);
    exit('El supervisor solicitado no existe.');
}

if (empty($_SESSION['csrf_editar_supervisor'])) {
    $_SESSION['csrf_editar_supervisor'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $csrf = $_POST['csrf'] ?? '';
    if (!is_string($csrf) || !hash_equals($_SESSION['csrf_editar_supervisor'], $csrf)) {
        http_response_code(419);
        exit('El formulario expiró. Recarga la página e inténtalo de nuevo.');
    }

    $nombre   = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');
    $id_rol   = filter_var($_POST['id_rol'] ?? null, FILTER_VALIDATE_INT) ?: 0;

    try {
        actualizarFuncionario($pdo, $id_editar, $nombre, $apellido, $correo, $id_rol);
        $funcionario = obtenerFuncionarioPorId($pdo, $id_editar);
        $mensaje = "<div class='alert alert-success'>¡Datos del supervisor actualizados y auditados correctamente!</div>";
    } catch (DomainException | InvalidArgumentException $e) {
        $mensaje = "<div class='alert alert-danger'>" . htmlspecialchars($e->getMessage()) . "</div>";
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $mensaje = "<div class='alert alert-danger'>No se pudieron guardar los cambios ni registrar la auditoría en la base de datos.</div>";
    }
}

$roles = obtenerRoles($pdo);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlus - Modificación de Supervisor</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="../css/navegacion.css">
    <link rel="stylesheet" href="../css/marketplus-theme.css?v=1">
</head>
<body>

    <?php renderNavegacion(null, BASE_URL . '/index.php'); ?>
    <div class="brand-header">
        <h1>Market<span>Plus</span></h1>
        <p>Panel de Gestión de Equipo</p>
    </div>

    <div class="card-container">
        <div class="card-top-bar"></div>
        <form method="POST" action="editar_supervisor.php?id=<?php echo (int) $id_editar; ?>">
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf_editar_supervisor'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-title">
                Modificar Datos de Supervisor
            </div>

            <?php echo $mensaje; ?>

            <div class="form-row">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($funcionario['nombre']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Apellido</label>
                    <input type="text" name="apellido" value="<?php echo htmlspecialchars($funcionario['apellido']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" name="correo" value="<?php echo htmlspecialchars($funcionario['correo']); ?>" required>
            </div>

            <div class="form-group">
                <label>Rol asignado</label>
                <select name="id_rol" required>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?php echo $rol['id_rol']; ?>" <?php echo ($rol['id_rol'] == $funcionario['id_rol']) ? 'selected' : ''; ?>>
                            <?php echo ucfirst($rol['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn-submit">
                Guardar Cambios
            </button>
        </form>
    </div>

</body>
</html>