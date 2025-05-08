<?php
/**
 * Editor de código interactivo de WebCraft Academy
 * 
 * Este archivo implementa el editor de código principal con CodeMirror
 * que permite a los usuarios escribir, previsualizar y guardar código HTML, CSS y JS.
 */

// Definir constante para permitir acceso a los archivos de configuración
define('WEBCRAFT', true);

// Incluir archivo de configuración global si no está ya incluido
if (!defined('BASE_PATH')) {
    require_once '../config.php';
}

// Verificar autenticación
require_once '../includes/auth/auth.php';
if (!isUserLoggedIn()) {
    // Redirigir a login
    header('Location: ../index.php?page=login&redirect=editor');
    exit;
}

// Obtener ID del proyecto si se está editando uno existente
$projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : null;
$projectData = null;

// Si hay un ID de proyecto, cargar sus datos
if ($projectId) {
    try {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM projects WHERE project_id = ? AND user_id = ?");
        $stmt->execute([$projectId, $_SESSION['user_id']]);
        $projectData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$projectData) {
            // Proyecto no encontrado o no pertenece al usuario
            header('Location: editor.php');
            exit;
        }
    } catch (PDOException $e) {
        // Error al cargar el proyecto
        $error = "Error al cargar el proyecto. Por favor intente nuevamente.";
    }
}

// Título de la página
$pageTitle = $projectData ? 'Editando: ' . htmlspecialchars($projectData['title']) : 'Nuevo Proyecto';

// Obtener modo del editor (lesson, challenge, free)
$editorMode = isset($_GET['mode']) ? $_GET['mode'] : 'free';
$lessonId = isset($_GET['lesson_id']) ? (int)$_GET['lesson_id'] : null;
$challengeId = isset($_GET['challenge_id']) ? (int)$_GET['challenge_id'] : null;

// Datos iniciales para los editores
$htmlContent = $projectData ? $projectData['html_content'] : '<!DOCTYPE html>\n<html lang="es">\n<head>\n    <meta charset="UTF-8">\n    <meta name="viewport" content="width=device-width, initial-scale=1.0">\n    <title>Mi Proyecto</title>\n</head>\n<body>\n    <h1>¡Hola Mundo!</h1>\n    <p>Escribe tu código HTML aquí...</p>\n</body>\n</html>';
$cssContent = $projectData ? $projectData['css_content'] : 'body {\n    font-family: Arial, sans-serif;\n    margin: 0;\n    padding: 20px;\n    line-height: 1.6;\n    color: #333;\n}\n\nh1 {\n    color: #0066cc;\n}';
$jsContent = $projectData ? $projectData['js_content'] : '// Tu código JavaScript aquí\nconsole.log("¡Bienvenido a WebCraft!");\n\n// Ejemplo: Cambiar el contenido de un elemento\n// document.querySelector("h1").textContent = "¡Hola desde JavaScript!";';

