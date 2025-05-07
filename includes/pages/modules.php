<?php
// Verificar que el script se ejecuta dentro del contexto adecuado
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Obtener datos del usuario
$user = getCurrentUser();

// Verificar si se solicita un módulo específico
$module_id = isset($_GET['module_id']) ? (int)$_GET['module_id'] : null;

// Si hay un módulo específico, obtener sus detalles
$specific_module = null;
if ($module_id) {
    try {
        $db = getDbConnection();
        
        // Obtener detalles del módulo
        $stmt = $db->prepare("
            SELECT m.module_id, m.title, m.description, m.icon,
                  (SELECT COUNT(*) FROM lessons WHERE module_id = m.module_id) as total_lessons,
                  (SELECT COUNT(*) FROM lessons l JOIN progress p ON l.lesson_id = p.lesson_id 
                   WHERE l.module_id = m.module_id AND p.user_id = ? AND p.completed = 1) as completed_lessons
            FROM modules m
            WHERE m.module_id = ? AND m.is_active = 1
        ");
        $stmt->execute([$_SESSION['user_id'], $module_id]);
        $specific_module = $stmt->fetch();
        
        if ($specific_module) {
            // Calcular porcentaje de progreso
            $specific_module['progress_percentage'] = 0;
            if ($specific_module['total_lessons'] > 0) {
                $specific_module['progress_percentage'] = ($specific_module['completed_lessons'] / $specific_module['total_lessons']) * 100;
            }
            
            // Obtener lecciones del módulo
            $stmt = $db->prepare("
                SELECT l.lesson_id, l.title, l.content, l.order_index, l.xp_reward, l.estimated_time,
                       CASE WHEN p.completed = 1 THEN TRUE ELSE FALSE END as completed,
                       p.score, p.completion_date
                FROM lessons l
                LEFT JOIN progress p ON l.lesson_id = p.lesson_id AND p.user_id = ?
                WHERE l.module_id = ? AND l.is_active = 1
                ORDER BY l.order_index ASC
            ");
            $stmt->execute([$_SESSION['user_id'], $module_id]);
            $specific_module['lessons'] = $stmt->fetchAll();
        }
        
    } catch (PDOException $e) {
        // Manejar error
        if (DEV_MODE) {
            $error = 'Error de base de datos: ' . $e->getMessage();
        } else {
            $error = 'Error al cargar el módulo. Por favor, intenta nuevamente más tarde.';
        }
    }
}

// Si no hay módulo específico o no se encontró, mostrar todos los módulos
if (!$module_id || !$specific_module) {
    try {
        $db = getDbConnection();
        
        // Obtener todos los módulos con estadísticas
        $stmt = $db->prepare("
            SELECT m.module_id, m.title, m.description, m.order_index, m.icon,
                  (SELECT COUNT(*) FROM lessons WHERE module_id = m.module_id) as total_lessons,
                  (SELECT COUNT(*) FROM lessons l JOIN progress p ON l.lesson_id = p.lesson_id 
                   WHERE l.module_id = m.module_id AND p.user_id = ? AND p.completed = 1) as completed_lessons
            FROM modules m
            WHERE m.is_active = 1
            ORDER BY m.order_index ASC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $all_modules = $stmt->fetchAll();
        
        // Calcular porcentaje de progreso para cada módulo
        foreach ($all_modules as &$module) {
            $module['progress_percentage'] = 0;
            if ($module['total_lessons'] > 0) {
                $module['progress_percentage'] = ($module['completed_lessons'] / $module['total_lessons']) * 100;
            }
        }
        
    } catch (PDOException $e) {
        // Manejar error
        if (DEV_MODE) {
            $error = 'Error de base de datos: ' . $e->getMessage();
        } else {
            $error = 'Error al cargar los módulos. Por favor, intenta nuevamente más tarde.';
        }
        
        $all_modules = [];
    }
}
?>

<div class="modules-container">
    <?php if ($specific_module): ?>
        <!-- Vista de módulo específico -->
        <div class="module-header">
            <div class="module-header-content">
                <a href="index.php?page=modules" class="back-link">
                    <i class="fas fa-arrow-left"></i> Todos los módulos
                </a>
                
                <div class="module-title-wrapper">
                    <i class="<?php echo htmlspecialchars($specific_module['icon']); ?>"></i>
                    <h1><?php echo htmlspecialchars($specific_module['title']); ?></h1>
                </div>
                
                <div class="module-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $specific_module['progress_percentage']; ?>%"></div>
                    </div>
                    <div class="progress-text">
                        <span><?php echo $specific_module['completed_lessons']; ?>/<?php echo $specific_module['total_lessons']; ?> lecciones completadas</span>
                        <span><?php echo round($specific_module['progress_percentage']); ?>% completado</span>
                    </div>
                </div>
                
                <div class="module-description">
                    <?php echo nl2br(htmlspecialchars($specific_module['description'])); ?>
                </div>
            </div>
        </div>
        
        <div class="module-content">
            <h2>Lecciones</h2>
            
            <?php if (empty($specific_module['lessons'])): ?>
                <div class="empty-state">
                    <i class="fas fa-book"></i>
                    <p>Este módulo aún no tiene lecciones disponibles.</p>
                </div>
            <?php else: ?>
                <div class="lessons-timeline">
                    <?php foreach ($specific_module['lessons'] as $index => $lesson): ?>
                        <div class="lesson-card <?php echo $lesson['completed'] ? 'completed' : ''; ?>">
                            <div class="lesson-number">
                                <?php if ($lesson['completed']): ?>
                                    <div class="completed-indicator">
                                        <i class="fas fa-check"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="lesson-indicator">
                                        <?php echo $index + 1; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="lesson-content">
                                <h3 class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></h3>
                                
                                <div class="lesson-meta">
                                    <span class="lesson-time">
                                        <i class="fas fa-clock"></i> <?php echo $lesson['estimated_time']; ?> min
                                    </span>
                                    <span class="lesson-xp">
                                        <i class="fas fa-star"></i> <?php echo $lesson['xp_reward']; ?> XP
                                    </span>
                                    <?php if ($lesson['completed']): ?>
                                        <span class="lesson-completed-date">
                                            <i class="fas fa-calendar-check"></i> 
                                            Completado: <?php echo date('d/m/Y', strtotime($lesson['completion_date'])); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="lesson-actions">
                                    <a href="index.php?page=lessons&lesson_id=<?php echo $lesson['lesson_id']; ?>" class="btn btn-primary">
                                        <?php echo $lesson['completed'] ? 'Repasar Lección' : 'Iniciar Lección'; ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
    <?php else: ?>
        <!-- Vista de todos los módulos -->
        <div class="modules-header">
            <h1>Módulos de Aprendizaje</h1>
            <p>Explora nuestros módulos y comienza tu camino en el desarrollo web.</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($all_modules)): ?>
            <div class="empty-state">
                <i class="fas fa-cubes"></i>
                <p>No hay módulos disponibles actualmente.</p>
            </div>
        <?php else: ?>
            <div class="modules-grid">
                <?php foreach ($all_modules as $module): ?>
                    <div class="module-card">
                        <div class="module-card-header">
                            <div class="module-icon">
                                <i class="<?php echo htmlspecialchars($module['icon']); ?>"></i>
                            </div>
                            <h2 class="module-title"><?php echo htmlspecialchars($module['title']); ?></h2>
                        </div>
                        
                        <div class="module-card-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $module['progress_percentage']; ?>%"></div>
                            </div>
                            <div class="progress-text">
                                <span><?php echo $module['completed_lessons']; ?>/<?php echo $module['total_lessons']; ?> lecciones</span>
                                <span><?php echo round($module['progress_percentage']); ?>%</span>
                            </div>
                        </div>
                        
                        <div class="module-card-content">
                            <p class="module-description"><?php echo htmlspecialchars(substr($module['description'], 0, 150) . (strlen($module['description']) > 150 ? '...' : '')); ?></p>
                        </div>
                        
                        <div class="module-card-footer">
                            <a href="index.php?page=modules&module_id=<?php echo $module['module_id']; ?>" class="btn btn-primary">Ver Módulo</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
