<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once '../funciones/conexion.php';

header('Content-Type: application/json; charset=utf-8');

try {

    $fecha_inicio = $_GET['fecha_inicio'] ?? '';
    $fecha_fin    = $_GET['fecha_fin'] ?? '';
    $id_cliente   = !empty($_GET['id_cliente']) ? $_GET['id_cliente'] : null;
    $id_producto  = !empty($_GET['id_producto']) ? $_GET['id_producto'] : null;


    $where = ["1=1"];
    $params = [];

    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    
        $where[] = "v.fecha_hora BETWEEN :fecha_inicio AND :fecha_fin";
        $params[':fecha_inicio'] = $fecha_inicio . ' 00:00:00';
        $params[':fecha_fin']    = $fecha_fin . ' 23:59:59';
    }

    if (!empty($id_cliente)) {
        $where[] = "v.id_cliente = :id_cliente";
        $params[':id_cliente'] = $id_cliente;
    }

    if (!empty($id_producto)) {
        $where[] = "dv.id_producto = :id_producto";
        $params[':id_producto'] = $id_producto;
    }

    $whereSql = implode(" AND ", $where);

    $sql = "SELECT 
                v.id_ventas,
                v.fecha_hora,
                CONCAT(c.nombre, ' ', c.apellidos) AS cliente,
                p.nombre AS producto,
                dv.cantidad,
                dv.precio_unitario,
                dv.subtotal,
                v.total_venta
            FROM Ventas v
            INNER JOIN Clientes c ON v.id_cliente = c.id_cliente
            INNER JOIN Detalle_Ventas dv ON v.id_ventas = dv.id_ventas
            INNER JOIN Productos p ON dv.id_producto = p.id_producto
            WHERE $whereSql
            ORDER BY v.fecha_hora DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);


    echo json_encode([
        'status' => 'success',
        'data' => $reportes
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al consultar datos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}