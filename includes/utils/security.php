<?php
/**
 * WebCraft Academy - Utilidades de Seguridad
 * 
 * Este archivo contiene funciones relacionadas con la seguridad de la aplicación,
 * como generación y validación de tokens CSRF, limpieza de entradas, etc.
 */

// Definir constante para permitir acceso a los archivos de configuración
if (!defined('WEBCRAFT')) {
    define('WEBCRAFT', true);
}

// Incluir archivo de configuración global si no está ya incluido
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../config.php';
}

/**
 * Genera un token CSRF (Cross-Site Request Forgery)
 * 
 * @param string $formName Nombre del formulario para el que se genera el token
 * @return string Token CSRF generado
 */
function generateCSRFToken($formName = 'default') {
    // Iniciar sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Generar un token aleatorio
    $token = bin2hex(random_bytes(32));
    
    // Almacenar el token en la sesión
    $_SESSION['csrf_tokens'][$formName] = [
        'token' => $token,
        'time' => time()
    ];
    
    return $token;
}

/**
 * Valida un token CSRF
 * 
 * @param string $token Token a validar
 * @param string $formName Nombre del formulario asociado al token
 * @param int $expireTime Tiempo de expiración en segundos (por defecto 3600 = 1 hora)
 * @return bool True si el token es válido, false en caso contrario
 */
function validateCSRFToken($token, $formName = 'default', $expireTime = 3600) {
    // Iniciar sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verificar si existe el token en la sesión
    if (!isset($_SESSION['csrf_tokens'][$formName])) {
        return false;
    }
    
    $storedToken = $_SESSION['csrf_tokens'][$formName]['token'];
    $tokenTime = $_SESSION['csrf_tokens'][$formName]['time'];
    
    // Verificar si el token ha expirado
    if (time() - $tokenTime > $expireTime) {
        // Eliminar el token expirado
        unset($_SESSION['csrf_tokens'][$formName]);
        return false;
    }
    
    // Comparar los tokens
    if (hash_equals($storedToken, $token)) {
        // Token válido, eliminar para prevenir reutilización
        unset($_SESSION['csrf_tokens'][$formName]);
        return true;
    }
    
    return false;
}

/**
 * Limpia y sanitiza una cadena de texto
 * 
 * @param string $input Cadena a limpiar
 * @return string Cadena limpia
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Genera un identificador único para sesiones
 * 
 * @return string Identificador único
 */
function generateUniqueId() {
    return bin2hex(random_bytes(16));
}

/**
 * Verifica si una solicitud es AJAX
 * 
 * @return bool True si la solicitud es AJAX, false en caso contrario
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * Verifica el origen de una solicitud HTTP para prevenir CSRF
 * 
 * @return bool True si el origen es válido, false en caso contrario
 */
function isValidOrigin() {
    if (!isset($_SERVER['HTTP_REFERER'])) {
        return false;
    }
    
    $referer = parse_url($_SERVER['HTTP_REFERER']);
    $host = $_SERVER['HTTP_HOST'];
    
    return isset($referer['host']) && $referer['host'] === $host;
}

/**
 * Genera un enlace de restablecimiento de contraseña
 * 
 * @param int $userId ID del usuario
 * @return string Token de restablecimiento
 */
function generatePasswordResetToken($userId) {
    $token = bin2hex(random_bytes(32));
    $expireTime = time() + 3600; // 1 hora
    
    try {
        $db = getDbConnection();
        
        // Eliminar tokens anteriores
        $stmt = $db->prepare("DELETE FROM password_resets WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Crear nuevo token
        $stmt = $db->prepare("
            INSERT INTO password_resets (user_id, token, expire_time) 
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([$userId, $token, $expireTime]);
        
        return $token;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Valida un token de restablecimiento de contraseña
 * 
 * @param string $token Token a validar
 * @return int|bool ID del usuario si el token es válido, false en caso contrario
 */
function validatePasswordResetToken($token) {
    try {
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            SELECT user_id FROM password_resets 
            WHERE token = ? AND expire_time > ?
        ");
        
        $stmt->execute([$token, time()]);
        $userId = $stmt->fetchColumn();
        
        return $userId ? (int)$userId : false;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Limpia los tokens CSRF expirados de la sesión
 * 
 * @param int $expireTime Tiempo de expiración en segundos (por defecto 3600 = 1 hora)
 */
function cleanupCSRFTokens($expireTime = 3600) {
    if (!isset($_SESSION['csrf_tokens']) || !is_array($_SESSION['csrf_tokens'])) {
        return;
    }
    
    $currentTime = time();
    
    foreach ($_SESSION['csrf_tokens'] as $formName => $tokenData) {
        if ($currentTime - $tokenData['time'] > $expireTime) {
            unset($_SESSION['csrf_tokens'][$formName]);
        }
    }
}

// Limpiar tokens CSRF expirados al cargar el archivo
if (session_status() !== PHP_SESSION_NONE) {
    cleanupCSRFTokens();
}
