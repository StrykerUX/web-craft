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

// Variables para el formulario
$error = '';
$success = '';
$username = '';
$email = '';
$display_name = '';

// Procesar formulario de registro si es enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    // Obtener y sanitizar datos
    $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validaciones básicas
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor, ingresa un correo electrónico válido.';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $error = 'El nombre de usuario debe tener entre 3 y 50 caracteres.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'El nombre de usuario solo puede contener letras, números y guiones bajos.';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        try {
            $pdo = getDbConnection();
            
            // Verificar si el usuario o email ya existen
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetchColumn() > 0) {
                // Verificar cuál existe para un mensaje más específico
                $stmt = $pdo->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                $existing = $stmt->fetch();
                
                if ($existing['username'] === $username) {
                    $error = 'El nombre de usuario ya está en uso. Por favor, elige otro.';
                } else {
                    $error = 'El correo electrónico ya está registrado. ¿Olvidaste tu contraseña?';
                }
            } else {
                // Crear el hash de la contraseña
                $password_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
                
                // Preparar display_name si está vacío
                if (empty($display_name)) {
                    $display_name = $username;
                }
                
                // Insertar nuevo usuario
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, email, password_hash, display_name, registration_date)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                
                if ($stmt->execute([$username, $email, $password_hash, $display_name])) {
                    // Registro exitoso, obtener el ID del usuario
                    $user_id = $pdo->lastInsertId();
                    
                    // Crear preferencias por defecto
                    $stmt = $pdo->prepare("
                        INSERT INTO user_preferences (user_id, theme_preference, difficulty_preference)
                        VALUES (?, 'system', 'beginner')
                    ");
                    $stmt->execute([$user_id]);
                    
                    // Registrar logro "Primer Paso"
                    $stmt = $pdo->prepare("
                        INSERT INTO user_achievements (user_id, achievement_id)
                        SELECT ?, achievement_id FROM achievements WHERE achievement_name = 'Primer Paso'
                    ");
                    $stmt->execute([$user_id]);
                    
                    // Iniciar sesión automáticamente
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['developer_level'] = 'Principiante';
                    
                    // Redirigir a onboarding o dashboard según corresponda
                    $redirect = filter_input(INPUT_GET, 'redirect', FILTER_SANITIZE_STRING);
                    $allowedRedirects = ['dashboard', 'profile', 'modules', 'lessons', 'challenges', 'editor'];
                    
                    if (!empty($redirect) && in_array($redirect, $allowedRedirects)) {
                        header('Location: index.php?page=' . $redirect);
                    } else {
                        header('Location: index.php?page=dashboard');
                    }
                    exit;
                } else {
                    $error = 'Error al crear la cuenta. Por favor, intenta nuevamente.';
                }
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

<div class="register-container">
    <div class="auth-form-wrapper">
        <div class="auth-form">
            <div class="auth-header">
                <h1>Crear Cuenta</h1>
                <p>Únete a WebCraft Academy y comienza tu camino como desarrollador web</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="index.php?page=register<?php echo isset($_GET['redirect']) ? '&redirect=' . htmlspecialchars($_GET['redirect']) : ''; ?>" class="register-form">
                <div class="form-group">
                    <label for="username">Nombre de Usuario <span class="required">*</span></label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required autocomplete="username" pattern="[a-zA-Z0-9_]+" minlength="3" maxlength="50">
                    </div>
                    <small class="form-text">Solo letras, números y guiones bajos. Entre 3 y 50 caracteres.</small>
                </div>
                
                <div class="form-group">
                    <label for="email">Correo Electrónico <span class="required">*</span></label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required autocomplete="email">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="display_name">Nombre para Mostrar</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-id-card"></i>
                        <input type="text" id="display_name" name="display_name" value="<?php echo htmlspecialchars($display_name); ?>" autocomplete="name">
                    </div>
                    <small class="form-text">Opcional. Si lo dejas vacío, se usará tu nombre de usuario.</small>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña <span class="required">*</span></label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required autocomplete="new-password" minlength="8">
                        <button type="button" class="password-toggle" aria-label="Ver contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small class="form-text">Mínimo 8 caracteres. Se recomienda incluir letras mayúsculas, minúsculas, números y símbolos.</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña <span class="required">*</span></label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" minlength="8">
                        <button type="button" class="password-toggle" aria-label="Ver contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <div class="terms-agree">
                        <input type="checkbox" id="terms" name="terms" value="1" required>
                        <label for="terms">Acepto los <a href="index.php?page=terms" target="_blank">Términos y Condiciones</a> y la <a href="index.php?page=privacy" target="_blank">Política de Privacidad</a>.</label>
                    </div>
                </div>
                
                <div class="form-submit">
                    <button type="submit" name="register" class="btn btn-primary btn-block">Crear Cuenta</button>
                </div>
            </form>
            
            <div class="auth-separator">
                <span>O</span>
            </div>
            
            <div class="social-auth">
                <button type="button" class="btn btn-social btn-github">
                    <i class="fab fa-github"></i> Registrarse con GitHub
                </button>
                <button type="button" class="btn btn-social btn-google">
                    <i class="fab fa-google"></i> Registrarse con Google
                </button>
            </div>
            
            <div class="auth-footer">
                ¿Ya tienes una cuenta? <a href="index.php?page=login<?php echo isset($_GET['redirect']) ? '&redirect=' . htmlspecialchars($_GET['redirect']) : ''; ?>">Iniciar Sesión</a>
            </div>
        </div>
        
        <div class="auth-info">
            <div class="auth-info-content">
                <h2>¡Únete a WebCraft Academy!</h2>
                <p>Al registrarte, obtendrás acceso a todos nuestros módulos y herramientas para convertirte en un desarrollador web profesional.</p>
                
                <div class="auth-benefits">
                    <div class="auth-benefit">
                        <i class="fas fa-check-circle"></i>
                        <span>Acceso a todos los módulos de aprendizaje</span>
                    </div>
                    <div class="auth-benefit">
                        <i class="fas fa-check-circle"></i>
                        <span>Editor de código interactivo en tiempo real</span>
                    </div>
                    <div class="auth-benefit">
                        <i class="fas fa-check-circle"></i>
                        <span>Seguimiento de progreso personalizado</span>
                    </div>
                    <div class="auth-benefit">
                        <i class="fas fa-check-circle"></i>
                        <span>Desafíos y proyectos prácticos</span>
                    </div>
                    <div class="auth-benefit">
                        <i class="fas fa-check-circle"></i>
                        <span>Comunidad de estudiantes y mentores</span>
                    </div>
                    <div class="auth-benefit">
                        <i class="fas fa-check-circle"></i>
                        <span>Sistema de gamificación para motivarte</span>
                    </div>
                </div>
                
                <div class="auth-testimonial">
                    <div class="testimonial-content">
                        <p>"WebCraft transformó mi aprendizaje. En 3 meses pasé de no saber nada a construir mi primer sitio web profesional."</p>
                    </div>
                    <div class="testimonial-author">
                        <img src="assets/images/testimonials/user2.jpg" alt="Carlos Rodríguez" class="author-avatar">
                        <div class="author-info">
                            <h4>Carlos Rodríguez</h4>
                            <p>Desarrollador Web</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
