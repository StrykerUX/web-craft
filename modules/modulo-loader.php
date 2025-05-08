<?php
/**
 * Cargador de Módulos y Lecciones
 * 
 * Este archivo proporciona funciones para cargar y gestionar los módulos
 * y lecciones de la plataforma WebCraft Academy.
 */

// Definir constante para permitir acceso a los archivos de configuración solo si no está ya definida
if (!defined('WEBCRAFT')) {
    define('WEBCRAFT', true);
}

// Incluir archivo de configuración si aún no está incluido
if (!defined('BASE_PATH')) {
    require_once '../config.php';
}

/**
 * Obtiene todos los módulos disponibles
 * 
 * @return array Arreglo con la información de todos los módulos
 */
function getModulos() {
    $modulos = [];
    
    // Directorios de módulos
    $directorios = [
        'fundamentos-html',
        'estilizacion-css',
        'interactividad-javascript',
        'mejoras-jquery',
        'animaciones-gsap',
        'backend-php'
    ];
    
    // Solo incluir carpetas que existan
    foreach ($directorios as $directorio) {
        $rutaIndice = __DIR__ . '/' . $directorio . '/index.json';
        
        if (file_exists($rutaIndice)) {
            $contenido = file_get_contents($rutaIndice);
            $modulo = json_decode($contenido, true);
            
            if ($modulo) {
                // Añadir ruta del directorio para cargar recursos
                $modulo['directorio'] = $directorio;
                $modulos[] = $modulo;
            }
        }
    }
    
    // Ordenar por ID para mantener el orden correcto
    usort($modulos, function($a, $b) {
        return $a['id'] <=> $b['id'];
    });
    
    return $modulos;
}

/**
 * Obtiene la información de un módulo específico
 * 
 * @param int $moduloId ID del módulo a obtener
 * @return array|null Información del módulo o null si no existe
 */
function getModulo($moduloId) {
    $modulos = getModulos();
    
    foreach ($modulos as $modulo) {
        if ($modulo['id'] == $moduloId) {
            return $modulo;
        }
    }
    
    return null;
}

/**
 * Obtiene todas las lecciones de un módulo
 * 
 * @param int $moduloId ID del módulo
 * @return array Arreglo con la información de todas las lecciones del módulo
 */
function getLecciones($moduloId) {
    $modulo = getModulo($moduloId);
    
    if (!$modulo) {
        return [];
    }
    
    return isset($modulo['lecciones']) ? $modulo['lecciones'] : [];
}

/**
 * Obtiene la información detallada de una lección específica
 * 
 * @param int $moduloId ID del módulo
 * @param int $leccionId ID de la lección
 * @return array|null Información detallada de la lección o null si no existe
 */
function getLeccion($moduloId, $leccionId) {
    $modulo = getModulo($moduloId);
    
    if (!$modulo || !isset($modulo['directorio'])) {
        return null;
    }
    
    $rutaLeccion = __DIR__ . '/' . $modulo['directorio'] . '/leccion' . $leccionId . '.json';
    
    if (file_exists($rutaLeccion)) {
        $contenido = file_get_contents($rutaLeccion);
        $leccion = json_decode($contenido, true);
        
        if ($leccion) {
            // Añadir información del módulo a la lección
            $leccion['modulo_id'] = $modulo['id'];
            $leccion['modulo_titulo'] = $modulo['titulo'];
            
            return $leccion;
        }
    }
    
    return null;
}

/**
 * Verifica si un usuario ha completado una lección específica
 * 
 * @param int $userId ID del usuario
 * @param int $leccionId ID de la lección
 * @return bool True si ha completado la lección, false en caso contrario
 */
