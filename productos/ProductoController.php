<?php
/**
 * MarketPlus - Controlador de Productos (P.O.O.)
 * Cumple con los Criterios de Aceptación:
 * - Restricción de Acceso y Control de Roles (RBAC) con HTTP 403 Forbidden.
 * - Validación estricta de SKU / Código único antes de procesar archivos.
 * - Carga, validación MIME real (finfo) y renombrado seguro de imágenes.
 * - Consultas preparadas PDO contra SQL Injection y sanitización XSS.
 * - Registro en tabla de auditoría con ID del usuario y marca de tiempo.
 */

class ProductoController
{
    private PDO $pdo;
    private string $directorioSubidas;
    private string $prefijoRelativoSubidas = 'img/productos/';
    private int $tamanoMaximoBytes = 2097152; // 2 MB (2 * 1024 * 1024)
    private array $mimesPermitidos = [
        'image/jpeg' => 'jpg',
        'image/jpg'  => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    public function __construct(?PDO $pdo = null)
    {
        $raizProyecto = dirname(__DIR__);
        $dirImgProductos = $raizProyecto . '/img/productos/';
        $dirUploads = $raizProyecto . '/uploads/';

        if (!is_dir($dirImgProductos)) {
            @mkdir($dirImgProductos, 0775, true);
        }

        if (is_dir($dirImgProductos) && is_writable($dirImgProductos)) {
            $this->directorioSubidas = $dirImgProductos;
            $this->prefijoRelativoSubidas = 'img/productos/';
        } else {
            if (!is_dir($dirUploads)) {
                @mkdir($dirUploads, 0775, true);
            }
            $this->directorioSubidas = $dirUploads;
            $this->prefijoRelativoSubidas = 'uploads/';
        }
        
        if ($pdo instanceof PDO) {
            $this->pdo = $pdo;
        } else {
            $this->pdo = $this->obtenerConexion();
        }
    }

    /**
     * Establece la conexión segura a la base de datos con tolerancia multi-entorno.
     */
    private function obtenerConexion(): PDO
    {
        // 1. Intentar variable global $pdo si ya fue instanciada
        global $pdo;
        if (isset($pdo) && ($pdo instanceof PDO)) {
            return $pdo;
        }

        // 2. Cargar e intentar función conectar() oficial del proyecto
        if (!function_exists('conectar') && file_exists(dirname(__DIR__) . '/funciones/conexion.php')) {
            require_once dirname(__DIR__) . '/funciones/conexion.php';
        }
        if (function_exists('conectar')) {
            $conexion = conectar();
            if ($conexion instanceof PDO) {
                return $conexion;
            }
        }

        // 3. Contingencia mediante variables de entorno (Docker / servidor)
        $db   = getenv('DB_NAME') ?: '';
        $user = getenv('DB_USER') ?: '';
        $pass = getenv('DB_PASSWORD') ?: '';
        $host = getenv('DB_HOST') ?: 'localhost';
        $charset = 'utf8mb4';

        if ($db !== '' && $user !== '') {
            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            return new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 2
            ]);
        }

