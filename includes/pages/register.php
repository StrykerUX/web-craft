<?php
// Verificar que el script se ejecuta dentro del contexto adecuado
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Incluir procesamiento de registro
require_once 'includes/auth/register.php';

// Generar token CSRF
$csrf_token = generateCSRFToken();
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
                <!-- Token CSRF oculto -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
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
                    <label for="full_name">Nombre Completo</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-id-card"></i>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" autocomplete="name">
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
