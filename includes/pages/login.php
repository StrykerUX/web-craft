<?php
/**
 * Página de inicio de sesión para WebCraft Academy
 */

// Prevenir acceso directo a este archivo
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Incluir procesamiento de login
require_once 'includes/auth/login.php';

// Redireccionar si ya está autenticado
if (isAuthenticated()) {
    header('Location: index.php?page=dashboard');
    exit;
}
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Iniciar Sesión</h1>
            <p>Accede a tu cuenta para continuar aprendiendo</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form class="auth-form" method="POST" action="">
            <!-- Campo oculto para protección CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <div class="form-group">
                <label for="username_email">Usuario o Correo electrónico</label>
                <input 
                    type="text" 
                    id="username_email" 
                    name="username_email" 
                    value="<?php echo htmlspecialchars($usernameOrEmail); ?>" 
                    required 
                    autofocus
                    class="form-control"
                >
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="password-input-group">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        class="form-control"
                    >
                    <button type="button" class="toggle-password" aria-label="Mostrar contraseña">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-check">
                <input 
                    type="checkbox" 
                    id="remember" 
                    name="remember" 
                    class="form-check-input"
                    <?php echo $remember ? 'checked' : ''; ?>
                >
                <label class="form-check-label" for="remember">Recordarme</label>
            </div>
            
            <div class="auth-actions">
                <button type="submit" name="login_submit" class="btn btn-primary btn-block">
                    Iniciar Sesión
                </button>
            </div>
        </form>
        
        <div class="auth-links">
            <a href="index.php?page=register" class="btn-link">¿No tienes cuenta? Regístrate</a>
            <a href="index.php?page=forgot-password" class="btn-link">¿Olvidaste tu contraseña?</a>
        </div>
        
        <div class="auth-separator">
            <span>o</span>
        </div>
        
        <div class="social-login">
            <p>Iniciar sesión con:</p>
            <div class="social-buttons">
                <button class="btn btn-social btn-google" disabled title="Próximamente">
                    <i class="fab fa-google"></i> Google
                </button>
                <button class="btn btn-social btn-github" disabled title="Próximamente">
                    <i class="fab fa-github"></i> GitHub
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Script para mostrar/ocultar contraseña
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButtons = document.querySelectorAll('.toggle-password');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                
                // Cambiar tipo de input
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                    this.setAttribute('aria-label', 'Ocultar contraseña');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                    this.setAttribute('aria-label', 'Mostrar contraseña');
                }
            });
        });
    });
</script>
