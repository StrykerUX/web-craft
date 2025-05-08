<?php
/**
 * API para guardar proyectos de usuarios
 * 
 * Este archivo procesa las solicitudes AJAX para guardar o actualizar
 * proyectos creados en el editor de código.
 */

// Definir constante para permitir acceso a los archivos de configuración
define('WEBCRAFT', true);

// Incluir archivo de configuración
require_once '../../config.php';

// Verificar autenticación
require_once '../auth/auth.php';
if (!isUserLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado. Debe iniciar sesión para guardar proyectos.'
    ]);
    exit;
}

// Verificar método de solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido. Use POST para esta solicitud.'
    ]);
    exit;
}

// Obtener datos de la solicitud
$requestData = json_decode(file_get_contents('php://input'), true);
if (!$requestData) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Datos de solicitud inválidos.'
    ]);
    exit;
}

// Validar datos requeridos
$requiredFields = ['title', 'html_content', 'css_content', 'js_content'];
foreach ($requiredFields as $field) {
    if (!isset($requestData[$field])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => "El campo '{$field}' es requerido."
        ]);
        exit;
    }
}

// Obtener datos del proyecto
$title = trim($requestData['title']);
$description = isset($requestData['description']) ? trim($requestData['description']) : '';
$htmlContent = $requestData['html_content'];
$cssContent = $requestData['css_content'];
$jsContent = $requestData['js_content'];
$isPublic = isset($requestData['is_public']) ? (bool)$requestData['is_public'] : false;
$projectId = isset($requestData['project_id']) ? (int)$requestData['project_id'] : null;

// Validar título
if (empty($title)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'El título del proyecto no puede estar vacío.'
    ]);
    exit;
}

try {
    $db = getDbConnection();
    
    // Verificar si es una actualización o una inserción
    if ($projectId) {
        // Verificar que el proyecto pertenezca al usuario actual
        $stmt = $db->prepare("SELECT user_id FROM projects WHERE project_id = ?");
        $stmt->execute([$projectId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project || $project['user_id'] != $_SESSION['user_id']) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'No tiene permiso para editar este proyecto.'
            ]);
            exit;
        }
        
        // Actualizar proyecto existente
        $stmt = $db->prepare("
            UPDATE projects 
            SET title = ?, description = ?, html_content = ?, css_content = ?, js_content = ?, 
                is_public = ?, last_modified = NOW() 
            WHERE project_id = ?
        ");
        
        $stmt->execute([
            $title, $description, $htmlContent, $cssContent, $jsContent, 
            $isPublic, $projectId
        ]);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Proyecto actualizado correctamente.',
            'project_id' => $projectId
        ]);
    } else {
        // Crear nuevo proyecto
        $stmt = $db->prepare("
            INSERT INTO projects (user_id, title, description, html_content, css_content, js_content, 
                is_public, creation_date, last_modified) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            $_SESSION['user_id'], $title, $description, $htmlContent, $cssContent, $jsContent, 
            $isPublic
        ]);
        
        $newProjectId = $db->lastInsertId();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Proyecto guardado correctamente.',
            'project_id' => $newProjectId
        ]);
    }
    
} catch (PDOException $e) {
    // Error en la base de datos
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => DEV_MODE ? 'Error de base de datos: ' . $e->getMessage() : 'Error al guardar el proyecto. Intente nuevamente más tarde.'
    ]);
    exit;
}
