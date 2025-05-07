<?php
// Verificar que el script se ejecuta dentro del contexto adecuado
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Verificar si ya está autenticado, redirigir a dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php?page=dashboard');
    exit;
}

// Procesar formulario de login si es enviado
$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // Obtener y sanitizar datos
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'] ?? '';
    
    // Validar datos
    if (empty($username) || empty($password)) {
        $error = 'Por favor, ingresa tu usuario y contraseña.';
    } else {
        try {
            // Buscar usuario
            $stmt = getDbConnection()->prepare("SELECT user_id, username, password_hash, account_status, developer_level FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Verificar estado de la cuenta
                if ($user['account_status'] !== 'active') {
                    $error = 'Tu cuenta está ' . ($user['account_status'] === 'suspended' ? 'suspendida' : 'inactiva') . '. Por favor, contacta a soporte.';
                } else {
                    // Iniciar sesión
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['developer_level'] = $user['developer_level'];
                    
                    // Actualizar último login
                    $updateStmt = getDbConnection()->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
                    $updateStmt->execute([$user['user_id']]);
                    
                    // Redirigir según corresponda
                    $redirect = filter_input(INPUT_GET, 'redirect', FILTER_SANITIZE_STRING);
                    $allowedRedirects = ['dashboard', 'profile', 'modules', 'lessons', 'challenges', 'editor'];
                    
                    if (!empty($redirect) && in_array($redirect, $allowedRedirects)) {
                        header('Location: index.php?page=' . $redirect);
                    } else {
                        header('Location: index.php?page=dashboard');
                    }
                    exit;
                }
            } else {
                $error = 'Usuario o contraseña incorrectos. Por favor, intenta nuevamente.';
            }
        } catch (PDOException $e) {
            $error = 'Error al procesar la solicitud. Por favor, intenta nuevamente más tarde.';
            if (DEV_MODE) {
                // En desarrollo mostramos el error para depuración
                $error .= ' [' . $e->getMessage() . ']';
            }
        }
    }
}
?>

<div class="login-container">
    <div class="auth-form-wrapper">
        <div class="auth-form">
            <div class="auth-header">
                <h1>Iniciar Sesión</h1>
                <p>Accede a tu cuenta de WebCraft Academy</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="index.php?page=login<?php echo isset($_GET['redirect']) ? '&redirect=' . htmlspecialchars($_GET['redirect']) : ''; ?>" class="login-form">
                <div class="form-group">
                    <label for="username">Usuario o Correo Electrónico</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required autocomplete="username">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                        <button type="button" class="password-toggle" aria-label="Ver contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember" value="1">
                        <label for="remember">Recordarme</label>
                    </div>
                    <a href="index.php?page=reset-password" class="forgot-password">¿Olvidaste tu contraseña?</a>
                </div>
                
                <div class="form-submit">
                    <button type="submit" name="login" class="btn btn-primary btn-block">Iniciar Sesión</button>
                </div>
            </form>
            
            <div class="auth-separator">
                <span>O</span>
            </div>
            
            <div class="social-auth">
                <button type="button" class="btn btn-social btn-github">
                    <i class="fab fa-github"></i> Continuar con GitHub
                </button>
                <button type="button" class="btn btn-social btn-google">
                    <i class="fab fa-google"></i> Continuar con Google
                </button>
            </div>
            
            <div class="auth-footer">
                ¿No tienes una cuenta? <a href="index.php?page=register<?php echo isset($_GET['redirect']) ? '&redirect=' . htmlspecialchars($_GET['redirect']) : ''; ?>">Regístrate</a>
            </div>
        </div>
        
        <div class="auth-info">
            <div class="auth-info-content">
                <h2>¡Bienvenido a WebCraft Academy!</h2>
                <p>Una plataforma educativa interactiva donde aprenderás desarrollo web a través de proyectos reales y desafíos divertidos.</p>
                
                <div class="auth-features">
                    <div class="auth-feature">
                        <i class="fas fa-graduation-cap"></i>
                        <div>
                            <h3>Aprende Haciendo</h3>
                            <p>80% práctica, 20% teoría. Concepto probado para aprender más rápido.</p>
                        </div>
                    </div>
                    
                    <div class="auth-feature">
                        <i class="fas fa-code"></i>
                        <div>
                            <h3>Proyectos Reales</h3>
                            <p>Construye sitios y aplicaciones que puedes agregar a tu portafolio.</p>
                        </div>
                    </div>
                    
                    <div class="auth-feature">
                        <i class="fas fa-users"></i>
                        <div>
                            <h3>Comunidad Activa</h3>
                            <p>Comparte, colabora y aprende con otros estudiantes y mentores.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
