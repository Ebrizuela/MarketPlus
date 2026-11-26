<?php
/**
 * MarketPlus - Registro de Nuevos Productos en Catálogo (HU-039)
 * Cumple con los Criterios de Aceptación:
 * - Control de Acceso RBAC estricto: Administrador y Supervisor (HTTP 403 a no autorizados).
 * - Carga de imágenes con validación de tipo MIME real (.jpg, .jpeg, .png, .webp) y límite de 2 MB.
 * - Validación de unicidad de Código / SKU en servidor.
 * - Sanitización anti-XSS y consultas preparadas con PDO anti-SQLi.
 * - Registro en auditoría con ID del usuario creador y marca temporal.
 * - Enlace directo al módulo oficial Eliminar_producto.php para el rol Administrador.
 */

if (!defined('BASE_URL')) {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $basePath = ($scriptDir === '/' || $scriptDir === '\\') ? '' : rtrim(str_replace('\\', '/', $scriptDir), '/');
    define('BASE_URL', preg_replace('#/(ventas|productos|administrador|supervisor|auth|public|includes|funciones).*$#i', '', $basePath));
}

date_default_timezone_set('America/Santiago');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/ProductoController.php';

$controller = new ProductoController();

// 1. Criterio RBAC: Bloquea y retorna HTTP 403 Forbidden si el rol no es Administrador o Supervisor
$usuarioActual = $controller->verificarPermisosRBAC();
$esSupervisor = strtolower($usuarioActual['rol']) === 'supervisor';
$esAdmin = !$esSupervisor && strtolower($usuarioActual['rol']) === 'administrador';

$categorias = $controller->obtenerCategorias();

$resultado = null;
$valores = [
    'codigo'        => '',
    'nombre'        => '',
    'id_categorias' => '',
    'precio'        => '',
    'stock'         => '',
    'descripcion'   => ''
];
$errores = [];

// 2. Procesar envío del formulario (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valores = [
        'codigo'        => trim($_POST['codigo'] ?? ''),
        'nombre'        => trim($_POST['nombre'] ?? ''),
        'id_categorias' => trim($_POST['id_categorias'] ?? ''),
        'precio'        => trim($_POST['precio'] ?? ''),
        'stock'         => trim($_POST['stock'] ?? ''),
        'descripcion'   => trim($_POST['descripcion'] ?? '')
    ];

    $resultado = $controller->registrarProducto($_POST, $_FILES, $usuarioActual);

    if ($resultado['exito']) {
        $valores = [
            'codigo'        => '',
            'nombre'        => '',
            'id_categorias' => '',
            'precio'        => '',
            'stock'         => '',
            'descripcion'   => ''
        ];
    } else {
        $errores = $resultado['errores'] ?? [];
    }
}

$urlDashboard = $esSupervisor ? '../Supervisor/index.php' : '../administrador/index.php';
$etiquetaPanel = $esSupervisor ? 'Panel de supervisión' : 'Panel administrativo';
$tituloPagina = $esAdmin ? 'Gestión de Productos' : 'Registrar Nuevo Producto';
$urlCatalogo = '../index.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina) ?> - MarketPlus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/marketplus.css">
    <link rel="stylesheet" href="../css/marketplus-theme.css?v=1">
    <link rel="stylesheet" href="../css/productos.css?v=5">
