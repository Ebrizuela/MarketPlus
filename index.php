<?php
ob_start();

require_once __DIR__ . '/funciones/conexion.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/funciones/usuarios_funciones.php';
require_once __DIR__ . '/funciones/imagenes_productos.php';

try {
    $pdo = conectar();
} catch (PDOException $e) {
    error_log('Error de conexión en el catálogo: ' . $e->getMessage());
    http_response_code(503);
    exit('No se pudo conectar con la base de datos. Revisa la configuración PDO en funciones/conexion.php.');
}

$mensaje = "";

if (isset($_SESSION['mensaje_flash'])) {
    $mensaje = $_SESSION['mensaje_flash'];
    unset($_SESSION['mensaje_flash']);
}

$esAdmin = esAdmin();
$estaLogueado = estaLogueado();
$rolActual = $_SESSION['usuario']['rol'] ?? null;

$columnaImagenExiste = false;
$columnaCodigoExiste = false;
$columnaEstadoExiste = false;

try {
    $checkCol = $pdo->query("SHOW COLUMNS FROM Productos LIKE 'imagen'");
    if ($checkCol && $checkCol->rowCount() > 0) {
        $columnaImagenExiste = true;
    } else {
        $pdo->exec("ALTER TABLE Productos ADD COLUMN imagen VARCHAR(255) DEFAULT NULL");
        $columnaImagenExiste = true;
    }
} catch (PDOException $e) {
    $columnaImagenExiste = false;
}

try {
    $checkCod = $pdo->query("SHOW COLUMNS FROM Productos LIKE 'codigo'");
    if ($checkCod && $checkCod->rowCount() > 0) {
        $columnaCodigoExiste = true;
    } else {
        $pdo->exec("ALTER TABLE Productos ADD COLUMN codigo VARCHAR(50) DEFAULT NULL");
        $columnaCodigoExiste = true;
    }
} catch (PDOException $e) {
    $columnaCodigoExiste = false;
}

try {
    $checkEstado = $pdo->query("SHOW COLUMNS FROM Productos LIKE 'estado'");
    $columnaEstadoExiste = $checkEstado && $checkEstado->rowCount() > 0;
} catch (PDOException $e) {
    $columnaEstadoExiste = false;
}

$nombresSimples = [
    'Tecnología' => 'Tecnología',
    'Software y Licencias' => 'Software',
    'Línea Blanca y Electrodomésticos' => 'Electrodomésticos',
    'Herramientas y Ferretería' => 'Ferretería',
    'Muebles y Oficina' => 'Oficina',
    'Seguridad y Vigilancia' => 'Seguridad',
    'Insumos de Aseo y Limpieza' => 'Aseo y Limpieza'
];

function guardarImagenComoJPG($archivoTmp, $extension, $rutaDestino, $calidad = 85) {
    if (!extension_loaded('gd')) {
        return move_uploaded_file($archivoTmp, $rutaDestino);
    }
    $imagen = null;
    switch ($extension) {
        case 'jpg': case 'jpeg': $imagen = @imagecreatefromjpeg($archivoTmp); break;
        case 'png': $imagen = @imagecreatefrompng($archivoTmp); break;
        case 'gif': $imagen = @imagecreatefromgif($archivoTmp); break;
        case 'webp': if (function_exists('imagecreatefromwebp')) $imagen = @imagecreatefromwebp($archivoTmp); break;
    }
    if (!$imagen) return move_uploaded_file($archivoTmp, $rutaDestino);
    $ancho = imagesx($imagen); $alto = imagesy($imagen);
    $lienzo = imagecreatetruecolor($ancho, $alto);
    $blanco = imagecolorallocate($lienzo, 255, 255, 255);
    imagefill($lienzo, 0, 0, $blanco);
    imagecopy($lienzo, $imagen, 0, 0, 0, 0, $ancho, $alto);
    $exito = imagejpeg($lienzo, $rutaDestino, $calidad);
    imagedestroy($imagen); imagedestroy($lienzo);
    return $exito;
}

