<?php
require_once __DIR__ . '/../includes/auth.php';

requerirRol(['vendedor']);
header('Location: dashboard.php');
exit;
