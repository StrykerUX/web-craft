<?php
/**
 * Procesamiento de inicio de sesión para WebCraft Academy
 * 
 * Este archivo maneja el procesamiento del formulario de inicio de sesión
 * y la redirección correspondiente.
 * 
 * @package WebCraft
 * @subpackage Authentication
 */

// Verificar que el script se ejecuta dentro del contexto adecuado
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Verificar si ya está autenticado, redirigir a dashboard
if (isset($_SESSION['user_id'])) {
    // Usamos JavaScript para redirigir en lugar de header()
    echo "<script>window.location.href = 'index.php?page=dashboard';</script>";
    return; // Terminamos el script pero no con exit para evitar problemas con el buffer
}

// Variables para el formulario
$error = '';
$username = '';

// Procesar formulario si es enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // Obtener y sanitizar datos
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) && $_POST['remember'] == '1';
    
    // Verificar token CSRF si está habilitado
    if (isset($_POST['csrf_token'])) {
        if (!verifyCSRFToken($_POST['csrf_token'])) {
            $error = 'Error de seguridad. Por favor, intenta nuevamente.';
        }
    }
    
    // Continuar solo si no hay errores
    if (empty($error)) {
        // Validar datos básicos
        if (empty($username) || empty($password)) {
            $error = 'Por favor, ingresa tu usuario y contraseña.';
        } else {
            // Verificar si la cuenta está bloqueada por demasiados intentos
            if (isAccountLocked($username)) {
                $error = 'Demasiados intentos de inicio de sesión. Por favor, intenta nuevamente más tarde.';
            } else {
                // Autenticar usuario
                $result = authenticateUser($username, $password);
                
                if ($result['success']) {
                    // Inicio de sesión exitoso
                    loginUser($result['user'], $remember);
                    
                    // Redirigir según corresponda usando JavaScript
                    $redirect = filter_input(INPUT_GET, 'redirect', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                    $allowedRedirects = ['dashboard', 'profile', 'modules', 'lessons', 'challenges', 'editor'];
                    
                    if (!empty($redirect) && in_array($redirect, $allowedRedirects)) {
                        echo "<script>window.location.href = 'index.php?page=" . $redirect . "';</script>";
                    } else {
                        echo "<script>window.location.href = 'index.php?page=dashboard';</script>";
                    }
                    return; // Terminamos el script pero no con exit
                } else {
                    // Inicio de sesión fallido
                    $error = $result['message'];
                    registerFailedLoginAttempt($username);
                }
            }
        }
    }
}