        throw new \RuntimeException("No fue posible conectar con la base de datos desde funciones/conexion.php.");
    }

    /**
     * Criterio RBAC: Verifica que el usuario tenga rol Administrador (1) o Supervisor (2).
     * Si no está autorizado, retorna HTTP 403 Forbidden y corta la ejecución.
     *
     * @return array Datos del usuario autenticado
     */
    public function verificarPermisosRBAC(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $idUsuario = null;
        $idRol = null;
        $nombre = 'Usuario';
        $rolTexto = '';

        // 1. Extraer desde sesión activa ($_SESSION['usuario'] o variables individuales)
        if (isset($_SESSION['usuario']) && is_array($_SESSION['usuario'])) {
            $idUsuario = $_SESSION['usuario']['id'] ?? $_SESSION['usuario']['id_funcionario'] ?? null;
            $nombre    = $_SESSION['usuario']['nombre_completo'] ?? $_SESSION['usuario']['nombre'] ?? 'Usuario';
            $rolTexto  = strtolower(trim((string)($_SESSION['usuario']['rol'] ?? '')));
            
            if ($rolTexto === 'administrador') $idRol = 1;
            elseif ($rolTexto === 'supervisor') $idRol = 2;
            elseif ($rolTexto === 'vendedor') $idRol = 3;
        }

        if ($idRol === null && isset($_SESSION['id_rol'])) {
            $idRol     = (int)$_SESSION['id_rol'];
            $idUsuario = $_SESSION['id_usuario'] ?? null;
            $nombre    = $_SESSION['nombre'] ?? $nombre;
            if ($idRol === 1) $rolTexto = 'administrador';
            elseif ($idRol === 2) $rolTexto = 'supervisor';
            elseif ($idRol === 3) $rolTexto = 'vendedor';
        }

        // 2. Extraer desde cookie JWT si la sesión de PHP no está inicializada
        if (($idRol === null || $idUsuario === null) && isset($_COOKIE['jwt_token'])) {
            $partesJwt = explode('.', $_COOKIE['jwt_token']);
            if (count($partesJwt) === 3) {
                $payloadJson = base64_decode(str_replace(['-', '_'], ['+', '/'], $partesJwt[1]));
                $payloadData = json_decode($payloadJson, true);
                if (is_array($payloadData) && isset($payloadData['exp']) && $payloadData['exp'] > time()) {
                    $idUsuario = $payloadData['id'] ?? null;
                    $idRol     = (int)($payloadData['rol'] ?? 0);
                    if ($idRol === 1) $rolTexto = 'administrador';
                    elseif ($idRol === 2) $rolTexto = 'supervisor';
                    elseif ($idRol === 3) $rolTexto = 'vendedor';
                }
            }
        }

        // 3. Validación estricta: Administrador (1) o Supervisor (2)
        $rolesPermitidosIds = [1, 2];
        $rolesPermitidosNombres = ['administrador', 'supervisor'];

        $esAutorizado = ($idRol !== null && in_array($idRol, $rolesPermitidosIds, true))
                     || in_array($rolTexto, $rolesPermitidosNombres, true);

        if (!$esAutorizado) {
            $this->denegarAcceso("Acceso denegado: Se requieren privilegios de Administrador o Supervisor para registrar productos en el catálogo.");
        }

        return [
            'id_usuario' => (int)$idUsuario,
            'nombre'     => $nombre,
            'id_rol'     => (int)$idRol,
            'rol'        => ucfirst($rolTexto)
        ];
    }

    /**
     * Bloquea la petición con HTTP 403 Forbidden y mensaje formal.
     */
    private function denegarAcceso(string $mensaje): void
    {
        http_response_code(403);
        
        // Registrar intento no autorizado en auditoría si se dispone de datos mínimos
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido', 0, 250);
            $stmt = $this->pdo->prepare("INSERT INTO auditoria (usuario_id, accion, detalles, ip, user_agent, fecha) VALUES (NULL, 'ACCESO_DENEGADO_PRODUCTOS', :detalles, :ip, :ua, NOW())");
            $stmt->execute([
                ':detalles' => substr($mensaje, 0, 250),
                ':ip'       => $ip,
                ':ua'       => $ua
            ]);
        } catch (\Throwable $e) {}

        echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden - Acceso Restringido</title>
    <link rel="stylesheet" href="../css/marketplus-theme.css?v=1">
    <link rel="stylesheet" href="../css/productos.css?v=2">
</head>
<body style="background: #f3f7ff; color: #0f172a; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px;">
    <div class="error-card">
        <div class="error-code">403</div>
        <div class="error-title">Acceso Prohibido (Forbidden)</div>
        <div class="error-msg">' . htmlspecialchars($mensaje) . '<br><br>Su rol actual no posee los privilegios de seguridad necesarios para publicar o alterar productos en el catálogo de MarketPlus.</div>
        <a href="../login.php" class="btn-return">Iniciar Sesión con Cuenta Autorizada</a>
    </div>
