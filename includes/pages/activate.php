<?php
// Verificar que el script se ejecuta dentro del contexto adecuado
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Iniciar buffer de salida para prevenir errores de headers
ob_start();

// Verificar si ya está autenticado, redirigir a dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php?page=dashboard');
    exit;
}

// Variables para la página
$error = '';
$success = '';

// Verificar si se proporciona un código de activación
$activation_code = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if ($activation_code) {
    // Procesar la activación
    $result = activateUserAccount($activation_code);
    
    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}
?>

<div class="activate-container">
    <div class="auth-form-wrapper auth-form-wrapper-compact">
        <div class="auth-form">
            <div class="auth-header">
                <h1>Activación de Cuenta</h1>
                <p>Confirma tu correo electrónico para activar tu cuenta</p>
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
                
                <div class="success-action">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <a href="index.php?page=login" class="btn btn-primary">Iniciar Sesión</a>
                </div>
            <?php else: ?>
                <?php if (empty($activation_code)): ?>
                    <div class="activate-info">
                        <p>Para activar tu cuenta, haz clic en el enlace que hemos enviado a tu correo electrónico.</p>
                        <p>Si no has recibido el correo de activación, verifica tu carpeta de spam o solicita un nuevo código de activación.</p>
                    </div>
                    
                    <div class="form-submit">
                        <a href="index.php?page=resend-activation" class="btn btn-outline">Reenviar Código de Activación</a>
                    </div>
                <?php else: ?>
                    <div class="error-action">
                        <div class="error-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <p>El código de activación es inválido o ha expirado. Por favor, solicita un nuevo código de activación.</p>
                        <a href="index.php?page=resend-activation" class="btn btn-outline">Solicitar Nuevo Código</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="auth-footer">
                <a href="index.php?page=login">Volver a Iniciar Sesión</a>
            </div>
        </div>
    </div>
</div>

<?php
// Limpia el buffer y permite que el script continúe
ob_end_clean();
?>
