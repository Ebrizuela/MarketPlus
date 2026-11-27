<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$idFuncionarioSesion = filter_var(
    $_SESSION['id_funcionario'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
$rolSesion = is_string($_SESSION['rol'] ?? null) ? strtolower(trim($_SESSION['rol'])) : null;
if (
    $idFuncionarioSesion === false
    || $idFuncionarioSesion === null
    || !is_string($rolSesion)
    || !in_array($rolSesion, ['administrador', 'vendedor'], true)
) {
    header('Location: ../login.php');
    exit;
}
$idFuncionarioSesion = (int) $idFuncionarioSesion;

require_once '../funciones/conexion.php';
require_once '../funciones/autorizacion_ventas.php';

$mediosPago = [];
$errorCarga = '';
$csrfToken = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
try {
    $pdo = conectar();
    if (!funcionarioTieneAccesoVentas($pdo, $idFuncionarioSesion, $rolSesion)) {
        session_unset();
        session_destroy();
        header('Location: ../login.php');
        exit;
    }

    $stmt = $pdo->query("SELECT id_medio_pago, nombre_medio FROM Medio_pago");
    $mediosPago = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log('No fue posible cargar los medios de pago: ' . $e->getMessage());
    $errorCarga = 'No fue posible cargar los medios de pago. Recargue la página o contacte a soporte.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <title>Módulo de Ventas</title>
    <link rel="stylesheet" href="../css/ventas.css">
</head>
<body>

<div class="container">
    <h2 class="page-title">Módulo de Ventas - Registrar Transacción</h2>
    <?php if ($rolSesion === 'administrador'): ?>
        <p><a class="btn btn-admin-home" href="/administrador/index.php">Volver al panel de administración</a></p>
    <?php endif; ?>
    <?php if ($errorCarga !== ''): ?>
        <p class="page-error" role="alert"><?= htmlspecialchars($errorCarga, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <!-- 1. Datos del Cliente -->
    <div class="card">
        <div class="card-header">
            <span class="step-badge">1</span> Datos del Cliente
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label for="rut_cliente">RUT del Cliente</label>
                    <div class="input-inline">
                        <input type="text" id="rut_cliente" class="form-control" placeholder="12345678-K" autocomplete="off" aria-describedby="rut-feedback">
                        <button class="btn btn-secondary" id="verificar_cliente" type="button">Verificar</button>
                    </div>
                    <div id="rut-feedback" class="feedback-text" role="status" aria-live="polite"></div>
                </div>
                <div class="form-group">
                    <label for="nombre_cliente">Nombre Cliente</label>
                    <input type="text" id="nombre_cliente" class="form-control" readonly placeholder="Búsqueda por RUT...">
                    <input type="hidden" id="id_cliente">
                </div>
            </div>
            <div id="formulario_nuevo_cliente" class="new-customer-form" hidden>
                <p>No encontramos un cliente con este RUT. Ingrese sus datos para crearlo.</p>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nuevo_nombre">Nombre</label>
                        <input type="text" id="nuevo_nombre" class="form-control" maxlength="45" autocomplete="given-name">
                    </div>
                    <div class="form-group">
                        <label for="nuevo_apellidos">Apellidos</label>
                        <input type="text" id="nuevo_apellidos" class="form-control" maxlength="45" autocomplete="family-name">
                    </div>
                    <div class="form-group">
                        <label for="nuevo_correo">Correo</label>
                        <input type="email" id="nuevo_correo" class="form-control" maxlength="45" autocomplete="email">
                    </div>
                    <div class="form-group">
                        <label for="nuevo_telefono">Teléfono</label>
                        <input type="tel" id="nuevo_telefono" class="form-control" maxlength="45" autocomplete="tel">
                    </div>
                    <div class="form-group">
                        <button class="btn btn-secondary" id="crear_cliente" type="button">Crear cliente</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Agregar Productos -->
    <div class="card">
        <div class="card-header">
            <span class="step-badge">2</span> Agregar Productos
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group product-selector">
                    <label for="select_producto">Producto</label>
                    <select id="select_producto" class="form-select"></select>
                </div>
                <div class="form-group">
                    <label for="cantidad_producto">Cantidad</label>
                    <input type="number" id="cantidad_producto" class="form-control" value="1" min="1" step="1">
                </div>
                <div class="form-group">
                    <button class="btn btn-primary" id="agregar_producto" type="button">+ Agregar producto</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Detalle de la Venta -->
    <div class="card">
        <div class="card-header">
            <span class="step-badge">3</span> Detalle de la Venta
        </div>
        <div class="card-body">
            <table class="tabla-detalle">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio U.</th>
                        <th>Subtotal</th>
                        <th style="text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody id="tabla_detalle">
                    <tr>
                        <td colspan="5" class="tabla-empty">No hay productos agregados a la venta.</td>
                    </tr>
                </tbody>
            </table>
            <div class="total-box">
                Total General: $<span id="total_general">0</span>
            </div>
        </div>
    </div>

    <!-- 4. Pago y Confirmación -->
    <div class="card">
        <div class="card-body">
            <div class="checkout-grid">
                <div class="form-group">
                    <label for="id_medio_pago">Medio de Pago</label>
                    <select id="id_medio_pago" class="form-select">
                        <?php foreach ($mediosPago as $mp): ?>
                            <option value="<?= $mp['id_medio_pago'] ?>"><?= htmlspecialchars($mp['nombre_medio']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <button class="btn btn-success" id="finalizar_venta" type="button" <?= $errorCarga !== '' || count($mediosPago) === 0 ? 'disabled' : '' ?>>Guardar y Registrar Venta</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="toast-container" class="toast-container" aria-live="polite" aria-atomic="true"></div>

<!-- Carga del script JS externo -->
<script src="../js/registrar_venta.js"></script>
</body>
</html>