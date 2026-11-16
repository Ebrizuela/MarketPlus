<?php
/**
 * Lógica del Sistema y Gestión del Carrito
 */

// Verificar y adaptar las columnas necesarias en la BD
function verificarYAdaptarColumnasPDO($pdo) {
    $columnas = ['columnaImagenExiste' => false, 'columnaCodigoExiste' => false];

    try {
        $checkCol = $pdo->query("SHOW COLUMNS FROM Productos LIKE 'imagen'");
        if ($checkCol && $checkCol->rowCount() > 0) {
            $columnas['columnaImagenExiste'] = true;
        } else {
            $pdo->exec("ALTER TABLE Productos ADD COLUMN imagen VARCHAR(255) DEFAULT NULL");
            $columnas['columnaImagenExiste'] = true;
        }
    } catch (PDOException $e) {
        $columnas['columnaImagenExiste'] = false;
    }

    try {
        $checkCod = $pdo->query("SHOW COLUMNS FROM Productos LIKE 'codigo'");
        if ($checkCod && $checkCod->rowCount() > 0) {
            $columnas['columnaCodigoExiste'] = true;
        } else {
            $pdo->exec("ALTER TABLE Productos ADD COLUMN codigo VARCHAR(50) DEFAULT NULL");
            $columnas['columnaCodigoExiste'] = true;
        }
    } catch (PDOException $e) {
        $columnas['columnaCodigoExiste'] = false;
    }

    return $columnas;
}

