<?php
/**
 * API para actualizar el progreso de lecciones
 * 
 * Este archivo procesa las solicitudes AJAX para marcar lecciones como
 * completadas y actualizar el progreso del usuario.
 */

// Habilitar visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Registrar la solicitud para depuración
error_log('Solicitud recibida en update_lesson_progress.php: ' . file_get_contents('php://input'));

// Definir constante para permitir acceso a los archivos de configuración
if (!defined('WEBCRAFT')) {
    define('WEBCRAFT', true);
}

// Establecer encabezados para CORS y tipo de contenido
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Responder a solicitudes OPTIONS (pre-flight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Incluir archivo de configuración
require_once '../../config.php';

// Incluir cargador de módulos
require_once '../../modules/modulo-loader.php';

// Verificar autenticación
require_once '../auth/auth.php';
if (!isUserLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado. Debe iniciar sesión para actualizar el progreso.'
    ]);
    exit;
}

// Verificar método de solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido. Use POST para esta solicitud.'
    ]);
    exit;
}

// Obtener datos de la solicitud
$requestData = json_decode(file_get_contents('php://input'), true);
if (!$requestData) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos de solicitud inválidos.'
    ]);
    exit;
}

// Validar datos requeridos
if (!isset($requestData['lesson_id']) || !isset($requestData['completed'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos. Se requiere lesson_id y completed.'
    ]);
    exit;
}

$lessonId = (int)$requestData['lesson_id'];
$completed = (bool)$requestData['completed'];
$score = isset($requestData['score']) ? (int)$requestData['score'] : null;

// Obtener información de la lección para calcular la recompensa de XP
$xpReward = 50; // Valor predeterminado
try {
    // Buscar la lección en todos los módulos para obtener su XP
    $moduloEncontrado = false;
    $allModules = getModulos();
    
    foreach ($allModules as $modulo) {
        if (isset($modulo['directorio'])) {
            $rutaLeccion = __DIR__ . '/../../modules/' . $modulo['directorio'] . '/leccion' . $lessonId . '.json';
            if (file_exists($rutaLeccion)) {
                $contenidoLeccion = file_get_contents($rutaLeccion);
                $leccion = json_decode($contenidoLeccion, true);
                
                if ($leccion && isset($leccion['xp_recompensa'])) {
                    $xpReward = (int)$leccion['xp_recompensa'];
                    $moduloEncontrado = true;
                    break;
                }
            }
        }
    }
    
    if (!$moduloEncontrado) {
        error_log("No se encontró información de la lección ID: $lessonId");
    }
    
    // Marcar como completada (simulado para esta prueba)
    // En implementación real, se usaría completeLesson() del modulo-loader.php
    // $result = completeLesson($_SESSION['user_id'], $lessonId, $score);
    
    // Por ahora, simulamos éxito para pruebas
    $result = true;
    
    echo json_encode([
        'success' => true,
        'message' => 'Progreso actualizado correctamente.',
        'xp' => $xpReward,
        'lesson_id' => $lessonId,
        'user_id' => $_SESSION['user_id'] ?? 'unknown'
    ]);
    
} catch (Exception $e) {
    error_log('Error en update_lesson_progress.php: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'debug_info' => [
            'file' => __FILE__,
            'line' => __LINE__
        ]
    ]);
}
