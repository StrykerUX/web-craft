<?php
// Verificar que el script se ejecuta dentro del contexto adecuado
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Obtener datos completos del usuario
$user = getCurrentUser(true);

// Obtener estadísticas del usuario
try {
    $db = getDbConnection();
    
    // Contar módulos completados
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT m.module_id) as completed_modules
        FROM progress p
        JOIN lessons l ON p.lesson_id = l.lesson_id
        JOIN modules m ON l.module_id = m.module_id
        WHERE p.user_id = ? AND p.completed = 1
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $modules_stats = $stmt->fetch();
    $completed_modules = $modules_stats['completed_modules'] ?? 0;
    
    // Contar lecciones completadas
    $stmt = $db->prepare("
        SELECT COUNT(*) as completed_lessons
        FROM progress
        WHERE user_id = ? AND completed = 1
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $lessons_stats = $stmt->fetch();
    $completed_lessons = $lessons_stats['completed_lessons'] ?? 0;
    
    // Obtener total de tiempo estudiado
    $stmt = $db->prepare("
        SELECT SUM(time_spent) as total_time
        FROM progress
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $time_stats = $stmt->fetch();
    $total_time = $time_stats['total_time'] ?? 0;
    
    // Obtener logros
    $stmt = $db->prepare("
        SELECT a.title, a.description, a.icon, ua.date_earned
        FROM user_achievements ua
        JOIN achievements a ON ua.achievement_id = a.achievement_id
        WHERE ua.user_id = ?
        ORDER BY ua.date_earned DESC
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $achievements = $stmt->fetchAll();
    
    // Obtener proyectos recientes
    $stmt = $db->prepare("
        SELECT project_id, title, description, last_modified, thumbnail
        FROM projects
        WHERE user_id = ?
        ORDER BY last_modified DESC
        LIMIT 3
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_projects = $stmt->fetchAll();
    
    // Obtener próximas lecciones (no completadas)
    $stmt = $db->prepare("
        SELECT l.lesson_id, l.title, m.title as module_title, m.icon as module_icon
        FROM lessons l
        JOIN modules m ON l.module_id = m.module_id
        LEFT JOIN progress p ON l.lesson_id = p.lesson_id AND p.user_id = ?
        WHERE p.progress_id IS NULL OR p.completed = 0
        ORDER BY m.order_index, l.order_index
        LIMIT 3
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $next_lessons = $stmt->fetchAll();
    
    // Obtener desafíos disponibles
    $stmt = $db->prepare("
        SELECT c.challenge_id, c.title, c.difficulty, c.xp_reward
        FROM challenges c
        LEFT JOIN challenge_attempts ca ON c.challenge_id = ca.challenge_id AND ca.user_id = ?
        WHERE ca.attempt_id IS NULL OR ca.is_completed = 0
        ORDER BY c.difficulty, RAND()
        LIMIT 3
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $available_challenges = $stmt->fetchAll();
    
    // Obtener todos los módulos para el menú de navegación
    $stmt = $db->prepare("
        SELECT m.*, 
            (SELECT COUNT(*) FROM lessons WHERE module_id = m.module_id) as total_lessons,
            (SELECT COUNT(*) FROM lessons l JOIN progress p ON l.lesson_id = p.lesson_id 
             WHERE l.module_id = m.module_id AND p.user_id = ? AND p.completed = 1) as completed_lessons
        FROM modules m
        WHERE m.is_active = 1
        ORDER BY m.order_index
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $all_modules = $stmt->fetchAll();
    
} catch (PDOException $e) {
    // En caso de error, inicializar con valores predeterminados
    if (DEV_MODE) {
        error_log('Error al obtener estadísticas del dashboard: ' . $e->getMessage());
    }
    $completed_modules = 0;
    $completed_lessons = 0;
    $total_time = 0;
    $achievements = [];
    $recent_projects = [];
    $next_lessons = [];
    $available_challenges = [];
    $all_modules = [];
}

// Calcular nivel de experiencia y progreso
$current_xp = $user['xp_points'] ?? 0;
$level_thresholds = [
    'Principiante' => 0,
    'Novato' => 100,
    'Aprendiz' => 300,
    'Desarrollador' => 600,
    'Maestro' => 1000
];

$current_level = 'Principiante';
$next_level = 'Novato';
$xp_for_next_level = 100;
$level_progress = 0;

foreach ($level_thresholds as $level => $threshold) {
    if ($current_xp >= $threshold) {
        $current_level = $level;
    } else {
        $next_level = $level;
        $xp_for_next_level = $threshold;
        $prev_threshold = $level_thresholds[$current_level];
        $level_progress = ($current_xp - $prev_threshold) / ($threshold - $prev_threshold) * 100;
        break;
    }
}

// Formatear tiempo total de estudio
function formatTimeSpent($seconds) {
    if ($seconds < 60) {
        return "$seconds seg";
    } elseif ($seconds < 3600) {
        return floor($seconds / 60) . " min";
    } else {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return "$hours h $minutes min";
    }
}

$formatted_time = formatTimeSpent($total_time);

// Formatear fecha de último acceso
function formatLastLogin($date) {
    if (!$date) return "Primera visita";
    
    $now = new DateTime();
    $last = new DateTime($date);
    $diff = $now->diff($last);
    
    if ($diff->days == 0) {
        if ($diff->h == 0) {
            if ($diff->i == 0) {
                return "Hace unos segundos";
            }
            return "Hace " . $diff->i . " minutos";
        }
        return "Hace " . $diff->h . " horas";
    } elseif ($diff->days == 1) {
        return "Ayer";
    } elseif ($diff->days < 7) {
        return "Hace " . $diff->days . " días";
    } else {
        return $last->format('d/m/Y');
    }
}

$last_login = formatLastLogin($user['last_login'] ?? null);
?>

<div class="dashboard-container">
    <!-- Barra lateral de navegación -->
    <aside class="dashboard-sidebar">
        <div class="user-profile-mini">
            <div class="user-avatar">
                <img src="assets/images/avatars/<?php echo htmlspecialchars($user['avatar'] ?? 'default.png'); ?>" alt="Avatar de <?php echo htmlspecialchars($user['username']); ?>">
                <span class="user-level-badge"><?php echo htmlspecialchars($current_level); ?></span>
            </div>
            <div class="user-info">
                <h3 class="user-name"><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></h3>
                <div class="xp-progress">
                    <div class="xp-bar">
                        <div class="xp-fill" style="width: <?php echo $level_progress; ?>%"></div>
                    </div>
                    <span class="xp-text"><?php echo $current_xp; ?> XP</span>
                </div>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li class="nav-item active">
                    <a href="index.php?page=dashboard" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php?page=modules" class="nav-link">
                        <i class="fas fa-book"></i>
                        <span>Módulos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php?page=challenges" class="nav-link">
                        <i class="fas fa-trophy"></i>
                        <span>Desafíos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php?page=projects" class="nav-link">
                        <i class="fas fa-code"></i>
                        <span>Mis Proyectos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php?page=editor" class="nav-link">
                        <i class="fas fa-edit"></i>
                        <span>Editor</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php?page=forum" class="nav-link">
                        <i class="fas fa-comments"></i>
                        <span>Foro</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php?page=profile" class="nav-link">
                        <i class="fas fa-user-cog"></i>
                        <span>Perfil</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="modules-accordion">
            <h3 class="accordion-title">Mis Módulos</h3>
            <div class="accordion-content">
                <ul class="modules-list">
                    <?php foreach ($all_modules as $module): ?>
                        <?php 
                            // Calcular porcentaje de progreso
                            $module_progress = 0;
                            if ($module['total_lessons'] > 0) {
                                $module_progress = ($module['completed_lessons'] / $module['total_lessons']) * 100;
                            }
                        ?>
                        <li class="module-item">
                            <a href="index.php?page=modules&module_id=<?php echo $module['module_id']; ?>" class="module-link">
                                <i class="<?php echo htmlspecialchars($module['icon']); ?>"></i>
                                <span><?php echo htmlspecialchars($module['title']); ?></span>
                                <div class="module-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $module_progress; ?>%"></div>
                                    </div>
                                    <span class="progress-text"><?php echo $module['completed_lessons']; ?>/<?php echo $module['total_lessons']; ?></span>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    
                    <?php if (empty($all_modules)): ?>
                        <li class="module-item empty">
                            <span>No hay módulos disponibles</span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </aside>
    
    <!-- Contenido principal del dashboard -->
    <main class="dashboard-content">
        <header class="dashboard-header">
            <h1>Mi Dashboard</h1>
            <div class="header-actions">
                <div class="last-login">
                    <i class="fas fa-clock"></i>
                    <span>Último acceso: <?php echo $last_login; ?></span>
                </div>
                <a href="index.php?page=editor" class="btn btn-primary">
                    <i class="fas fa-code"></i>
                    <span>Ir al Editor</span>
                </a>
            </div>
        </header>
        
        <!-- Estadísticas rápidas -->
        <section class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="stat-info">
                    <h3>Lecciones Completadas</h3>
                    <div class="stat-value"><?php echo $completed_lessons; ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="stat-info">
                    <h3>Módulos Completados</h3>
                    <div class="stat-value"><?php echo $completed_modules; ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-award"></i>
                </div>
                <div class="stat-info">
                    <h3>Experiencia Total</h3>
                    <div class="stat-value"><?php echo $current_xp; ?> XP</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3>Tiempo de Estudio</h3>
                    <div class="stat-value"><?php echo $formatted_time; ?></div>
                </div>
            </div>
        </section>
        
        <!-- Contenido principal en dos columnas -->
        <div class="dashboard-main">
            <!-- Columna izquierda -->
            <div class="dashboard-column">
                <!-- Progreso del nivel -->
                <section class="dashboard-card level-progress-card">
                    <div class="card-header">
                        <h2>Mi Progreso</h2>
                    </div>
                    <div class="card-body">
                        <div class="level-info">
                            <div class="current-level">
                                <span class="level-label">Nivel Actual</span>
                                <span class="level-name"><?php echo htmlspecialchars($current_level); ?></span>
                            </div>
                            <div class="next-level">
                                <span class="level-label">Siguiente Nivel</span>
                                <span class="level-name"><?php echo htmlspecialchars($next_level); ?></span>
                            </div>
                        </div>
                        
                        <div class="level-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $level_progress; ?>%"></div>
                            </div>
                            <div class="progress-stats">
                                <span class="current-xp"><?php echo $current_xp; ?> XP</span>
                                <span class="next-level-xp"><?php echo $xp_for_next_level; ?> XP</span>
                            </div>
                        </div>
                        
                        <div class="xp-needed">
                            <span>Necesitas <?php echo $xp_for_next_level - $current_xp; ?> XP más para subir al siguiente nivel</span>
                        </div>
                    </div>
                </section>
                
                <!-- Próximas lecciones -->
                <section class="dashboard-card">
                    <div class="card-header">
                        <h2>Próximas Lecciones</h2>
                        <a href="index.php?page=modules" class="card-action">Ver Todas</a>
                    </div>
                    <div class="card-body">
                        <ul class="lessons-list">
                            <?php foreach ($next_lessons as $lesson): ?>
                                <li class="lesson-item">
                                    <div class="lesson-module-icon">
                                        <i class="<?php echo htmlspecialchars($lesson['module_icon']); ?>"></i>
                                    </div>
                                    <div class="lesson-info">
                                        <h3 class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></h3>
                                        <span class="lesson-module"><?php echo htmlspecialchars($lesson['module_title']); ?></span>
                                    </div>
                                    <a href="index.php?page=lessons&lesson_id=<?php echo $lesson['lesson_id']; ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-play"></i>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            
                            <?php if (empty($next_lessons)): ?>
                                <li class="lesson-item empty">
                                    <div class="empty-message">
                                        <i class="fas fa-check-circle"></i>
                                        <span>¡Has completado todas las lecciones disponibles!</span>
                                    </div>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </section>
                
                <!-- Proyectos recientes -->
                <section class="dashboard-card">
                    <div class="card-header">
                        <h2>Mis Proyectos Recientes</h2>
                        <a href="index.php?page=projects" class="card-action">Ver Todos</a>
                    </div>
                    <div class="card-body">
                        <div class="projects-grid">
                            <?php foreach ($recent_projects as $project): ?>
                                <div class="project-card">
                                    <div class="project-thumbnail">
                                        <?php if (!empty($project['thumbnail'])): ?>
                                            <img src="<?php echo htmlspecialchars($project['thumbnail']); ?>" alt="Miniatura del proyecto">
                                        <?php else: ?>
                                            <div class="no-thumbnail">
                                                <i class="fas fa-code"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="project-info">
                                        <h3 class="project-title"><?php echo htmlspecialchars($project['title']); ?></h3>
                                        <p class="project-description"><?php echo htmlspecialchars(substr($project['description'], 0, 60) . (strlen($project['description']) > 60 ? '...' : '')); ?></p>
                                        <span class="project-date">Actualizado: <?php echo date('d/m/Y', strtotime($project['last_modified'])); ?></span>
                                    </div>
                                    <div class="project-actions">
                                        <a href="index.php?page=editor&project_id=<?php echo $project['project_id']; ?>" class="btn btn-sm btn-outline">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <a href="index.php?page=preview&project_id=<?php echo $project['project_id']; ?>" class="btn btn-sm btn-outline">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (empty($recent_projects)): ?>
                                <div class="empty-projects">
                                    <div class="empty-message">
                                        <i class="fas fa-folder-open"></i>
                                        <p>No tienes proyectos todavía</p>
                                    </div>
                                    <a href="index.php?page=editor" class="btn btn-primary">Crear Proyecto</a>
                                </div>
                            <?php else: ?>
                                <div class="new-project-card">
                                    <a href="index.php?page=editor" class="new-project-link">
                                        <div class="new-project-icon">
                                            <i class="fas fa-plus"></i>
                                        </div>
                                        <span>Nuevo Proyecto</span>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </div>
            
            <!-- Columna derecha -->
            <div class="dashboard-column">
                <!-- Desafíos disponibles -->
                <section class="dashboard-card">
                    <div class="card-header">
                        <h2>Desafíos Disponibles</h2>
                        <a href="index.php?page=challenges" class="card-action">Ver Todos</a>
                    </div>
                    <div class="card-body">
                        <ul class="challenges-list">
                            <?php foreach ($available_challenges as $challenge): ?>
                                <li class="challenge-item">
                                    <div class="challenge-difficulty <?php echo $challenge['difficulty']; ?>">
                                        <span><?php echo ucfirst($challenge['difficulty']); ?></span>
                                    </div>
                                    <div class="challenge-info">
                                        <h3 class="challenge-title"><?php echo htmlspecialchars($challenge['title']); ?></h3>
                                        <span class="challenge-reward"><?php echo $challenge['xp_reward']; ?> XP</span>
                                    </div>
                                    <a href="index.php?page=challenges&challenge_id=<?php echo $challenge['challenge_id']; ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            
                            <?php if (empty($available_challenges)): ?>
                                <li class="challenge-item empty">
                                    <div class="empty-message">
                                        <i class="fas fa-medal"></i>
                                        <span>¡Has completado todos los desafíos disponibles!</span>
                                    </div>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </section>
                
                <!-- Logros -->
                <section class="dashboard-card">
                    <div class="card-header">
                        <h2>Mis Logros</h2>
                        <a href="index.php?page=achievements" class="card-action">Ver Todos</a>
                    </div>
                    <div class="card-body">
                        <ul class="achievements-list">
                            <?php foreach ($achievements as $achievement): ?>
                                <li class="achievement-item">
                                    <div class="achievement-icon">
                                        <i class="<?php echo htmlspecialchars($achievement['icon']); ?>"></i>
                                    </div>
                                    <div class="achievement-info">
                                        <h3 class="achievement-title"><?php echo htmlspecialchars($achievement['title']); ?></h3>
                                        <p class="achievement-description"><?php echo htmlspecialchars($achievement['description']); ?></p>
                                        <span class="achievement-date">Obtenido: <?php echo date('d/m/Y', strtotime($achievement['date_earned'])); ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                            
                            <?php if (empty($achievements)): ?>
                                <li class="achievement-item empty">
                                    <div class="empty-message">
                                        <i class="fas fa-trophy"></i>
                                        <span>¡Completa desafíos y lecciones para ganar logros!</span>
                                    </div>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </section>
                
                <!-- Consejos rápidos -->
                <section class="dashboard-card tips-card">
                    <div class="card-header">
                        <h2>Consejos Rápidos</h2>
                        <button class="refresh-tips">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="tip">
                            <div class="tip-icon">
                                <i class="fas fa-lightbulb"></i>
                            </div>
                            <div class="tip-content">
                                <p>Completa desafíos para ganar experiencia adicional y desbloquear logros especiales.</p>
                            </div>
                        </div>
                    </div>
                </section>
                
                <!-- Comunidad / Foro -->
                <section class="dashboard-card">
                    <div class="card-header">
                        <h2>Comunidad</h2>
                        <a href="index.php?page=forum" class="card-action">Ir al Foro</a>
                    </div>
                    <div class="card-body">
                        <div class="community-stats">
                            <div class="stat">
                                <div class="stat-value">2,345</div>
                                <div class="stat-label">Estudiantes</div>
                            </div>
                            <div class="stat">
                                <div class="stat-value">876</div>
                                <div class="stat-label">Temas</div>
                            </div>
                            <div class="stat">
                                <div class="stat-value">56</div>
                                <div class="stat-label">Activos hoy</div>
                            </div>
                        </div>
                        <a href="index.php?page=forum" class="btn btn-outline btn-block">Participar en el Foro</a>
                    </div>
                </section>
            </div>
        </div>
    </main>
</div>

<!-- Área de configuración (panel desplegable) -->
<div class="settings-panel" id="settingsPanel">
    <div class="settings-header">
        <h2>Configuración</h2>
        <button class="close-settings" id="closeSettings">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="settings-content">
        <section class="settings-section">
            <h3>Tema</h3>
            <div class="theme-selector">
                <label class="theme-option">
                    <input type="radio" name="theme" value="light" <?php echo ($user['theme_preference'] === 'light' ? 'checked' : ''); ?>>
                    <span class="theme-preview light-theme">
                        <i class="fas fa-sun"></i>
                        <span>Claro</span>
                    </span>
                </label>
                
                <label class="theme-option">
                    <input type="radio" name="theme" value="dark" <?php echo ($user['theme_preference'] === 'dark' ? 'checked' : ''); ?>>
                    <span class="theme-preview dark-theme">
                        <i class="fas fa-moon"></i>
                        <span>Oscuro</span>
                    </span>
                </label>
                
                <label class="theme-option">
                    <input type="radio" name="theme" value="system" <?php echo ($user['theme_preference'] === 'system' ? 'checked' : ''); ?>>
                    <span class="theme-preview system-theme">
                        <i class="fas fa-desktop"></i>
                        <span>Sistema</span>
                    </span>
                </label>
            </div>
        </section>
        
        <section class="settings-section">
            <h3>Accesibilidad</h3>
            <div class="accessibility-options">
                <div class="form-group">
                    <label for="fontSize">Tamaño de Texto</label>
                    <select id="fontSize" class="form-control">
                        <option value="normal">Normal</option>
                        <option value="large">Grande</option>
                        <option value="larger">Más Grande</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="contrast">Contraste</label>
                    <select id="contrast" class="form-control">
                        <option value="normal">Normal</option>
                        <option value="high">Alto Contraste</option>
                    </select>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="reduceMotion" class="form-check-input">
                    <label for="reduceMotion" class="form-check-label">Reducir Animaciones</label>
                </div>
            </div>
        </section>
        
        <section class="settings-section">
            <h3>Notificaciones</h3>
            <div class="notification-options">
                <div class="form-check">
                    <input type="checkbox" id="emailNotifications" class="form-check-input" checked>
                    <label for="emailNotifications" class="form-check-label">Notificaciones por Email</label>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="achievementNotifications" class="form-check-input" checked>
                    <label for="achievementNotifications" class="form-check-label">Notificaciones de Logros</label>
                </div>
            </div>
        </section>
    </div>
    
    <div class="settings-footer">
        <button class="btn btn-primary" id="saveSettings">Guardar Cambios</button>
        <button class="btn btn-outline" id="cancelSettings">Cancelar</button>
    </div>
</div>
