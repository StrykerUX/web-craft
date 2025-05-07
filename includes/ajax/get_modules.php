<?php
/**
 * Obtener datos de módulos vía AJAX
 * 
 * Este archivo procesa las solicitudes AJAX para obtener información
 * sobre los módulos y sus lecciones.
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
    // Nota: Para los módulos básicos, podríamos permitir acceso público
    // pero para este ejemplo requerimos autenticación
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado. Debes iniciar sesión para obtener esta información.'
    ]);
    exit;
}

try {
    $db = getDbConnection();
    $userId = $_SESSION['user_id'];
    
    // Obtener todos los módulos activos
    $stmt = $db->prepare("
        SELECT m.module_id, m.title, m.description, m.order_index, m.icon
        FROM modules m
        WHERE m.is_active = 1
        ORDER BY m.order_index ASC
    ");
    $stmt->execute();
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Para cada módulo, obtener sus lecciones y el progreso del usuario
    foreach ($modules as &$module) {
        // Obtener lecciones del módulo
        $stmt = $db->prepare("
            SELECT l.lesson_id, l.title, l.order_index, l.xp_reward, l.estimated_time,
                   CASE WHEN p.completed = 1 THEN TRUE ELSE FALSE END as completed
            FROM lessons l
            LEFT JOIN progress p ON l.lesson_id = p.lesson_id AND p.user_id = ?
            WHERE l.module_id = ? AND l.is_active = 1
            ORDER BY l.order_index ASC
        ");
        $stmt->execute([$userId, $module['module_id']]);
        $module['lessons'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular estadísticas del módulo
        $totalLessons = count($module['lessons']);
        $completedLessons = 0;
        
        foreach ($module['lessons'] as $lesson) {
            if ($lesson['completed']) {
                $completedLessons++;
            }
        }
        
        $module['total_lessons'] = $totalLessons;
        $module['completed_lessons'] = $completedLessons;
        $module['completion_percentage'] = $totalLessons > 0 ? ($completedLessons / $totalLessons) * 100 : 0;
        
        // Por defecto, el módulo está expandido si tiene progreso pero no está completo
        $module['expanded'] = $completedLessons > 0 && $completedLessons < $totalLessons;
    }
    
    // Enviar respuesta
    echo json_encode([
        'success' => true,
        'modules' => $modules
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener los datos de módulos.',
        'dev_message' => DEV_MODE ? $e->getMessage() : null
    ]);
}
