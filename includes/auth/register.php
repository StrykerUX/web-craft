<?php
/**
 * Procesamiento de registro para WebCraft Academy
 * 
 * Este archivo maneja el procesamiento del formulario de registro
 * y la creación de nuevas cuentas de usuario.
 */

// Prevenir acceso directo a este archivo
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Incluir archivos de autenticación si no están incluidos
require_once 'includes/auth/auth.php';

// Inicializar variables
$errors = [];
$successMessage = '';
$formData = [
    'username' => '',
    'email' => ''
];

// Procesar formulario enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_submit'])) {
    // Validar CSRF token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Error de seguridad. Por favor, recarga la página e intenta de nuevo.';
    } else {
        // Obtener y sanear datos del formulario
        $formData['username'] = trim($_POST['username'] ?? '');
        $formData['email'] = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Verificar aceptación de términos
        if (!isset($_POST['accept_terms']) || $_POST['accept_terms'] !== 'on') {
            $errors[] = 'Debes aceptar los términos y condiciones para registrarte.';
        }
        
        // Intentar registro si no hay errores
        if (empty($errors)) {
            $registerResult = registerUser(
                $formData['username'], 
                $formData['email'], 
                $password, 
                $confirmPassword
            );
            
            if ($registerResult['success']) {
                $successMessage = $registerResult['message'];
                // Limpiar datos del formulario después de un registro exitoso
                $formData = [
                    'username' => '',
                    'email' => ''
                ];
            } else {
                $errors[] = $registerResult['message'];
            }
        }
    }
}
