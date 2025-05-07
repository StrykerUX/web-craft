<?php
/**
 * Guardar configuración del usuario vía AJAX
 * 
 * Este archivo procesa las solicitudes AJAX para guardar las preferencias
 * y configuraciones del usuario.
 */

// Definir constante para permitir acceso a los archivos de configuración
define('WEBCRAFT', true);

// Incluir archivos necesarios
require_once '../../config.php';
require_once '../auth/auth.php';

// Iniciar o continuar sesión
session_name(SESSION_NAME);
session_start([
    'cookie_lifetime' => SESSION_LIFETIME,
    'cookie_path' => SESSION_PATH,
    'cookie_secure' => SESSION_SECURE,
    'cookie_httponly' => SESSION_HTTPONLY,
    'use_strict_mode' => true
]);

// Verificar si el usuario está autenticado
if (!isUserLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado. Debes iniciar sesión para realizar esta acción.'
    ]);
    exit;
}

// Verificar que la solicitud sea POST y contenga datos JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido. Utiliza POST.'
    ]);
    exit;
}

// Obtener datos JSON de la solicitud
$json_data = file_get_contents('php://input');
$settings = json_decode($json_data, true);

if ($settings === null) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Datos inválidos. Se esperaba un objeto JSON.'
    ]);
    exit;
}

try {
    $db = getDbConnection();
    
    // Validar preferencias
    $valid_themes = ['light', 'dark', 'system'];
    if (isset($settings['theme_preference']) && !in_array($settings['theme_preference'], $valid_themes)) {
        $settings['theme_preference'] = 'system';
    }
    
    // Crear array para actualización
    $updates = [];
    $params = [];
    
    // Actualizar theme_preference si existe
    if (isset($settings['theme_preference'])) {
        $updates[] = 'theme_preference = ?';
        $params[] = $settings['theme_preference'];
    }
    
    // Convertir opciones de accesibilidad a JSON si existen
    $accessibility_settings = [];
    if (isset($settings['font_size'])) {
        $accessibility_settings['font_size'] = $settings['font_size'];
    }
    if (isset($settings['contrast'])) {
        $accessibility_settings['contrast'] = $settings['contrast'];
    }
    if (isset($settings['reduce_motion'])) {
        $accessibility_settings['reduce_motion'] = (bool)$settings['reduce_motion'];
    }
    
    // Agregar configuración de accesibilidad si hay opciones
    if (!empty($accessibility_settings)) {
        $updates[] = 'accessibility_settings = ?';
        $params[] = json_encode($accessibility_settings);
    }
    
    // Actualizar configuración si hay campos para actualizar
    if (!empty($updates)) {
        // Agregar user_id para la condición WHERE
        $params[] = $_SESSION['user_id'];
        
        // Preparar y ejecutar la consulta
        $stmt = $db->prepare('
            UPDATE user_profiles
            SET ' . implode(', ', $updates) . '
            WHERE user_id = ?
        ');
        
        $stmt->execute($params);
        
        // Verificar si se actualizó algún registro
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Configuración guardada exitosamente.'
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'No hubo cambios en la configuración.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'No se proporcionaron datos para actualizar.'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar la configuración.',
        'dev_message' => DEV_MODE ? $e->getMessage() : null
    ]);
}
