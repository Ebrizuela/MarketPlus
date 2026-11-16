<?php
require_once 'funciones/conexion.php';

try {
    $hash = password_hash('Lider2026', PASSWORD_BCRYPT);
    $pdo->exec("UPDATE funcionarios SET contrasena = '$hash'");
    echo "✅ Todas las contraseñas fueron actualizadas a Lider2026 exitosamente.";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>