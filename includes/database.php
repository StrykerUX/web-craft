<?php
/**
 * Biblioteca de funciones de base de datos para WebCraft Academy
 * 
 * Este archivo contiene funciones auxiliares para operaciones comunes
 * de base de datos utilizadas en toda la plataforma.
 */

// Prevenir acceso directo a este archivo
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

/**
 * Obtiene los módulos disponibles para un usuario específico
 * 
 * @param int $userId ID del usuario
 * @param bool $includeInactive Incluir módulos inactivos
 * @return array Arreglo de módulos
 */
function getModules($userId, $includeInactive = false) {
    try {
        $db = getDbConnection();
        
        $activeClause = $includeInactive ? '' : 'AND m.is_active = TRUE';
        
        $sql = "
            SELECT m.module_id, m.module_name, m.module_description, m.module_order, 
                   m.icon_class, m.required_points, COUNT(l.lesson_id) AS lesson_count,
                   (
                       SELECT COUNT(*)
                       FROM lessons l2 
                       JOIN user_progress up ON l2.lesson_id = up.lesson_id
                       WHERE l2.module_id = m.module_id
                       AND up.user_id = ?
                       AND up.status = 'completed'
                   ) AS completed_lessons
            FROM modules m
            LEFT JOIN lessons l ON m.module_id = l.module_id AND l.is_active = TRUE
            WHERE 1=1 {$activeClause}
            GROUP BY m.module_id
            ORDER BY m.module_order ASC
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        if (DEV_MODE) {
            error_log('Error al obtener módulos: ' . $e->getMessage());
        }
        return [];
    }
}

/**
 * Obtiene un módulo específico con sus lecciones
 * 
 * @param int $moduleId ID del módulo
 * @param int $userId ID del usuario para obtener progreso
 * @return array|null Datos del módulo o null si no existe
 */
