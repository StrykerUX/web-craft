<?php
/**
 * API para actualizar el progreso de lecciones
 * 
 * Este archivo procesa las solicitudes AJAX para marcar lecciones como
 * completadas y actualizar el progreso del usuario.
 */

// Definir constante para permitir acceso a los archivos de configuración
if (!defined('WEBCRAFT')) {
    define('WEBCRAFT', true);
}

// Incluir archivo de configuración
require_once '../../config.php';

// Incluir cargador de módulos
require_once '../../modules/modulo-loader.php';

// Verificar autenticación
require_once '../auth/auth.php';
if (!isUserLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado. Debe iniciar sesión para actualizar el progreso.'
    ]);
    exit;
}

// Verificar método de solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido. Use POST para esta solicitud.'
    ]);
    exit;
}

// Obtener datos de la solicitud
$requestData = json_decode(file_get_contents('php://input'), true);
if (!$requestData) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Datos de solicitud inválidos.'
    ]);
    exit;
}

// Validar datos requeridos
if (!isset($requestData['lesson_id']) || !isset($requestData['completed'])) {
    header('Content-Type: application/json');
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
$xpReward = 0;
$allModules = getModulos();
foreach ($allModules as $modulo) {
    foreach ($modulo['lecciones'] as $leccion) {
        if ($leccion['id'] == $lessonId) {
            $xpReward = $leccion['xp_recompensa'] ?? 0;
            break 2;
        }
    }
}

try {
    // Marcar la lección como completada
    $result = completeLesson($_SESSION['user_id'], $lessonId, $score);
    
    if ($result) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Progreso actualizado correctamente.',
            'xp' => $xpReward
        ]);
    } else {
        throw new Exception('Error al actualizar el progreso.');
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => DEV_MODE ? 'Error: ' . $e->getMessage() : 'Error al actualizar el progreso. Intente nuevamente más tarde.'
    ]);
}
