<?php
// Verificar que el script se ejecuta dentro del contexto adecuado
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}
?>
<nav class="main-nav">
    <div class="container">
        <div class="nav-wrapper">
            <!-- Logo -->
            <div class="logo">
                <a href="index.php">
                    <img src="assets/images/webcraft-logo.svg" alt="WebCraft Academy Logo" class="logo-img">
                    <span class="logo-text">WebCraft Academy</span>
                </a>
            </div>
            
            <!-- Menú Principal -->
            <ul class="nav-menu">
                <li class="nav-item <?php echo $page === 'home' ? 'active' : ''; ?>">
                    <a href="index.php" class="nav-link">Inicio</a>
                </li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Menú para usuarios autenticados -->
                    <li class="nav-item <?php echo $page === 'dashboard' ? 'active' : ''; ?>">
                        <a href="index.php?page=dashboard" class="nav-link">Dashboard</a>
                    </li>
                    <li class="nav-item <?php echo $page === 'modules' ? 'active' : ''; ?>">
                        <a href="index.php?page=modules" class="nav-link">Módulos</a>
                    </li>
                    <li class="nav-item <?php echo $page === 'challenges' ? 'active' : ''; ?>">
                        <a href="index.php?page=challenges" class="nav-link">Desafíos</a>
                    </li>
                    <li class="nav-item <?php echo $page === 'forum' ? 'active' : ''; ?>">
                        <a href="index.php?page=forum" class="nav-link">Foro</a>
                    </li>
                <?php else: ?>
                    <!-- Menú para visitantes -->
                    <li class="nav-item">
                        <a href="index.php?page=modules" class="nav-link">Módulos</a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <!-- Menú de Usuario / Autenticación -->
            <div class="user-menu">
                <?php if (isset($user) && $user): ?>
                    <!-- Usuario autenticado -->
                    <div class="user-profile dropdown">
                        <button class="dropdown-toggle">
                            <?php if (!empty($user['profile_image'])): ?>
                                <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Foto de perfil" class="user-avatar">
                            <?php else: ?>
                                <div class="user-avatar-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                            <span class="user-name"><?php echo htmlspecialchars($user['display_name'] ?? $user['username']); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="index.php?page=profile"><i class="fas fa-user-circle"></i> Perfil</a></li>
                            <li><a href="index.php?page=dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                            <li><a href="index.php?page=settings"><i class="fas fa-cog"></i> Ajustes</a></li>
                            <li class="dropdown-divider"></li>
                            <li><a href="includes/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                        </ul>
                    </div>
                    
                    <!-- Nivel y XP del usuario -->
                    <div class="user-level">
                        <div class="level-badge" title="<?php echo htmlspecialchars($user['developer_level']); ?>">
                            <i class="fas fa-code"></i>
                            <span class="level-text"><?php echo htmlspecialchars($user['developer_level']); ?></span>
                        </div>
                        <div class="xp-bar" title="<?php echo htmlspecialchars($user['experience_points']); ?> XP">
                            <div class="xp-progress" style="width: <?php echo min(100, ($user['experience_points'] % 1000) / 10); ?>%"></div>
                            <span class="xp-text"><?php echo htmlspecialchars($user['experience_points']); ?> XP</span>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Botones de Login/Registro -->
                    <div class="auth-buttons">
                        <a href="index.php?page=login" class="btn btn-outline <?php echo $page === 'login' ? 'active' : ''; ?>">Iniciar Sesión</a>
                        <a href="index.php?page=register" class="btn btn-primary <?php echo $page === 'register' ? 'active' : ''; ?>">Registrarse</a>
                    </div>
                <?php endif; ?>
                
                <!-- Cambio de tema -->
                <button id="theme-toggle" class="theme-toggle" aria-label="Cambiar tema">
                    <i class="fas fa-sun theme-icon-light"></i>
                    <i class="fas fa-moon theme-icon-dark"></i>
                </button>
            </div>
            
            <!-- Botón de menú móvil -->
            <button class="mobile-menu-toggle" aria-label="Menú">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
        </div>
    </div>
</nav>

<!-- Barra de progreso para módulo actual (solo visible en páginas de lecciones) -->
<?php if ($page === 'lessons' && isset($_GET['module_id'])): ?>
    <?php
    // Obtener información del módulo actual
    try {
        $moduleId = (int) $_GET['module_id'];
        $stmt = getDbConnection()->prepare("SELECT module_name FROM modules WHERE module_id = ?");
        $stmt->execute([$moduleId]);
        $moduleInfo = $stmt->fetch();
        
        // Si está autenticado, obtenemos progreso
        $moduleProgress = 0;
        if (isset($_SESSION['user_id'])) {
            $stmt = getDbConnection()->prepare("
                SELECT 
                    COUNT(CASE WHEN p.status = 'completed' THEN 1 ELSE NULL END) as completed,
                    COUNT(l.lesson_id) as total
                FROM 
                    lessons l
                LEFT JOIN 
                    user_progress p ON l.lesson_id = p.lesson_id AND p.user_id = ?
                WHERE 
                    l.module_id = ?
            ");
            $stmt->execute([$_SESSION['user_id'], $moduleId]);
            $progressData = $stmt->fetch();
            
            if ($progressData && $progressData['total'] > 0) {
                $moduleProgress = ($progressData['completed'] / $progressData['total']) * 100;
            }
        }
    } catch (PDOException $e) {
        $moduleInfo = null;
        $moduleProgress = 0;
        if (DEV_MODE) {
            echo "<!-- Error: " . $e->getMessage() . " -->";
        }
    }
    
    if ($moduleInfo):
    ?>
    <div class="module-progress-bar">
        <div class="container">
            <div class="module-info">
                <h3 class="module-name"><?php echo htmlspecialchars($moduleInfo['module_name']); ?></h3>
                <div class="progress-wrapper">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $moduleProgress; ?>%"></div>
                    </div>
                    <span class="progress-text"><?php echo round($moduleProgress); ?>% completado</span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php endif; ?>
