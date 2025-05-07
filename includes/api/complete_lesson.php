<?php
/**
 * API para marcar lecciones como completadas
 * 
 * Este archivo procesa las solicitudes para marcar una lección como completada
 * y actualiza el progreso del usuario.
 */

// Definir esta constante para permitir la inclusión en config.php
define('WEBCRAFT', true);

// Incluir archivos necesarios
require_once '../../config.php';

// Iniciar o continuar sesión
session_name(SESSION_NAME);
session_start([
    'cookie_lifetime' => SESSION_LIFETIME,
    'cookie_path' => SESSION_PATH,
    'cookie_secure' => SESSION_SECURE,
    'cookie_httponly' => SESSION_HTTPONLY,
    'use_strict_mode' => true
]);

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    // Redirigir al login si no está autenticado
    header('Location: ../../index.php?page=login&redirect=lessons');
    exit;
}

// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirigir a la página de módulos si no es una solicitud POST
    header('Location: ../../index.php?page=modules');
    exit;
}

// Obtener ID de la lección
$lessonId = isset($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : 0;

// Si no se proporciona un ID de lección válido, redirigir a la página de módulos
if ($lessonId <= 0) {
    header('Location: ../../index.php?page=modules');
    exit;
}

try {
    $db = getDbConnection();
    
    // Verificar que la lección exista y esté activa
    $stmt = $db->prepare("
        SELECT l.*, m.module_id 
        FROM lessons l 
        JOIN modules m ON l.module_id = m.module_id 
        WHERE l.lesson_id = ? AND l.is_active = 1 AND m.is_active = 1
    ");
    $stmt->execute([$lessonId]);
    $lesson = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si la lección no existe o no está activa, redirigir a la página de módulos
    if (!$lesson) {
        header('Location: ../../index.php?page=modules');
        exit;
    }
    
    // Verificar si ya existe un registro de progreso para esta lección
    $stmt = $db->prepare("
        SELECT * FROM progress 
        WHERE user_id = ? AND lesson_id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $lessonId]);
    $progress = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Calcular tiempo estimado de la lección si no está establecido
    $timeSpent = $lesson['estimated_time'] ? $lesson['estimated_time'] * 60 : 300; // En segundos
    
    if ($progress) {
        // Actualizar el registro existente
        $stmt = $db->prepare("
            UPDATE progress 
            SET completed = 1, 
                completion_date = NOW(), 
                score = COALESCE(score, 100), 
                time_spent = COALESCE(time_spent, ?) 
            WHERE progress_id = ?
        ");
        $stmt->execute([$timeSpent, $progress['progress_id']]);
    } else {
        // Crear un nuevo registro de progreso
        $stmt = $db->prepare("
            INSERT INTO progress 
            (user_id, lesson_id, completed, completion_date, score, time_spent) 
            VALUES (?, ?, 1, NOW(), 100, ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $lessonId, $timeSpent]);
    }
    
    // Actualizar XP del usuario
    $stmt = $db->prepare("
        UPDATE user_profiles 
        SET xp_points = xp_points + ? 
        WHERE user_id = ?
    ");
    $stmt->execute([$lesson['xp_reward'], $_SESSION['user_id']]);
    
    // Verificar si todas las lecciones del módulo están completadas
    $stmt = $db->prepare("
        SELECT COUNT(l.lesson_id) AS total,
               SUM(CASE WHEN p.completed = 1 THEN 1 ELSE 0 END) AS completed
        FROM lessons l
        LEFT JOIN progress p ON l.lesson_id = p.lesson_id AND p.user_id = ?
        WHERE l.module_id = ? AND l.is_active = 1
    ");
    $stmt->execute([$_SESSION['user_id'], $lesson['module_id']]);
    $moduleProgress = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si todas las lecciones están completadas, otorgar bonificación
    if ($moduleProgress['total'] > 0 && $moduleProgress['completed'] >= $moduleProgress['total']) {
        // Bonificación por completar módulo
        $bonusXP = 100; // XP adicionales por completar el módulo
        
        $stmt = $db->prepare("
            UPDATE user_profiles 
            SET xp_points = xp_points + ? 
            WHERE user_id = ?
        ");
        $stmt->execute([$bonusXP, $_SESSION['user_id']]);
        
        // Aquí se podría verificar y otorgar logros por completar el módulo
        // ...
    }
    
    // Actualizar el nivel del usuario según sus XP totales
    $stmt = $db->prepare("
        SELECT xp_points FROM user_profiles WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $totalXP = $profile['xp_points'];
    $newLevel = 'Principiante';
    
    // Determinar nivel basado en XP
    if ($totalXP >= 5000) {
        $newLevel = 'Maestro';
    } elseif ($totalXP >= 2000) {
        $newLevel = 'Desarrollador';
    } elseif ($totalXP >= 1000) {
        $newLevel = 'Aprendiz';
    } elseif ($totalXP >= 500) {
        $newLevel = 'Novato';
    }
    
    // Actualizar nivel si ha cambiado
    $stmt = $db->prepare("
        UPDATE user_profiles 
        SET level = ? 
        WHERE user_id = ? AND level != ?
    ");
    $stmt->execute([$newLevel, $_SESSION['user_id'], $newLevel]);
    
    // Redirigir de vuelta a la lección o a la siguiente lección si hay una
    $stmt = $db->prepare("
        SELECT lesson_id FROM lessons
        WHERE module_id = ? AND order_index > ? AND is_active = 1
        ORDER BY order_index ASC
        LIMIT 1
    ");
    $stmt->execute([$lesson['module_id'], $lesson['order_index']]);
    $nextLesson = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($nextLesson) {
        // Redirigir a la siguiente lección
        header('Location: ../../index.php?page=lessons&module_id=' . $lesson['module_id'] . '&lesson_id=' . $nextLesson['lesson_id'] . '&completed=1');
    } else {
        // Volver a la lección actual con mensaje de completado
        header('Location: ../../index.php?page=lessons&module_id=' . $lesson['module_id'] . '&lesson_id=' . $lessonId . '&completed=1');
    }
    
} catch (PDOException $e) {
    // Manejar error de base de datos
    if (DEV_MODE) {
        die("Error: " . $e->getMessage());
    }
    
    // En producción, redirigir con mensaje de error
    header('Location: ../../index.php?page=lessons&module_id=' . $lesson['module_id'] . '&lesson_id=' . $lessonId . '&error=1');
}
