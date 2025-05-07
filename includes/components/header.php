<?php
/**
 * Componente de encabezado para WebCraft Academy
 * 
 * Este componente contiene la barra de navegación y el menú principal.
 * Actualizado para funcionar con Bootstrap 5.
 */

// Prevenir acceso directo a este archivo
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Determinar si el usuario está autenticado
$isAuthenticated = isset($_SESSION['user_id']);
$currentUser = $isAuthenticated && function_exists('getCurrentUser') ? getCurrentUser() : null;
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'home';
?>

<div class="container">
    <nav class="navbar navbar-expand-md">
        <a href="index.php" class="navbar-brand">
            <img src="assets/images/logo.svg" alt="WebCraft Academy" class="logo-img">
        </a>
        
        <button class="navbar-toggler menu-toggle" type="button" id="menu-toggle" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Mostrar/ocultar navegación">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="collapse navbar-collapse" id="navbar-nav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a href="index.php" class="nav-link <?php echo $currentPage === 'home' ? 'active' : ''; ?>">
                        Inicio
                    </a>
                </li>
                
                <?php if ($isAuthenticated): ?>
                    <li class="nav-item">
                        <a href="index.php?page=dashboard" class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?page=modules" class="nav-link <?php echo $currentPage === 'modules' ? 'active' : ''; ?>">
                            Módulos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?page=projects" class="nav-link <?php echo $currentPage === 'projects' ? 'active' : ''; ?>">
                            Proyectos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?page=forum" class="nav-link <?php echo $currentPage === 'forum' ? 'active' : ''; ?>">
                            Foro
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="index.php?page=about" class="nav-link <?php echo $currentPage === 'about' ? 'active' : ''; ?>">
                            Acerca de
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?page=features" class="nav-link <?php echo $currentPage === 'features' ? 'active' : ''; ?>">
                            Características
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?page=pricing" class="nav-link <?php echo $currentPage === 'pricing' ? 'active' : ''; ?>">
                            Planes
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <div class="navbar-actions d-flex align-items-center">
                <!-- Botón de tema -->
                <button class="theme-toggle" id="theme-toggle" aria-label="Cambiar tema">
                    <i class="fas fa-moon"></i>
                </button>
                
                <?php if ($isAuthenticated): ?>
                    <!-- Usuario autenticado -->
                    <div class="dropdown ms-3">
                        <button class="dropdown-toggle user-menu-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php if (!empty($currentUser['profile_image'])): ?>
                                <img src="<?php echo htmlspecialchars($currentUser['profile_image']); ?>" alt="Avatar" class="user-avatar">
                            <?php else: ?>
                                <div class="user-avatar-placeholder">
                                    <?php echo isset($currentUser['username']) ? strtoupper(substr($currentUser['username'], 0, 1)) : 'U'; ?>
                                </div>
                            <?php endif; ?>
                        </button>
                        
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <div class="dropdown-item disabled">
                                    <div class="dropdown-user-name">
                                        <?php echo htmlspecialchars($currentUser['display_name'] ?? $currentUser['username'] ?? 'Usuario'); ?>
                                    </div>
                                    <div class="dropdown-user-level">
                                        <?php echo htmlspecialchars($currentUser['developer_level'] ?? 'Principiante'); ?>
                                    </div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a href="index.php?page=profile" class="dropdown-item">
                                    <i class="fas fa-user"></i> Mi perfil
                                </a>
                            </li>
                            <li>
                                <a href="index.php?page=dashboard" class="dropdown-item">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="index.php?page=settings" class="dropdown-item">
                                    <i class="fas fa-cog"></i> Configuración
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a href="includes/auth/logout.php" class="dropdown-item logout-item">
                                    <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Usuario no autenticado -->
                    <div class="auth-buttons ms-3">
                        <a href="index.php?page=login" class="btn btn-outline-primary me-2">Iniciar sesión</a>
                        <a href="index.php?page=register" class="btn btn-primary">Registrarse</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</div>
