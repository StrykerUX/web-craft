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
            
            <!-- Botón para actualizar vista previa -->
            <button id="runProject" class="btn-primary">
                <i class="fas fa-play"></i> Ejecutar
            </button>
            
            <!-- Botón para cambiar tema (claro/oscuro) -->
            <button id="toggleTheme" class="btn-icon">
                <i class="fas fa-moon"></i>
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
                    <li class="file-item active" data-file="html"><i class="fab fa-html5"></i> index.html</li>
                    <li class="file-item" data-file="css"><i class="fab fa-css3-alt"></i> styles.css</li>
                    <li class="file-item" data-file="js"><i class="fab fa-js"></i> script.js</li>
                </ul>
            </div>
            
            <div class="sidebar-section">
                <h3>Opciones</h3>
                <div class="options-list">
                    <div class="option-item">
                        <label class="checkbox-label">
                            <input type="checkbox" id="autoRefresh" checked>
                            <span>Actualización automática</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <?php if ($editorMode === 'lesson' && $lessonId): ?>
            <div class="sidebar-section">
                <h3>Instrucciones</h3>
                <div class="lesson-instructions" id="lessonInstructions">
                    <!-- Las instrucciones de la lección se cargarán aquí mediante AJAX -->
                    <p>Cargando instrucciones...</p>
                </div>
                <button id="checkLesson" class="btn-success btn-full">
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
            <!-- Pestañas para los diferentes editores -->
            <div class="editor-tabs">
                <div class="tab-buttons">
                    <button id="refreshPreview" class="btn-icon">
                        <i class="fas fa-sync-alt"></i> Actualizar Vista
                    </button>
                </div>
                
                <div class="device-buttons">
                    <button class="btn-icon device-btn" data-device="mobile" title="Vista móvil">
                        <i class="fas fa-mobile-alt"></i>
                    </button>
                    <button class="btn-icon device-btn" data-device="tablet" title="Vista tablet">
                        <i class="fas fa-tablet-alt"></i>
                    </button>
                    <button class="btn-icon device-btn active" data-device="desktop" title="Vista escritorio">
                        <i class="fas fa-desktop"></i>
                    </button>
                </div>
            </div>
            
            <!-- Contenedor de editores -->
            <div class="editor-content">
                <!-- Paneles de editores -->
                <div class="editor-panel active" id="htmlPanel">
                    <textarea id="htmlCode"><?php echo htmlspecialchars($htmlContent); ?></textarea>
                </div>
                
                <div class="editor-panel" id="cssPanel">
                    <textarea id="cssCode"><?php echo htmlspecialchars($cssContent); ?></textarea>
                </div>
                
                <div class="editor-panel" id="jsPanel">
                    <textarea id="jsCode"><?php echo htmlspecialchars($jsContent); ?></textarea>
                </div>
                
                <!-- Panel de vista previa -->
                <div class="preview-frame-container device-desktop">
                    <iframe id="previewFrame" title="Vista previa del código"></iframe>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de guardar proyecto -->
    <div class="modal" id="saveProjectModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Guardar Proyecto</h2>
                <button class="modal-close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="saveProjectForm">
                    <div class="form-group">
                        <label for="saveTitle">Título del Proyecto</label>
                        <input type="text" id="saveTitle" required>
                    </div>
                    <div class="form-group">
                        <label for="saveDescription">Descripción (opcional)</label>
                        <textarea id="saveDescription"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="saveVisibility">Visibilidad</label>
                        <select id="saveVisibility">
                            <option value="0">Privado (solo yo)</option>
                            <option value="1">Público (todos)</option>
                        </select>
                    </div>
                    <div class="form-group form-actions">
                        <button type="button" class="btn-secondary" data-dismiss="modal">Cancelar</button>
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
                <button class="modal-close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <h3>Atajos de Teclado</h3>
                <ul class="keyboard-shortcuts">
                    <li><kbd>Ctrl</kbd> + <kbd>S</kbd> - Guardar proyecto</li>
                    <li><kbd>Ctrl</kbd> + <kbd>Space</kbd> - Autocompletado</li>
                    <li><kbd>Ctrl</kbd> + <kbd>/</kbd> - Comentar línea</li>
                    <li><kbd>Ctrl</kbd> + <kbd>F</kbd> - Buscar</li>
                </ul>
                
                <h3>Consejos Rápidos</h3>
                <ul>
                    <li>Utiliza el botón "Actualizar" para ver los cambios en la vista previa.</li>
                    <li>Marca "Actualización automática" para ver los cambios en tiempo real.</li>
                    <li>Prueba diferentes tamaños de pantalla para verificar el diseño responsive.</li>
                </ul>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/hint/show-hint.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/hint/html-hint.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/hint/css-hint.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/hint/javascript-hint.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/comment/comment.min.js"></script>
    
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
