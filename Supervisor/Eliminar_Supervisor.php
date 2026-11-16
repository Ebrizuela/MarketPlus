<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


require_once '../funciones/conexion.php';

$mensaje = "";

try {
    $pdo = conectar();

    $pdo->exec("CREATE TABLE IF NOT EXISTS auditoria_usuarios (
        id_auditoria INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario_afectado INT NOT NULL,
        accion VARCHAR(50) NOT NULL,
        realizado_por VARCHAR(100) NOT NULL,
        fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
        detalles VARCHAR(255)
    )");

    // 2. Asegurar columna activo en funcionarios (Criterio 3: borrado lógico)
    try {
        $colActivo = $pdo->query("SHOW COLUMNS FROM funcionarios LIKE 'activo'");
        if ($colActivo && $colActivo->rowCount() === 0) {
            $pdo->exec("ALTER TABLE funcionarios ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1");
        }
    } catch (PDOException $e) {}

    // 3. Procesar eliminación lógica de supervisor (Criterios 1, 2 y 3)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_funcionario'])) {
        $idFuncionario = (int)$_POST['id_funcionario'];
        $adminActual = "nibhernandez25@cftsa.cl";

        $stmt = $pdo->prepare("UPDATE funcionarios SET activo = 0 WHERE id_funcionario = :id");
        $stmt->execute([':id' => $idFuncionario]);

        if ($stmt->rowCount() > 0) {
            $stmtAudit = $pdo->prepare("INSERT INTO auditoria_usuarios (id_usuario_afectado, accion, realizado_por, detalles) 
                                        VALUES (:id, 'ELIMINACION_LOGICA', :admin, 'Baja lógica de supervisor manteniendo histórico de ventas')");
            $stmtAudit->execute([
                ':id' => $idFuncionario,
                ':admin' => $adminActual
            ]);

            header("Location: supervisor.php?msg=eliminado");
            exit;
        } else {
            $mensaje = "<div style='background-color:#fee2e2; color:#b91c1c; padding:12px; border-radius:6px; margin-bottom:15px;'>No se pudo actualizar el estado del supervisor.</div>";
        }
    }

    if (isset($_GET['msg']) && $_GET['msg'] === 'eliminado') {
        $mensaje = "<div style='background-color:#dcfce7; color:#15803d; padding:12px; border-radius:6px; margin-bottom:15px; font-weight:bold;'>¡Supervisor dado de baja con éxito y registrado en auditoría!</div>";
    }

    // 4. Consultar supervisores activos (id_rol = 2) desde funcionarios y Roles
    $sql = "SELECT f.id_funcionario, f.nombre, f.apellido, f.correo, f.rut, r.nombre AS rol_nombre 
            FROM funcionarios f 
            INNER JOIN Roles r ON f.id_rol = r.id_rol 
            WHERE f.id_rol = 2 AND (f.activo = 1 OR f.activo IS NULL)
            ORDER BY f.id_funcionario ASC";
    $supervisores = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("<div style='color:red; text-align:center; padding:30px; font-family:sans-serif;'><h3>Error de base de datos</h3>" . htmlspecialchars($e->getMessage()) . "</div>");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MarketPlus - Gestión de Supervisores</title>
    <link rel="stylesheet" href="../css/estilos.css?v=<?= time() ?>">
</head>
<body style="background-color: #f8fafc; font-family: sans-serif; margin: 0; padding: 0;">

    <header class="lider-header" style="background: #2563eb; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center;">
        <div class="logo" style="font-size: 22px; font-weight: bold;">MarketPlus</div>
        <div>Módulo Administrativo</div>
    </header>

    <nav style="max-width: 1000px; margin: 20px auto 10px auto; padding: 0 15px; display: flex; justify-content: space-between;">
        <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.href='../index.php';" style="cursor: pointer; padding: 6px 14px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff;">
            ← Volver
        </button>
        <a href="../index.php" style="text-decoration: none; padding: 6px 12px; background: #2563eb; color: #fff; border-radius: 6px; font-size: 13px;">🏠 Inicio</a>
    </nav>

    <main style="max-width: 1000px; margin: 20px auto; padding: 0 15px;">
        <h2 style="color: #1e293b; margin-bottom: 5px;">Supervisores Activos en el Sistema</h2>
        <p style="color: #64748b; margin-bottom: 20px; font-size: 14px;">Administre el personal con rol de Supervisor registrado en la tabla Funcionarios.</p>

        <?= $mensaje ?>

        <div style="background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #2563eb; color: white;">
                        <th style="padding: 12px 16px;">ID</th>
                        <th style="padding: 12px 16px;">Nombre Completo</th>
                        <th style="padding: 12px 16px;">RUT</th>
                        <th style="padding: 12px 16px;">Correo</th>
                        <th style="padding: 12px 16px; text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($supervisores)): ?>
                        <?php foreach ($supervisores as $sup): ?>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 12px 16px; font-weight: bold;">#<?= htmlspecialchars($sup['id_funcionario']) ?></td>
                                <td style="padding: 12px 16px;"><?= htmlspecialchars($sup['nombre'] . ' ' . $sup['apellido']) ?></td>
                                <td style="padding: 12px 16px;"><?= htmlspecialchars($sup['rut']) ?></td>
                                <td style="padding: 12px 16px;"><?= htmlspecialchars($sup['correo']) ?></td>
                                <td style="padding: 12px 16px; text-align: center;">
                                    <form method="POST" action="supervisor.php" onsubmit="return confirm('¿Está seguro de que desea eliminar al supervisor <?= addslashes($sup['nombre'] . ' ' . $sup['apellido']) ?>?\n\nEsta acción registrará la baja en auditoría y mantendrá sus ventas intactas.');">
                                        <input type="hidden" name="id_funcionario" value="<?= $sup['id_funcionario'] ?>">
                                        <button type="submit" style="background: #ef4444; color: white; border: none; padding: 6px 14px; border-radius: 5px; cursor: pointer; font-size: 13px; font-weight: 600;">
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="padding: 30px; text-align: center; color: #64748b;">No hay supervisores activos registrados en Funcionarios.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>