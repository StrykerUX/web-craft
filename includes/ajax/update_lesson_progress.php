<?php
/**
 * Actualizar progreso de lección vía AJAX
 * 
 * Este archivo procesa las solicitudes AJAX para actualizar el estado
 * de progreso de una lección.
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
$data = json_decode($json_data, true);

if ($data === null) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Datos inválidos. Se esperaba un objeto JSON.'
    ]);
    exit;
}

// Verificar que se proporcionaron los datos necesarios
if (!isset($data['lesson_id']) || !isset($data['completed'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos. Se requiere lesson_id y completed.'
    ]);
    exit;
}

$lessonId = (int)$data['lesson_id'];
$completed = (bool)$data['completed'];
$userId = $_SESSION['user_id'];

try {
    $db = getDbConnection();
    
    // Verificar si ya existe un registro de progreso para esta lección
    $stmt = $db->prepare("
        SELECT progress_id, completed, score, time_spent
        FROM progress
        WHERE user_id = ? AND lesson_id = ?
    ");
    $stmt->execute([$userId, $lessonId]);
    $progress = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($progress) {
        // Actualizar registro existente
        $stmt = $db->prepare("
            UPDATE progress
            SET completed = ?,
                completion_date = " . ($completed ? 'NOW()' : 'NULL') . "
            WHERE progress_id = ?
        ");
        $stmt->execute([$completed ? 1 : 0, $progress['progress_id']]);
    } else {
        // Crear nuevo registro
        $stmt = $db->prepare("
            INSERT INTO progress (user_id, lesson_id, completed, completion_date, score, time_spent)
            VALUES (?, ?, ?, " . ($completed ? 'NOW()' : 'NULL') . ", 0, 0)
        ");
        $stmt->execute([$userId, $lessonId, $completed ? 1 : 0]);
    }
    
    // Si se marca como completada, otorgar XP
    if ($completed && (!$progress || !$progress['completed'])) {
        // Obtener cantidad de XP de la lección
        $stmt = $db->prepare("SELECT xp_reward FROM lessons WHERE lesson_id = ?");
        $stmt->execute([$lessonId]);
        $lesson = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($lesson) {
            $xpReward = (int)$lesson['xp_reward'];
            
            // Actualizar XP del usuario
            $stmt = $db->prepare("
                UPDATE user_profiles
                SET xp_points = xp_points + ?
                WHERE user_id = ?
            ");
            $stmt->execute([$xpReward, $userId]);
            
            // Verificar si se debe actualizar el nivel
            $stmt = $db->prepare("
                SELECT xp_points, level FROM user_profiles WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Niveles y umbrales de XP
            $levelThresholds = [
                'Principiante' => 0,
                'Novato' => 100,
                'Aprendiz' => 300,
                'Desarrollador' => 600,
                'Maestro' => 1000
            ];
            
            // Determinar el nivel correspondiente
            $newLevel = 'Principiante';
            foreach ($levelThresholds as $level => $threshold) {
                if ($profile['xp_points'] >= $threshold) {
                    $newLevel = $level;
                }
            }
            
            // Actualizar nivel si es necesario
            if ($newLevel !== $profile['level']) {
                $stmt = $db->prepare("
                    UPDATE user_profiles
                    SET level = ?
                    WHERE user_id = ?
                ");
                $stmt->execute([$newLevel, $userId]);
                
                // Notificar cambio de nivel
                $levelUp = true;
            }
            
            // Actualizar tablero de líderes
            $stmt = $db->prepare("
                INSERT INTO leaderboard (user_id, xp_total, last_updated)
                VALUES (?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                xp_total = xp_total + ?, last_updated = NOW()
            ");
            $stmt->execute([$userId, $xpReward, $xpReward]);
            
            // Verificar si se completó todo el módulo
            $stmt = $db->prepare("
                SELECT m.module_id, m.title,
                       COUNT(l.lesson_id) as total_lessons,
                       SUM(CASE WHEN p.completed = 1 THEN 1 ELSE 0 END) as completed_lessons
                FROM modules m
                JOIN lessons l ON m.module_id = l.module_id
                LEFT JOIN progress p ON l.lesson_id = p.lesson_id AND p.user_id = ?
                WHERE l.module_id = (SELECT module_id FROM lessons WHERE lesson_id = ?)
                GROUP BY m.module_id, m.title
            ");
            $stmt->execute([$userId, $lessonId]);
            $moduleProgress = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $moduleCompleted = false;
            if ($moduleProgress && $moduleProgress['total_lessons'] > 0 && 
                $moduleProgress['total_lessons'] == $moduleProgress['completed_lessons']) {
                $moduleCompleted = true;
                
                // Otorgar logro de módulo completado si existe
                $stmt = $db->prepare("
                    SELECT achievement_id FROM achievements 
                    WHERE condition_type = 'module_completion' 
                    AND condition_value = ?
                ");
                $stmt->execute([$moduleProgress['module_id']]);
                $achievement = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($achievement) {
                    // Verificar si ya tiene el logro
                    $stmt = $db->prepare("
                        SELECT COUNT(*) FROM user_achievements
                        WHERE user_id = ? AND achievement_id = ?
                    ");
                    $stmt->execute([$userId, $achievement['achievement_id']]);
                    
                    if ($stmt->fetchColumn() == 0) {
                        // Otorgar logro
                        $stmt = $db->prepare("
                            INSERT INTO user_achievements (user_id, achievement_id, date_earned)
                            VALUES (?, ?, NOW())
                        ");
                        $stmt->execute([$userId, $achievement['achievement_id']]);
                        
                        // Obtener detalles del logro
                        $stmt = $db->prepare("
                            SELECT title, description, xp_reward FROM achievements
                            WHERE achievement_id = ?
                        ");
                        $stmt->execute([$achievement['achievement_id']]);
                        $achievementDetails = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        // Otorgar XP adicional por logro
                        if ($achievementDetails && $achievementDetails['xp_reward'] > 0) {
                            $stmt = $db->prepare("
                                UPDATE user_profiles
                                SET xp_points = xp_points + ?
                                WHERE user_id = ?
                            ");
                            $stmt->execute([$achievementDetails['xp_reward'], $userId]);
                            
                            // Actualizar tablero de líderes
                            $stmt = $db->prepare("
                                UPDATE leaderboard
                                SET xp_total = xp_total + ?
                                WHERE user_id = ?
                            ");
                            $stmt->execute([$achievementDetails['xp_reward'], $userId]);
                        }
                    }
                }
            }
            
            // Preparar respuesta
            $response = [
                'success' => true,
                'message' => 'Progreso actualizado exitosamente',
                'xp_gained' => $xpReward,
                'total_xp' => $profile['xp_points'] + $xpReward
            ];
            
            // Agregar información de nivel si hubo cambio
            if (isset($levelUp) && $levelUp) {
                $response['level_up'] = true;
                $response['new_level'] = $newLevel;
            }
            
            // Agregar información de módulo si se completó
            if ($moduleCompleted) {
                $response['module_completed'] = true;
                $response['module_title'] = $moduleProgress['title'];
            }
            
            echo json_encode($response);
            exit;
        }
    }
    
    // Respuesta básica si no se otorgó XP
    echo json_encode([
        'success' => true,
        'message' => 'Progreso actualizado exitosamente'
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar el progreso.',
        'dev_message' => DEV_MODE ? $e->getMessage() : null
    ]);
}
