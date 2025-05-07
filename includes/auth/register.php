<?php
/**
 * Procesamiento de registro de usuarios para WebCraft Academy
 * 
 * Este archivo maneja el procesamiento del formulario de registro
 * y la creación de nuevos usuarios.
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

// Variables para el formulario
$error = '';
$success = '';
$username = '';
$email = '';
$full_name = '';

// Procesar formulario si es enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    // Obtener y sanitizar datos
    $username = htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $email = htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = htmlspecialchars($_POST['full_name'] ?? '', ENT_QUOTES, 'UTF-8');
    $terms = isset($_POST['terms']) && $_POST['terms'] == '1';
    
    // Verificar token CSRF si está habilitado
    if (isset($_POST['csrf_token'])) {
        if (!verifyCSRFToken($_POST['csrf_token'])) {
            $error = 'Error de seguridad. Por favor, intenta nuevamente.';
        }
    }
    
    // Verificar aceptación de términos
    if (empty($error) && !$terms) {
        $error = 'Debes aceptar los Términos y Condiciones y la Política de Privacidad.';
    }
    
    // Verificar que las contraseñas coincidan
    if (empty($error) && $password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden.';
    }
    
    // Continuar solo si no hay errores
    if (empty($error)) {
        // Registrar usuario
        $result = registerUser($username, $email, $password, $full_name);
        
        if ($result['success']) {
            // Registro exitoso
            // Iniciar sesión automáticamente
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['username'] = $result['username'];
            $_SESSION['developer_level'] = $result['developer_level'];
            
            // Redirigir según corresponda
            $redirect = filter_input(INPUT_GET, 'redirect', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $allowedRedirects = ['dashboard', 'profile', 'modules', 'lessons', 'challenges', 'editor'];
            
            if (!empty($redirect) && in_array($redirect, $allowedRedirects)) {
                header('Location: index.php?page=' . $redirect);
            } else {
                header('Location: index.php?page=dashboard');
            }
            exit;
        } else {
            // Error en el registro
            $error = $result['message'];
        }
    }
}

// Limpia el buffer y permite que el script continúe
ob_end_clean();
