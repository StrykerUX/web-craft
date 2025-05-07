<?php
/**
 * Página principal de WebCraft Academy
 * 
 * Este archivo es el punto de entrada principal para la plataforma educativa.
 * Gestiona la carga de la interfaz principal y el enrutamiento básico.
 */

// Definir constante para permitir acceso a los archivos de configuración
define('WEBCRAFT', true);

// Incluir archivo de configuración
require_once 'config.php';

// Incluir funciones de autenticación
require_once 'includes/auth/auth.php';

// Iniciar o continuar sesión
session_name(SESSION_NAME);
session_start([
    'cookie_lifetime' => SESSION_LIFETIME,
    'cookie_path' => SESSION_PATH,
    'cookie_secure' => SESSION_SECURE,
    'cookie_httponly' => SESSION_HTTPONLY,
    'use_strict_mode' => true
]);

// Función para cargar componentes de página
function loadPageComponent($component) {
    $file = 'includes/components/' . $component . '.php';
    if (file_exists($file)) {
        include $file;
    } else {
        echo "<!-- Error: Componente '$component' no encontrado -->";
    }
}

// Determinar la página solicitada
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Lista de páginas válidas
$validPages = [
    'home', 'login', 'register', 'dashboard', 'profile', 
    'modules', 'lessons', 'challenges', 'editor', 'forum'
];

// Verificar si la página solicitada es válida
if (!in_array($page, $validPages)) {
    $page = 'home'; // Redirigir a home si la página no es válida
}

// Verificar autenticación para páginas protegidas
$protectedPages = ['dashboard', 'profile', 'modules', 'lessons', 'challenges', 'editor'];

if (in_array($page, $protectedPages) && !isset($_SESSION['user_id'])) {
    // Redirigir a login si intenta acceder a una página protegida sin autenticación
    header('Location: index.php?page=login&redirect=' . urlencode($page));
    exit;
}

// Cargar el contenido de la página
$pageFile = 'includes/pages/' . $page . '.php';
$pageExists = file_exists($pageFile);

// Obtener datos del usuario si está autenticado
$user = null;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = getDbConnection()->prepare("SELECT user_id, username, display_name, developer_level, experience_points, profile_image FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        // Manejar error (en producción, registrar en log en lugar de mostrar)
        if (DEV_MODE) {
            echo "<!-- Error de base de datos: " . $e->getMessage() . " -->";
        }
    }
}

// Título de la página
$pageTitle = ucfirst($page) . ' | ' . APP_NAME;
if ($page === 'home') {
    $pageTitle = APP_NAME . ' - Aprende desarrollo web de manera interactiva y divertida';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- Meta tags -->
    <meta name="description" content="WebCraft Academy - Plataforma educativa interactiva para aprender desarrollo web mediante un enfoque práctico y gamificado.">
    <meta name="keywords" content="desarrollo web, HTML, CSS, JavaScript, PHP, aprendizaje interactivo, codificación, programación">
    <meta name="author" content="WebCraft Academy">
    
    <!-- Favicon -->
    <link rel="icon" href="assets/images/favicon.ico">
    <link rel="apple-touch-icon" href="assets/images/apple-touch-icon.png">
    
    <!-- Bootstrap 5 CSS completo -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts - Inter y Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Hojas de estilo personalizadas -->
    <link rel="stylesheet" href="assets/css/main.css">
    
    <!-- Página específica CSS si existe -->
    <?php if (file_exists('assets/css/pages/' . $page . '.css')): ?>
    <link rel="stylesheet" href="assets/css/pages/<?php echo $page; ?>.css">
    <?php endif; ?>
    
    <!-- Detección de preferencias de tema del sistema -->
    <script>
        // Detectar preferencia de tema del usuario
        const prefersDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        // Obtener tema guardado o usar el detectado del sistema
        const savedTheme = localStorage.getItem('theme') || (prefersDarkMode ? 'dark' : 'light');
        
        // Aplicar tema al elemento HTML
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body class="page-<?php echo $page; ?>">
    <!-- Header principal -->
    <header class="main-header">
        <?php loadPageComponent('header'); ?>
    </header>
    
    <!-- Contenido principal -->
    <main class="main-content">
        <?php
        if ($pageExists) {
            include $pageFile;
        } else {
            // Página no encontrada
            echo '<div class="error-container">';
            echo '<h1>Página en construcción</h1>';
            echo '<p>Lo sentimos, esta sección aún está en desarrollo.</p>';
            echo '<a href="index.php" class="btn btn-primary">Volver al inicio</a>';
            echo '</div>';
        }
        ?>
    </main>
    
    <!-- Footer -->
    <footer class="main-footer">
        <?php loadPageComponent('footer'); ?>
    </footer>
    
    <!-- Bootstrap 5 JS Bundle con Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery y GSAP -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    
    <!-- Scripts personalizados -->
    <script src="assets/js/theme-switcher.js"></script>
    <script src="assets/js/main.js"></script>
    
    <!-- Página específica JS si existe -->
    <?php if (file_exists('assets/js/pages/' . $page . '.js')): ?>
    <script src="assets/js/pages/<?php echo $page; ?>.js"></script>
    <?php endif; ?>
</body>
</html>
