<?php
/**
 * Procesamiento de restablecimiento de contraseña para WebCraft Academy
 * 
 * Este archivo maneja la solicitud y procesamiento de restablecimiento de contraseña.
 * 
 * @package WebCraft
 * @subpackage Authentication
 */

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

// Variables para control del proceso
$step = isset($_GET['step']) ? $_GET['step'] : 'request';
$error = '';
$success = '';
$email = '';
$token_data = null;

// Verificar si se está procesando un token
if ($step === 'reset' && isset($_GET['selector']) && isset($_GET['validator'])) {
    $selector = filter_input(INPUT_GET, 'selector', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $validator = filter_input(INPUT_GET, 'validator', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    $token_result = verifyPasswordResetToken($selector, $validator);
    
    if ($token_result['success']) {
        $token_data = $token_result;
    } else {
        $error = $token_result['message'];
        $step = 'invalid_token';
    }
}

// Procesar solicitud de restablecimiento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_reset'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    // Verificar token CSRF si está habilitado
    if (isset($_POST['csrf_token'])) {
        if (!verifyCSRFToken($_POST['csrf_token'])) {
            $error = 'Error de seguridad. Por favor, intenta nuevamente.';
        }
    }
    
    // Continuar solo si no hay errores
    if (empty($error)) {
        if (empty($email)) {
            $error = 'Por favor, ingresa tu correo electrónico.';
        } else {
            // Generar token de restablecimiento
            $result = generatePasswordResetToken($email);
            
            if ($result['success']) {
                // Aquí normalmente enviaríamos un email con el link de reseteo
                // Para propósitos de desarrollo, mostramos el link en pantalla
                if (DEV_MODE) {
                    $success = 'Se ha generado un enlace de restablecimiento. <br><strong>Enlace de desarrollo (solo visible en modo desarrollo):</strong> <a href="' . $result['reset_url'] . '">' . $result['reset_url'] . '</a>';
                } else {
                    $success = 'Si tu correo electrónico está registrado, recibirás un enlace para restablecer tu contraseña. Por favor, revisa tu bandeja de entrada.';
                    
                    // Código para enviar email (implementar función sendEmail)
                    // sendEmail($result['email'], 'Restablece tu contraseña - WebCraft Academy', 'email_templates/reset_password.php', $result);
                }
                
                // No mostrar error incluso si el email no existe, por seguridad
                $email = '';
            } else {
                // En producción, por seguridad no indicamos si el email existe o no
                if (DEV_MODE && isset($result['dev_message'])) {
                    $error = $result['message'] . ' - ' . $result['dev_message'];
                } else {
                    $success = 'Si tu correo electrónico está registrado, recibirás un enlace para restablecer tu contraseña. Por favor, revisa tu bandeja de entrada.';
                    $email = '';
                }
            }
        }
    }
}

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $selector = filter_input(INPUT_POST, 'selector', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $validator = filter_input(INPUT_POST, 'validator', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Verificar token CSRF si está habilitado
    if (isset($_POST['csrf_token'])) {
        if (!verifyCSRFToken($_POST['csrf_token'])) {
            $error = 'Error de seguridad. Por favor, intenta nuevamente.';
        }
    }
    
    // Continuar solo si no hay errores
    if (empty($error)) {
        // Verificar token nuevamente
        $token_result = verifyPasswordResetToken($selector, $validator);
        
        if (!$token_result['success']) {
            $error = 'El enlace de restablecimiento ha expirado o es inválido. Por favor, solicita un nuevo enlace.';
            $step = 'invalid_token';
        } else if ($token_result['user_id'] != $user_id) {
            $error = 'Error de validación. Por favor, solicita un nuevo enlace de restablecimiento.';
            $step = 'invalid_token';
        } else if (empty($password)) {
            $error = 'Por favor, ingresa una nueva contraseña.';
        } else if ($password !== $confirm_password) {
            $error = 'Las contraseñas no coinciden.';
        } else if (strlen($password) < 8) {
            $error = 'La contraseña debe tener al menos 8 caracteres.';
        } else {
            // Cambiar contraseña
            $result = changeUserPassword($user_id, $password);
            
            if ($result['success']) {
                $success = 'Tu contraseña ha sido restablecida exitosamente. Ahora puedes iniciar sesión con tu nueva contraseña.';
                $step = 'success';
            } else {
                $error = $result['message'];
                if (DEV_MODE && isset($result['dev_message'])) {
                    $error .= ' - ' . $result['dev_message'];
                }
            }
        }
    }
}

// Exportar variables para que la vista pueda utilizarlas
$reset_vars = [
    'step' => $step,
    'error' => $error,
    'success' => $success,
    'email' => $email,
    'token_data' => $token_data
];

// Limpia el buffer y permite que el script continúe
ob_end_clean();
