<?php
ob_start();
session_start();

require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/../includes/auth.php';

// 🔒 Solo administradores
requerirRol(['administrador']);

$errorCategorias = null;
try {
    $pdo = conectar();
} catch (PDOException $e) {
    error_log('Error de conexión en categorías: ' . $e->getMessage());
    http_response_code(503);
    exit('No se pudo conectar con la base de datos para cargar las categorías.');
}

$esAdmin = esAdmin();
$rolActual = $_SESSION['usuario']['rol'] ?? null;
$mensaje = "";

if (isset($_SESSION['mensaje_flash'])) {
    $mensaje = $_SESSION['mensaje_flash'];
    unset($_SESSION['mensaje_flash']);
}

// CREAR CATEGORÍA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_categoria'])) {
    $nombreCat = trim($_POST['nombre_categoria'] ?? '');
    if (!empty($nombreCat)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Categorias (nombre) VALUES (?)");
            if ($stmt->execute([$nombreCat])) {
                $_SESSION['mensaje_flash'] = "<div class='alert success'>¡Categoría creada exitosamente!</div>";
                header("Location: " . BASE_URL . "/administrador/categorias.php");
                exit();
            }
        } catch (PDOException $e) {
            $mensaje = "<div class='alert error'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        $mensaje = "<div class='alert error'>El nombre de la categoría no puede estar vacío.</div>";
    }
}

// ELIMINAR CATEGORÍA
if ($esAdmin && isset($_GET['eliminar'])) {
    $idEliminar = (int)$_GET['eliminar'];
    try {
        // Verificar que no tenga productos asociados
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM Productos WHERE id_categorias = ?");
        $stmtCheck->execute([$idEliminar]);
        if ($stmtCheck->fetchColumn() > 0) {
            $_SESSION['mensaje_flash'] = "<div class='alert error'>No se puede eliminar: la categoría tiene productos asociados.</div>";
        } else {
            $stmtDel = $pdo->prepare("DELETE FROM Categorias WHERE id_categorias = ?");
            $stmtDel->execute([$idEliminar]);
            $_SESSION['mensaje_flash'] = "<div class='alert success'>¡Categoría eliminada correctamente!</div>";
        }
    } catch (PDOException $e) {
        $_SESSION['mensaje_flash'] = "<div class='alert error'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    header("Location: " . BASE_URL . "/administrador/categorias.php");
    exit();
}

// LISTAR CATEGORÍAS
$categoriasList = [];
try {
    $stmtCat = $pdo->query("SELECT * FROM Categorias ORDER BY id_categorias ASC");
    if ($stmtCat) {
        $categoriasList = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log('Error al listar categorías: ' . $e->getMessage());
    $errorCategorias = 'No se pudieron cargar las categorías. Comprueba la conexión a la base de datos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlus - Categorías</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/estilos.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="container">

    <?php echo $mensaje; ?>

    <div class="header">
        <div>
            <h2 style="margin:0; color: #2563eb;">MarketPlus - Categorías</h2>
        </div>

        <div style="display: flex; gap: 15px; align-items: center;">
            <span style="background: #10b981; color: white; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 13px;">Modo Admin</span>

            <div class="menu-hamburguesa-container">
                <button class="btn-hamburguesa" aria-label="Abrir menú">☰ Menú</button>
                <div class="menu-desplegable">
                    <a href="<?php echo BASE_URL; ?>/index.php">Catálogo Principal</a>
                    <a href="<?php echo BASE_URL; ?>/public/carrito.php">Ver Carrito</a>
                    <a href="<?php echo BASE_URL; ?>/administrador/categorias.php">Gestión de Categorías</a>
                    <a href="<?php echo BASE_URL; ?>/administrador/inventario.php">Control de Inventario</a>
                    <a href="<?php echo BASE_URL; ?>/supervisor/devoluciones.php">Gestor de Devoluciones</a>
                    <div class="menu-divider"></div>
                    <a href="<?php echo BASE_URL; ?>/includes/logout.php" class="item-logout">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-bottom: 20px;">Crear Nueva Categoría</h3>
        <form action="" method="POST">
            <div class="field-col-12">
                <label>Nombre de la Categoría:</label>
                <input type="text" name="nombre_categoria" required placeholder="Ej: Electrónica, Hogar, Ropa...">
            </div>
            <button type="submit" name="crear_categoria" class="btn" style="margin-top: 15px;">Guardar Categoría</button>
        </form>
    </div>

    <div class="card">
        <h3 style="margin-bottom: 20px;">Listado de Categorías</h3>
        <?php if ($errorCategorias !== null): ?>
            <p class="alert error"><?php echo htmlspecialchars($errorCategorias, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php elseif (empty($categoriasList)): ?>
            <p style="text-align: center; color: var(--text-muted); padding: 20px 0;">No hay categorías registradas.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categoriasList as $cat): ?>
                        <tr>
                            <td><strong>#<?php echo $cat['id_categorias']; ?></strong></td>
                            <td><?php echo htmlspecialchars($cat['nombre']); ?></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>/administrador/categorias.php?eliminar=<?php echo $cat['id_categorias']; ?>" 
                                   onclick="return confirm('¿Eliminar esta categoría?');"
                                   style="color: var(--danger); text-decoration: none; font-weight: 600; font-size: 13px;">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

</body>
</html>