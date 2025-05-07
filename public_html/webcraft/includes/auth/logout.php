<?php
/**
 * Proceso de cierre de sesión para WebCraft Academy
 * 
 * Este script maneja el proceso de logout seguro para los usuarios.
 */

// Iniciar sesión si no está iniciada
session_start();

// Definir constante para permitir acceso a los archivos de configuración
define('WEBCRAFT', true);

// Incluir archivo de configuración
require_once '../../config.php';

// Eliminar todas las variables de sesión
$_SESSION = array();

// Si se usa un cookie de sesión, eliminarla
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Redirigir a la página principal
header('Location: ../../index.php');
exit;
