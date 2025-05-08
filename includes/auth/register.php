<?php
/**
 * Procesamiento de registro para WebCraft Academy
 * 
 * Este archivo maneja el procesamiento del formulario de registro
 * y la creación de nuevas cuentas de usuario.
 * 
 * @package WebCraft
 * @subpackage Authentication
 */

// Verificar que el script se ejecuta dentro del contexto adecuado
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Incluir utilidades de seguridad si no están incluidas aún
if (!function_exists('generateCSRFToken')) {
    require_once 'includes/utils/security.php';
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
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']) && $_POST['terms'] == '1';
    
    // Verificar token CSRF
    if (isset($_POST['csrf_token'])) {
        if (!validateCSRFToken($_POST['csrf_token'], 'register')) {
            $error = 'Error de seguridad. Por favor, intenta nuevamente.';
        }
    }
    
    // Continuar solo si no hay errores
    if (empty($error)) {
        // Validar datos básicos
        if (empty($username) || empty($email) || empty($password)) {
            $error = 'Por favor, completa todos los campos obligatorios.';
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Por favor, ingresa un correo electrónico válido.';
        } else if (strlen($username) < 3 || strlen($username) > 50) {
            $error = 'El nombre de usuario debe tener entre 3 y 50 caracteres.';
        } else if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $error = 'El nombre de usuario solo puede contener letras, números y guiones bajos.';
        } else if (strlen($password) < 8) {
            $error = 'La contraseña debe tener al menos 8 caracteres.';
        } else if ($password !== $confirm_password) {
            $error = 'Las contraseñas no coinciden. Por favor, inténtalo de nuevo.';
        } else if (!$terms) {
            $error = 'Debes aceptar los términos y condiciones.';
        } else {
            // Si el nombre completo está vacío, usar el nombre de usuario
            if (empty($full_name)) {
                $full_name = $username;
            }
            
            // Intentar registrar al usuario
            $result = registerUser($username, $email, $password, $full_name);
            
            if ($result['success']) {
                // Registro exitoso
                $success = $result['message'];
                
                // Limpiar campos para prevenir reenvío de formulario
                $username = '';
                $email = '';
                $full_name = '';
                
                // Redirigir a login si no hubo errores (usando JavaScript)
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'index.php?page=login" . (isset($_GET['redirect']) ? '&redirect=' . htmlspecialchars($_GET['redirect']) : '') . "';
                    }, 3000);
                </script>";
            } else {
                // Registro fallido
                $error = $result['message'];
            }
        }
    }
}
