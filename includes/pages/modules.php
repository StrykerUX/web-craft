<?php
/**
 * Página de módulos para WebCraft Academy
 * 
 * Esta página muestra la lista de módulos disponibles o el detalle de un módulo específico
 */

// Prevenir acceso directo a este archivo
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Requerir autenticación
requireAuthentication('modules');

// Incluir funciones de base de datos si no están cargadas
require_once 'includes/database.php';

// Obtener usuario actual
$currentUser = getCurrentUser();

// Verificar si se solicita un módulo específico
$moduleId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($moduleId) {
    // Vista de detalle de módulo
    $module = getModule($moduleId, $currentUser['user_id']);
    
    // Verificar si el módulo existe
    if (!$module) {
        echo '<div class="error-container">
                <h1>Módulo no encontrado</h1>
                <p>El módulo que buscas no existe o no está disponible.</p>
                <a href="index.php?page=modules" class="btn btn-primary">Ver todos los módulos</a>
              </div>';
        return;
    }
    
    // Verificar si el usuario tiene suficientes puntos para el módulo
    $isUnlocked = $currentUser['experience_points'] >= $module['required_points'];
?>

<div class="module-detail-container">
    <div class="module-header">
        <div class="container">
            <div class="module-header-content">
                <a href="index.php?page=modules" class="back-link">
                    <i class="fas fa-arrow-left"></i> Volver a módulos
                </a>
                <div class="module-icon-large">
                    <i class="<?php echo htmlspecialchars($module['icon_class']); ?>"></i>
                </div>
                <h1 class="module-title"><?php echo htmlspecialchars($module['module_name']); ?></h1>
                <p class="module-description"><?php echo htmlspecialchars($module['module_description']); ?></p>
                
                <div class="module-stats">
                    <div class="stat">
                        <span class="stat-value"><?php echo $module['total_lessons']; ?></span>
                        <span class="stat-label">Lecciones</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value"><?php echo $module['completed_lessons']; ?></span>
                        <span class="stat-label">Completadas</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value"><?php echo $module['total_xp_earned']; ?> XP</span>
                        <span class="stat-label">Ganados</span>
                    </div>
                </div>
                
                <div class="module-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $module['completion_percentage']; ?>%"></div>
                    </div>
                    <div class="progress-text"><?php echo $module['completion_percentage']; ?>% completado</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="module-content">
        <div class="container">
            <?php if (!$isUnlocked): ?>
                <div class="module-locked">
                    <div class="locked-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h2>Módulo bloqueado</h2>
                    <p>Necesitas <?php echo $module['required_points']; ?> puntos de experiencia para desbloquear este módulo.</p>
                    <p>Actualmente tienes <?php echo $currentUser['experience_points']; ?> XP.</p>
                    <a href="index.php?page=dashboard" class="btn btn-primary">Volver al Dashboard</a>
                </div>
            <?php else: ?>
                <div class="lessons-list">
                    <h2 class="section-title">Lecciones</h2>
                    
                    <?php if (empty($module['lessons'])): ?>
                        <div class="empty-state">
                            <p>No hay lecciones disponibles en este momento.</p>
                            <p>¡Vuelve pronto para ver nuevo contenido!</p>
                        </div>
                    <?php else: ?>
                        <div class="lessons-timeline">
                            <?php foreach ($module['lessons'] as $index => $lesson): 
                                $isAvailable = $lesson['is_available'];
                                $statusClass = '';
                                $statusIcon = '';
                                
                                if ($lesson['status'] === 'completed') {
                                    $statusClass = 'completed';
                                    $statusIcon = '<i class="fas fa-check-circle"></i>';
                                } elseif ($lesson['status'] === 'in_progress') {
                                    $statusClass = 'in-progress';
                                    $statusIcon = '<i class="fas fa-clock"></i>';
                                } elseif (!$isAvailable) {
                                    $statusClass = 'locked';
                                    $statusIcon = '<i class="fas fa-lock"></i>';
                                } else {
                                    $statusClass = 'available';
                                    $statusIcon = '<i class="fas fa-circle"></i>';
                                }
                            ?>
                                <div class="lesson-item <?php echo $statusClass; ?>">
                                    <div class="lesson-number"><?php echo $index + 1; ?></div>
                                    <div class="lesson-content">
                                        <div class="lesson-status">
                                            <?php echo $statusIcon; ?>
                                        </div>
                                        <div class="lesson-info">
                                            <h3 class="lesson-title"><?php echo htmlspecialchars($lesson['lesson_title']); ?></h3>
                                            <p class="lesson-description"><?php echo htmlspecialchars($lesson['lesson_description']); ?></p>
                                            <div class="lesson-meta">
                                                <?php if ($lesson['estimated_time_minutes']): ?>
                                                    <span class="lesson-time">
                                                        <i class="fas fa-clock"></i> <?php echo $lesson['estimated_time_minutes']; ?> min
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <span class="lesson-xp">
                                                    <i class="fas fa-star"></i> <?php echo $lesson['xp_reward']; ?> XP
                                                </span>
                                                
                                                <?php if ($lesson['status'] === 'in_progress'): ?>
                                                    <div class="lesson-progress">
                                                        <div class="small-progress-bar">
                                                            <div class="small-progress-fill" style="width: <?php echo $lesson['completion_percentage']; ?>%"></div>
                                                        </div>
                                                        <span class="small-progress-text"><?php echo $lesson['completion_percentage']; ?>%</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="lesson-action">
                                            <?php if ($isAvailable): ?>
                                                <a href="index.php?page=lessons&id=<?php echo $lesson['lesson_id']; ?>" class="btn btn-sm <?php echo $lesson['status'] === 'completed' ? 'btn-outline' : 'btn-primary'; ?>">
                                                    <?php
                                                    if ($lesson['status'] === 'completed') {
                                                        echo 'Repasar';
                                                    } elseif ($lesson['status'] === 'in_progress') {
                                                        echo 'Continuar';
                                                    } else {
                                                        echo 'Comenzar';
                                                    }
                                                    ?>
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-disabled" disabled>Bloqueada</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="module-resources">
                    <h2 class="section-title">Recursos adicionales</h2>
                    
                    <div class="resources-grid">
                        <div class="resource-card">
                            <div class="resource-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="resource-content">
                                <h3 class="resource-title">Documentación</h3>
                                <p class="resource-description">Referencia completa de <?php echo htmlspecialchars($module['module_name']); ?></p>
                                <a href="#" class="btn btn-sm btn-outline">Ver documentación</a>
                            </div>
                        </div>
                        
                        <div class="resource-card">
                            <div class="resource-icon">
                                <i class="fas fa-video"></i>
                            </div>
                            <div class="resource-content">
                                <h3 class="resource-title">Videotutoriales</h3>
                                <p class="resource-description">Tutoriales en video paso a paso</p>
                                <a href="#" class="btn btn-sm btn-outline">Ver videos</a>
                            </div>
                        </div>
                        
                        <div class="resource-card">
                            <div class="resource-icon">
                                <i class="fas fa-file-code"></i>
                            </div>
                            <div class="resource-content">
                                <h3 class="resource-title">Ejemplos de código</h3>
                                <p class="resource-description">Ejemplos prácticos para usar</p>
                                <a href="#" class="btn btn-sm btn-outline">Ver ejemplos</a>
                            </div>
                        </div>
                        
                        <div class="resource-card">
                            <div class="resource-icon">
                                <i class="fas fa-puzzle-piece"></i>
                            </div>
                            <div class="resource-content">
                                <h3 class="resource-title">Ejercicios adicionales</h3>
                                <p class="resource-description">Practica más con estos ejercicios</p>
                                <a href="#" class="btn btn-sm btn-outline">Ver ejercicios</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
} else {
    // Vista de lista de módulos
    $modules = getModules($currentUser['user_id']);
?>

<div class="modules-list-container">
    <div class="modules-header">
        <div class="container">
            <h1 class="page-title">Módulos de aprendizaje</h1>
            <p class="page-description">Explora los módulos disponibles y elige por dónde empezar</p>
        </div>
    </div>
    
    <div class="container">
        <div class="modules-list">
            <?php if (empty($modules)): ?>
                <div class="empty-state">
                    <p>No hay módulos disponibles en este momento.</p>
                    <p>¡Vuelve pronto para ver nuevo contenido!</p>
                </div>
            <?php else: ?>
                <div class="modules-grid">
                    <?php foreach ($modules as $module): 
                        // Calcular progreso del módulo
                        $totalLessons = (int)$module['lesson_count'];
                        $completedLessons = (int)$module['completed_lessons'];
                        $completionPercentage = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
                        
                        // Determinar estado del módulo
                        $moduleStatus = 'unlocked';
                        if ($currentUser['experience_points'] < $module['required_points']) {
                            $moduleStatus = 'locked';
                        } elseif ($completionPercentage == 100) {
                            $moduleStatus = 'completed';
                        }
                    ?>
                        <div class="module-card <?php echo $moduleStatus; ?>">
                            <div class="module-header">
                                <div class="module-icon">
                                    <i class="<?php echo htmlspecialchars($module['icon_class']); ?>"></i>
                                </div>
                                <div class="module-badge">
                                    <?php if ($moduleStatus === 'completed'): ?>
                                        <span class="badge badge-completed">Completado</span>
                                    <?php elseif ($moduleStatus === 'locked'): ?>
                                        <span class="badge badge-locked">Bloqueado</span>
                                    <?php elseif ($completedLessons > 0): ?>
                                        <span class="badge badge-in-progress">En progreso</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="module-body">
                                <h2 class="module-title"><?php echo htmlspecialchars($module['module_name']); ?></h2>
                                <p class="module-description"><?php echo htmlspecialchars($module['module_description']); ?></p>
                                
                                <div class="module-stats">
                                    <div class="stat">
                                        <span class="stat-value"><?php echo $totalLessons; ?></span>
                                        <span class="stat-label">Lecciones</span>
                                    </div>
                                    <div class="stat">
                                        <span class="stat-value"><?php echo $completedLessons; ?></span>
                                        <span class="stat-label">Completadas</span>
                                    </div>
                                </div>
                                
                                <div class="module-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $completionPercentage; ?>%"></div>
                                    </div>
                                    <div class="progress-text"><?php echo $completionPercentage; ?>% completado</div>
                                </div>
                            </div>
                            
                            <?php if ($moduleStatus === 'locked'): ?>
                                <div class="module-locked-overlay">
                                    <div class="locked-content">
                                        <i class="fas fa-lock"></i>
                                        <p>Necesitas <?php echo $module['required_points']; ?> XP</p>
                                        <p class="current-xp">Tienes: <?php echo $currentUser['experience_points']; ?> XP</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="module-footer">
                                <a href="index.php?page=modules&id=<?php echo $module['module_id']; ?>" class="btn <?php echo $moduleStatus === 'locked' ? 'btn-disabled' : 'btn-primary'; ?>" <?php echo $moduleStatus === 'locked' ? 'disabled' : ''; ?>>
                                    <?php
                                    if ($moduleStatus === 'locked') {
                                        echo 'Bloqueado';
                                    } elseif ($moduleStatus === 'completed') {
                                        echo 'Repasar';
                                    } elseif ($completedLessons > 0) {
                                        echo 'Continuar';
                                    } else {
                                        echo 'Comenzar';
                                    }
                                    ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
}
?>
