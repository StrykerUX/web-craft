<?php
/**
 * Página de registro para WebCraft Academy
 */

// Prevenir acceso directo a este archivo
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Incluir procesamiento de registro
require_once 'includes/auth/register.php';

// Redireccionar si ya está autenticado
if (isAuthenticated()) {
    header('Location: index.php?page=dashboard');
    exit;
}
?>

<div class="register-container">
    <div class="register-header">
        <h1>Crear Cuenta</h1>
        <p>Únete a WebCraft Academy y comienza tu viaje como desarrollador web</p>
    </div>
    
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success">
            <p><?php echo htmlspecialchars($successMessage); ?></p>
            <p><a href="index.php?page=login" class="btn-link">Iniciar sesión ahora</a></p>
        </div>
    <?php else: ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <!-- Campo oculto para protección CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <div class="form-group">
                <label for="username">Nombre de usuario</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    value="<?php echo htmlspecialchars($formData['username'] ?? ''); ?>" 
                    required 
                    autofocus
                    class="form-control"
                    minlength="3"
                    maxlength="50"
                    pattern="[a-zA-Z0-9_]+"
                    title="Solo letras, números y guiones bajos"
                >
                <small>3-50 caracteres, solo letras, números y guiones bajos.</small>
            </div>
            
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" 
                    required 
                    class="form-control"
                >
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="password-field">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        class="form-control"
                        minlength="8"
                    >
                    <button type="button" class="password-toggle" aria-label="Mostrar contraseña">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small>Mínimo 8 caracteres.</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmar contraseña</label>
                <div class="password-field">
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required 
                        class="form-control"
                        minlength="8"
                    >
                    <button type="button" class="password-toggle" aria-label="Mostrar contraseña">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="terms-checkbox">
                <input 
                    type="checkbox" 
                    id="accept_terms" 
                    name="accept_terms" 
                    required
                >
                <label for="accept_terms">
                    Acepto los <a href="index.php?page=terms" target="_blank">términos y condiciones</a> y la <a href="index.php?page=privacy" target="_blank">política de privacidad</a>
                </label>
            </div>
            
            <button type="submit" name="register_submit" class="register-btn">
                Crear Cuenta
            </button>
            
            <div class="login-link">
                <a href="index.php?page=login">¿Ya tienes cuenta? Inicia sesión</a>
            </div>
            
            <div class="separator">
                <span>o</span>
            </div>
            
            <div class="social-login">
                <a href="#" class="social-btn btn-google" disabled title="Próximamente">
                    <i class="fab fa-google"></i> Google
                </a>
                <a href="#" class="social-btn btn-github" disabled title="Próximamente">
                    <i class="fab fa-github"></i> GitHub
                </a>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
    // Script para mostrar/ocultar contraseña
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButtons = document.querySelectorAll('.password-toggle');
        
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
        
        // Validar que las contraseñas coinciden
        const passwordForm = document.querySelector('form');
        
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(e) {
                const password = document.getElementById('password');
                const confirmPassword = document.getElementById('confirm_password');
                
                if (password.value !== confirmPassword.value) {
                    e.preventDefault();
                    alert('Las contraseñas no coinciden');
                    confirmPassword.focus();
                }
            });
        }
        
        // Deshabilitar botones sociales
        const socialButtons = document.querySelectorAll('.social-btn[disabled]');
        socialButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                alert('Inicio de sesión social próximamente');
            });
        });
    });
</script>
