<?php
// filepath: c:\Users\sandr\proyectos\src\administrador\registro.php
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../funciones/usuarios_funciones.php';
require_once __DIR__ . '/../funciones/navegacion.php';

requerirRol(['administrador']);
$pdo = conectar();

$mensaje = "";

// Variables para el modo Edición vs Creación
$id_editar = isset($_GET['editar_id']) ? intval($_GET['editar_id']) : null;
$usuario_editar = null;

if ($id_editar) {
    try {
        $usuario_editar = obtenerSupervisorPorId($pdo, $id_editar);
        if (!$usuario_editar) {
            http_response_code(404);
            exit('El supervisor solicitado no existe.');
        }
    } catch (Exception $e) {
        $mensaje = "<div class='alert alert-danger'>Error al obtener usuario: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Procesar Formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (empty($_SESSION['csrf_registro_usuarios'])) {
            $_SESSION['csrf_registro_usuarios'] = bin2hex(random_bytes(32));
        }
        $csrf = $_POST['csrf'] ?? '';
        if (!is_string($csrf) || !hash_equals($_SESSION['csrf_registro_usuarios'], $csrf)) {
            http_response_code(419);
            exit('El formulario expiró. Recarga la página e inténtalo de nuevo.');
        }
    $nombre   = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $correo   = normalizarCorreo($_POST['correo'] ?? '');
    $id_rol   = filter_var($_POST['id_rol'] ?? null, FILTER_VALIDATE_INT) ?: 0;
    $action   = $_POST['action'] ?? 'crear';

    if ($action === 'actualizar' && $id_editar) {
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mensaje = "<div class='alert alert-danger'>El formato del correo electrónico no es válido.</div>";
        } else {
            try {
                actualizarFuncionario($pdo, $id_editar, $nombre, $apellido, $correo, $id_rol);
                $mensaje = "<div class='alert alert-success'>¡Datos del usuario actualizados correctamente!</div>";
                $id_editar = null;
                $usuario_editar = null;
            } catch (Exception $e) {
                $mensaje = "<div class='alert alert-danger'>Error al actualizar: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    } else {
        $contrasena = $_POST['contrasena'] ?? '';
        if (!validarContrasena($contrasena)) {
            $mensaje = "<div class='alert alert-danger'>La contraseña debe tener al menos 8 caracteres, incluyendo letras y números.</div>";
        } else {
            try {
                registrarPersonaConConfirmacion($pdo, $nombre, $apellido, $correo, $contrasena, $id_rol);
                $mensaje = "<div class='alert alert-success'>Usuario registrado y correo aceptado por el servidor de correo para <strong>" . htmlspecialchars($correo) . "</strong>.</div>";
            } catch (DomainException | InvalidArgumentException $e) {
                $mensaje = "<div class='alert alert-danger'>" . htmlspecialchars($e->getMessage()) . "</div>";
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $mensaje = "<div class='alert alert-danger'>El correo electrónico ya se encuentra registrado.</div>";
                } else {
                    error_log($e->getMessage());
                    $mensaje = "<div class='alert alert-danger'>No se pudo completar el registro. Revisa la conexión a la base de datos.</div>";
                }
            } catch (Throwable $e) {
                error_log($e->getMessage());
                $mensaje = "<div class='alert alert-danger'>No se creó la cuenta porque el servidor de correo rechazó la confirmación. Verifica que el correo PHP del hosting esté configurado.</div>";
            }
        }
    }
}

// Cargar listas requeridas de forma segura
$roles = [];
$lista_funcionarios = [];

try {
    $roles = obtenerRoles($pdo);
} catch (Exception $e) {
    $mensaje .= "<div class='alert alert-danger'>Error al cargar roles: " . htmlspecialchars($e->getMessage()) . "</div>";
}

