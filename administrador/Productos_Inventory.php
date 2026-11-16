<?php
require_once __DIR__ . '/../funciones/navegacion.php';

requerirRol(['administrador']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos e inventario - MarketPlus</title>
    <link rel="stylesheet" href="../css/navegacion.css">
    <style>
        body { margin: 0; background: #f2f5f7; color: #1e293b; font-family: Arial, sans-serif; }
        main { max-width: 1000px; margin: 32px auto; padding: 0 20px; }
        .module-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 16px; }
        .module-card { display: block; padding: 22px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; color: inherit; text-decoration: none; box-shadow: 0 4px 16px rgba(15, 23, 42, .06); }
        .module-card:hover { border-color: #2b6cb0; transform: translateY(-2px); }
        .module-card h2 { margin: 0 0 8px; font-size: 19px; }
        .module-card p { margin: 0; color: #64748b; line-height: 1.5; }
    </style>
    <link rel="stylesheet" href="../css/marketplus-theme.css?v=1">
</head>
<body>
    <?php renderNavegacion(null, BASE_URL . '/index.php'); ?>
    <main>
        <h1>Productos e inventario</h1>
        <p>Selecciona una tarea para gestionar el catálogo y el stock.</p>
        <div class="module-grid">
            <a class="module-card" href="inventario.php">
                <h2>Control de inventario</h2>
                <p>Consulta productos, categorías, precios y existencias.</p>
            </a>
            <a class="module-card" href="categorias.php">
                <h2>Gestionar categorías</h2>
                <p>Crear categorías y revisar las existentes.</p>
            </a>
            <a class="module-card" href="../index.php">
                <h2>Catálogo y productos</h2>
                <p>Revisar el catálogo y usar las herramientas de producto disponibles para administración.</p>
            </a>
        </div>
    </main>
</body>
</html>