function getModule($moduleId, $userId) {
    try {
        $db = getDbConnection();
        
        // Obtener datos del módulo
        $moduleStmt = $db->prepare("
            SELECT m.module_id, m.module_name, m.module_description, m.module_order, 
                   m.icon_class, m.required_points
            FROM modules m
            WHERE m.module_id = ?
        ");
        $moduleStmt->execute([$moduleId]);
        $module = $moduleStmt->fetch();
        
        if (!$module) {
            return null;
        }
        
        // Obtener lecciones del módulo con progreso del usuario
        $lessonsStmt = $db->prepare("
            SELECT l.lesson_id, l.lesson_title, l.lesson_description, l.lesson_order,
                   l.estimated_time_minutes, l.xp_reward, l.required_lesson_id,
                   up.status, up.completion_percentage, up.xp_earned,
                   CASE 
                       WHEN up.status = 'completed' THEN TRUE
                       WHEN up.status = 'in_progress' THEN TRUE
                       WHEN req_l.lesson_id IS NULL THEN TRUE
                       WHEN EXISTS (
                           SELECT 1 FROM user_progress req_up 
                           WHERE req_up.user_id = ? 
                           AND req_up.lesson_id = l.required_lesson_id
                           AND req_up.status = 'completed'
                       ) THEN TRUE
                       ELSE FALSE
                   END AS is_available
            FROM lessons l
            LEFT JOIN user_progress up ON l.lesson_id = up.lesson_id AND up.user_id = ?
            LEFT JOIN lessons req_l ON l.required_lesson_id = req_l.lesson_id
            WHERE l.module_id = ? AND l.is_active = TRUE
            ORDER BY l.lesson_order ASC
        ");
        $lessonsStmt->execute([$userId, $userId, $moduleId]);
        $module['lessons'] = $lessonsStmt->fetchAll();
        
        // Calcular estadísticas del módulo
        $totalLessons = count($module['lessons']);
        $completedLessons = 0;
        $totalXpEarned = 0;
        
        foreach ($module['lessons'] as $lesson) {
            if ($lesson['status'] === 'completed') {
                $completedLessons++;
                $totalXpEarned += $lesson['xp_earned'];
            }
        }
        
        $module['total_lessons'] = $totalLessons;
        $module['completed_lessons'] = $completedLessons;
        $module['completion_percentage'] = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
        $module['total_xp_earned'] = $totalXpEarned;
        
        return $module;
    } catch (PDOException $e) {
        if (DEV_MODE) {
            error_log('Error al obtener módulo: ' . $e->getMessage());
        }
        return null;
    }
}

/**
 * Obtiene los detalles de una lección específica con su contenido
 * 
 * @param int $lessonId ID de la lección
 * @param int $userId ID del usuario para obtener progreso
 * @return array|null Datos de la lección o null si no existe
 */
function getLesson($lessonId, $userId) {
    try {
        $db = getDbConnection();
        
        // Obtener datos de la lección
        $lessonStmt = $db->prepare("
            SELECT l.lesson_id, l.module_id, l.lesson_title, l.lesson_description,
                   l.lesson_content, l.lesson_order, l.estimated_time_minutes,
                   l.xp_reward, m.module_name, m.icon_class,
                   up.status, up.completion_percentage, up.xp_earned,
                   up.started_at, up.completed_at, up.last_position
            FROM lessons l
            JOIN modules m ON l.module_id = m.module_id
            LEFT JOIN user_progress up ON l.lesson_id = up.lesson_id AND up.user_id = ?
            WHERE l.lesson_id = ? AND l.is_active = TRUE
        ");
        $lessonStmt->execute([$userId, $lessonId]);
        $lesson = $lessonStmt->fetch();
        
        if (!$lesson) {
            return null;
        }
        
        // Verificar si la lección es accesible para el usuario
        $isAvailable = true;
        
        // Si tiene lección previa requerida, verificar si está completada
        if (!empty($lesson['required_lesson_id'])) {
            $reqStmt = $db->prepare("
                SELECT status FROM user_progress
                WHERE user_id = ? AND lesson_id = ?
            ");
            $reqStmt->execute([$userId, $lesson['required_lesson_id']]);
            $requiredLesson = $reqStmt->fetch();
            
            if (!$requiredLesson || $requiredLesson['status'] !== 'completed') {
                $isAvailable = false;
            }
        }
        
        $lesson['is_available'] = $isAvailable;
        
        // Obtener lecciones anterior y siguiente
        $prevStmt = $db->prepare("
            SELECT lesson_id, lesson_title 
            FROM lessons
            WHERE module_id = ? AND lesson_order < ? AND is_active = TRUE
            ORDER BY lesson_order DESC
            LIMIT 1
        ");
        $prevStmt->execute([$lesson['module_id'], $lesson['lesson_order']]);
        $lesson['prev_lesson'] = $prevStmt->fetch();
        
        $nextStmt = $db->prepare("
            SELECT lesson_id, lesson_title 
            FROM lessons
            WHERE module_id = ? AND lesson_order > ? AND is_active = TRUE
            ORDER BY lesson_order ASC
            LIMIT 1
        ");
        $nextStmt->execute([$lesson['module_id'], $lesson['lesson_order']]);
        $lesson['next_lesson'] = $nextStmt->fetch();
        
        return $lesson;
    } catch (PDOException $e) {
        if (DEV_MODE) {
            error_log('Error al obtener lección: ' . $e->getMessage());
        }
        return null;
    }
}

/**
 * Actualiza el progreso de un usuario en una lección
 * 
 * @param int $userId ID del usuario
 * @param int $lessonId ID de la lección
 * @param string $status Estado ('not_started', 'in_progress', 'completed')
 * @param int $completionPercentage Porcentaje de finalización (0-100)
 * @param int $xpEarned Puntos XP ganados
 * @param string|null $lastPosition Última posición en la lección
 * @return bool Éxito de la operación
 */
function updateLessonProgress($userId, $lessonId, $status, $completionPercentage = 0, $xpEarned = 0, $lastPosition = null) {
    try {
        $db = getDbConnection();
        
        // Verificar si ya existe un registro de progreso
        $checkStmt = $db->prepare("
            SELECT progress_id FROM user_progress
            WHERE user_id = ? AND lesson_id = ?
        ");
        $checkStmt->execute([$userId, $lessonId]);
        $existingProgress = $checkStmt->fetch();
        
        // Preparar datos para actualización
        $now = date('Y-m-d H:i:s');
        
        if ($existingProgress) {
            // Actualizar registro existente
            $sql = "
                UPDATE user_progress SET
                    status = ?,
                    completion_percentage = ?,
                    xp_earned = ?,
                    last_position = ?
            ";
            
            // Actualizar started_at si está comenzando la lección
            if ($status === 'in_progress' && $completionPercentage === 0) {
                $sql .= ", started_at = ?";
            }
            
            // Actualizar completed_at si está completando la lección
            if ($status === 'completed') {
                $sql .= ", completed_at = ?";
            }
            
            $sql .= " WHERE user_id = ? AND lesson_id = ?";
            
            $params = [$status, $completionPercentage, $xpEarned, $lastPosition];
            
            if ($status === 'in_progress' && $completionPercentage === 0) {
                $params[] = $now;
            }
            
            if ($status === 'completed') {
                $params[] = $now;
            }
            
            $params[] = $userId;
            $params[] = $lessonId;
            
            $stmt = $db->prepare($sql);
            $success = $stmt->execute($params);
        } else {
            // Crear nuevo registro
            $sql = "
                INSERT INTO user_progress (
                    user_id, lesson_id, status, completion_percentage,
                    xp_earned, last_position
            ";
            
            if ($status === 'in_progress' || $status === 'completed') {
                $sql .= ", started_at";
            }
            
            if ($status === 'completed') {
                $sql .= ", completed_at";
            }
            
            $sql .= ") VALUES (?, ?, ?, ?, ?, ?";
            
            if ($status === 'in_progress' || $status === 'completed') {
                $sql .= ", ?";
            }
            
            if ($status === 'completed') {
                $sql .= ", ?";
            }
            
            $sql .= ")";
            
            $params = [$userId, $lessonId, $status, $completionPercentage, $xpEarned, $lastPosition];
            
            if ($status === 'in_progress' || $status === 'completed') {
                $params[] = $now;
            }
            
            if ($status === 'completed') {
                $params[] = $now;
            }
            
            $stmt = $db->prepare($sql);
            $success = $stmt->execute($params);
        }
        
        // Si completó la lección, actualizar XP del usuario
        if ($success && $status === 'completed') {
            $updateXpStmt = $db->prepare("
                UPDATE users
                SET experience_points = experience_points + ?
                WHERE user_id = ?
            ");
            $updateXpStmt->execute([$xpEarned, $userId]);
            
            // Actualizar nivel del usuario basado en XP
            $updateLevelStmt = $db->prepare("
                UPDATE users
                SET developer_level = 
                    CASE
                        WHEN experience_points < 500 THEN 'Principiante'
                        WHEN experience_points < 2000 THEN 'Novato'
                        WHEN experience_points < 5000 THEN 'Aprendiz'
                        WHEN experience_points < 10000 THEN 'Desarrollador'
                        ELSE 'Maestro'
                    END
                WHERE user_id = ?
            ");
            $updateLevelStmt->execute([$userId]);
            
            // Verificar logros desbloqueados
            checkAchievements($userId);
        }
        
        return $success;
    } catch (PDOException $e) {
        if (DEV_MODE) {
            error_log('Error al actualizar progreso: ' . $e->getMessage());
        }
        return false;
    }
}

/**
 * Verifica y desbloquea logros para un usuario
 * 
 * @param int $userId ID del usuario
 */
function checkAchievements($userId) {
    try {
        $db = getDbConnection();
        
        // 1. Logro: Primera lección completada
        $firstLessonStmt = $db->prepare("
            SELECT COUNT(*) AS completed_count
            FROM user_progress
            WHERE user_id = ? AND status = 'completed'
        ");
        $firstLessonStmt->execute([$userId]);
        $completedCount = $firstLessonStmt->fetch()['completed_count'];
        
        if ($completedCount === 1) {
            unlockAchievement($userId, 'first_step');
        }
        
        // 2. Logro: Completar 5 lecciones de HTML
        $htmlLessonsStmt = $db->prepare("
            SELECT COUNT(*) AS html_count
            FROM user_progress up
            JOIN lessons l ON up.lesson_id = l.lesson_id
            JOIN modules m ON l.module_id = m.module_id
            WHERE up.user_id = ? AND up.status = 'completed'
            AND m.module_name = 'Fundamentos HTML'
        ");
        $htmlLessonsStmt->execute([$userId]);
        $htmlCount = $htmlLessonsStmt->fetch()['html_count'];
        
        if ($htmlCount >= 5) {
            unlockAchievement($userId, 'html_explorer');
        }
        
        // 3. Logro: Completar todo el módulo CSS
        $cssModuleStmt = $db->prepare("
            SELECT 
                (SELECT COUNT(*) FROM lessons l JOIN modules m ON l.module_id = m.module_id 
                 WHERE m.module_name = 'Estilización con CSS' AND l.is_active = TRUE) AS total_lessons,
                (SELECT COUNT(*) FROM user_progress up 
                 JOIN lessons l ON up.lesson_id = l.lesson_id 
                 JOIN modules m ON l.module_id = m.module_id 
                 WHERE up.user_id = ? AND up.status = 'completed' 
                 AND m.module_name = 'Estilización con CSS') AS completed_lessons
        ");
        $cssModuleStmt->execute([$userId]);
        $cssModule = $cssModuleStmt->fetch();
        
        if ($cssModule['total_lessons'] > 0 && $cssModule['completed_lessons'] === $cssModule['total_lessons']) {
            unlockAchievement($userId, 'css_master');
        }
        
        // 4. Logro: Completar primer proyecto web
        // Este se desbloquea manualmente en otra parte del código
        
        // 5. Logro: Días consecutivos (racha)
        $streakStmt = $db->prepare("
            SELECT COUNT(DISTINCT DATE(completed_at)) AS streak_days
            FROM user_progress
            WHERE user_id = ?
            AND completed_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
            AND status = 'completed'
            ORDER BY completed_at DESC
        ");
        $streakStmt->execute([$userId]);
        $streakDays = $streakStmt->fetch()['streak_days'];
        
        if ($streakDays >= 7) {
            unlockAchievement($userId, 'tireless');
        }
    } catch (PDOException $e) {
        if (DEV_MODE) {
            error_log('Error al verificar logros: ' . $e->getMessage());
        }
    }
}

/**
 * Desbloquea un logro para un usuario si no lo tiene
 * 
 * @param int $userId ID del usuario
 * @param string $achievementCode Código del logro
 * @return bool Éxito de la operación
 */
function unlockAchievement($userId, $achievementCode) {
    try {
        $db = getDbConnection();
        
        // Obtener ID del logro
        $achievementStmt = $db->prepare("
            SELECT achievement_id, xp_reward FROM achievements
            WHERE achievement_code = ?
        ");
        $achievementStmt->execute([$achievementCode]);
        $achievement = $achievementStmt->fetch();
        
        if (!$achievement) {
            return false;
        }
        
        // Verificar si ya tiene el logro
        $checkStmt = $db->prepare("
            SELECT user_achievement_id FROM user_achievements
            WHERE user_id = ? AND achievement_id = ?
        ");
        $checkStmt->execute([$userId, $achievement['achievement_id']]);
        
        if ($checkStmt->rowCount() > 0) {
            return false; // Ya tiene el logro
        }
        
        // Otorgar logro
        $unlockStmt = $db->prepare("
            INSERT INTO user_achievements (user_id, achievement_id, achieved_at)
            VALUES (?, ?, NOW())
        ");
        $success = $unlockStmt->execute([$userId, $achievement['achievement_id']]);
        
        // Actualizar XP del usuario
        if ($success) {
            $updateXpStmt = $db->prepare("
                UPDATE users
                SET experience_points = experience_points + ?
                WHERE user_id = ?
            ");
            $updateXpStmt->execute([$achievement['xp_reward'], $userId]);
            
            // Actualizar nivel del usuario basado en XP
            $updateLevelStmt = $db->prepare("
                UPDATE users
                SET developer_level = 
                    CASE
                        WHEN experience_points < 500 THEN 'Principiante'
                        WHEN experience_points < 2000 THEN 'Novato'
                        WHEN experience_points < 5000 THEN 'Aprendiz'
                        WHEN experience_points < 10000 THEN 'Desarrollador'
                        ELSE 'Maestro'
                    END
                WHERE user_id = ?
            ");
            $updateLevelStmt->execute([$userId]);
        }
        
        return $success;
    } catch (PDOException $e) {
        if (DEV_MODE) {
            error_log('Error al desbloquear logro: ' . $e->getMessage());
        }
        return false;
    }
}

/**
 * Obtiene los logros de un usuario
 * 
 * @param int $userId ID del usuario
 * @return array Arreglo de logros
 */
function getUserAchievements($userId) {
    try {
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            SELECT a.achievement_id, a.achievement_name, a.achievement_description,
                   a.achievement_icon, a.achievement_type, a.xp_reward,
                   ua.achieved_at
            FROM user_achievements ua
            JOIN achievements a ON ua.achievement_id = a.achievement_id
            WHERE ua.user_id = ?
            ORDER BY ua.achieved_at DESC
        ");
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        if (DEV_MODE) {
            error_log('Error al obtener logros: ' . $e->getMessage());
        }
        return [];
    }
}

/**
 * Obtiene todos los logros disponibles indicando cuáles tiene el usuario
 * 
 * @param int $userId ID del usuario
 * @return array Arreglo de logros
 */
function getAllAchievements($userId) {
    try {
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            SELECT a.achievement_id, a.achievement_name, a.achievement_description,
                   a.achievement_icon, a.achievement_type, a.xp_reward, a.is_secret,
                   CASE WHEN ua.user_id IS NOT NULL THEN TRUE ELSE FALSE END AS is_unlocked,
                   ua.achieved_at
            FROM achievements a
            LEFT JOIN user_achievements ua ON a.achievement_id = ua.achievement_id AND ua.user_id = ?
            ORDER BY a.achievement_type, a.xp_reward
        ");
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        if (DEV_MODE) {
            error_log('Error al obtener todos los logros: ' . $e->getMessage());
        }
        return [];
    }
}

/**
 * Obtener estadísticas generales del usuario
 * 
 * @param int $userId ID del usuario
 * @return array Estadísticas del usuario
 */
function getUserStats($userId) {
    try {
        $db = getDbConnection();
        
        // Obtener estadísticas generales
        $statsStmt = $db->prepare("
            SELECT 
                (SELECT COUNT(*) FROM user_progress WHERE user_id = ? AND status = 'completed') AS lessons_completed,
                (SELECT COUNT(*) FROM user_achievements WHERE user_id = ?) AS achievements_count,
                (SELECT COUNT(DISTINCT DATE(completed_at)) FROM user_progress WHERE user_id = ? AND status = 'completed' AND completed_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)) AS streak_days,
                (SELECT experience_points FROM users WHERE user_id = ?) AS xp_total,
                (SELECT developer_level FROM users WHERE user_id = ?) AS level
        ");
        $statsStmt->execute([$userId, $userId, $userId, $userId, $userId]);
        $stats = $statsStmt->fetch();
        
        // Obtener progreso por módulo
        $moduleProgressStmt = $db->prepare("
            SELECT m.module_name, m.module_order,
                   COUNT(l.lesson_id) AS total_lessons,
                   COUNT(CASE WHEN up.status = 'completed' THEN 1 END) AS completed_lessons,
                   ROUND(COUNT(CASE WHEN up.status = 'completed' THEN 1 END) * 100.0 / COUNT(l.lesson_id)) AS completion_percentage
            FROM modules m
            JOIN lessons l ON m.module_id = l.module_id
            LEFT JOIN user_progress up ON l.lesson_id = up.lesson_id AND up.user_id = ?
            WHERE l.is_active = TRUE AND m.is_active = TRUE
            GROUP BY m.module_id
            ORDER BY m.module_order
        ");
        $moduleProgressStmt->execute([$userId]);
        $stats['modules_progress'] = $moduleProgressStmt->fetchAll();
        
        // Calcular tiempo total de estudio (en minutos)
        $timeStmt = $db->prepare("
            SELECT SUM(
                CASE
                    WHEN up.status = 'completed' THEN l.estimated_time_minutes
                    WHEN up.status = 'in_progress' THEN ROUND(l.estimated_time_minutes * (up.completion_percentage / 100.0))
                    ELSE 0
                END
            ) AS total_time
            FROM user_progress up
            JOIN lessons l ON up.lesson_id = l.lesson_id
            WHERE up.user_id = ?
        ");
        $timeStmt->execute([$userId]);
        $stats['study_time_minutes'] = $timeStmt->fetch()['total_time'] ?? 0;
        
        return $stats;
    } catch (PDOException $e) {
        if (DEV_MODE) {
            error_log('Error al obtener estadísticas: ' . $e->getMessage());
        }
        return [
            'lessons_completed' => 0,
            'achievements_count' => 0,
            'streak_days' => 0,
            'xp_total' => 0,
            'level' => 'Principiante',
            'modules_progress' => [],
            'study_time_minutes' => 0
        ];
    }
}
