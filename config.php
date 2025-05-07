<?php
/**
 * Archivo de configuración principal para WebCraft Academy
 * 
 * Este archivo contiene las configuraciones globales para la aplicación,
 * incluyendo credenciales de base de datos, rutas base, y otras constantes.
 */

// Prevenir acceso directo a este archivo
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Configuración de la base de datos
define('DB_HOST', 'localhost');     // Host de la base de datos
define('DB_NAME', 'u171418069_webcraft');  // Nombre de la base de datos
define('DB_USER', 'u171418069_imstryker');  // Usuario de la base de datos
define('DB_PASS', '9l/wwLcfcY');   // Contraseña de la base de datos
define('DB_CHARSET', 'utf8mb4');    // Codificación de caracteres

// URLs y Rutas del sistema
define('BASE_URL', 'https://yourwebsite.com/webcraft/'); // Cambiar por la URL de producción
define('BASE_PATH', __DIR__);       // Ruta base del sistema
define('ASSETS_URL', BASE_URL . 'assets/');
define('ASSETS_PATH', BASE_PATH . '/assets/');

// Configuración de sesión
define('SESSION_NAME', 'webcraft_session');
define('SESSION_LIFETIME', 7200);   // 2 horas en segundos
define('SESSION_PATH', '/');
define('SESSION_SECURE', false);    // Cambiar a true en producción con HTTPS
define('SESSION_HTTPONLY', true);

// Opciones de seguridad
define('HASH_COST', 10);            // Costo para el algoritmo de hashing de contraseñas

// Configuración de la aplicación
define('APP_NAME', 'WebCraft Academy');
define('APP_VERSION', '1.0.0');
define('DEV_MODE', true);           // Cambiar a false en producción
define('MAX_LOGIN_ATTEMPTS', 5);    // Máximo de intentos de inicio de sesión
define('LOCKOUT_TIME', 900);        // Tiempo de bloqueo después de máximos intentos (15 min)

// Configuración de correo electrónico
define('MAIL_FROM', 'noreply@yourwebsite.com');
define('MAIL_NAME', 'WebCraft Academy');
define('MAIL_REPLY_TO', 'support@yourwebsite.com');

// Función de conexión a la base de datos
function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        if (DEV_MODE) {
            echo "Error de conexión: " . $e->getMessage();
        } else {
            echo "Error de conexión a la base de datos. Por favor, contacte al administrador.";
        }
        exit;
    }
}

// Configuración de errores basada en el modo de desarrollo
if (DEV_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}
