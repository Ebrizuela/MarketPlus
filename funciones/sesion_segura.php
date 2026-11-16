<?php
// src/funciones/sesion_segura.php

/**
 * 1. Inicia la sesión configurando la cookie para que muera al cerrar el navegador
 *    y protege contra secuestro de sesión (Session Hijacking vía XSS).
 */
function iniciarSesionSegura() {
    if (session_status() === PHP_SESSION_NONE) {
        // Criterio: El sistema invalida la sesión al cerrar el navegador (lifetime = 0)
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => false, // true en HTTPS, false en HTTP local
            'httponly' => true,  // Previene lectura por JavaScript (Session Hijacking)
            'samesite' => 'Lax'
        ]);
        session_start();
    }
}

/**
 * 2. Criterio: Se genera un nuevo token de sesión al iniciar sesión.
 *    Protege contra Session Fixation. Esta función la llama el compañero al validar login.
 */
function regenerarTokenInicioSesion() {
    iniciarSesionSegura();
    session_regenerate_id(true); // Genera ID nuevo y borra el anterior
    $_SESSION['ultimo_acceso'] = time(); // Inicia el conteo de inactividad
}

/**
 * 3. Criterio: La sesión expira automáticamente después de 15 minutos de inactividad.
 */
function verificarInactividadSesion() {
    iniciarSesionSegura();

    $limite_inactividad = 15 * 60; // 900 segundos (15 minutos)

    if (isset($_SESSION['ultimo_acceso'])) {
        $tiempo_inactivo = time() - $_SESSION['ultimo_acceso'];

        if ($tiempo_inactivo > $limite_inactividad) {
            // Expiró: destruir variables y cookie
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }
            session_destroy();
            return false; // Sesión caducada
        }
    }

    // Si sigue activa, renovamos la marca de tiempo con la actividad actual
    $_SESSION['ultimo_acceso'] = time();
    return true; // Sesión válida
}