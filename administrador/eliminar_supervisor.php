<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/conexion.php';

requerirRol(['administrador']);

$pdo = conectar();
$mensaje = $_SESSION['mensaje_eliminar_supervisor'] ?? '';
unset($_SESSION['mensaje_eliminar_supervisor']);

$columnaActivo = $pdo->query("SHOW COLUMNS FROM funcionarios LIKE 'activo'");
if ($columnaActivo->rowCount() === 0) {
    $pdo->exec('ALTER TABLE funcionarios ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1');
}

$pdo->exec("CREATE TABLE IF NOT EXISTS auditoria_usuarios (
    id_auditoria INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario_afectado INT NOT NULL,
    accion VARCHAR(50) NOT NULL,
    realizado_por VARCHAR(100) NOT NULL,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    detalles VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$columnaIdAuditoria = $pdo->query("SHOW COLUMNS FROM auditoria_usuarios LIKE 'id_auditoria'")
    ->fetch(PDO::FETCH_ASSOC);
if ($columnaIdAuditoria && stripos($columnaIdAuditoria['Extra'], 'auto_increment') === false) {
    $pdo->exec('ALTER TABLE auditoria_usuarios MODIFY id_auditoria INT NOT NULL AUTO_INCREMENT');
}

if (empty($_SESSION['csrf_eliminar_supervisor'])) {
    $_SESSION['csrf_eliminar_supervisor'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf'] ?? '';
    if (!is_string($csrf) || !hash_equals($_SESSION['csrf_eliminar_supervisor'], $csrf)) {
        http_response_code(419);
        exit('El formulario expiró. Recarga la página e inténtalo de nuevo.');
    }

    $idFuncionario = filter_var($_POST['id_funcionario'] ?? null, FILTER_VALIDATE_INT);
    if (!$idFuncionario || $idFuncionario < 1) {
        http_response_code(400);
        exit('El identificador del supervisor no es válido.');
    }

    try {
        $pdo->beginTransaction();
        $consulta = $pdo->prepare(
            "SELECT f.id_funcionario, f.nombre, f.apellido
             FROM funcionarios f
             INNER JOIN Roles r ON r.id_rol = f.id_rol
             WHERE f.id_funcionario = :id
               AND LOWER(r.nombre) = 'supervisor'
               AND (f.activo = 1 OR f.activo IS NULL)
             FOR UPDATE"
        );
        $consulta->execute([':id' => $idFuncionario]);
        $supervisor = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$supervisor) {
            throw new DomainException('El supervisor no existe o ya fue dado de baja.');
        }

        $actualizar = $pdo->prepare(
            'UPDATE funcionarios SET activo = 0 WHERE id_funcionario = :id'
        );
        $actualizar->execute([':id' => $idFuncionario]);

        $ventasHistoricas = $pdo->prepare(
            'SELECT COUNT(*) FROM Ventas WHERE id_funcionario = :id'
        );
        $ventasHistoricas->execute([':id' => $idFuncionario]);
        $cantidadVentasHistoricas = (int) $ventasHistoricas->fetchColumn();

        $usuarioAdministrador = $_SESSION['usuario'];
        $correoAdministrador = trim((string) ($usuarioAdministrador['correo'] ?? ''));
        $nombreAdministrador = trim((string) ($usuarioAdministrador['nombre'] ?? ''));
        $realizadoPor = $correoAdministrador !== '' ? $correoAdministrador : ($nombreAdministrador !== '' ? $nombreAdministrador : 'Administrador');

        $auditar = $pdo->prepare(
            "INSERT INTO auditoria_usuarios
                (id_usuario_afectado, accion, realizado_por, fecha_hora, detalles)
             VALUES
                (:id, 'ELIMINACION_LOGICA', :admin, NOW(), :detalles)"
        );
        $auditar->execute([
            ':id' => $idFuncionario,
            ':admin' => $realizadoPor,
            ':detalles' => 'Baja lógica realizada por administrador; '
                . $cantidadVentasHistoricas . ' ventas históricas conservadas.',
        ]);

        $pdo->commit();
        $_SESSION['mensaje_eliminar_supervisor'] = 'Supervisor dado de baja correctamente; su historial de ventas se conservó.';
    } catch (DomainException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['mensaje_eliminar_supervisor'] = $e->getMessage();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error al dar de baja al supervisor: ' . $e->getMessage());
        $_SESSION['mensaje_eliminar_supervisor'] = 'No se pudo dar de baja al supervisor. Inténtalo nuevamente.';
    }

    header('Location: eliminar_supervisor.php');
    exit;
}

$supervisores = $pdo->query(
    "SELECT f.id_funcionario, f.nombre, f.apellido, f.correo, f.rut
     FROM funcionarios f
     INNER JOIN Roles r ON r.id_rol = f.id_rol
     WHERE LOWER(r.nombre) = 'supervisor'
       AND (f.activo = 1 OR f.activo IS NULL)
     ORDER BY f.id_funcionario ASC"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlus - Eliminar supervisores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/marketplus.css" rel="stylesheet">
    <link href="../css/marketplus-theme.css?v=1" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <p class="text-uppercase text-primary fw-semibold mb-1 small">Panel administrativo</p>
                <h1 class="h2 mb-0">Eliminar supervisor</h1>
            </div>
            <a class="btn btn-outline-primary" href="index.php">Volver al dashboard</a>
        </div>

        <?php if ($mensaje !== ''): ?>
            <div class="alert alert-info" role="status"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="card panel-card">
            <div class="card-body">
                <p class="text-muted">La baja es lógica: el supervisor dejará de estar activo y sus ventas se conservarán.</p>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>RUT</th>
                                <th>Correo</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($supervisores): ?>
                                <?php foreach ($supervisores as $supervisor): ?>
                                    <?php
                                    $nombreCompleto = $supervisor['nombre'] . ' ' . $supervisor['apellido'];
                                    $confirmacion = '¿Confirma eliminar al supervisor ' . $nombreCompleto
                                        . '? Se dará de baja y sus ventas históricas se conservarán.';
                                    ?>
                                    <tr>
                                        <td>#<?= (int) $supervisor['id_funcionario'] ?></td>
                                        <td><?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($supervisor['rut'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($supervisor['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-center">
                                            <form method="POST" action="eliminar_supervisor.php" onsubmit="return confirm(<?= htmlspecialchars(json_encode($confirmacion), ENT_QUOTES, 'UTF-8') ?>)">
                                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_eliminar_supervisor'], ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="id_funcionario" value="<?= (int) $supervisor['id_funcionario'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Dar de baja</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No hay supervisores activos registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
