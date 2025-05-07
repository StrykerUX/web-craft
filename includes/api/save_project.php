<?php
/**
 * API para guardar proyectos
 * 
 * Este archivo procesa las solicitudes AJAX para guardar proyectos de usuario.
 */

// Definir esta constante para permitir la inclusión en config.php
define('WEBCRAFT', true);

// Incluir archivos necesarios
require_once '../../config.php';

// Iniciar o continuar sesión
session_name(SESSION_NAME);
session_start([
    'cookie_lifetime' => SESSION_LIFETIME,
    'cookie_path' => SESSION_PATH,
    'cookie_secure' => SESSION_SECURE,
    'cookie_httponly' => SESSION_HTTPONLY,
    'use_strict_mode' => true
]);

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    // Responder con error si no está autenticado
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado. Por favor inicia sesión.'
    ]);
    exit;
}

// Verificar que sea una solicitud POST y tenga el Content-Type correcto
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido. Use POST.'
    ]);
    exit;
}

// Obtener el contenido JSON de la solicitud
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Verificar que se hayan recibido datos válidos
if (!$data || json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Datos JSON inválidos.'
    ]);
    exit;
}

// Validar campos requeridos
if (empty($data['title'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'El título del proyecto es obligatorio.'
    ]);
    exit;
}

try {
    $db = getDbConnection();
    
    // Determinar si es un proyecto nuevo o una actualización
    $isNewProject = empty($data['project_id']) || $data['project_id'] <= 0;
    
    if ($isNewProject) {
        // Insertar nuevo proyecto
        $stmt = $db->prepare("
            INSERT INTO projects 
            (user_id, title, description, html_content, css_content, js_content, is_public, creation_date, last_modified) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            $_SESSION['user_id'],
            $data['title'],
            $data['description'] ?? '',
            $data['html_content'] ?? '',
            $data['css_content'] ?? '',
            $data['js_content'] ?? '',
            $data['is_public'] ? 1 : 0
        ]);
        
        $projectId = $db->lastInsertId();
        
        // Responder con éxito
        echo json_encode([
            'success' => true,
            'message' => 'Proyecto creado correctamente',
            'project_id' => $projectId
        ]);
    } else {
        // Verificar que el proyecto pertenezca al usuario actual
        $stmt = $db->prepare("
            SELECT user_id FROM projects WHERE project_id = ?
        ");
        $stmt->execute([$data['project_id']]);
        $project = $stmt->fetch();
        
        if (!$project || $project['user_id'] != $_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permiso para modificar este proyecto.'
            ]);
            exit;
        }
        
        // Actualizar proyecto existente
        $stmt = $db->prepare("
            UPDATE projects 
            SET title = ?, description = ?, html_content = ?, css_content = ?, js_content = ?, 
                is_public = ?, last_modified = NOW() 
            WHERE project_id = ? AND user_id = ?
        ");
        
        $stmt->execute([
            $data['title'],
            $data['description'] ?? '',
            $data['html_content'] ?? '',
            $data['css_content'] ?? '',
            $data['js_content'] ?? '',
            $data['is_public'] ? 1 : 0,
            $data['project_id'],
            $_SESSION['user_id']
        ]);
        
        // Responder con éxito
        echo json_encode([
            'success' => true,
            'message' => 'Proyecto actualizado correctamente',
            'project_id' => $data['project_id']
        ]);
    }
} catch (PDOException $e) {
    // Manejar errores de base de datos
    http_response_code(500);
    
    // En producción, no exponer detalles del error
    $errorMessage = DEV_MODE ? $e->getMessage() : 'Error al guardar el proyecto. Inténtalo de nuevo más tarde.';
    
    echo json_encode([
        'success' => false,
        'message' => $errorMessage
    ]);
    
    // Registrar error en el sistema
    error_log('Error al guardar proyecto: ' . $e->getMessage());
}
