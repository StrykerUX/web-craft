<?php
// Verificar que el script se ejecuta dentro del contexto adecuado
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Incluir procesamiento de restablecimiento de contraseña
require_once 'includes/auth/reset-password.php';

// Generar token CSRF
$csrf_token = generateCSRFToken();

// Obtener variables del proceso
extract($reset_vars);
?>

<div class="reset-password-container">
    <div class="auth-form-wrapper auth-form-wrapper-compact">
        <div class="auth-form">
            <div class="auth-header">
                <?php if ($step === 'request'): ?>
                    <h1>Restablecer Contraseña</h1>
                    <p>Ingresa tu correo electrónico para recibir instrucciones</p>
                <?php elseif ($step === 'reset'): ?>
                    <h1>Nueva Contraseña</h1>
                    <p>Crea una nueva contraseña para tu cuenta</p>
                <?php elseif ($step === 'success'): ?>
                    <h1>¡Contraseña Restablecida!</h1>
                    <p>Tu contraseña ha sido actualizada exitosamente</p>
                <?php elseif ($step === 'invalid_token'): ?>
                    <h1>Enlace Inválido</h1>
                    <p>El enlace de restablecimiento es inválido o ha expirado</p>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($step === 'request'): ?>
                <!-- Formulario de solicitud de restablecimiento -->
                <form method="POST" action="index.php?page=reset-password" class="reset-form">
                    <!-- Token CSRF oculto -->
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required autocomplete="email">
                        </div>
                        <small class="form-text">Ingresa el correo electrónico asociado a tu cuenta.</small>
                    </div>
                    
                    <div class="form-submit">
                        <button type="submit" name="request_reset" class="btn btn-primary btn-block">Enviar Instrucciones</button>
                    </div>
                </form>
            <?php elseif ($step === 'reset' && $token_data): ?>
                <!-- Formulario de nueva contraseña -->
                <form method="POST" action="index.php?page=reset-password&step=reset&selector=<?php echo urlencode($_GET['selector']); ?>&validator=<?php echo urlencode($_GET['validator']); ?>" class="reset-form">
                    <!-- Token CSRF oculto -->
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="user_id" value="<?php echo $token_data['user_id']; ?>">
                    <input type="hidden" name="selector" value="<?php echo htmlspecialchars($_GET['selector']); ?>">
                    <input type="hidden" name="validator" value="<?php echo htmlspecialchars($_GET['validator']); ?>">
                    
                    <div class="form-info">
                        <div class="user-info">
                            <i class="fas fa-user-circle"></i>
                            <div>
                                <span class="username"><?php echo htmlspecialchars($token_data['username']); ?></span>
                                <span class="email"><?php echo htmlspecialchars($token_data['email']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Nueva Contraseña <span class="required">*</span></label>
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
                    
                    <div class="form-submit">
                        <button type="submit" name="reset_password" class="btn btn-primary btn-block">Cambiar Contraseña</button>
                    </div>
                </form>
            <?php elseif ($step === 'success'): ?>
                <!-- Mensaje de éxito -->
                <div class="success-action">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <a href="index.php?page=login" class="btn btn-primary">Iniciar Sesión</a>
                </div>
            <?php elseif ($step === 'invalid_token'): ?>
                <!-- Mensaje de token inválido -->
                <div class="error-action">
                    <div class="error-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <p>El enlace que has utilizado es inválido o ha expirado. Por favor, solicita un nuevo enlace de restablecimiento.</p>
                    <a href="index.php?page=reset-password" class="btn btn-primary">Solicitar Nuevo Enlace</a>
                </div>
            <?php endif; ?>
            
            <div class="auth-footer">
                <a href="index.php?page=login">Volver a Iniciar Sesión</a>
            </div>
        </div>
    </div>
</div>
