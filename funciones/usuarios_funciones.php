<?php

function validarContrasena($contrasena) {
    return strlen($contrasena) >= 8
        && preg_match('/[A-Za-z]/', $contrasena)
        && preg_match('/[0-9]/', $contrasena);
}

function normalizarCorreo($correo) {
    return strtolower(trim($correo));
}

function existeCorreo($pdo, $correo, $idExcluir = null) {
    $sql = "SELECT COUNT(*) FROM funcionarios WHERE LOWER(TRIM(correo)) = :correo";
    $parametros = [':correo' => normalizarCorreo($correo)];
    if ($idExcluir !== null) {
        $sql .= ' AND id_funcionario <> :id';
        $parametros[':id'] = $idExcluir;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);
    return (bool) $stmt->fetchColumn();
}

function registrarPersona($pdo, $nombre, $apellido, $correo, $contrasena, $id_rol) {
    $nombre = trim($nombre);
    $apellido = trim($apellido);
    $correo = normalizarCorreo($correo);

    if ($nombre === '' || $apellido === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Ingresa nombre, apellido y un correo válido.');
    }

    if (strlen($correo) > 45) {
        throw new InvalidArgumentException('El correo no puede superar los 45 caracteres permitidos por la base de datos.');
    }

    if (existeCorreo($pdo, $correo)) {
        throw new DomainException('El correo electrónico ya se encuentra registrado.');
    }

    $hashContrasena = password_hash($contrasena, PASSWORD_BCRYPT);

    $rutDefault       = '11.111.111-9';
    $direccionDefault = 'Sin Información';
    $telefonoDefault  = '+56900000000';

    $sql = "INSERT INTO funcionarios (id_rol, nombre, apellido, rut, direccion, telefono, correo, contrasena) 
            VALUES (:id_rol, :nombre, :apellido, :rut, :direccion, :telefono, :correo, :contrasena)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':id_rol'     => $id_rol,
        ':nombre'     => $nombre,
        ':apellido'   => $apellido,
        ':rut'        => $rutDefault,
        ':direccion'  => $direccionDefault,
        ':telefono'   => $telefonoDefault,
        ':correo'     => $correo,
        ':contrasena' => $hashContrasena
    ]);
}

function enviarCorreoBienvenida($nombre, $apellido, $correo) {
    $asunto = 'Confirmación de Registro - MarketPlus';
    $mensaje = "Hola $nombre $apellido,\n\nTu cuenta en MarketPlus ha sido creada con éxito.\n\nSaludos,\nEquipo MarketPlus";
    $cabeceras = "From: no-reply@marketplus.cl\r\n" .
                 "Reply-To: no-reply@marketplus.cl\r\n" .
                 "X-Mailer: PHP/" . phpversion();

    $avisoCorreo = null;
    set_error_handler(function ($nivel, $texto) use (&$avisoCorreo) {
        $avisoCorreo = $texto;
        return true;
    });

    try {
        $enviado = mail($correo, $asunto, $mensaje, $cabeceras);
    } finally {
        restore_error_handler();
    }

    if (!$enviado) {
        throw new RuntimeException('mail() no pudo entregar el mensaje: ' . ($avisoCorreo ?: 'el agente de correo rechazó el envío.'));
    }

    return true;
}

function registrarPersonaConConfirmacion($pdo, $nombre, $apellido, $correo, $contrasena, $id_rol) {
    $correo = normalizarCorreo($correo);
    $bloqueoNombre = 'marketplus-email-' . substr(hash('sha256', $correo), 0, 47);
    $bloqueo = $pdo->prepare('SELECT GET_LOCK(:nombre, 10)');
    $bloqueo->execute([':nombre' => $bloqueoNombre]);

    if ((int) $bloqueo->fetchColumn() !== 1) {
        throw new RuntimeException('No se pudo comprobar si el correo está disponible. Inténtalo nuevamente.');
    }

    try {
        $pdo->beginTransaction();
        if (existeCorreo($pdo, $correo)) {
            throw new DomainException('El correo electrónico ya se encuentra registrado.');
        }

        if (!registrarPersona($pdo, $nombre, $apellido, $correo, $contrasena, $id_rol)) {
            throw new RuntimeException('No se pudo guardar el usuario.');
        }

        enviarCorreoBienvenida($nombre, $apellido, $correo);
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    } finally {
        try {
            $liberarBloqueo = $pdo->prepare('SELECT RELEASE_LOCK(:nombre)');
            $liberarBloqueo->execute([':nombre' => $bloqueoNombre]);
        } catch (Throwable $error) {
            error_log($error->getMessage());
        }
    }
}