</body>
</html>';
        exit();
    }

    /**
     * Obtiene las categorías disponibles desde la base de datos para poblar el dropdown.
     */
    public function obtenerCategorias(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT id_categorias, nombre, descripcion FROM Categorias ORDER BY nombre ASC");
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("[ProductoController] Error al obtener categorías: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica la unicidad del Código / SKU en la tabla Productos.
     *
     * @param string $sku Código a validar
     * @param int|null $excluirIdProducto Si es edición, excluye el ID propio
     * @return bool True si el SKU ya existe (duplicado), False si está disponible
     */
    public function verificarSkuExiste(string $sku, ?int $excluirIdProducto = null): bool
    {
        $sql = "SELECT COUNT(*) FROM Productos WHERE LOWER(TRIM(codigo)) = LOWER(TRIM(:codigo)) AND activo = 1";
        $params = [':codigo' => $sku];

        if ($excluirIdProducto !== null) {
            $sql .= " AND id_producto != :id_excluir";
            $params[':id_excluir'] = $excluirIdProducto;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    /**
     * Valida y sanitiza los campos de texto y numéricos del formulario.
     */
    public function validarDatos(array $post): array
    {
        $errores = [];

        $codigo       = strtoupper(trim($post['codigo'] ?? ''));
        $nombre       = trim($post['nombre'] ?? '');
        $idCategoria  = (int)($post['id_categorias'] ?? 0);
        $precio       = isset($post['precio']) ? (float)$post['precio'] : -1;
        $stock        = isset($post['stock']) ? (int)$post['stock'] : -1;
        $descripcion  = trim($post['descripcion'] ?? '');

        // 1. Código / SKU
        if (empty($codigo)) {
            $errores['codigo'] = "El código / SKU del producto es obligatorio.";
        } elseif (!preg_match('/^[A-Z0-9_-]{3,50}$/', $codigo)) {
            $errores['codigo'] = "El SKU debe contener entre 3 y 50 caracteres alfanuméricos (letras, números, guiones).";
        }

        // 2. Nombre
        if (empty($nombre)) {
            $errores['nombre'] = "El nombre del producto es obligatorio.";
        } elseif (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 255) {
            $errores['nombre'] = "El nombre debe tener entre 3 y 255 caracteres.";
        }

        // 3. Categoría
        if ($idCategoria <= 0) {
            $errores['id_categorias'] = "Debe seleccionar una categoría válida.";
        } else {
            $stmtCat = $this->pdo->prepare("SELECT COUNT(*) FROM Categorias WHERE id_categorias = ?");
            $stmtCat->execute([$idCategoria]);
            if ($stmtCat->fetchColumn() == 0) {
                $errores['id_categorias'] = "La categoría seleccionada no existe en el sistema.";
            }
        }

        // 4. Precio Unitario
        if ($precio <= 0) {
            $errores['precio'] = "El precio debe ser un valor numérico positivo mayor a $0.";
        }

        // 5. Stock Inicial
        if ($stock < 0) {
            $errores['stock'] = "El stock inicial debe ser un número entero mayor o igual a 0.";
        }

        return [
            'es_valido' => empty($errores),
            'errores'   => $errores,
            'datos'     => [
                'codigo'        => $codigo,
                'nombre'        => $nombre,
                'id_categorias' => $idCategoria,
                'precio'        => (int)round($precio),
                'stock'         => $stock,
                'descripcion'   => $descripcion
            ]
        ];
    }

    /**
     * Procesa, valida y almacena físicamente la imagen del producto.
     * Criterio: Valida tipo MIME real en servidor con finfo, límite 2MB y renombra de forma segura.
     *
     * @param array $archivo Array correspondiente de $_FILES['imagen']
     * @param string $sku Código SKU sanitizado
     * @return array [exito => bool, ruta_relativa => string, error => string]
     */
    public function procesarImagen(array $archivo, string $sku): array
    {
        // 1. Validar si se adjuntó archivo y no hubo error de subida
        if (!isset($archivo['error']) || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
            // Imagen no subida: se permite usar fallback corporativo si es opcional
            return [
                'exito'         => true,
                'ruta_relativa' => 'img/default-product.svg',
                'es_fallback'   => true
            ];
        }

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            $mensajesError = [
                UPLOAD_ERR_INI_SIZE   => "El archivo excede el tamaño máximo permitido por el servidor.",
                UPLOAD_ERR_FORM_SIZE  => "El archivo excede el límite del formulario.",
                UPLOAD_ERR_PARTIAL    => "El archivo fue subido solo parcialmente.",
                UPLOAD_ERR_NO_TMP_DIR => "Falta la carpeta temporal en el servidor.",
                UPLOAD_ERR_CANT_WRITE => "Fallo al escribir el archivo en el disco.",
                UPLOAD_ERR_EXTENSION  => "Una extensión de PHP detuvo la carga del archivo."
            ];
            return [
                'exito' => false,
                'error' => $mensajesError[$archivo['error']] ?? "Error desconocido al subir la imagen."
            ];
        }

        // 2. Validación de tamaño (Límite estricto de 2 MB)
        if ($archivo['size'] > $this->tamanoMaximoBytes) {
            return [
                'exito' => false,
                'error' => "El tamaño de la imagen supera el límite permitido de 2 MB (tamaño actual: " . round($archivo['size'] / 1048576, 2) . " MB)."
            ];
        }

        // 3. Validación de Tipo MIME real en el servidor (finfo)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeReal = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);

        if (!array_key_exists($mimeReal, $this->mimesPermitidos)) {
            return [
                'exito' => false,
                'error' => "Formato de imagen inválido ({$mimeReal}). Solo se permiten archivos .jpg, .jpeg, .png y .webp."
            ];
        }

        // 4. Sanitización y Nomenclatura Única anti-Path Traversal y anti-RCE
        $extension = $this->mimesPermitidos[$mimeReal];
        $skuLimpio  = preg_replace('/[^a-zA-Z0-9_-]/', '', $sku);
        $timestamp  = time();
        $randomHash = bin2hex(random_bytes(6));
        $nombreFinal = sprintf("prod_%s_%d_%s.%s", $skuLimpio, $timestamp, $randomHash, $extension);
        
        $rutaDestinoAbsoluta = $this->directorioSubidas . $nombreFinal;

        // 5. Mover archivo con verificación de seguridad
        if (!move_uploaded_file($archivo['tmp_name'], $rutaDestinoAbsoluta)) {
            return [
                'exito' => false,
                'error' => "No fue posible almacenar la imagen en el directorio del servidor. Verifique los permisos de escritura."
            ];
        }

        @chmod($rutaDestinoAbsoluta, 0644);

        return [
            'exito'         => true,
            'ruta_relativa' => $this->prefijoRelativoSubidas . $nombreFinal,
            'es_fallback'   => false
        ];
    }

    /**
     * Flujo principal: Registra un nuevo producto cumpliendo todas las reglas de negocio.
     *
     * @param array $post Datos de $_POST
     * @param array $files Datos de $_FILES
     * @param array $usuario Datos del usuario emisor autenticado
     * @return array Resultado de la operación
     */
    public function registrarProducto(array $post, array $files, array $usuario): array
    {
        // 1. Validar campos
        $validacion = $this->validarDatos($post);
        if (!$validacion['es_valido']) {
            return [
                'exito'   => false,
                'errores' => $validacion['errores'],
                'mensaje' => "Existen campos con formato incorrecto o incompletos."
            ];
        }

        $datos = $validacion['datos'];

        // 2. Regla de Negocio: Validación de Unicidad de SKU antes de procesar imagen
        if ($this->verificarSkuExiste($datos['codigo'])) {
            return [
                'exito'   => false,
                'errores' => ['codigo' => "El código / SKU '{$datos['codigo']}' ya se encuentra registrado. Debe indicar un SKU único."],
                'mensaje' => "El código / SKU ingresado ya existe en el catálogo. No se procesó ninguna imagen."
            ];
        }

        // 3. Procesar y almacenar imagen física en servidor
        $resultadoImagen = $this->procesarImagen($files['imagen'] ?? [], $datos['codigo']);
        if (!$resultadoImagen['exito']) {
            return [
                'exito'   => false,
                'errores' => ['imagen' => $resultadoImagen['error']],
                'mensaje' => $resultadoImagen['error']
            ];
        }

        $rutaImagenDb = $resultadoImagen['ruta_relativa'];

        // 4. Inserción en MySQL con Consulta Preparada contra SQLi
        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO Productos (
                        id_categorias, 
                        nombre, 
                        descripcion, 
                        precio, 
                        stock, 
                        imagen, 
                        codigo, 
                        activo
                    ) VALUES (
                        :id_categorias, 
                        :nombre, 
                        :descripcion, 
                        :precio, 
                        :stock, 
                        :imagen, 
                        :codigo, 
                        1
                    )";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_categorias' => $datos['id_categorias'],
                ':nombre'        => $datos['nombre'],
                ':descripcion'   => $datos['descripcion'],
                ':precio'        => $datos['precio'],
                ':stock'         => $datos['stock'],
                ':imagen'        => $rutaImagenDb,
                ':codigo'        => $datos['codigo']
            ]);

            $nuevoIdProducto = (int)$this->pdo->lastInsertId();

            $this->pdo->commit();

            // 5. Criterio de Auditoría (fuera de la transacción para que nunca aborte el registro)
            $this->registrarAuditoria(
                (int)$usuario['id_usuario'],
                $nuevoIdProducto,
                $datos['codigo'],
                $datos['nombre'],
                $datos['precio'],
                $datos['stock']
            );

            return [
                'exito'           => true,
                'mensaje'         => "¡Producto registrado exitosamente en el catálogo oficial!",
                'id_producto'     => $nuevoIdProducto,
                'codigo'          => $datos['codigo'],
                'nombre'          => $datos['nombre'],
                'imagen_guardada' => $rutaImagenDb
            ];

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            // Si falló la BD y se había subido una imagen física nueva, eliminarla para no dejar huérfanos
            if (!$resultadoImagen['es_fallback'] && file_exists(dirname(__DIR__) . '/' . $rutaImagenDb)) {
                @unlink(dirname(__DIR__) . '/' . $rutaImagenDb);
            }

            error_log("[ProductoController] Error crítico al insertar producto: " . $e->getMessage());

            return [
                'exito'   => false,
                'mensaje' => "Ocurrió un error en el servidor al intentar guardar el producto. Por favor intente nuevamente."
            ];
        }
    }

    /**
     * Registra el evento en la tabla de auditoría corporativa.
     */
    private function registrarAuditoria(int $usuarioId, int $idProducto, string $codigo, string $nombre, int $precio, int $stock): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido', 0, 250);
        $detalles = sprintf(
            "Creación de nuevo producto. ID: %d | SKU: %s | Nombre: %s | Precio: $%s | Stock: %d",
            $idProducto,
            $codigo,
            $nombre,
            number_format($precio, 0, ',', '.'),
            $stock
        );

        // Registro en tabla auditoria
        try {
            $stmt = $this->pdo->prepare("INSERT INTO auditoria (usuario_id, accion, detalles, ip, user_agent, fecha) VALUES (:uid, 'CREAR_PRODUCTO', :detalles, :ip, :ua, NOW())");
            $stmt->execute([
                ':uid'      => $usuarioId ?: null,
                ':detalles' => $detalles,
                ':ip'       => $ip,
                ':ua'       => $ua
            ]);
        } catch (\Throwable $e) {
            error_log("[ProductoController] Aviso de auditoria: " . $e->getMessage());
        }

        // Registro complementario en logs_seguridad
        try {
            $stmtLog = $this->pdo->prepare("INSERT INTO logs_seguridad (usuario, accion, detalle, ip_origen) VALUES (?, 'Registro de Producto', ?, ?)");
            $stmtLog->execute([
                $_SESSION['usuario']['nombre'] ?? 'Funcionario',
                substr($detalles, 0, 250),
                $ip
            ]);
        } catch (\Throwable $e) {}
    }

    /**
     * Obtiene los productos registrados para la vista de inventario.
     */
    public function obtenerListadoProductos(int $limite = 50, string $busqueda = '', int $idCategoria = 0): array
    {
        $sql = "SELECT p.*, c.nombre AS categoria_nombre 
                FROM Productos p 
                INNER JOIN Categorias c ON p.id_categorias = c.id_categorias 
                WHERE p.activo = 1";
        $params = [];

        if (!empty($busqueda)) {
            $sql .= " AND (p.nombre LIKE :b1 OR p.codigo LIKE :b2 OR p.descripcion LIKE :b3)";
            $params[':b1'] = "%$busqueda%";
            $params[':b2'] = "%$busqueda%";
            $params[':b3'] = "%$busqueda%";
        }

        if ($idCategoria > 0) {
            $sql .= " AND p.id_categorias = :cat";
            $params[':cat'] = $idCategoria;
        }

        $sql .= " ORDER BY p.id_producto DESC LIMIT " . (int)$limite;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
