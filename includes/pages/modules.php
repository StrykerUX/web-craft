<?php
/**
 * Módulos educativos de WebCraft Academy
 * 
 * Esta página muestra todos los módulos educativos disponibles
 * y permite a los usuarios acceder a ellos.
 */

// Verificar que el acceso sea a través del punto de entrada correcto
if (!defined('WEBCRAFT')) {
    header('Location: ../../index.php');
    exit;
}

// Obtener todos los módulos activos
try {
    $db = getDbConnection();
    $stmt = $db->prepare("
        SELECT * FROM modules 
        WHERE is_active = 1 
        ORDER BY order_index ASC
    ");
    $stmt->execute();
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener el progreso del usuario si está autenticado
    $userProgress = [];
    if (isset($_SESSION['user_id'])) {
        $stmt = $db->prepare("
            SELECT m.module_id, 
                   COUNT(l.lesson_id) AS total_lessons,
                   COUNT(p.progress_id) AS completed_lessons
            FROM modules m
            LEFT JOIN lessons l ON m.module_id = l.module_id AND l.is_active = 1
            LEFT JOIN progress p ON l.lesson_id = p.lesson_id AND p.user_id = ? AND p.completed = 1
            WHERE m.is_active = 1
            GROUP BY m.module_id
        ");
        $stmt->execute([$_SESSION['user_id']]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $userProgress[$row['module_id']] = [
                'total' => $row['total_lessons'],
                'completed' => $row['completed_lessons'],
                'percentage' => $row['total_lessons'] > 0 
                    ? round(($row['completed_lessons'] / $row['total_lessons']) * 100) 
                    : 0
            ];
        }
    }
} catch (PDOException $e) {
    // Manejar error de base de datos
    if (DEV_MODE) {
        echo "<!-- Error: " . $e->getMessage() . " -->";
    }
    $modules = [];
}
?>

<div class="modules-container">
    <div class="modules-header">
        <h1>Módulos de Aprendizaje</h1>
        <p class="modules-description">
            Explora nuestros módulos interactivos y aprende desarrollo web paso a paso.
            Comienza desde lo básico y avanza hasta dominar tecnologías avanzadas.
        </p>
    </div>
    
    <?php if (empty($modules)): ?>
    
    <div class="modules-empty">
        <div class="empty-state">
            <i class="fas fa-book-open empty-icon"></i>
            <h2>Módulos en desarrollo</h2>
            <p>Estamos trabajando para ofrecerte contenido educativo de alta calidad muy pronto.</p>
        </div>
    </div>
    
    <?php else: ?>
    
    <div class="modules-grid">
        <?php foreach ($modules as $module): ?>
            <?php 
            // Determinar estado del módulo
            $moduleProgress = isset($userProgress[$module['module_id']]) ? $userProgress[$module['module_id']] : null;
            $completionPercentage = $moduleProgress ? $moduleProgress['percentage'] : 0;
            $moduleStatus = '';
            
            if ($completionPercentage == 100) {
                $moduleStatus = 'completed';
            } elseif ($completionPercentage > 0) {
                $moduleStatus = 'in-progress';
            }
            
            // Determinar si el módulo está bloqueado
            $isLocked = false;
            if ($module['order_index'] > 1) {
                // Verificar si el módulo anterior está completado
                $previousModuleId = $module['order_index'] - 1;
                foreach ($modules as $prevModule) {
                    if ($prevModule['order_index'] == $previousModuleId) {
                        $prevProgress = isset($userProgress[$prevModule['module_id']]) ? $userProgress[$prevModule['module_id']] : null;
                        $isLocked = !$prevProgress || $prevProgress['percentage'] < 80;
                        break;
                    }
                }
            }
            ?>
            
            <div class="module-card <?php echo $moduleStatus; ?> <?php echo $isLocked ? 'locked' : ''; ?>">
                <div class="module-icon">
                    <i class="<?php echo htmlspecialchars($module['icon']); ?>"></i>
                </div>
                <div class="module-info">
                    <h2 class="module-title"><?php echo htmlspecialchars($module['title']); ?></h2>
                    <p class="module-description"><?php echo htmlspecialchars($module['description']); ?></p>
                    
                    <?php if ($moduleProgress): ?>
                    <div class="module-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $completionPercentage; ?>%"></div>
                        </div>
                        <span class="progress-text"><?php echo $completionPercentage; ?>% completado</span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="module-actions">
                        <?php if ($isLocked): ?>
                            <span class="module-locked">
                                <i class="fas fa-lock"></i> Completa el módulo anterior para desbloquear
                            </span>
                        <?php else: ?>
                            <a href="index.php?page=lessons&module_id=<?php echo $module['module_id']; ?>" class="btn btn-primary">
                                <?php echo $moduleProgress && $moduleProgress['completed'] > 0 ? 'Continuar' : 'Comenzar'; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php endif; ?>
    
    <div class="modules-footer">
        <div class="learning-path">
            <h3>Tu camino de aprendizaje</h3>
            <div class="path-steps">
                <?php 
                $totalModules = count($modules);
                foreach ($modules as $index => $module): 
                    $progress = isset($userProgress[$module['module_id']]) ? $userProgress[$module['module_id']]['percentage'] : 0;
                    $stepClass = 'path-step';
                    if ($progress == 100) {
                        $stepClass .= ' completed';
                    } elseif ($progress > 0) {
                        $stepClass .= ' in-progress';
                    }
                ?>
                    <div class="<?php echo $stepClass; ?>">
                        <div class="step-number"><?php echo $index + 1; ?></div>
                        <div class="step-label"><?php echo htmlspecialchars($module['title']); ?></div>
                        <?php if ($index < $totalModules - 1): ?>
                            <div class="step-connector"></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
