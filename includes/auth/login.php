<?php
/**
 * Procesamiento de inicio de sesión para WebCraft Academy
 * 
 * Este archivo maneja el procesamiento del formulario de inicio de sesión
 * y la redirección después de un login exitoso.
 */

// Prevenir acceso directo a este archivo
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Incluir archivos de autenticación si no están incluidos
require_once 'includes/auth/auth.php';

// Inicializar variables
$errors = [];
$usernameOrEmail = '';
$remember = false;

// Procesar formulario enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    // Validar CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Error de seguridad. Por favor, recarga la página e intenta de nuevo.';
    } else {
        // Obtener y sanear datos del formulario
        $usernameOrEmail = trim($_POST['username_email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']) && $_POST['remember'] === 'on';
        
        // Intentar inicio de sesión
        $loginResult = loginUser($usernameOrEmail, $password, $remember);
        
        if ($loginResult['success']) {
            // Determinar página de redirección
            $redirect = isset($_GET['redirect']) && !empty($_GET['redirect']) 
                      ? $_GET['redirect'] 
                      : 'dashboard';
            
            // Redirigir al usuario
            header('Location: index.php?page=' . $redirect);
            exit;
        } else {
            $errors[] = $loginResult['message'];
        }
    }
}
