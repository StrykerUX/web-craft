<?php
/**
 * Componente de encabezado para WebCraft Academy
 * 
 * Este componente contiene la barra de navegación y el menú principal.
 */

// Prevenir acceso directo a este archivo
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Determinar si el usuario está autenticado
$isAuthenticated = isAuthenticated();
$currentUser = $isAuthenticated ? getCurrentUser() : null;
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'home';
?>

<div class="container">
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">
            <i class="fas fa-code-branch"></i>
            <span>WebCraft Academy</span>
        </a>
        
        <button class="menu-toggle" id="menu-toggle" aria-label="Abrir menú">
            <i class="fas fa-bars"></i>
        </button>
        
        <ul class="navbar-nav" id="navbar-nav">
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
        
        <div class="navbar-actions">
            <button class="theme-toggle" id="theme-toggle" aria-label="Cambiar tema">
                <i class="fas fa-moon"></i>
            </button>
            
            <?php if ($isAuthenticated): ?>
                <div class="dropdown">
                    <button class="dropdown-toggle user-menu-toggle" id="userDropdown" aria-expanded="false">
                        <?php if (!empty($currentUser['profile_image'])): ?>
                            <img src="<?php echo htmlspecialchars($currentUser['profile_image']); ?>" alt="Avatar" class="user-avatar">
                        <?php else: ?>
                            <div class="user-avatar-placeholder">
                                <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </button>
                    
                    <div class="dropdown-menu" aria-labelledby="userDropdown" id="userDropdownMenu">
                        <div class="dropdown-user-info">
                            <div class="dropdown-user-name">
                                <?php echo htmlspecialchars($currentUser['display_name'] ?? $currentUser['username']); ?>
                            </div>
                            <div class="dropdown-user-level">
                                <?php echo htmlspecialchars($currentUser['developer_level']); ?>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="index.php?page=profile" class="dropdown-item">
                            <i class="fas fa-user"></i> Mi perfil
                        </a>
                        <a href="index.php?page=dashboard" class="dropdown-item">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="index.php?page=settings" class="dropdown-item">
                            <i class="fas fa-cog"></i> Configuración
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="includes/auth/logout.php?csrf_token=<?php echo generateCsrfToken(); ?>" class="dropdown-item logout-item">
                            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="index.php?page=login" class="btn btn-outline login-btn">Iniciar sesión</a>
                    <a href="index.php?page=register" class="btn btn-accent register-btn">Registrarse</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Menú móvil
    const menuToggle = document.getElementById('menu-toggle');
    const navbarNav = document.getElementById('navbar-nav');
    
    if (menuToggle && navbarNav) {
        menuToggle.addEventListener('click', function() {
            navbarNav.classList.toggle('active');
            
            // Cambiar ícono del botón
            const icon = menuToggle.querySelector('i');
            if (icon) {
                if (navbarNav.classList.contains('active')) {
                    icon.className = 'fas fa-times';
                    menuToggle.setAttribute('aria-label', 'Cerrar menú');
                } else {
                    icon.className = 'fas fa-bars';
                    menuToggle.setAttribute('aria-label', 'Abrir menú');
                }
            }
        });
    }
    
    // Dropdown de usuario
    const userDropdown = document.getElementById('userDropdown');
    const userDropdownMenu = document.getElementById('userDropdownMenu');
    
    if (userDropdown && userDropdownMenu) {
        userDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdownMenu.classList.toggle('show');
            userDropdown.setAttribute('aria-expanded', userDropdownMenu.classList.contains('show'));
        });
        
        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', function() {
            if (userDropdownMenu.classList.contains('show')) {
                userDropdownMenu.classList.remove('show');
                userDropdown.setAttribute('aria-expanded', 'false');
            }
        });
        
        // Evitar cierre al hacer clic dentro del dropdown
        userDropdownMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
});
</script>