function obtenerRoles($pdo) {
    $stmt = $pdo->query("SELECT id_rol, nombre FROM Roles WHERE estado = 'Activo'");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerSupervisores($pdo) {
    $sql = "SELECT f.id_funcionario, f.nombre, f.apellido, f.correo, f.id_rol, r.nombre AS nombre_rol
            FROM funcionarios f
            INNER JOIN Roles r ON f.id_rol = r.id_rol
            WHERE r.nombre = 'Supervisor'
            ORDER BY f.id_funcionario ASC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerFuncionarioPorId($pdo, $id_funcionario) {
    $stmt = $pdo->prepare('SELECT * FROM funcionarios WHERE id_funcionario = :id');
    $stmt->execute([':id' => $id_funcionario]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function obtenerSupervisorPorId($pdo, $id_funcionario) {
    $stmt = $pdo->prepare("SELECT f.*, r.nombre AS nombre_rol
                           FROM funcionarios f
                           INNER JOIN Roles r ON f.id_rol = r.id_rol
                           WHERE f.id_funcionario = :id AND r.nombre = 'Supervisor'");
    $stmt->execute([':id' => $id_funcionario]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function actualizarFuncionario($pdo, $id_funcionario, $nombre, $apellido, $correo, $id_rol) {
    $nombre = trim((string) $nombre);
    $apellido = trim((string) $apellido);
    $correo = normalizarCorreo($correo);
    $id_rol = filter_var($id_rol, FILTER_VALIDATE_INT);

    if ($nombre === '' || $apellido === '' || $correo === '' || !$id_rol) {
        throw new Exception("Todos los campos obligatorios deben ser completados.");
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("El formato del correo electrónico no es válido.");
    }

    if (strlen($correo) > 45) {
        throw new Exception("El correo no puede superar los 45 caracteres permitidos por la base de datos.");
    }

    $rol = $pdo->prepare("SELECT nombre FROM Roles WHERE id_rol = :id AND estado = 'Activo'");
    $rol->execute([':id' => $id_rol]);
    $nombreRolNuevo = $rol->fetchColumn();
    if (!$nombreRolNuevo) {
        throw new InvalidArgumentException("El rol seleccionado no está activo.");
    }

    $nombreBloqueo = 'marketplus-email-' . substr(hash('sha256', $correo), 0, 47);
    $bloqueo = $pdo->prepare('SELECT GET_LOCK(:nombre, 10)');
    $bloqueo->execute([':nombre' => $nombreBloqueo]);
    if ((int) $bloqueo->fetchColumn() !== 1) {
        throw new RuntimeException('No se pudo comprobar si el correo está disponible. Inténtalo nuevamente.');
    }

    try {
        if (existeCorreo($pdo, $correo, $id_funcionario)) {
            throw new DomainException("El correo electrónico ya se encuentra registrado.");
        }

        $pdo->beginTransaction();
        try {
            $supervisor = $pdo->prepare("SELECT f.id_funcionario, f.nombre, f.apellido, f.correo, f.id_rol, r.nombre AS nombre_rol
                                         FROM funcionarios f
                                         INNER JOIN Roles r ON f.id_rol = r.id_rol
                                         WHERE f.id_funcionario = :id AND r.nombre = 'Supervisor'
                                         FOR UPDATE");
            $supervisor->execute([':id' => $id_funcionario]);
            $supervisorAnterior = $supervisor->fetch(PDO::FETCH_ASSOC);
            if (!$supervisorAnterior) {
                throw new DomainException("Solo se pueden modificar funcionarios que actualmente tengan rol de Supervisor.");
            }

            $sql = "UPDATE funcionarios
                    SET nombre = :nombre,
                        apellido = :apellido,
                        correo = :correo,
                        id_rol = :id_rol
                    WHERE id_funcionario = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre'   => $nombre,
                ':apellido' => $apellido,
                ':correo'   => $correo,
                ':id_rol'   => $id_rol,
                ':id'       => $id_funcionario
            ]);

            $detallesAuditoria = json_encode([
                'supervisor_id' => (int) $id_funcionario,
                'anterior' => [
                    'nombre' => $supervisorAnterior['nombre'],
                    'apellido' => $supervisorAnterior['apellido'],
                    'correo' => $supervisorAnterior['correo'],
                    'id_rol' => (int) $supervisorAnterior['id_rol'],
                    'rol' => $supervisorAnterior['nombre_rol'],
                ],
                'nuevo' => [
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'correo' => $correo,
                    'id_rol' => (int) $id_rol,
                    'rol' => $nombreRolNuevo,
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

            $auditoria = $pdo->prepare('INSERT INTO auditoria (usuario_id, accion, detalles, ip, user_agent)
                                        VALUES (:usuario_id, :accion, :detalles, :ip, :user_agent)');
            $auditoria->execute([
                ':usuario_id' => $_SESSION['usuario_id'] ?? null,
                ':accion' => 'actualizar_supervisor',
                ':detalles' => $detallesAuditoria,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                ':user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : null,
            ]);

            $pdo->commit();
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $error;
        }
    } finally {
        try {
            $liberarBloqueo = $pdo->prepare('SELECT RELEASE_LOCK(:nombre)');
            $liberarBloqueo->execute([':nombre' => $nombreBloqueo]);
        } catch (Throwable $error) {
            error_log($error->getMessage());
        }
    }

    return true;
}