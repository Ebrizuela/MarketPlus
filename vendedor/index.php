<?php
require_once __DIR__ . '/../includes/auth.php';

requerirRol(['vendedor']);
header('Location: ../ventas/consultar_ventas.php');
exit;