// Obtener categorías
function obtenerCategorias($pdo) {
    try {
        $stmt = $pdo->query("SELECT id_categorias, nombre FROM Categorias ORDER BY id_categorias ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Obtener catálogo
function obtenerProductos($pdo, $columnaImagenExiste, $columnaCodigoExiste) {
    try {
        $columnasSelect = "p.id_producto, p.nombre, p.descripcion, p.precio, p.stock, c.nombre AS categoria";
        if ($columnaImagenExiste) $columnasSelect .= ", p.imagen";
        if ($columnaCodigoExiste) $columnasSelect .= ", p.codigo";

        $sqlQuery = "SELECT $columnasSelect 
                     FROM Productos p 
                     INNER JOIN Categorias c ON p.id_categorias = c.id_categorias 
                     ORDER BY p.id_producto DESC";

        $stmtList = $pdo->query($sqlQuery);
        return $stmtList ? $stmtList->fetchAll(PDO::FETCH_ASSOC) : [];
    } catch (PDOException $e) {
        return [];
    }
}

// Obtener un solo producto
function obtenerProductoPorId($pdo, $idProducto, $columnaImagenExiste, $columnaCodigoExiste) {
    try {
        $columnasSelect = "p.id_producto, p.nombre, p.descripcion, p.precio, p.stock, c.nombre AS categoria";
        if ($columnaImagenExiste) $columnasSelect .= ", p.imagen";
        if ($columnaCodigoExiste) $columnasSelect .= ", p.codigo";

        $sqlQuery = "SELECT $columnasSelect 
                     FROM Productos p 
                     INNER JOIN Categorias c ON p.id_categorias = c.id_categorias 
                     WHERE p.id_producto = ?";

        $stmt = $pdo->prepare($sqlQuery);
        $stmt->execute([$idProducto]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

// Subir imagen
function procesarSubidaImagen($file, &$errorImagen) {
    $imagenRuta = NULL;
    $errorImagen = false;

    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $file['tmp_name'];
        $fileName      = $file['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $mimesPermitidos       = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $tipoMime              = mime_content_type($fileTmpPath);

        if (in_array($fileExtension, $extensionesPermitidas) && in_array($tipoMime, $mimesPermitidos)) {
            $uploadFileDir = 'uploads/';

            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $nuevoNombre = uniqid('prod_', true) . '.' . $fileExtension;
            $destPath    = $uploadFileDir . $nuevoNombre;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $imagenRuta = $destPath;
            }
        } else {
            $errorImagen = true;
        }
    }

    return $imagenRuta;
}

// Crear producto
function crearProducto($pdo, $datos, $columnaImagenExiste, $columnaCodigoExiste) {
    $codigo       = trim($datos['codigo'] ?? '');
    $nombre       = trim($datos['nombre'] ?? '');
    $descripcion  = trim($datos['descripcion'] ?? '');
    $precio       = isset($datos['precio']) ? (int)$datos['precio'] : -1;
    $id_categoria = isset($datos['id_categorias']) ? (int)$datos['id_categorias'] : 0;
    $stock        = isset($datos['stock']) ? (int)$datos['stock'] : -1;

    $errorImagen = false;
    $imagenRuta  = procesarSubidaImagen($_FILES['imagen'] ?? null, $errorImagen);

    $codigoExiste = false;
    if (!empty($codigo) && $columnaCodigoExiste) {
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM Productos WHERE codigo = ?");
        $stmtCheck->execute([$codigo]);
        if ($stmtCheck->fetchColumn() > 0) {
            $codigoExiste = true;
        }
    }

    if (empty($codigo)) {
        return "<div class='alert error'>Error: El código único es obligatorio.</div>";
    } elseif ($codigoExiste) {
        return "<div class='alert error'>Error: El código '$codigo' ya pertenece a otro producto.</div>";
    } elseif (empty($nombre)) {
        return "<div class='alert error'>Error: El nombre del producto es obligatorio.</div>";
    } elseif ($precio < 0) {
        return "<div class='alert error'>Error: El precio no es válido.</div>";
    } elseif ($stock < 0) {
        return "<div class='alert error'>Error: El stock no es válido.</div>";
    } elseif ($id_categoria <= 0) {
        return "<div class='alert error'>Error: Selecciona una categoría válida.</div>";
    } elseif ($errorImagen) {
        return "<div class='alert error'>Error: Formato de imagen no permitido.</div>";
    }

    try {
        if ($columnaImagenExiste && $columnaCodigoExiste) {
            $sql = "INSERT INTO Productos (id_categorias, codigo, nombre, descripcion, precio, stock, imagen) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmtInsert = $pdo->prepare($sql);
            $ejecutado = $stmtInsert->execute([$id_categoria, $codigo, $nombre, $descripcion, $precio, $stock, $imagenRuta]);
        } elseif ($columnaCodigoExiste) {
            $sql = "INSERT INTO Productos (id_categorias, codigo, nombre, descripcion, precio, stock) VALUES (?, ?, ?, ?, ?, ?)";
            $stmtInsert = $pdo->prepare($sql);
            $ejecutado = $stmtInsert->execute([$id_categoria, $codigo, $nombre, $descripcion, $precio, $stock]);
        } else {
            $sql = "INSERT INTO Productos (id_categorias, nombre, descripcion, precio, stock) VALUES (?, ?, ?, ?, ?)";
            $stmtInsert = $pdo->prepare($sql);
            $ejecutado = $stmtInsert->execute([$id_categoria, $nombre, $descripcion, $precio, $stock]);
        }

        if ($ejecutado) {
            $_SESSION['mensaje_flash'] = "<div class='alert success'>¡Producto registrado exitosamente!</div>";
            header("Location: index.php");
            exit();
        } else {
            return "<div class='alert error'>Error al guardar el producto.</div>";
        }
    } catch (PDOException $e) {
        return "<div class='alert error'>Error en la base de datos: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Eliminar producto
function eliminarProducto($pdo, $idEliminar, $columnaImagenExiste) {
    try {
        $prodImg = null;
        if ($columnaImagenExiste) {
            $stmtImg = $pdo->prepare("SELECT imagen FROM Productos WHERE id_producto = ?");
            $stmtImg->execute([$idEliminar]);
            $prodImg = $stmtImg->fetch(PDO::FETCH_ASSOC);
        }

        $stmtDelete = $pdo->prepare("DELETE FROM Productos WHERE id_producto = ?");
        if ($stmtDelete->execute([$idEliminar])) {
            if ($columnaImagenExiste && !empty($prodImg['imagen']) && file_exists($prodImg['imagen'])) {
                @unlink($prodImg['imagen']);
            }
            
            $fotosLocales = glob(__DIR__ . "/img/{$idEliminar}.*");
            if ($fotosLocales) {
                foreach ($fotosLocales as $f) {
                    if (file_exists($f)) @unlink($f);
                }
            }

            // Si está en el carrito, removerlo también
            if (isset($_SESSION['carrito'][$idEliminar])) {
                unset($_SESSION['carrito'][$idEliminar]);
            }

            $_SESSION['mensaje_flash'] = "<div class='alert success'>¡Producto eliminado correctamente!</div>";
        } else {
            $_SESSION['mensaje_flash'] = "<div class='alert error'>Error al eliminar el producto.</div>";
        }
    } catch (PDOException $e) {
        $_SESSION['mensaje_flash'] = "<div class='alert error'>Error en la base de datos: " . htmlspecialchars($e->getMessage()) . "</div>";
    }

    header("Location: index.php");
    exit();
}

// Resolver ruta de imagen
function obtenerRutaImagenProducto($row) {
    $imgCol = $row['imagen'] ?? null;
    $idProd = $row['id_producto'];

    if (!empty($imgCol) && file_exists($imgCol)) {
        return $imgCol;
    }

    $coincidencias = glob("img/{$idProd}.*");
    if (!empty($coincidencias) && file_exists($coincidencias[0])) {
        return $coincidencias[0];
    }

    $nombreLower = mb_strtolower($row['nombre']);
    if (strpos($nombreLower, 'caixun') !== false || strpos($nombreLower, 'tv') !== false) {
        if (file_exists('tv.png')) return 'tv.png';
        if (file_exists('tv.jpg')) return 'tv.jpg';
        if (file_exists('img/tv.png')) return 'img/tv.png';
        return 'https://m.media-amazon.com/images/I/71LJJrKbezL._AC_SL1500_.jpg';
    } elseif (strpos($nombreLower, 'hp') !== false || strpos($nombreLower, 'portátil') !== false || strpos($nombreLower, 'ryzen') !== false) {
        if (file_exists('hp.png')) return 'hp.png';
        if (file_exists('hp.jpg')) return 'hp.jpg';
        if (file_exists('img/hp.png')) return 'img/hp.png';
        return 'https://m.media-amazon.com/images/I/71TPdaC65NW._AC_SL1500_.jpg';
    }

    return 'https://placehold.co/300x200?text=Sin+Imagen';
}

/* --- FUNCIONES DEL CARRITO DE COMPRAS --- */

// Contar total de unidades en el carrito
function contarItemsCarrito() {
    if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
        return 0;
    }
    $total = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $total += $item['cantidad'];
    }
    return $total;
}

// Agregar o incrementar producto al carrito
function agregarAlCarrito($pdo, $idProducto, $columnaImagenExiste, $columnaCodigoExiste, $cantidad = 1) {
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    $producto = obtenerProductoPorId($pdo, $idProducto, $columnaImagenExiste, $columnaCodigoExiste);
    if (!$producto) {
        return false;
    }

    if (isset($_SESSION['carrito'][$idProducto])) {
        $_SESSION['carrito'][$idProducto]['cantidad'] += $cantidad;
    } else {
        $_SESSION['carrito'][$idProducto] = [
            'id'       => $producto['id_producto'],
            'nombre'   => $producto['nombre'],
            'precio'   => (float)$producto['precio'],
            'imagen'   => obtenerRutaImagenProducto($producto),
            'cantidad' => $cantidad
        ];
    }
    return true;
}

// Actualizar cantidad
function actualizarCantidadCarrito($idProducto, $cantidad) {
    if (isset($_SESSION['carrito'][$idProducto])) {
        if ($cantidad > 0) {
            $_SESSION['carrito'][$idProducto]['cantidad'] = $cantidad;
        } else {
            unset($_SESSION['carrito'][$idProducto]);
        }
    }
}

// Eliminar un producto del carrito
function eliminarDelCarrito($idProducto) {
    if (isset($_SESSION['carrito'][$idProducto])) {
        unset($_SESSION['carrito'][$idProducto]);
    }
}