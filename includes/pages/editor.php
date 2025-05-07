<?php
/**
 * Editor interactivo de WebCraft Academy
 * 
 * Este archivo implementa el editor de código interactivo con CodeMirror,
 * vista previa en tiempo real y sistema de guardado de proyectos.
 */

// Verificar que el acceso sea a través del punto de entrada correcto
if (!defined('WEBCRAFT')) {
    header('Location: ../../index.php');
    exit;
}

// Obtener proyecto si se solicita editar uno existente
$projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
$projectData = null;

if ($projectId > 0 && isset($_SESSION['user_id'])) {
    try {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT * FROM projects 
            WHERE project_id = ? AND user_id = ?
        ");
        $stmt->execute([$projectId, $_SESSION['user_id']]);
        $projectData = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Manejar error de base de datos
        if (DEV_MODE) {
            echo "<!-- Error: " . $e->getMessage() . " -->";
        }
    }
}

// Definir contenido inicial si es un nuevo proyecto
$htmlContent = $projectData ? $projectData['html_content'] : '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Proyecto</title>
</head>
<body>
    <h1>Mi Primer Proyecto en WebCraft</h1>
    <p>¡Hola Mundo!</p>
</body>
</html>';

$cssContent = $projectData ? $projectData['css_content'] : 'body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 20px;
    background-color: #f5f5f5;
}

h1 {
    color: #333;
    border-bottom: 2px solid #ddd;
    padding-bottom: 10px;
}

p {
    color: #666;
}';

$jsContent = $projectData ? $projectData['js_content'] : '// JavaScript code goes here
document.addEventListener("DOMContentLoaded", function() {
    console.log("¡El documento está listo!");
});';

// Título del proyecto (para nuevo o existente)
$projectTitle = $projectData ? $projectData['title'] : 'Nuevo Proyecto';
$projectDescription = $projectData ? $projectData['description'] : '';
$isPublic = $projectData ? $projectData['is_public'] : 0;
?>

<div class="editor-container">
    <!-- Barra de herramientas superior -->
    <div class="editor-toolbar">
        <div class="project-info">
            <input type="text" id="project-title" placeholder="Título del proyecto" value="<?php echo htmlspecialchars($projectTitle); ?>" class="project-title-input">
            <textarea id="project-description" placeholder="Descripción (opcional)" class="project-description-input"><?php echo htmlspecialchars($projectDescription); ?></textarea>
        </div>
        
        <div class="editor-actions">
            <button id="save-project" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
            <button id="run-project" class="btn btn-success"><i class="fas fa-play"></i> Ejecutar</button>
            <div class="project-visibility">
                <label for="project-public">
                    <input type="checkbox" id="project-public" <?php echo $isPublic ? 'checked' : ''; ?>>
                    Proyecto público
                </label>
            </div>
        </div>
    </div>
    
    <!-- Panel principal del editor -->
    <div class="editor-main">
        <!-- Panel de tabs para HTML, CSS y JS -->
        <div class="editor-tabs">
            <button class="tab-btn active" data-target="html">HTML</button>
            <button class="tab-btn" data-target="css">CSS</button>
            <button class="tab-btn" data-target="js">JavaScript</button>
            <button class="tab-btn" data-target="preview">Vista Previa</button>
        </div>
        
        <!-- Contenedores de editores CodeMirror -->
        <div class="editor-content">
            <div id="html-editor" class="code-editor active">
                <textarea id="html-code"><?php echo htmlspecialchars($htmlContent); ?></textarea>
            </div>
            
            <div id="css-editor" class="code-editor">
                <textarea id="css-code"><?php echo htmlspecialchars($cssContent); ?></textarea>
            </div>
            
            <div id="js-editor" class="code-editor">
                <textarea id="js-code"><?php echo htmlspecialchars($jsContent); ?></textarea>
            </div>
            
            <div id="preview-panel" class="code-editor">
                <iframe id="preview-iframe" sandbox="allow-scripts"></iframe>
            </div>
        </div>
    </div>
    
    <!-- Panel inferior con consola y herramientas -->
    <div class="editor-footer">
        <div class="console-panel">
            <div class="console-header">
                <span>Consola</span>
                <button id="clear-console" class="btn btn-small"><i class="fas fa-trash"></i> Limpiar</button>
            </div>
            <div id="console-output" class="console-output"></div>
        </div>
    </div>
</div>

<!-- Template para mensajes del sistema -->
<div id="system-message" class="system-message hidden"></div>

<!-- Modal de Guardar Proyecto -->
<div id="save-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Guardar Proyecto</h2>
        <form id="save-project-form">
            <input type="hidden" id="project-id" value="<?php echo $projectId; ?>">
            <div class="form-group">
                <label for="modal-project-title">Título del proyecto</label>
                <input type="text" id="modal-project-title" required>
            </div>
            <div class="form-group">
                <label for="modal-project-description">Descripción (opcional)</label>
                <textarea id="modal-project-description"></textarea>
            </div>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" id="modal-project-public">
                    Hacer este proyecto público
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar Proyecto</button>
                <button type="button" class="btn btn-secondary cancel-save">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts específicos para el editor -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/edit/closetag.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/edit/closebrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/lint/lint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/hint/show-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/hint/html-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/hint/css-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/hint/javascript-hint.min.js"></script>

<!-- Estilos específicos para CodeMirror -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/hint/show-hint.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/lint/lint.min.css">