try {
    $lista_funcionarios = obtenerSupervisores($pdo);
} catch (Exception $e) {
    $mensaje .= "<div class='alert alert-danger'>Error al cargar la lista de funcionarios: " . htmlspecialchars($e->getMessage()) . "</div>";
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlus - Gestión de Usuarios</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="../css/navegacion.css">
    <link rel="stylesheet" href="../css/marketplus-theme.css?v=1">
</head>
<body class="admin-users-page">

    <?php renderNavegacion(null, BASE_URL . '/index.php'); ?>
    <div class="brand-header">
        <h1>Market<span>Plus</span></h1>
        <p>Panel de Administración de Usuarios</p>
    </div>

    <main class="admin-layout">
    <!-- Formulario de Registro / Edición -->
    <div class="card-container">
        <div class="card-top-bar"></div>
        <form method="POST" action="registro.php<?php echo $id_editar ? '?editar_id='.$id_editar : ''; ?>" id="formRegistro">
            
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf_registro_usuarios'] ??= bin2hex(random_bytes(32)), ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="action" value="<?php echo $id_editar ? 'actualizar' : 'crear'; ?>">

            <div class="form-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--lider-blue)" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                <?php echo $id_editar ? 'Modificar datos de usuario' : 'Crear nueva cuenta'; ?>
            </div>

            <?php echo $mensaje; ?>

            <div class="form-row">
                <div class="form-group">
                    <label>Nombre</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </span>
                        <input type="text" name="nombre" placeholder="Ej. Carlos" value="<?php echo htmlspecialchars($_POST['nombre'] ?? $usuario_editar['nombre'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Apellido</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </span>
                        <input type="text" name="apellido" placeholder="Ej. Pérez" value="<?php echo htmlspecialchars($_POST['apellido'] ?? $usuario_editar['apellido'] ?? ''); ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Correo Electrónico</label>
                <div class="input-wrapper">
                    <span class="input-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    </span>
                    <input type="email" name="correo" placeholder="ejemplo@marketplus.cl" value="<?php echo htmlspecialchars($_POST['correo'] ?? $usuario_editar['correo'] ?? ''); ?>" required>
                </div>
            </div>

            <?php if (!$id_editar): ?>
            <div class="form-group">
                <label>Contraseña</label>
                <div class="input-wrapper">
                    <span class="input-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    </span>
                    <input type="password" id="inputContrasena" name="contrasena" placeholder="••••••••" required>
                    <button type="button" class="toggle-password">
                        <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
                <div class="password-hint">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    Mínimo 8 caracteres, combinando letras y números.
                </div>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Rol asignado</label>
                <div class="input-wrapper">
                    <span class="input-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path><path d="M21 21v-2a4 4 0 0 0-3-3.85"></path><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"></path><path d="M1 21v-2a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v2"></path></svg>
                    </span>
                    <select name="id_rol" required>
                        <?php foreach ($roles as $rol): 
                            $rol_id = $rol['id_rol'] ?? $rol['id'];
                            $selected = (($usuario_editar && $usuario_editar['id_rol'] == $rol_id) || (isset($_POST['id_rol']) && $_POST['id_rol'] == $rol_id)) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $rol_id; ?>" <?php echo $selected; ?>>
                                <?php echo ucfirst($rol['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <?php echo $id_editar ? 'Guardar Cambios' : 'Registrar Usuario'; ?>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </button>

            <?php if ($id_editar): ?>
                <a href="registro.php" class="btn-cancelar">Cancelar Edición</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Tabla de Supervisores Registrados -->
    <div class="tabla-container">
        <h3>Lista de Supervisores</h3>
        <div class="table-scroll">
        <table class="tabla-usuarios">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($lista_funcionarios)): ?>
                    <?php foreach ($lista_funcionarios as $f): ?>
                        <tr>
                            <td><?php echo $f['id_funcionario']; ?></td>
                            <td><?php echo htmlspecialchars($f['nombre'] . ' ' . $f['apellido']); ?></td>
                            <td><?php echo htmlspecialchars($f['correo']); ?></td>
                            <td><span class="badge-rol"><?php echo htmlspecialchars($f['nombre_rol']); ?></span></td>
                            <td>
                                <a href="registro.php?editar_id=<?php echo $f['id_funcionario']; ?>" class="btn-editar">
                                    Editar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="texto-vacio">No hay supervisores registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
    </main>

    <div class="footer-note">
        MarketPlus Admin System &copy; 2026 - Módulo de Gestión
    </div>

    <script src="../js/registro.js"></script>
</body>
</html>