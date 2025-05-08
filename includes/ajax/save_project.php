<?php
/**
 * WebCraft Academy - Endpoint AJAX para guardar proyectos
 * 
 * Este archivo maneja las solicitudes para guardar proyectos de los usuarios
 * en la base de datos, tanto para crear nuevos proyectos como para actualizar existentes.
 */

// Definir constante para permitir acceso a los archivos de configuración
define('WEBCRAFT', true);

// Incluir archivo de configuración global
require_once '../../config.php';

// Verificar autenticación
require_once '../auth/auth.php';
if (!isUserLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

// Asegurarse de que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos JSON del cuerpo de la solicitud
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Verificar que los datos sean válidos
if (!$data || !isset($data['title'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    $db = getDbConnection();
    
    // Preparar los datos para la inserción/actualización
    $userId = $_SESSION['user_id'];
    $title = $data['title'];
    $description = isset($data['description']) ? $data['description'] : '';
    $htmlContent = isset($data['html_content']) ? $data['html_content'] : '';
    $cssContent = isset($data['css_content']) ? $data['css_content'] : '';
    $jsContent = isset($data['js_content']) ? $data['js_content'] : '';
    $isPublic = isset($data['is_public']) ? (bool)$data['is_public'] : false;
    $projectId = isset($data['project_id']) && $data['project_id'] > 0 ? (int)$data['project_id'] : null;
    
    if ($projectId) {
        // Actualizar proyecto existente
        // Primero verificar que el proyecto pertenezca al usuario
        $stmt = $db->prepare("SELECT user_id FROM projects WHERE project_id = ?");
        $stmt->execute([$projectId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project || $project['user_id'] != $userId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para modificar este proyecto']);
            exit;
        }
        
        // Actualizar el proyecto
        $stmt = $db->prepare("
            UPDATE projects SET 
                title = ?, 
                description = ?, 
                html_content = ?,
                css_content = ?,
                js_content = ?,
                is_public = ?,
                last_modified = CURRENT_TIMESTAMP
            WHERE project_id = ?
        ");
        
        $stmt->execute([
            $title,
            $description,
            $htmlContent,
            $cssContent,
            $jsContent,
            $isPublic ? 1 : 0,
            $projectId
        ]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Proyecto actualizado correctamente', 'project_id' => $projectId]);
    } else {
        // Crear nuevo proyecto
        $stmt = $db->prepare("
            INSERT INTO projects (
                user_id, title, description, html_content, css_content, js_content, 
                creation_date, last_modified, is_public
            ) VALUES (
                ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, ?
            )
        ");
        
        $stmt->execute([
            $userId,
            $title,
            $description,
            $htmlContent,
            $cssContent,
            $jsContent,
            $isPublic ? 1 : 0
        ]);
        
        $newProjectId = $db->lastInsertId();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Proyecto creado correctamente', 'project_id' => $newProjectId]);
    }
} catch (PDOException $e) {
    // Error de base de datos
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
}
