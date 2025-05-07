<?php
/**
 * Eliminar cuenta de usuario vía AJAX
 * 
 * Este archivo procesa las solicitudes AJAX para eliminar la cuenta
 * del usuario actual.
 */

// Definir constante para permitir acceso a los archivos de configuración
define('WEBCRAFT', true);

// Incluir archivos necesarios
require_once '../../config.php';
require_once '../auth/auth.php';

// Iniciar o continuar sesión
session_name(SESSION_NAME);
session_start([
    'cookie_lifetime' => SESSION_LIFETIME,
    'cookie_path' => SESSION_PATH,
    'cookie_secure' => SESSION_SECURE,
    'cookie_httponly' => SESSION_HTTPONLY,
    'use_strict_mode' => true
]);

// Verificar si el usuario está autenticado
if (!isUserLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado. Debes iniciar sesión para realizar esta acción.'
    ]);
    exit;
}

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido. Utiliza POST.'
    ]);
    exit;
}

// Verificar token CSRF
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Error de seguridad. Token inválido o expirado.'
    ]);
    exit;
}

try {
    $db = getDbConnection();
    $userId = $_SESSION['user_id'];
    
    // Iniciar transacción para garantizar que todo se elimine correctamente
    $db->beginTransaction();
    
    // 1. Eliminar logros de usuario
    $stmt = $db->prepare("DELETE FROM user_achievements WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // 2. Eliminar tokens de usuario
    $stmt = $db->prepare("DELETE FROM user_tokens WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // 3. Eliminar tokens de restablecimiento de contraseña
    $stmt = $db->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // 4. Eliminar códigos de activación
    $stmt = $db->prepare("DELETE FROM activation_codes WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // 5. Eliminar intentos de inicio de sesión (si están asociados al usuario)
    $stmt = $db->prepare("DELETE FROM login_attempts WHERE username_or_email = ?");
    $stmt->execute([$_SESSION['username']]);
    
    // 6. Eliminar comentarios en proyectos
    $stmt = $db->prepare("DELETE FROM project_comments WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // 7. Eliminar respuestas en el foro
    $stmt = $db->prepare("DELETE FROM forum_replies WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // 8. Eliminar temas del foro (primero debemos eliminar las respuestas asociadas)
    $stmt = $db->prepare("SELECT topic_id FROM forum_topics WHERE user_id = ?");
    $stmt->execute([$userId]);
    $topics = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($topics)) {
        $placeholders = implode(',', array_fill(0, count($topics), '?'));
        $stmt = $db->prepare("DELETE FROM forum_replies WHERE topic_id IN ($placeholders)");
        $stmt->execute($topics);
        
        $stmt = $db->prepare("DELETE FROM forum_topics WHERE user_id = ?");
        $stmt->execute([$userId]);
    }
    
    // 9. Eliminar intentos de desafíos
    $stmt = $db->prepare("DELETE FROM challenge_attempts WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // 10. Eliminar proyectos
    $stmt = $db->prepare("DELETE FROM projects WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // 11. Eliminar progreso
    $stmt = $db->prepare("DELETE FROM progress WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // 12. Eliminar datos del tablero de líderes
    $stmt = $db->prepare("DELETE FROM leaderboard WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // 13. Eliminar perfil de usuario
    $stmt = $db->prepare("DELETE FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // 14. Finalmente, eliminar el usuario
    $stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // Confirmar todos los cambios
    $db->commit();
    
    // Eliminar la sesión
    logoutUser(true);
    
    // Responder con éxito
    echo json_encode([
        'success' => true,
        'message' => 'Tu cuenta ha sido eliminada exitosamente.'
    ]);
    
} catch (PDOException $e) {
    // Revertir cambios en caso de error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar la cuenta. Por favor, intenta nuevamente más tarde.',
        'dev_message' => DEV_MODE ? $e->getMessage() : null
    ]);
}
