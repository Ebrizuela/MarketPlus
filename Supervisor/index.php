<?php
require_once __DIR__ . '/../includes/auth.php';

requerirRol(['supervisor']);
header('Location: Dashboard_Supervisor.php');
exit;
