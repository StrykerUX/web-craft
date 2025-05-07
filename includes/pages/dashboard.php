<?php
/**
 * Dashboard de usuario para WebCraft Academy
 * 
 * Esta página muestra el panel principal del usuario con su progreso,
 * módulos disponibles, logros y actividad reciente.
 */

// Prevenir acceso directo a este archivo
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Requerir autenticación
requireAuthentication('dashboard');

// Obtener datos del usuario actual
$currentUser = getCurrentUser();
$userPreferences = getUserPreferences($currentUser['user_id']);

// Obtener estadísticas y datos para el dashboard
try {
    $db = getDbConnection();
    
    // Obtener módulos disponibles
    $modulesStmt = $db->prepare("
        SELECT m.module_id, m.module_name, m.module_description, m.module_order, m.icon_class,
               m.required_points, COUNT(l.lesson_id) AS lesson_count
        FROM modules m
        LEFT JOIN lessons l ON m.module_id = l.module_id AND l.is_active = TRUE
        WHERE m.is_active = TRUE
        GROUP BY m.module_id
        ORDER BY m.module_order ASC
    ");
    $modulesStmt->execute();
    $modules = $modulesStmt->fetchAll();
    
    // Obtener progreso general
    $progressStmt = $db->prepare("
        SELECT 
            COUNT(CASE WHEN up.status = 'completed' THEN 1 END) AS completed_lessons,
            COUNT(l.lesson_id) AS total_lessons,
            SUM(CASE WHEN up.status = 'completed' THEN up.xp_earned ELSE 0 END) AS total_xp
        FROM lessons l
        LEFT JOIN user_progress up ON l.lesson_id = up.lesson_id AND up.user_id = ?
        WHERE l.is_active = TRUE
    ");
    $progressStmt->execute([$currentUser['user_id']]);
    $progress = $progressStmt->fetch();
    
    // Calcular porcentaje de progreso
    $completionPercentage = 0;
    if ($progress['total_lessons'] > 0) {
        $completionPercentage = round(($progress['completed_lessons'] / $progress['total_lessons']) * 100);
    }
    
    // Obtener logros recientes
    $achievementsStmt = $db->prepare("
        SELECT a.achievement_name, a.achievement_description, a.achievement_icon, 
               a.achievement_type, a.xp_reward, ua.achieved_at
        FROM user_achievements ua
        JOIN achievements a ON ua.achievement_id = a.achievement_id
        WHERE ua.user_id = ?
        ORDER BY ua.achieved_at DESC
        LIMIT 5
    ");
    $achievementsStmt->execute([$currentUser['user_id']]);
    $recentAchievements = $achievementsStmt->fetchAll();
    
    // Obtener lecciones recomendadas (basadas en progreso actual)
    $recommendedStmt = $db->prepare("
        SELECT l.lesson_id, l.lesson_title, l.lesson_description, 
               m.module_name, m.icon_class, up.status, up.completion_percentage
        FROM lessons l
        JOIN modules m ON l.module_id = m.module_id
        LEFT JOIN user_progress up ON l.lesson_id = up.lesson_id AND up.user_id = ?
        WHERE l.is_active = TRUE 
        AND (up.status IN ('not_started', 'in_progress') OR up.status IS NULL)
        ORDER BY 
            CASE WHEN up.status = 'in_progress' THEN 0 ELSE 1 END,
            m.module_order ASC,
            l.lesson_order ASC
        LIMIT 3
    ");
    $recommendedStmt->execute([$currentUser['user_id']]);
    $recommendedLessons = $recommendedStmt->fetchAll();
    
} catch (PDOException $e) {
    // Manejar error
    if (DEV_MODE) {
        $error = 'Error en la base de datos: ' . $e->getMessage();
    } else {
        $error = 'Ocurrió un error al cargar los datos. Por favor, inténtalo de nuevo más tarde.';
    }
}
?>

<div class="dashboard-container">
    <!-- Encabezado de bienvenida -->
    <div class="dashboard-welcome">
        <div class="dashboard-welcome-content">
            <h1>Bienvenido, <?php echo htmlspecialchars($currentUser['display_name'] ?? $currentUser['username']); ?></h1>
            <p class="level-badge"><?php echo htmlspecialchars($currentUser['developer_level']); ?></p>
            <p>Continúa tu viaje como desarrollador web. Has completado <?php echo $progress['completed_lessons']; ?> de <?php echo $progress['total_lessons']; ?> lecciones.</p>
        </div>
        <div class="user-stats">
            <div class="stat-box">
                <span class="stat-value"><?php echo number_format($currentUser['experience_points']); ?></span>
                <span class="stat-label">Puntos XP</span>
            </div>
            <div class="stat-box">
                <span class="stat-value"><?php echo $completionPercentage; ?>%</span>
                <span class="stat-label">Completado</span>
            </div>
        </div>
    </div>
    
    <!-- Mensaje de error si existe -->
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <!-- Contenido principal del dashboard -->
    <div class="dashboard-content">
        <div class="dashboard-grid">
            <!-- Columna izquierda: Módulos y progreso -->
            <div class="dashboard-column">
                <section class="dashboard-section continue-learning">
                    <h2 class="section-title">Continúa Aprendiendo</h2>
                    <?php if (empty($recommendedLessons)): ?>
                        <div class="empty-state">
                            <p>¡Felicidades! Has completado todas las lecciones disponibles.</p>
                            <p>Revisa pronto para nuevos contenidos.</p>
                        </div>
                    <?php else: ?>
                        <div class="lesson-cards">
                            <?php foreach ($recommendedLessons as $lesson): ?>
                                <div class="lesson-card">
                                    <div class="lesson-card-icon">
                                        <i class="<?php echo htmlspecialchars($lesson['icon_class']); ?>"></i>
                                    </div>
                                    <div class="lesson-card-content">
                                        <h3 class="lesson-title"><?php echo htmlspecialchars($lesson['lesson_title']); ?></h3>
                                        <p class="lesson-module"><?php echo htmlspecialchars($lesson['module_name']); ?></p>
                                        <?php if ($lesson['status'] === 'in_progress'): ?>
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: <?php echo $lesson['completion_percentage']; ?>%"></div>
                                            </div>
                                            <span class="progress-text"><?php echo $lesson['completion_percentage']; ?>% Completado</span>
                                        <?php else: ?>
                                            <span class="status-badge new">Nueva</span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="index.php?page=lessons&id=<?php echo $lesson['lesson_id']; ?>" class="lesson-card-action">
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
                
                <section class="dashboard-section">
                    <h2 class="section-title">Módulos de Aprendizaje</h2>
                    
                    <div class="modules-grid">
                        <?php foreach ($modules as $module): ?>
                            <?php
                            // Calcular progreso del módulo
                            $moduleProgressStmt = $db->prepare("
                                SELECT 
                                    COUNT(CASE WHEN up.status = 'completed' THEN 1 END) AS completed_lessons,
                                    COUNT(l.lesson_id) AS total_lessons
                                FROM lessons l
                                LEFT JOIN user_progress up ON l.lesson_id = up.lesson_id AND up.user_id = ?
                                WHERE l.module_id = ? AND l.is_active = TRUE
                            ");
                            $moduleProgressStmt->execute([$currentUser['user_id'], $module['module_id']]);
                            $moduleProgress = $moduleProgressStmt->fetch();
                            
                            $moduleCompletionPercentage = 0;
                            if ($moduleProgress['total_lessons'] > 0) {
                                $moduleCompletionPercentage = round(($moduleProgress['completed_lessons'] / $moduleProgress['total_lessons']) * 100);
                            }
                            
                            // Determinar estado del módulo
                            $moduleStatus = 'unlocked';
                            if ($currentUser['experience_points'] < $module['required_points']) {
                                $moduleStatus = 'locked';
                            } elseif ($moduleCompletionPercentage == 100) {
                                $moduleStatus = 'completed';
                            }
                            ?>
                            
                            <div class="module-card <?php echo $moduleStatus; ?>">
                                <div class="module-icon">
                                    <i class="<?php echo htmlspecialchars($module['icon_class']); ?>"></i>
                                </div>
                                <div class="module-content">
                                    <h3 class="module-title"><?php echo htmlspecialchars($module['module_name']); ?></h3>
                                    <p class="module-description"><?php echo htmlspecialchars($module['module_description']); ?></p>
                                    
                                    <div class="module-stats">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $moduleCompletionPercentage; ?>%"></div>
                                        </div>
                                        <div class="module-progress-text">
                                            <?php echo $moduleProgress['completed_lessons']; ?>/<?php echo $moduleProgress['total_lessons']; ?> lecciones
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if ($moduleStatus === 'locked'): ?>
                                    <div class="module-locked-overlay">
                                        <i class="fas fa-lock"></i>
                                        <p><?php echo $module['required_points']; ?> XP necesarios</p>
                                    </div>
                                <?php endif; ?>
                                
                                <a href="index.php?page=modules&id=<?php echo $module['module_id']; ?>" class="module-link" <?php echo $moduleStatus === 'locked' ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                                    <span>Explorar módulo</span>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
            
            <!-- Columna derecha: Logros, estadísticas y actividad -->
            <div class="dashboard-column">
                <section class="dashboard-section user-profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?php if (!empty($currentUser['profile_image'])): ?>
                                <img src="<?php echo htmlspecialchars($currentUser['profile_image']); ?>" alt="Avatar de <?php echo htmlspecialchars($currentUser['display_name'] ?? $currentUser['username']); ?>">
                            <?php else: ?>
                                <div class="avatar-placeholder">
                                    <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="profile-info">
                            <h3><?php echo htmlspecialchars($currentUser['display_name'] ?? $currentUser['username']); ?></h3>
                            <div class="profile-level">
                                <div class="level-progress">
                                    <div class="level-progress-bar" style="width: <?php echo min(100, ($currentUser['experience_points'] % 1000) / 10); ?>%"></div>
                                </div>
                                <span class="level-text"><?php echo htmlspecialchars($currentUser['developer_level']); ?></span>
                            </div>
                        </div>
                        <a href="index.php?page=profile" class="profile-edit-link">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                    <div class="profile-stats">
                        <div class="stat-row">
                            <div class="stat-item">
                                <span class="stat-value"><?php echo number_format($currentUser['experience_points']); ?></span>
                                <span class="stat-label">XP Total</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-value"><?php echo $progress['completed_lessons']; ?></span>
                                <span class="stat-label">Lecciones Completadas</span>
                            </div>
                        </div>
                        <div class="stat-row">
                            <?php 
                            // Contar logros obtenidos
                            $achievementsCountStmt = $db->prepare("
                                SELECT COUNT(*) AS achievement_count 
                                FROM user_achievements 
                                WHERE user_id = ?
                            ");
                            $achievementsCountStmt->execute([$currentUser['user_id']]);
                            $achievementsCount = $achievementsCountStmt->fetch()['achievement_count'];
                            
                            // Contar días de racha
                            $streakStmt = $db->prepare("
                                SELECT COUNT(DISTINCT DATE(completed_at)) AS streak_days
                                FROM user_progress
                                WHERE user_id = ?
                                AND completed_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
                                AND status = 'completed'
                                ORDER BY completed_at DESC
                            ");
                            $streakStmt->execute([$currentUser['user_id']]);
                            $streakDays = $streakStmt->fetch()['streak_days'];
                            ?>
                            <div class="stat-item">
                                <span class="stat-value"><?php echo $achievementsCount; ?></span>
                                <span class="stat-label">Logros</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-value"><?php echo $streakDays; ?></span>
                                <span class="stat-label">Días de Racha</span>
                            </div>
                        </div>
                    </div>
                    <div class="profile-actions">
                        <a href="index.php?page=profile" class="btn btn-outline-secondary">
                            <i class="fas fa-user"></i> Ver Perfil
                        </a>
                        <a href="index.php?page=projects" class="btn btn-outline-secondary">
                            <i class="fas fa-code"></i> Mis Proyectos
                        </a>
                    </div>
                </section>
                
                <section class="dashboard-section">
                    <h2 class="section-title">Logros Recientes</h2>
                    <?php if (empty($recentAchievements)): ?>
                        <div class="empty-state">
                            <p>Aún no has conseguido ningún logro.</p>
                            <p>¡Completa lecciones para desbloquear logros!</p>
                        </div>
                    <?php else: ?>
                        <div class="achievements-list">
                            <?php foreach ($recentAchievements as $achievement): ?>
                                <div class="achievement-item">
                                    <div class="achievement-icon">
                                        <?php if (!empty($achievement['achievement_icon'])): ?>
                                            <img src="assets/images/achievements/<?php echo htmlspecialchars($achievement['achievement_icon']); ?>" alt="">
                                        <?php else: ?>
                                            <i class="fas fa-trophy"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="achievement-info">
                                        <h3 class="achievement-title"><?php echo htmlspecialchars($achievement['achievement_name']); ?></h3>
                                        <p class="achievement-description"><?php echo htmlspecialchars($achievement['achievement_description']); ?></p>
                                        <div class="achievement-meta">
                                            <span class="achievement-type <?php echo $achievement['achievement_type']; ?>">
                                                <?php 
                                                switch ($achievement['achievement_type']) {
                                                    case 'skill':
                                                        echo 'Habilidad';
                                                        break;
                                                    case 'progress':
                                                        echo 'Progreso';
                                                        break;
                                                    case 'challenge':
                                                        echo 'Desafío';
                                                        break;
                                                    case 'social':
                                                        echo 'Social';
                                                        break;
                                                    default:
                                                        echo ucfirst($achievement['achievement_type']);
                                                }
                                                ?>
                                            </span>
                                            <span class="achievement-points">+<?php echo $achievement['xp_reward']; ?> XP</span>
                                            <span class="achievement-date">
                                                <?php echo date('d/m/Y', strtotime($achievement['achieved_at'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="view-all-link">
                            <a href="index.php?page=achievements">Ver todos los logros</a>
                        </div>
                    <?php endif; ?>
                </section>
                
                <section class="dashboard-section cta-section">
                    <div class="cta-card">
                        <h3>Comparte tu progreso</h3>
                        <p>¡Muestra a la comunidad lo que estás aprendiendo!</p>
                        <div class="cta-buttons">
                            <a href="#" class="btn btn-accent btn-share-twitter">
                                <i class="fab fa-twitter"></i> Twitter
                            </a>
                            <a href="#" class="btn btn-accent btn-share-linkedin">
                                <i class="fab fa-linkedin"></i> LinkedIn
                            </a>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<script>
// Scripts para compartir en redes sociales
document.addEventListener('DOMContentLoaded', function() {
    // Botón compartir en Twitter
    const twitterBtn = document.querySelector('.btn-share-twitter');
    if (twitterBtn) {
        twitterBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const level = <?php echo json_encode($currentUser['developer_level']); ?>;
            const completedLessons = <?php echo json_encode($progress['completed_lessons']); ?>;
            
            const text = `¡Estoy aprendiendo desarrollo web en WebCraft Academy! Ya soy ${level} y he completado ${completedLessons} lecciones. 🚀 #WebCraftAcademy #CodingJourney`;
            const url = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(text);
            window.open(url, '_blank');
        });
    }
    
    // Botón compartir en LinkedIn
    const linkedInBtn = document.querySelector('.btn-share-linkedin');
    if (linkedInBtn) {
        linkedInBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = 'https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(window.location.origin);
            window.open(url, '_blank');
        });
    }
});
</script>
