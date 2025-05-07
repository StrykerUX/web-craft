<?php
/**
 * Procesamiento de cierre de sesión para WebCraft Academy
 * 
 * Este archivo maneja el cierre de sesión del usuario y la redirección
 * a la página de inicio.
 */

// Definir constante para permitir acceso a los archivos de configuración
define('WEBCRAFT', true);

// Incluir archivo de configuración
require_once '../../config.php';

// Incluir archivos de autenticación
require_once '../auth/auth.php';

// Verificar token CSRF para prevenir cierre de sesión por CSRF
$validRequest = false;

// Comprobar si hay un token CSRF en la URL o POST
if (isset($_GET['csrf_token']) && !empty($_GET['csrf_token'])) {
    $validRequest = validateCsrfToken($_GET['csrf_token']);
} elseif (isset($_POST['csrf_token']) && !empty($_POST['csrf_token'])) {
    $validRequest = validateCsrfToken($_POST['csrf_token']);
}

// Si no hay token o no es válido, redirigir a la página de inicio
if (!$validRequest && DEV_MODE === false) {
    header('Location: ../../index.php');
    exit;
}

// Cerrar sesión
logoutUser();

// Redirigir a la página de inicio
header('Location: ../../index.php');
exit;
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