// CREAR PRODUCTO
if ($esAdmin && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crear_producto'])) {
    $codigo = trim($_POST['codigo'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = isset($_POST['precio']) ? (int)$_POST['precio'] : -1;
    $id_categoria = isset($_POST['id_categorias']) ? (int)$_POST['id_categorias'] : 0;
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : -1;

    $codigoExiste = false;
    if (!empty($codigo) && $columnaCodigoExiste) {
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM Productos WHERE codigo = ?");
        $stmtCheck->execute([$codigo]);
        if ($stmtCheck->fetchColumn() > 0) $codigoExiste = true;
    }

    if (empty($codigo)) {
        $mensaje = "<div class='alert error'>Error: El código único del producto es obligatorio.</div>";
    } elseif ($codigoExiste) {
        $mensaje = "<div class='alert error'>Error: El código '$codigo' ya pertenece a otro producto. Debe ser único.</div>";
    } elseif (empty($nombre)) {
        $mensaje = "<div class='alert error'>Error: El nombre del producto es obligatorio.</div>";
    } elseif (empty($descripcion)) {
        $mensaje = "<div class='alert error'>Error: La descripción del producto es obligatoria.</div>";
    } elseif ($precio < 0) {
        $mensaje = "<div class='alert error'>Error: El precio ingresado no es válido.</div>";
    } elseif ($stock < 0) {
        $mensaje = "<div class='alert error'>Error: El stock ingresado no es válido.</div>";
    } elseif ($id_categoria <= 0) {
        $mensaje = "<div class='alert error'>Error: Debes seleccionar una categoría válida.</div>";
    } else {
        try {
            if ($columnaCodigoExiste) {
                $sql = "INSERT INTO Productos (id_categorias, codigo, nombre, descripcion, precio, stock) VALUES (?, ?, ?, ?, ?, ?)";
                $stmtInsert = $pdo->prepare($sql);
                $ejecutado = $stmtInsert->execute([$id_categoria, $codigo, $nombre, $descripcion, $precio, $stock]);
            } else {
                $sql = "INSERT INTO Productos (id_categorias, nombre, descripcion, precio, stock) VALUES (?, ?, ?, ?, ?)";
                $stmtInsert = $pdo->prepare($sql);
                $ejecutado = $stmtInsert->execute([$id_categoria, $nombre, $descripcion, $precio, $stock]);
            }

            if ($ejecutado) {
                $nuevoId = (int)$pdo->lastInsertId();

                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['imagen']['tmp_name'];
                    $fileName = $_FILES['imagen']['name'];
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                    
                    if (in_array($fileExtension, $extensionesPermitidas)) {
                        $imgDir = __DIR__ . '/uploads/';
                        if (!is_dir($imgDir)) mkdir($imgDir, 0755, true);
                        $rutaDestino = $imgDir . $nuevoId . '.jpg';
                        
                        if (guardarImagenComoJPG($fileTmpPath, $fileExtension, $rutaDestino)) {
                            $rutaEnBD = 'uploads/' . $nuevoId . '.jpg';
                            $stmtUpd = $pdo->prepare("UPDATE Productos SET imagen = ? WHERE id_producto = ?");
                            $stmtUpd->execute([$rutaEnBD, $nuevoId]);
                        }
                    }
                }

                $_SESSION['mensaje_flash'] = "<div class='alert success'>¡Producto registrado exitosamente!</div>";
                header("Location: " . BASE_URL . "/index.php");
                exit();
            } else {
                $mensaje = "<div class='alert error'>Error al guardar el producto.</div>";
            }
        } catch (PDOException $e) {
            $mensaje = "<div class='alert error'>Error en la base de datos: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

$categoriasList = [];
try {
    $stmtCat = $pdo->query("SELECT id_categorias, nombre FROM Categorias ORDER BY id_categorias ASC");
    $categoriasList = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error al cargar categorías del catálogo: ' . $e->getMessage());
}

$terminoBusqueda = trim($_GET['buscar'] ?? '');
$idProductoSeleccionado = isset($_GET['producto']) ? (int) $_GET['producto'] : 0;
$productos = [];
$errorProductos = null;
try {
    $columnasSelect = "p.id_producto, p.nombre, p.descripcion, p.precio, p.stock, c.nombre AS categoria";
    if ($columnaImagenExiste) {
        $columnasSelect .= ", p.imagen";
    }
    if ($columnaCodigoExiste) {
        $columnasSelect .= ", p.codigo";
    }

    $sqlQuery = "SELECT $columnasSelect
                 FROM Productos p
                 INNER JOIN Categorias c ON p.id_categorias = c.id_categorias";
    $parametros = [];
    $condiciones = [];
    if ($columnaEstadoExiste) {
        $condiciones[] = 'p.estado = 1';
    }
    if ($terminoBusqueda !== '') {
        $busqueda = '(p.nombre LIKE :nombre OR p.descripcion LIKE :descripcion OR c.nombre LIKE :categoria';
        $parametros = [
            ':nombre' => '%' . $terminoBusqueda . '%',
            ':descripcion' => '%' . $terminoBusqueda . '%',
            ':categoria' => '%' . $terminoBusqueda . '%',
        ];
        if ($columnaCodigoExiste) {
            $busqueda .= ' OR p.codigo LIKE :codigo';
            $parametros[':codigo'] = '%' . $terminoBusqueda . '%';
        }
        $condiciones[] = $busqueda . ')';
    }
    if ($idProductoSeleccionado > 0) {
        $condiciones[] = 'p.id_producto = :id_producto';
        $parametros[':id_producto'] = $idProductoSeleccionado;
    }
    if ($condiciones) {
        $sqlQuery .= ' WHERE ' . implode(' AND ', $condiciones);
    }
    $sqlQuery .= ' ORDER BY p.id_producto DESC';
    $stmtProductos = $pdo->prepare($sqlQuery);
    $stmtProductos->execute($parametros);
    $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error al consultar productos del catálogo: ' . $e->getMessage());
    $errorProductos = 'No fue posible cargar los productos. Inténtalo nuevamente más tarde.';
}

$productosCarrusel = [];
try {
    $columnasCarrusel = 'p.id_producto, p.nombre, p.descripcion, p.precio';
    if ($columnaImagenExiste) {
        $columnasCarrusel .= ', p.imagen';
    }

    $sqlCarrusel = "SELECT $columnasCarrusel FROM Productos p";
    if ($columnaEstadoExiste) {
        $sqlCarrusel .= ' WHERE p.estado = 1';
    }
    $sqlCarrusel .= ' ORDER BY p.id_producto DESC';
    $stmtCarrusel = $pdo->query($sqlCarrusel);
    $productosCarrusel = $stmtCarrusel->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error al cargar productos del carrusel: ' . $e->getMessage());
}

$cantidadCarrito = 0;
foreach ($_SESSION['carrito'] ?? [] as $itemCarrito) {
    $cantidadCarrito += (int) ($itemCarrito['cantidad'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlus - Catálogo</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/estilos.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/marketplus-theme.css?v=1">
</head>
<body>
<header class="mp-header">
    <a class="mp-logo" href="<?php echo BASE_URL; ?>/index.php">MarketPlus 🛒</a>
    <form class="mp-search-bar" action="<?php echo BASE_URL; ?>/index.php" method="GET" role="search">
        <input type="search" name="buscar" value="<?php echo htmlspecialchars($terminoBusqueda, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Buscar productos, marcas y más..." aria-label="Buscar productos">
        <button type="submit">Buscar</button>
    </form>
    <nav class="mp-user-actions" aria-label="Accesos de usuario">
        <?php if ($estaLogueado): ?>
            <?php if ($esAdmin): ?>
                <a class="mp-action-button" href="<?php echo BASE_URL; ?>/administrador/Productos_Inventory.php">← Volver a Productos e inventario</a>
            <?php elseif ($rolActual === 'supervisor'): ?>
                <a class="mp-action-button" href="<?php echo BASE_URL; ?>/Supervisor/index.php">📊 Dashboard</a>
            <?php elseif ($rolActual === 'vendedor'): ?>
                <a class="mp-action-button" href="<?php echo BASE_URL; ?>/ventas/consultar_ventas.php">📋 Ventas</a>
            <?php endif; ?>
            <a class="mp-action-button" href="<?php echo BASE_URL; ?>/includes/logout.php">Salir</a>
        <?php else: ?>
            <a class="mp-action-button" href="<?php echo BASE_URL; ?>/login.php">Iniciar Sesión</a>
        <?php endif; ?>
        <a class="mp-action-button" href="<?php echo BASE_URL; ?>/public/carrito.php">🛒 Carrito (<?php echo $cantidadCarrito; ?>)</a>
    </nav>
</header>

<main>
    <div id="alerta-mensaje" class="home-alert"><?php echo $mensaje; ?></div>
<div class="carousel-heading">
    <span class="carousel-eyebrow">Ofertas destacadas</span>
    <h1 id="ofertas-titulo">Grandes ofertas <span>para ti</span></h1>
    <p>Elige tu producto favorito y descubre precios increíbles.</p>
</div>
<section class="hero-carousel" aria-labelledby="ofertas-titulo">
    <div class="carousel-brand" aria-hidden="true">MarketPlus</div>
    <?php
    $primerSlide = true;
    $haySlides = false;
    $cantidadSlides = 0;

    foreach ($productosCarrusel as $productoCarrusel):
        $imagenCarrusel = obtenerImagenProducto($productoCarrusel, __DIR__);
        if ($imagenCarrusel === null) {
            continue;
        }

        $haySlides = true;
        $cantidadSlides++;
        $claseActiva = $primerSlide ? ' active' : '';
        $primerSlide = false;

        $urlCatalogo = BASE_URL . '/index.php?producto='
            . (int) $productoCarrusel['id_producto'] . '#productos';
    ?>
        <a class="carousel-slide<?php echo $claseActiva; ?>"
           href="<?php echo htmlspecialchars($urlCatalogo, ENT_QUOTES, 'UTF-8'); ?>">
            <img src="<?php echo htmlspecialchars($imagenCarrusel, ENT_QUOTES, 'UTF-8'); ?>"
                 alt="<?php echo htmlspecialchars($productoCarrusel['nombre'], ENT_QUOTES, 'UTF-8'); ?>">

            <div class="carousel-info">
                <h2><?php echo htmlspecialchars($productoCarrusel['nombre'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <p><?php echo htmlspecialchars($productoCarrusel['descripcion'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                <strong>$<?php echo number_format((float) $productoCarrusel['precio'], 0, ',', '.'); ?></strong>
            </div>
        </a>
    <?php endforeach; ?>

    <?php if (!$haySlides): ?>
        <div class="carousel-empty">No hay productos con imágenes para mostrar.</div>
    <?php endif; ?>

    <?php if ($cantidadSlides > 1): ?>
        <button class="carousel-control carousel-prev" type="button" aria-label="Imagen anterior">
            &#10094;
        </button>
        <button class="carousel-control carousel-next" type="button" aria-label="Imagen siguiente">
            &#10095;
        </button>
        <div class="carousel-indicators" aria-label="Seleccionar imagen">
            <?php for ($indicePunto = 0; $indicePunto < $cantidadSlides; $indicePunto++): ?>
                <button
                    class="carousel-dot<?php echo $indicePunto === 0 ? ' active' : ''; ?>"
                    type="button"
                    data-carousel-index="<?php echo $indicePunto; ?>"
                    aria-label="Ir a la imagen <?php echo $indicePunto + 1; ?>"
                    aria-current="<?php echo $indicePunto === 0 ? 'true' : 'false'; ?>">
                </button>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</section>

    <?php if ($esAdmin): ?>
    <section class="card admin-product-form">
        <h3>Registrar Nuevo Producto</h3>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-flex-container">
                <div class="field-col-6"><label>Código Único:</label><input type="text" name="codigo" required></div>
                <div class="field-col-6"><label>Categoría:</label>
                    <select name="id_categorias" required>
                        <option value="">Seleccione una categoría</option>
                        <?php foreach ($categoriasList as $cat): 
                            $nombreMostrar = $nombresSimples[$cat['nombre']] ?? $cat['nombre']; ?>
                            <option value="<?php echo $cat['id_categorias']; ?>"><?php echo htmlspecialchars($nombreMostrar); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field-col-6"><label>Precio:</label><input type="number" min="0" name="precio" required></div>
                <div class="field-col-6"><label>Nombre del producto:</label><input type="text" name="nombre" required></div>
                <div class="field-col-6"><label>Stock:</label><input type="number" min="0" name="stock" required></div>
                <div class="field-col-6"><label>Imagen del Producto:</label><input type="file" name="imagen" accept="image/*"><small style="color:#64748b; font-size:12px; margin-top:4px;">Se guardará como uploads/{ID}.jpg</small></div>
                <div class="field-col-12"><label>Descripción:</label><textarea name="descripcion" rows="3" required></textarea></div>
            </div>
            <button type="submit" name="crear_producto" class="btn" style="margin-top: 15px;">Guardar Producto</button>
        </form>
    </section>
    <?php endif; ?>

<section id="productos" aria-labelledby="productos-titulo">
        <h2 class="section-title" id="productos-titulo"><?php echo $terminoBusqueda === '' ? 'Productos Destacados' : 'Resultados de búsqueda'; ?></h2>
        <?php if ($terminoBusqueda !== ''): ?>
            <p class="search-summary">Resultados para: <strong><?php echo htmlspecialchars($terminoBusqueda, ENT_QUOTES, 'UTF-8'); ?></strong></p>
        <?php endif; ?>
        <?php if ($errorProductos !== null): ?>
            <p class="catalog-message catalog-error"><?php echo htmlspecialchars($errorProductos, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php elseif (!$productos): ?>
            <p class="catalog-message"><?php echo $terminoBusqueda === '' ? 'No hay productos disponibles por el momento.' : 'No encontramos productos que coincidan con tu búsqueda.'; ?></p>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($productos as $row):
                    $categoriaOriginal = $row['categoria'] ?? 'MarketPlus';
                    $categoriaMostrar = $nombresSimples[$categoriaOriginal] ?? $categoriaOriginal;
                    $imagenSrc = obtenerImagenProducto($row, __DIR__);
                    $urlDetalle = BASE_URL . '/public/detalle.php?id=' . (int) $row['id_producto'];
                    ?>
                    <a class="product-card" href="<?php echo htmlspecialchars($urlDetalle, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if ($imagenSrc !== null): ?>
                            <img class="product-img" src="<?php echo htmlspecialchars($imagenSrc, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8'); ?>" onerror="this.outerHTML='<div class=&quot;product-img product-img-empty&quot;>Imagen no disponible</div>';">
                        <?php else: ?>
                            <div class="product-img product-img-empty">Imagen no disponible</div>
                        <?php endif; ?>
                        <span class="badge-categoria"><?php echo htmlspecialchars($categoriaMostrar, ENT_QUOTES, 'UTF-8'); ?></span>
                        <h3 class="product-title"><?php echo htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <?php if (!empty($row['descripcion'])): ?>
                            <p class="product-description"><?php echo htmlspecialchars($row['descripcion'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                        <div class="price-tag">
                            <span class="current-price">$<?php echo number_format((float) $row['precio'], 0, ',', '.'); ?></span>
                        </div>
                        <span class="product-link">Ver producto <span aria-hidden="true">→</span></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<script>
    window.addEventListener('DOMContentLoaded', () => {
        const alerta = document.getElementById('alerta-mensaje');
        if (alerta && alerta.innerText.trim() !== "") {
            setTimeout(() => {
                alerta.style.transition = "opacity 0.6s ease";
                alerta.style.opacity = "0";
                setTimeout(() => { alerta.style.display = "none"; }, 600);
            }, 5000);
        }
        const slides = document.querySelectorAll('.carousel-slide');
        const puntos = document.querySelectorAll('.carousel-dot');
        const anterior = document.querySelector('.carousel-prev');
        const siguiente = document.querySelector('.carousel-next');
        let indiceSlide = 0;

        if (slides.length > 1) {
            const actualizarCarrusel = () => {
                slides.forEach((slide) => {
                    slide.classList.remove('active', 'is-current', 'is-prev', 'is-next');
                });
                puntos.forEach((punto, indice) => {
                    const esActual = indice === indiceSlide;
                    punto.classList.toggle('active', esActual);
                    punto.setAttribute('aria-current', esActual ? 'true' : 'false');
                });

                slides[indiceSlide].classList.add('active', 'is-current');

                if (slides.length === 2) {
                    slides[(indiceSlide + 1) % slides.length].classList.add('is-next');
                } else {
                    slides[(indiceSlide - 1 + slides.length) % slides.length].classList.add('is-prev');
                    slides[(indiceSlide + 1) % slides.length].classList.add('is-next');
                }
            };

            const mostrarSlide = (nuevoIndice) => {
                indiceSlide = (nuevoIndice + slides.length) % slides.length;
                actualizarCarrusel();
            };

            actualizarCarrusel();
            anterior.addEventListener('click', () => mostrarSlide(indiceSlide - 1));
            siguiente.addEventListener('click', () => mostrarSlide(indiceSlide + 1));
            puntos.forEach((punto, indice) => {
                punto.addEventListener('click', () => mostrarSlide(indice));
            });

            window.setInterval(() => {
                mostrarSlide(indiceSlide + 1);
            }, 4000);
        }
    });
</script>
</body>
</html>