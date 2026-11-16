<?php
// Funciones del módulo de recuperación de contraseñas

$rutasAutoload = [
    dirname(__DIR__, 2) . '/vendor/autoload.php',
    dirname(__DIR__) . '/vendor/autoload.php',
    '/opt/phpmailer/vendor/autoload.php',
];

foreach ($rutasAutoload as $rutaAutoload) {
    if (is_file($rutaAutoload)) {
        require_once $rutaAutoload;
        break;
    }
}

use PHPMailer\PHPMailer\PHPMailer;

function validarFortalezaClave($clave) {
    if (!is_string($clave) || strlen($clave) > 72) {
        return false;
    }

    // Mínimo 12 caracteres, al menos 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial
    $patron = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{12,}$/u';
    return preg_match($patron, $clave);
}

function asegurarTablaRecuperacion(PDO $pdo): void {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS recuperacion_tokens (
            id_recuperacion BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            correo VARCHAR(255) NOT NULL,
            token CHAR(64) NOT NULL,
            expira_en DATETIME NOT NULL,
            usado TINYINT(1) NOT NULL DEFAULT 0,
            UNIQUE KEY uq_recuperacion_tokens_token (token),
            KEY idx_recuperacion_tokens_correo_usado (correo, usado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function generarTokenRecuperacion($pdo, $correo) {
    asegurarTablaRecuperacion($pdo);

    $baseUrl = trim(getenv('APP_BASE_URL') ?: '');
    if ($baseUrl === '') {
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        $https = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
            || (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';
        $scheme = $https ? 'https' : 'http';
        $scriptDir = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $basePath = rtrim($scriptDir, '/\\');
        if ($basePath === '.' || $basePath === DIRECTORY_SEPARATOR) {
            $basePath = '';
        }
        $baseUrl = $scheme . '://' . $host . $basePath;
    }
    $baseUrl = rtrim($baseUrl, '/');
    $partesUrl = parse_url($baseUrl);
    $host = strtolower($partesUrl['host'] ?? '');
    $esLocal = in_array($host, ['localhost', '127.0.0.1', '::1'], true);

    if (!$partesUrl || $host === '' || (! $esLocal && strtolower($partesUrl['scheme'] ?? '') !== 'https')) {
        throw new RuntimeException('APP_BASE_URL debe ser una URL HTTPS pública; HTTP solo se permite en localhost.');
    }

    // Generar token criptográfico seguro de 64 caracteres
    $token = bin2hex(random_bytes(32));
    $tokenAlmacenado = hash('sha256', $token);

    // Expiración estricta de 30 minutos
    $fecha_expiracion = date('Y-m-d H:i:s', strtotime('+30 minutes'));

    $pdo->beginTransaction();
    try {
        // Invalidar solicitudes anteriores pendientes
        $stmtUpdate = $pdo->prepare("UPDATE recuperacion_tokens SET usado = 1 WHERE correo = :correo AND usado = 0");
        $stmtUpdate->execute(['correo' => $correo]);

        // Registrar nuevo token solo si se pudo enviar el correo
        $stmtInsert = $pdo->prepare("INSERT INTO recuperacion_tokens (correo, token, expira_en, usado) VALUES (:correo, :token, :expira, 0)");
        $stmtInsert->execute([
            'correo' => $correo,
            'token' => $tokenAlmacenado,
            'expira' => $fecha_expiracion
        ]);

        $datos = [
            'token' => $token,
            'expira_en' => $fecha_expiracion,
            'enlace' => $baseUrl . "/administrador/restablecer_clave.php?token=" . urlencode($token)
        ];

        if (!enviarCorreoRecuperacion($correo, $token, $datos['enlace'])) {
            throw new RuntimeException('El servidor no aceptó el envío del correo de recuperación.');
        }

        $pdo->commit();
        return $datos;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error creando recuperación para ' . $correo . ': ' . $e->getMessage());
        throw $e;
    }
}

function obtenerTokenValido($pdo, $token) {
    asegurarTablaRecuperacion($pdo);

    if (!is_string($token) || !preg_match('/\A[a-f0-9]{64}\z/i', $token)) {
        return ['valido' => false, 'error' => 'El enlace no es válido o ya fue utilizado.'];
    }

    $stmt = $pdo->prepare("SELECT * FROM recuperacion_tokens WHERE token = :token AND usado = 0 LIMIT 1");
    $stmt->execute(['token' => hash('sha256', $token)]);
    $datos = $stmt->fetch();

    if (!$datos) {
        return ['valido' => false, 'error' => 'El enlace no es válido o ya fue utilizado.'];
    }

    $ahora = date('Y-m-d H:i:s');
    if ($ahora > $datos['expira_en']) {
        return ['valido' => false, 'error' => 'El enlace de recuperación ha expirado (máximo 30 minutos).'];
    }

    return ['valido' => true, 'datos' => $datos];
}

function actualizarClaveUsuario($pdo, $correo, $id_recuperacion, $nuevaClave) {
    $clave_hasheada = password_hash($nuevaClave, PASSWORD_BCRYPT);

    $pdo->beginTransaction();
    try {
        $tokenStmt = $pdo->prepare("SELECT expira_en FROM recuperacion_tokens WHERE id_recuperacion = :id AND correo = :correo AND usado = 0 FOR UPDATE");
        $tokenStmt->execute(['id' => $id_recuperacion, 'correo' => $correo]);
        $token = $tokenStmt->fetch();

        if (!$token || strtotime($token['expira_en']) <= time()) {
            $pdo->rollBack();
            return false;
        }

        // Actualizar en funcionarios
        $updFunc = $pdo->prepare("UPDATE funcionarios SET contrasena = :clave WHERE correo = :correo");
        $updFunc->execute(['clave' => $clave_hasheada, 'correo' => $correo]);
        $usuariosActualizados = $updFunc->rowCount();

        // Actualizar en Clientes
        $updCli = $pdo->prepare("UPDATE Clientes SET clave = :clave WHERE correo = :correo");
        $updCli->execute(['clave' => $clave_hasheada, 'correo' => $correo]);
        $usuariosActualizados += $updCli->rowCount();

        if ($usuariosActualizados === 0) {
            throw new RuntimeException('No existe una cuenta asociada al token de recuperación.');
        }

        // Marcar token como consumido
        $updToken = $pdo->prepare("UPDATE recuperacion_tokens SET usado = 1 WHERE id_recuperacion = :id AND usado = 0");
        $updToken->execute(['id' => $id_recuperacion]);
        if ($updToken->rowCount() !== 1) {
            throw new RuntimeException('El token de recuperación ya fue utilizado.');
        }

        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error actualizando contraseña de recuperación: ' . $e->getMessage());
        return false;
    }
}

function enviarCorreoRecuperacion($correo, $token, $enlace) {
    if (!class_exists(PHPMailer::class)) {
        $remitente = trim(getenv('mail_from') ?: getenv('SMTP_FROM_EMAIL') ?: '');
        if ($remitente === '') {
            $dominio = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
            $dominio = parse_url('http://' . $dominio, PHP_URL_HOST) ?: 'localhost';
            $remitente = 'no-reply@' . $dominio;
        }

        if (!filter_var($remitente, FILTER_VALIDATE_EMAIL)) {
            error_log('Correo recuperación: remitente inválido para mail() nativo.');
            return false;
        }

        $asunto = '=?UTF-8?B?' . base64_encode('Recuperación de clave - MarketPlus') . '?=';
        $cuerpo = "Hola,\n\n"
            . "Has solicitado restablecer tu contraseña en MarketPlus.\n\n"
            . "Token de autorización: " . $token . "\n"
            . "Enlace seguro de recuperación: " . $enlace . "\n\n"
            . "Este enlace expira en un máximo de 30 minutos.\n"
            . "Si no solicitaste este cambio, puedes ignorar este mensaje.";
        $cabeceras = "From: MarketPlus <{$remitente}>\r\n"
            . "Reply-To: {$remitente}\r\n"
            . "MIME-Version: 1.0\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . 'X-Mailer: PHP/' . phpversion();

        $aceptado = mail($correo, $asunto, $cuerpo, $cabeceras);
        if (!$aceptado) {
            error_log('Correo recuperación: mail() nativo rechazó el mensaje.');
        }
        return $aceptado;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->Host = trim(getenv('mail_host') ?: getenv('SMTP_HOST') ?: '');
        $mail->SMTPAuth = filter_var(getenv('mail_auth') ?: getenv('SMTP_AUTH') ?: 'false', FILTER_VALIDATE_BOOLEAN);
        $mail->Username = getenv('mail_username') ?: getenv('SMTP_USERNAME') ?: '';
        $mail->Password = getenv('mail_password') ?: getenv('SMTP_PASSWORD') ?: '';
        $mail->Port = (int) (getenv('mail_port') ?: getenv('SMTP_PORT') ?: 587);
        $mail->CharSet = 'UTF-8';

        if ($mail->Host !== '') {
            $mail->isSMTP();

            if ($mail->SMTPAuth && ($mail->Username === '' || $mail->Password === '')) {
                throw new RuntimeException('La autenticación SMTP está activa, pero faltan mail_username o mail_password.');
            }

            $seguridad = strtolower(getenv('mail_encryption') ?: getenv('SMTP_SECURE') ?: 'tls');
            if ($seguridad === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($seguridad === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($seguridad !== 'none' && $seguridad !== '') {
                throw new RuntimeException('mail_encryption debe ser tls, ssl o none.');
            }
        } else {
            $mail->isMail();
        }

        $remitente = trim(getenv('mail_from') ?: getenv('SMTP_FROM_EMAIL') ?: $mail->Username);
        if ($remitente === '') {
            $dominio = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
            $dominio = parse_url('http://' . $dominio, PHP_URL_HOST) ?: 'localhost';
            $remitente = 'no-reply@' . $dominio;
        }
        if (!filter_var($remitente, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('mail_from debe ser una dirección de correo válida.');
        }

        $mail->setFrom($remitente, getenv('mail_from_name') ?: getenv('SMTP_FROM_NAME') ?: 'MarketPlus');
        $mail->addAddress($correo);
        $mail->Subject = 'Recuperación de clave - MarketPlus';
        $mail->Body = "Hola,\n\n"
            . "Has solicitado restablecer tu contraseña en MarketPlus.\n\n"
            . "Token de autorización: " . $token . "\n"
            . "Enlace seguro de recuperación: " . $enlace . "\n\n"
            . "Este enlace expira en un máximo de 30 minutos.\n"
            . "Si no solicitaste este cambio, puedes ignorar este mensaje.";

        return $mail->send();
    } catch (\Throwable $e) {
        error_log('Error enviando correo de recuperación: ' . $e->getMessage() . ' ' . $mail->ErrorInfo);
        return false;
    }
}