// Si estamos en modo lección, cargar contenido predefinido si existe
if ($editorMode === 'lesson' && $lessonId) {
    try {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT content FROM lessons WHERE lesson_id = ?");
        $stmt->execute([$lessonId]);
        $lessonData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($lessonData && $lessonData['content']) {
            // Decodificar el contenido JSON de la lección
            $content = json_decode($lessonData['content'], true);
            if ($content) {
                // Sobreescribir solo si hay contenido en la lección
                if (!empty($content['html_template'])) $htmlContent = $content['html_template'];
                if (!empty($content['css_template'])) $cssContent = $content['css_template'];
                if (!empty($content['js_template'])) $jsContent = $content['js_template'];
            }
        }
    } catch (PDOException $e) {
        // Error al cargar la lección
        $error = "Error al cargar la lección. Por favor intente nuevamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="<?php echo isset($_COOKIE['theme']) ? htmlspecialchars($_COOKIE['theme']) : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | WebCraft Academy</title>
    
    <!-- Favicon -->
    <link rel="icon" href="../assets/images/favicon.ico">
    
    <!-- Hojas de estilo principales -->
    <link rel="stylesheet" href="../assets/css/main.css">
    
    <!-- CodeMirror - Editor de código -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/theme/monokai.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/theme/eclipse.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/hint/show-hint.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/fold/foldgutter.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/lint/lint.min.css">
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS específico del editor -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Barra de navegación superior -->
    <nav class="editor-navbar">
        <div class="navbar-logo">
            <a href="../index.php">
                <img src="../assets/images/logo.png" alt="WebCraft Logo" class="logo-img">
                <span class="logo-text">WebCraft Academy</span>
            </a>
        </div>
        
        <div class="navbar-actions">
            <!-- Botón de guardar proyecto -->
            <button id="saveProject" class="btn-primary">
                <i class="fas fa-save"></i> Guardar
            </button>
            
            <!-- Botón de descargar proyecto -->
            <button id="downloadProject" class="btn-secondary">
                <i class="fas fa-download"></i> Descargar
            </button>
            
            <!-- Seleccionador de tema del editor -->
            <div class="theme-selector">
                <label for="editorTheme">Tema:</label>
                <select id="editorTheme">
                    <option value="monokai">Monokai (Oscuro)</option>
                    <option value="eclipse">Eclipse (Claro)</option>
                </select>
            </div>
            
            <!-- Botón de pantalla completa -->
            <button id="toggleFullscreen" class="btn-icon">
                <i class="fas fa-expand"></i>
            </button>
            
            <!-- Botón de ayuda -->
            <button id="showHelp" class="btn-icon">
                <i class="fas fa-question-circle"></i>
            </button>
            
            <!-- Enlace a perfil de usuario -->
            <a href="../index.php?page=profile" class="user-profile">
                <i class="fas fa-user-circle"></i>
            </a>
        </div>
    </nav>
    
    <!-- Contenedor principal del editor -->
    <div class="editor-container">
        <!-- Barra lateral izquierda con navegación y recursos -->
        <div class="editor-sidebar">
            <div class="sidebar-section">
                <h3>Proyecto</h3>
                <div class="project-details">
                    <input type="text" id="projectTitle" placeholder="Título del proyecto" value="<?php echo $projectData ? htmlspecialchars($projectData['title']) : 'Nuevo Proyecto'; ?>">
                    <textarea id="projectDescription" placeholder="Descripción (opcional)"><?php echo $projectData ? htmlspecialchars($projectData['description']) : ''; ?></textarea>
                </div>
            </div>
            
            <div class="sidebar-section">
                <h3>Archivos</h3>
                <ul class="file-list">
                    <li class="file active" data-file="html"><i class="fab fa-html5"></i> index.html</li>
                    <li class="file" data-file="css"><i class="fab fa-css3-alt"></i> styles.css</li>
                    <li class="file" data-file="js"><i class="fab fa-js"></i> script.js</li>
                </ul>
            </div>
            
            <?php if ($editorMode === 'lesson' && $lessonId): ?>
            <div class="sidebar-section">
                <h3>Instrucciones</h3>
                <div class="lesson-instructions" id="lessonInstructions">
                    <!-- Las instrucciones de la lección se cargarán aquí mediante AJAX -->
                    <p>Cargando instrucciones...</p>
                </div>
                <button id="checkExercise" class="btn-success btn-full">
                    <i class="fas fa-check-circle"></i> Verificar Ejercicio
                </button>
            </div>
            <?php endif; ?>
            
            <div class="sidebar-section">
                <h3>Recursos</h3>
                <div class="resources-list">
                    <a href="#" class="resource-link" data-resource="html-cheatsheet">
                        <i class="fas fa-file-code"></i> HTML Cheatsheet
                    </a>
                    <a href="#" class="resource-link" data-resource="css-cheatsheet">
                        <i class="fas fa-palette"></i> CSS Cheatsheet
                    </a>
                    <a href="#" class="resource-link" data-resource="js-cheatsheet">
                        <i class="fas fa-code"></i> JS Cheatsheet
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Área principal del editor -->
        <div class="editor-main">
            <div class="editor-tabs">
                <div class="tab-buttons">
                    <button class="tab-btn active" data-tab="editor">Editor</button>
                    <button class="tab-btn" data-tab="preview">Vista Previa</button>
                    <button class="tab-btn" data-tab="split">Vista Dividida</button>
                </div>
                
                <div class="editor-controls">
                    <button id="refreshPreview" class="btn-icon" title="Actualizar vista previa">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <div class="editor-resize-controls">
                        <button class="btn-icon resize-control" data-size="mobile" title="Vista móvil">
                            <i class="fas fa-mobile-alt"></i>
                        </button>
                        <button class="btn-icon resize-control" data-size="tablet" title="Vista tablet">
                            <i class="fas fa-tablet-alt"></i>
                        </button>
                        <button class="btn-icon resize-control active" data-size="desktop" title="Vista escritorio">
                            <i class="fas fa-desktop"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="editor-content">
                <!-- Panel de editores de código -->
                <div class="editor-panel active" id="editorPanel">
                    <div class="code-editors">
                        <div class="code-editor active" id="htmlEditor">
                            <div class="editor-header">
                                <h3><i class="fab fa-html5"></i> HTML</h3>
                            </div>
                            <textarea id="htmlCode"><?php echo htmlspecialchars($htmlContent); ?></textarea>
                        </div>
                        
                        <div class="code-editor" id="cssEditor">
                            <div class="editor-header">
                                <h3><i class="fab fa-css3-alt"></i> CSS</h3>
                            </div>
                            <textarea id="cssCode"><?php echo htmlspecialchars($cssContent); ?></textarea>
                        </div>
                        
                        <div class="code-editor" id="jsEditor">
                            <div class="editor-header">
                                <h3><i class="fab fa-js"></i> JavaScript</h3>
                            </div>
                            <textarea id="jsCode"><?php echo htmlspecialchars($jsContent); ?></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Panel de vista previa -->
                <div class="editor-panel" id="previewPanel">
                    <div class="preview-container">
                        <iframe id="previewFrame" title="Vista previa del código"></iframe>
                    </div>
                </div>
                
                <!-- Panel de vista dividida (editor + vista previa) -->
                <div class="editor-panel" id="splitPanel">
                    <div class="split-container">
                        <div class="split-editor">
                            <div class="code-editors split-mode">
                                <div class="code-editor active" id="splitHtmlEditor">
                                    <div class="editor-header">
                                        <h3><i class="fab fa-html5"></i> HTML</h3>
                                    </div>
                                    <textarea id="splitHtmlCode"><?php echo htmlspecialchars($htmlContent); ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="split-preview">
                            <iframe id="splitPreviewFrame" title="Vista previa dividida"></iframe>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Barra de estado inferior -->
            <div class="editor-statusbar">
                <div class="statusbar-left">
                    <span id="cursorPosition">Línea: 1, Columna: 1</span>
                </div>
                <div class="statusbar-right">
                    <span id="autoSaveStatus">Guardado automático: <span class="status-indicator">Activado</span></span>
                    <span id="lastSaved">Último guardado: Nunca</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de guardar proyecto -->
    <div class="modal" id="saveProjectModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Guardar Proyecto</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="saveProjectForm">
                    <div class="form-group">
                        <label for="modalProjectTitle">Título del Proyecto</label>
                        <input type="text" id="modalProjectTitle" required>
                    </div>
                    <div class="form-group">
                        <label for="modalProjectDescription">Descripción (opcional)</label>
                        <textarea id="modalProjectDescription"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="projectVisibility">Visibilidad</label>
                        <select id="projectVisibility">
                            <option value="private">Privado (solo yo)</option>
                            <option value="public">Público (todos)</option>
                        </select>
                    </div>
                    <div class="form-group form-actions">
                        <button type="button" class="btn-secondary modal-cancel">Cancelar</button>
                        <button type="submit" class="btn-primary">Guardar Proyecto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal de ayuda -->
    <div class="modal" id="helpModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Ayuda del Editor</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <h3>Atajos de Teclado</h3>
                <ul class="keyboard-shortcuts">
                    <li><kbd>Ctrl</kbd> + <kbd>S</kbd> - Guardar proyecto</li>
                    <li><kbd>Ctrl</kbd> + <kbd>Space</kbd> - Autocompletado</li>
                    <li><kbd>Alt</kbd> + <kbd>F</kbd> - Formatear código</li>
                    <li><kbd>Ctrl</kbd> + <kbd>/</kbd> - Comentar línea</li>
                    <li><kbd>Ctrl</kbd> + <kbd>F</kbd> - Buscar</li>
                    <li><kbd>F11</kbd> - Pantalla completa</li>
                </ul>
                
                <h3>Consejos Rápidos</h3>
                <ul>
                    <li>Utiliza el botón "Actualizar" para ver los cambios en la vista previa.</li>
                    <li>Activa el "Guardado automático" para guardar tu progreso cada 30 segundos.</li>
                    <li>Usa la "Vista Dividida" para ver tu código y resultado simultáneamente.</li>
                    <li>Prueba diferentes tamaños de pantalla para verificar el diseño responsive.</li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Modal de recursos -->
    <div class="modal" id="resourceModal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2 id="resourceTitle">Recurso</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div id="resourceContent"></div>
            </div>
        </div>
    </div>
    
    <!-- CodeMirror y dependencias -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/edit/closetag.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/edit/closebrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/fold/foldcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/fold/foldgutter.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/fold/xml-fold.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/fold/brace-fold.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/fold/comment-fold.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/hint/show-hint.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/hint/html-hint.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/hint/css-hint.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/hint/javascript-hint.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/selection/active-line.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/lint/lint.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/lint/html-lint.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/lint/css-lint.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/lint/javascript-lint.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/search/search.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/search/searchcursor.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/dialog/dialog.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/comment/comment.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Script específico del editor -->
    <script src="editor.js"></script>
    
    <!-- Inicialización de proyecto -->
    <script>
        // Datos del proyecto para JS
        const projectData = <?php echo $projectData ? json_encode($projectData) : 'null'; ?>;
        const editorMode = '<?php echo $editorMode; ?>';
        const lessonId = <?php echo $lessonId ? $lessonId : 'null'; ?>;
        const challengeId = <?php echo $challengeId ? $challengeId : 'null'; ?>;
    </script>
</body>
</html>
