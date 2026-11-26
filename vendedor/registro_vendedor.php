<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../funciones/usuarios_funciones.php';
require_once __DIR__ . '/../funciones/vendedor_funciones.php';
require_once __DIR__ . '/../funciones/navegacion.php';


requerirRol(['administrador']);

$pdo = conectar();
$mensaje = '';
$exito = false;

if (empty($_SESSION['csrf_registro_vendedor'])) {
    $_SESSION['csrf_registro_vendedor'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf'] ?? '';
    if (!is_string($csrf) || !hash_equals($_SESSION['csrf_registro_vendedor'], $csrf)) {
        http_response_code(419);
        exit('El formulario expiró. Recarga la página e inténtalo de nuevo.');
    }

    $nombre     = trim($_POST['nombre'] ?? '');
    $apellido   = trim($_POST['apellido'] ?? '');
    $correo     = normalizarCorreo($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if (!validarContrasena($contrasena)) {
        $mensaje = 'La contraseña debe tener al menos 8 caracteres, incluyendo letras y números.';
    } else {
        try {
      
            registrarPersonaConConfirmacion($pdo, $nombre, $apellido, $correo, $contrasena, ID_ROL_VENDEDOR);
            $mensaje = 'Vendedor registrado correctamente. Se envió la confirmación a ' . $correo . '.';
            $exito = true;
        } catch (DomainException | InvalidArgumentException $e) {
            $mensaje = $e->getMessage();
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $mensaje = 'El correo electrónico ya se encuentra registrado.';
            } else {
                error_log($e->getMessage());
                $mensaje = 'No se pudo completar el registro. Revisa la conexión a la base de datos.';
            }
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $mensaje = 'No se creó la cuenta porque el servidor de correo rechazó la confirmación. Verifica que el correo PHP del hosting esté configurado.';
        }
    }
}

$vendedores = [];
try {
    $vendedores = obtenerVendedores($pdo);
} catch (Exception $e) {
    error_log($e->getMessage());
    $mensaje = trim($mensaje . ' No se pudo cargar la lista de vendedores.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Vendedores - MarketPlus</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/navegacion.css">
    <link rel="stylesheet" href="../css/marketplus-theme.css?v=1">
    <link rel="stylesheet" href="../css/vendedor_registro.css">
</head>
<body class="vr-page">

    <?php renderNavegacion(null, BASE_URL . '/index.php'); ?>

    <header class="vr-header">
        <h1>Market<span>Plus</span></h1>
        <p>Registro de vendedores</p>
    </header>

    <main class="vr-layout">
        <!-- Formulario de alta -->
        <section class="vr-card">
            <div class="vr-card-top"></div>
            <div class="vr-card-body">
                <h2 class="vr-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0071ce" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                    Crear cuenta de vendedor
                </h2>

                <?php if ($mensaje !== ''): ?>
                    <div class="vr-alert <?php echo $exito ? 'vr-alert-success' : 'vr-alert-error'; ?>">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="registro_vendedor.php" autocomplete="off">
                    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf_registro_vendedor'], ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="vr-row">
                        <div class="vr-field">
                            <label for="nombre">Nombre</label>
                            <input class="vr-input" type="text" id="nombre" name="nombre" placeholder="Ej. Carlos" value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>" required>
                        </div>
                        <div class="vr-field">
                            <label for="apellido">Apellido</label>
                            <input class="vr-input" type="text" id="apellido" name="apellido" placeholder="Ej. Pérez" value="<?php echo htmlspecialchars($_POST['apellido'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="vr-field">
                        <label for="correo">Correo electrónico</label>
                        <input class="vr-input" type="email" id="correo" name="correo" placeholder="vendedor@marketplus.cl" value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>" required>
                    </div>

                    <div class="vr-field">
                        <label for="contrasena">Contraseña</label>
                        <input class="vr-input" type="password" id="contrasena" name="contrasena" placeholder="••••••••" required>
                        <p class="vr-hint">Mínimo 8 caracteres, combinando letras y números.</p>
                    </div>

                    <div class="vr-field">
                        <label>Rol asignado</label>
                        <span class="vr-rol">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-3-3.87"></path><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"></path><path d="M1 21v-2a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v2"></path></svg>
                            Vendedor
                        </span>
                    </div>

                    <button type="submit" class="vr-btn">
                        Registrar vendedor
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </button>
                </form>
            </div>
        </section>

       
        <section class="vr-card">
            <div class="vr-card-top"></div>
            <div class="vr-card-body">
                <h2 class="vr-title">Vendedores registrados</h2>
                <div class="vr-table-wrap">
                    <table class="vr-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Rol</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($vendedores)): ?>
                                <?php foreach ($vendedores as $v): ?>
                                    <tr>
                                        <td><?php echo (int) $v['id_funcionario']; ?></td>
                                        <td><?php echo htmlspecialchars($v['nombre'] . ' ' . $v['apellido']); ?></td>
                                        <td><?php echo htmlspecialchars($v['correo']); ?></td>
                                        <td><span class="vr-badge"><?php echo htmlspecialchars($v['nombre_rol']); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="vr-empty">Aún no hay vendedores registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <footer class="vr-footer">
        MarketPlus Admin System &copy; 2026 - Módulo de Vendedores
    </footer>

</body>
</html>