function hasCompletedLesson($userId, $leccionId) {
    try {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT completed FROM progress WHERE user_id = ? AND lesson_id = ?");
        $stmt->execute([$userId, $leccionId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result && $result['completed'] ? true : false;
    } catch (PDOException $e) {
        error_log('Error al verificar progreso: ' . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene el progreso de un usuario en todos los módulos
 * 
 * @param int $userId ID del usuario
 * @return array Arreglo con el progreso en cada módulo
 */
function getUserProgress($userId) {
    $modulos = getModulos();
    $progreso = [];
    
    try {
        $db = getDbConnection();
        
        foreach ($modulos as $modulo) {
            $totalLecciones = count($modulo['lecciones']);
            
            if ($totalLecciones > 0) {
                // Consultar lecciones completadas en este módulo
                $stmt = $db->prepare("
                    SELECT COUNT(*) as completadas 
                    FROM progress p 
                    JOIN lessons l ON p.lesson_id = l.lesson_id 
                    WHERE p.user_id = ? AND l.module_id = ? AND p.completed = 1
                ");
                $stmt->execute([$userId, $modulo['id']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $leccionesCompletadas = $result ? $result['completadas'] : 0;
                $porcentaje = $totalLecciones > 0 ? round(($leccionesCompletadas / $totalLecciones) * 100) : 0;
                
                $progreso[$modulo['id']] = [
                    'total_lecciones' => $totalLecciones,
                    'lecciones_completadas' => $leccionesCompletadas,
                    'porcentaje' => $porcentaje
                ];
            } else {
                $progreso[$modulo['id']] = [
                    'total_lecciones' => 0,
                    'lecciones_completadas' => 0,
                    'porcentaje' => 0
                ];
            }
        }
        
        return $progreso;
    } catch (PDOException $e) {
        error_log('Error al obtener progreso: ' . $e->getMessage());
        return [];
    }
}

/**
 * Marca una lección como completada para un usuario
 * 
 * @param int $userId ID del usuario
 * @param int $leccionId ID de la lección
 * @param int $score Puntaje obtenido (opcional)
 * @return bool True si se actualizó correctamente, false en caso contrario
 */
function completeLesson($userId, $leccionId, $score = null) {
    try {
        $db = getDbConnection();
        
        // Verificar si ya existe un registro de progreso
        $stmt = $db->prepare("SELECT progress_id FROM progress WHERE user_id = ? AND lesson_id = ?");
        $stmt->execute([$userId, $leccionId]);
        $existingProgress = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingProgress) {
            // Actualizar registro existente
            $stmt = $db->prepare("
                UPDATE progress 
                SET completed = 1, 
                    completion_date = NOW(), 
                    score = ? 
                WHERE user_id = ? AND lesson_id = ?
            ");
            $stmt->execute([$score, $userId, $leccionId]);
        } else {
            // Crear nuevo registro
            $stmt = $db->prepare("
                INSERT INTO progress 
                (user_id, lesson_id, completed, completion_date, score) 
                VALUES (?, ?, 1, NOW(), ?)
            ");
            $stmt->execute([$userId, $leccionId, $score]);
        }
        
        // Obtener la lección para añadir XP al usuario
        $leccion = null;
        $moduloId = null;
        
        // Primero buscar el módulo al que pertenece la lección
        $stmt = $db->prepare("SELECT module_id FROM lessons WHERE lesson_id = ?");
        $stmt->execute([$leccionId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $moduloId = $result['module_id'];
            $leccion = getLeccion($moduloId, $leccionId);
        }
        
        // Añadir XP al usuario si la lección tiene recompensa
        if ($leccion && isset($leccion['xp_recompensa']) && $leccion['xp_recompensa'] > 0) {
            $stmt = $db->prepare("
                UPDATE user_profiles 
                SET xp_points = xp_points + ? 
                WHERE user_id = ?
            ");
            $stmt->execute([$leccion['xp_recompensa'], $userId]);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log('Error al completar lección: ' . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene la siguiente lección recomendada para un usuario
 * 
 * @param int $userId ID del usuario
 * @return array|null Información de la siguiente lección recomendada o null
 */
function getNextRecommendedLesson($userId) {
    // Esta función recomendaría la siguiente lección basada en el progreso del usuario
    // Por ahora, simplemente retorna la primera lección no completada
    
    try {
        $modulos = getModulos();
        
        foreach ($modulos as $modulo) {
            if (isset($modulo['lecciones']) && !empty($modulo['lecciones'])) {
                foreach ($modulo['lecciones'] as $leccion) {
                    if (!hasCompletedLesson($userId, $leccion['id'])) {
                        return getLeccion($modulo['id'], $leccion['id']);
                    }
                }
            }
        }
        
        // Si todas las lecciones están completadas, recomendar el proyecto final del último módulo
        if (!empty($modulos)) {
            $ultimoModulo = end($modulos);
            
            if (isset($ultimoModulo['proyecto_final'])) {
                return [
                    'tipo' => 'proyecto_final',
                    'modulo_id' => $ultimoModulo['id'],
                    'modulo_titulo' => $ultimoModulo['titulo'],
                    'titulo' => $ultimoModulo['proyecto_final']['titulo'],
                    'descripcion' => $ultimoModulo['proyecto_final']['descripcion']
                ];
            }
        }
        
        // No hay recomendaciones disponibles
        return null;
    } catch (Exception $e) {
        error_log('Error al obtener lección recomendada: ' . $e->getMessage());
        return null;
    }
}
