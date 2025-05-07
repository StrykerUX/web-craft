<?php
/**
 * Lecciones de WebCraft Academy
 * 
 * Esta página muestra las lecciones de un módulo específico
 * y permite a los usuarios acceder al contenido educativo.
 */

// Verificar que el acceso sea a través del punto de entrada correcto
if (!defined('WEBCRAFT')) {
    header('Location: ../../index.php');
    exit;
}

// Obtener el ID del módulo de la URL
$moduleId = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 0;

// Obtener el ID de la lección si se proporciona
$lessonId = isset($_GET['lesson_id']) ? (int)$_GET['lesson_id'] : 0;

// Si no se proporciona un ID de módulo válido, redirigir a la página de módulos
if ($moduleId <= 0) {
    header('Location: index.php?page=modules');
    exit;
}

try {
    $db = getDbConnection();
    
    // Obtener información del módulo
    $stmt = $db->prepare("
        SELECT * FROM modules 
        WHERE module_id = ? AND is_active = 1
    ");
    $stmt->execute([$moduleId]);
    $module = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si el módulo no existe o no está activo, redirigir a la página de módulos
    if (!$module) {
        header('Location: index.php?page=modules');
        exit;
    }
    
    // Obtener todas las lecciones del módulo
    $stmt = $db->prepare("
        SELECT * FROM lessons 
        WHERE module_id = ? AND is_active = 1 
        ORDER BY order_index ASC
    ");
    $stmt->execute([$moduleId]);
    $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener progreso del usuario si está autenticado
    $userProgress = [];
    if (isset($_SESSION['user_id'])) {
        $stmt = $db->prepare("
            SELECT lesson_id, completed, score 
            FROM progress 
            WHERE user_id = ? AND lesson_id IN (
                SELECT lesson_id FROM lessons WHERE module_id = ?
            )
        ");
        $stmt->execute([$_SESSION['user_id'], $moduleId]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $userProgress[$row['lesson_id']] = [
                'completed' => $row['completed'],
                'score' => $row['score']
            ];
        }
    }
    
    // Si se proporciona un ID de lección, obtener su contenido
    $currentLesson = null;
    if ($lessonId > 0) {
        $stmt = $db->prepare("
            SELECT * FROM lessons 
            WHERE lesson_id = ? AND module_id = ? AND is_active = 1
        ");
        $stmt->execute([$lessonId, $moduleId]);
        $currentLesson = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si la lección no existe, redirigir a la primera lección del módulo
        if (!$currentLesson && !empty($lessons)) {
            header('Location: index.php?page=lessons&module_id=' . $moduleId . '&lesson_id=' . $lessons[0]['lesson_id']);
            exit;
        }
    } elseif (!empty($lessons)) {
        // Si no se proporciona un ID de lección pero hay lecciones disponibles,
        // seleccionar la primera o la última incompleta
        $currentLesson = $lessons[0]; // Por defecto, la primera lección
        
        // Buscar la última lección incompleta si el usuario está autenticado
        if (!empty($userProgress)) {
            foreach ($lessons as $lesson) {
                $isCompleted = isset($userProgress[$lesson['lesson_id']]) && $userProgress[$lesson['lesson_id']]['completed'];
                if (!$isCompleted) {
                    $currentLesson = $lesson;
                    break;
                }
            }
        }
        
        // Redirigir a la lección seleccionada
        header('Location: index.php?page=lessons&module_id=' . $moduleId . '&lesson_id=' . $currentLesson['lesson_id']);
        exit;
    }
    
} catch (PDOException $e) {
    // Manejar error de base de datos
    if (DEV_MODE) {
        echo "<!-- Error: " . $e->getMessage() . " -->";
    }
    $module = null;
    $lessons = [];
    $currentLesson = null;
}

// Función para determinar si una lección está bloqueada
function isLessonLocked($lesson, $lessons, $userProgress) {
    // La primera lección nunca está bloqueada
    if ($lesson['order_index'] == 1) {
        return false;
    }
    
    // Buscar la lección anterior
    $previousLessonId = null;
    foreach ($lessons as $l) {
        if ($l['order_index'] == $lesson['order_index'] - 1) {
            $previousLessonId = $l['lesson_id'];
            break;
        }
    }
    
    // Si no se encuentra la lección anterior, no está bloqueada
    if (!$previousLessonId) {
        return false;
    }
    
    // Verificar si la lección anterior está completada
    return !isset($userProgress[$previousLessonId]) || !$userProgress[$previousLessonId]['completed'];
}

// Obtener la siguiente y anterior lección para navegación
$prevLesson = null;
$nextLesson = null;
if ($currentLesson && !empty($lessons)) {
    foreach ($lessons as $i => $lesson) {
        if ($lesson['lesson_id'] == $currentLesson['lesson_id']) {
            if ($i > 0) {
                $prevLesson = $lessons[$i - 1];
            }
            if ($i < count($lessons) - 1) {
                $nextLesson = $lessons[$i + 1];
            }
            break;
        }
    }
}
?>

<div class="lessons-container">
    <?php if (!$module): ?>
    
    <div class="lessons-empty">
        <div class="empty-state">
            <i class="fas fa-exclamation-circle empty-icon"></i>
            <h2>Módulo no encontrado</h2>
            <p>Lo sentimos, el módulo que buscas no existe o no está disponible.</p>
            <a href="index.php?page=modules" class="btn btn-primary">Volver a Módulos</a>
        </div>
    </div>
    
    <?php elseif (!$currentLesson): ?>
    
    <div class="lessons-empty">
        <div class="empty-state">
            <i class="fas fa-book empty-icon"></i>
            <h2>No hay lecciones disponibles</h2>
            <p>Este módulo aún no tiene lecciones disponibles. ¡Vuelve pronto!</p>
            <a href="index.php?page=modules" class="btn btn-primary">Volver a Módulos</a>
        </div>
    </div>
    
    <?php else: ?>
    
    <div class="lessons-layout">
        <!-- Menú lateral de lecciones -->
        <div class="lessons-sidebar">
            <div class="sidebar-header">
                <h2><?php echo htmlspecialchars($module['title']); ?></h2>
                <a href="index.php?page=modules" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Volver a Módulos
                </a>
            </div>
            
            <div class="lessons-list">
                <?php foreach ($lessons as $lesson): 
                    $isCompleted = isset($userProgress[$lesson['lesson_id']]) && $userProgress[$lesson['lesson_id']]['completed'];
                    $isActive = $lesson['lesson_id'] == $currentLesson['lesson_id'];
                    $isLocked = isLessonLocked($lesson, $lessons, $userProgress);
                    $lessonClass = 'lesson-item';
                    if ($isActive) $lessonClass .= ' active';
                    if ($isCompleted) $lessonClass .= ' completed';
                    if ($isLocked) $lessonClass .= ' locked';
                ?>
                    <a href="<?php echo $isLocked ? 'javascript:void(0)' : 'index.php?page=lessons&module_id=' . $moduleId . '&lesson_id=' . $lesson['lesson_id']; ?>" 
                       class="<?php echo $lessonClass; ?>">
                        <span class="lesson-number"><?php echo $lesson['order_index']; ?></span>
                        <span class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></span>
                        <?php if ($isCompleted): ?>
                            <span class="lesson-status"><i class="fas fa-check-circle"></i></span>
                        <?php elseif ($isLocked): ?>
                            <span class="lesson-status"><i class="fas fa-lock"></i></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Contenido de la lección actual -->
        <div class="lesson-content">
            <div class="lesson-header">
                <h1><?php echo htmlspecialchars($currentLesson['title']); ?></h1>
                <div class="lesson-meta">
                    <span class="lesson-time">
                        <i class="far fa-clock"></i> 
                        <?php echo $currentLesson['estimated_time']; ?> minutos
                    </span>
                    <span class="lesson-xp">
                        <i class="fas fa-star"></i> 
                        <?php echo $currentLesson['xp_reward']; ?> XP
                    </span>
                </div>
            </div>
            
            <div class="lesson-body">
                <?php echo $currentLesson['content']; ?>
            </div>
            
            <div class="lesson-footer">
                <div class="lesson-navigation">
                    <?php if ($prevLesson): ?>
                    <a href="index.php?page=lessons&module_id=<?php echo $moduleId; ?>&lesson_id=<?php echo $prevLesson['lesson_id']; ?>" class="btn btn-secondary">
                        <i class="fas fa-chevron-left"></i> Lección Anterior
                    </a>
                    <?php endif; ?>
                    
                    <?php 
                    $isNextLocked = $nextLesson && isLessonLocked($nextLesson, $lessons, $userProgress);
                    if ($nextLesson && !$isNextLocked): 
                    ?>
                    <a href="index.php?page=lessons&module_id=<?php echo $moduleId; ?>&lesson_id=<?php echo $nextLesson['lesson_id']; ?>" class="btn btn-primary">
                        Siguiente Lección <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="lesson-completion">
                    <?php 
                    $isCompleted = isset($userProgress[$currentLesson['lesson_id']]) && $userProgress[$currentLesson['lesson_id']]['completed'];
                    if ($isCompleted): 
                    ?>
                    <div class="completion-status completed">
                        <i class="fas fa-check-circle"></i> Lección completada
                    </div>
                    <?php else: ?>
                    <form action="includes/api/complete_lesson.php" method="post" class="completion-form">
                        <input type="hidden" name="lesson_id" value="<?php echo $currentLesson['lesson_id']; ?>">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Marcar como completada
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div>