</head>
<body class="bg-light">

    <!-- Navbar oficial MarketPlus -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="<?= htmlspecialchars($urlDashboard) ?>">
                <i class="bi bi-shield-lock"></i> MarketPlus
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuProducto">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuProducto">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars($urlDashboard) ?>">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars($urlCatalogo) ?>">
                            <i class="bi bi-shop"></i> Catálogo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="registrar_producto.php">
                            <i class="bi bi-box-seam"></i> <?= $esAdmin ? 'Gestión de productos' : 'Nuevo producto' ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../ventas/consultar_ventas.php">
                            <i class="bi bi-cart-check"></i> Ventas registradas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../ventas/reportes_ventas.php">
                            <i class="bi bi-graph-up"></i> Reportes de ventas
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center text-white me-3 gap-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person-circle fs-5 me-2"></i>
                        <div>
                            <small class="d-block text-white-50"><?= htmlspecialchars($usuarioActual['rol']) ?></small>
                            <span><?= htmlspecialchars($usuarioActual['nombre']) ?></span>
                        </div>
                    </div>
                    <a class="btn btn-outline-light btn-sm btn-logout" href="../includes/logout.php" onclick="return confirm('¿Seguro que deseas cerrar sesión?');">
                        <i class="bi bi-box-arrow-right"></i> Cerrar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <!-- Encabezado estilo Dashboard de MarketPlus -->
        <div class="dashboard-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <p class="text-uppercase text-primary fw-semibold mb-1 small"><?= htmlspecialchars($etiquetaPanel) ?></p>
                <h2 class="mb-0"><?= htmlspecialchars($tituloPagina) ?></h2>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="<?= htmlspecialchars($urlDashboard) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Volver al Dashboard
                </a>
                <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill">Rol: <?= htmlspecialchars($usuarioActual['rol']) ?></span>
            </div>
        </div>

        <?php if ($esAdmin): ?>
            <!-- Pestañas de acceso unificado (enlace directo al módulo Eliminar_producto.php solo para Administrador) -->
            <div class="mb-4">
                <nav class="nav nav-pills mp-tabs" aria-label="Acciones de productos">
                    <a class="nav-link active" href="registrar_producto.php" aria-current="page">
                        <i class="bi bi-plus-circle me-1"></i> Registrar producto
                    </a>
                    <a class="nav-link tab-eliminar" href="Eliminar_producto.php">
                        <i class="bi bi-trash me-1"></i> Eliminar productos
                    </a>
                </nav>
            </div>
        <?php endif; ?>

        <!-- Tarjeta principal del formulario -->
        <div class="card panel-card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-box-seam text-primary me-2"></i>Datos del Nuevo Producto</span>
                <small class="text-muted fw-normal">Los campos marcados con <span class="req">*</span> son obligatorios</small>
            </div>
            <div class="card-body p-4">

                <?php if ($resultado && $resultado['exito']): ?>
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-start gap-3 mb-4" role="alert">
                        <i class="bi bi-check-circle-fill fs-4 mt-1"></i>
                        <div class="flex-grow-1">
                            <strong><?= htmlspecialchars($resultado['mensaje']) ?></strong>
                            <div class="small mt-1">
                                <strong>ID:</strong> #<?= (int)$resultado['id_producto'] ?> &bull;
                                <strong>SKU:</strong> <code><?= htmlspecialchars($resultado['codigo']) ?></code> &bull;
                                <strong>Artículo:</strong> <?= htmlspecialchars($resultado['nombre']) ?>
                            </div>
                            <div class="mt-3 d-flex gap-2 flex-wrap">
                                <a href="registrar_producto.php" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-lg me-1"></i> Registrar otro producto
                                </a>
                                <?php if ($esAdmin): ?>
                                    <a href="Eliminar_producto.php" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-trash me-1"></i> Ir a Eliminar productos
                                    </a>
                                <?php endif; ?>
                                <a href="<?= htmlspecialchars($urlCatalogo) ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-shop me-1"></i> Ver en catálogo
                                </a>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                <?php elseif ($resultado && !$resultado['exito']): ?>
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-start gap-3 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-4 mt-1"></i>
                        <div>
                            <strong>No se pudo registrar el producto:</strong><br>
                            <?= htmlspecialchars($resultado['mensaje']) ?>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="registrar_producto.php" enctype="multipart/form-data" id="formRegistroProducto" novalidate>
                    <div class="row g-3">

                        <!-- Código / SKU -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="codigo">
                                <i class="bi bi-upc-scan text-primary me-1"></i> Código / SKU <span class="req">*</span>
                            </label>
                            <input type="text"
                                   name="codigo"
                                   id="codigo"
                                   class="form-control <?= isset($errores['codigo']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($valores['codigo']) ?>"
                                   maxlength="50"
                                   required>
                            <span class="form-help">Identificador único del producto en inventario.</span>
                            <?php if (isset($errores['codigo'])): ?>
                                <div class="error-feedback"><i class="bi bi-x-circle"></i> <?= htmlspecialchars($errores['codigo']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Categoría -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="id_categorias">
                                <i class="bi bi-tags text-primary me-1"></i> Categoría <span class="req">*</span>
                            </label>
                            <select name="id_categorias"
                                    id="id_categorias"
                                    class="form-select <?= isset($errores['id_categorias']) ? 'is-invalid' : '' ?>"
                                    required>
                                <option value="">-- Seleccione una categoría --</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= (int)$cat['id_categorias'] ?>" <?= ((string)$valores['id_categorias'] === (string)$cat['id_categorias']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errores['id_categorias'])): ?>
                                <div class="error-feedback"><i class="bi bi-x-circle"></i> <?= htmlspecialchars($errores['id_categorias']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Nombre del Producto -->
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="nombre">
                                <i class="bi bi-card-heading text-primary me-1"></i> Nombre del Producto <span class="req">*</span>
                            </label>
                            <input type="text"
                                   name="nombre"
                                   id="nombre"
                                   class="form-control <?= isset($errores['nombre']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($valores['nombre']) ?>"
                                   maxlength="150"
                                   required>
                            <?php if (isset($errores['nombre'])): ?>
                                <div class="error-feedback"><i class="bi bi-x-circle"></i> <?= htmlspecialchars($errores['nombre']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Precio -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="precio">
                                <i class="bi bi-currency-dollar text-primary me-1"></i> Precio de Venta (CLP) <span class="req">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">$</span>
                                <input type="number"
                                       name="precio"
                                       id="precio"
                                       class="form-control <?= isset($errores['precio']) ? 'is-invalid' : '' ?>"
                                       value="<?= htmlspecialchars($valores['precio']) ?>"
                                       min="1"
                                       step="1"
                                       required>
                            </div>
                            <span class="form-help">Valor numérico entero mayor a $0.</span>
                            <?php if (isset($errores['precio'])): ?>
                                <div class="error-feedback"><i class="bi bi-x-circle"></i> <?= htmlspecialchars($errores['precio']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Stock -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="stock">
                                <i class="bi bi-layers text-primary me-1"></i> Stock Inicial <span class="req">*</span>
                            </label>
                            <input type="number"
                                   name="stock"
                                   id="stock"
                                   class="form-control <?= isset($errores['stock']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($valores['stock']) ?>"
                                   min="0"
                                   step="1"
                                   required>
                            <span class="form-help">Cantidad física disponible en inventario (entero &ge; 0).</span>
                            <?php if (isset($errores['stock'])): ?>
                                <div class="error-feedback"><i class="bi bi-x-circle"></i> <?= htmlspecialchars($errores['stock']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Imagen del Producto -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-image text-primary me-1"></i> Imagen del Producto <span class="req">*</span>
                            </label>
                            <div class="image-upload-wrapper" onclick="document.getElementById('inputImagen').click();">
                                <span class="image-upload-icon">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                </span>
                                <div class="fw-semibold text-dark">Haz clic aquí para seleccionar una imagen</div>
                                <div class="small text-muted mt-1">
                                    Formatos permitidos: <strong>.jpg, .jpeg, .png, .webp</strong> &bull; Tamaño máximo: <strong>2 MB</strong>
                                </div>
                                <input type="file"
                                       name="imagen"
                                       id="inputImagen"
                                       accept="image/jpeg,image/png,image/webp"
                                       class="d-none"
                                       onchange="previsualizarImagen(event)">
                            </div>

                            <div class="image-preview-box" id="previewContainer">
                                <img id="imagePreview" src="" alt="Previsualización">
                                <div class="image-preview-meta" id="previewMeta"></div>
                            </div>

                            <?php if (isset($errores['imagen'])): ?>
                                <div class="error-feedback mt-2"><i class="bi bi-x-circle"></i> <?= htmlspecialchars($errores['imagen']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Descripción -->
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="descripcion">
                                <i class="bi bi-text-paragraph text-primary me-1"></i> Descripción (Opcional)
                            </label>
                            <textarea name="descripcion"
                                      id="descripcion"
                                      rows="3"
                                      class="form-control"><?= htmlspecialchars($valores['descripcion']) ?></textarea>
                        </div>

                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                        <a href="<?= htmlspecialchars($urlDashboard) ?>" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-arrow-left me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4" id="btnGuardar">
                            <i class="bi bi-check-lg me-1"></i> Registrar Producto
                        </button>
                    </div>
                </form>

            </div>
            <div class="card-footer bg-white text-muted small d-flex justify-content-between flex-wrap gap-2 py-3 px-4">
                <span><strong>MarketPlus</strong> &bull; Gestión de Catálogo e Inventario</span>
                <span><i class="bi bi-shield-check text-success me-1"></i> Validación segura y auditoría activa</span>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previsualizarImagen(event) {
            const input = event.target;
            const container = document.getElementById('previewContainer');
            const preview = document.getElementById('imagePreview');
            const meta = document.getElementById('previewMeta');

            if (input.files && input.files[0]) {
                const file = input.files[0];
                const sizeMb = (file.size / (1024 * 1024)).toFixed(2);

                if (file.size > 2 * 1024 * 1024) {
                    alert('Atención: El archivo supera los 2 MB permitidos (' + sizeMb + ' MB). Por favor selecciona una imagen más liviana.');
                    input.value = '';
                    container.style.display = 'none';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    meta.innerHTML = '<strong>' + file.name + '</strong> (' + sizeMb + ' MB)';
                    container.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                container.style.display = 'none';
            }
        }
    </script>
</body>
</html>
