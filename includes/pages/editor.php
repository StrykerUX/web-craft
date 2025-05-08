<?php
/**
 * Editor de código interactivo para WebCraft Academy
 * 
 * Esta página proporciona un editor de código en vivo con CodeMirror, 
 * vista previa en tiempo real y capacidad para guardar proyectos.
 */

// Verificar acceso directo
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Obtener información del proyecto si se está editando uno existente
$projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
$projectData = null;

if ($projectId > 0 && isUserLoggedIn()) {
    // Obtener datos del proyecto desde la base de datos
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT * FROM projects WHERE project_id = ? AND user_id = ?");
    $stmt->execute([$projectId, $_SESSION['user_id']]);
    $projectData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Valores predeterminados para editor vacío
$htmlContent = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Proyecto</title>
    <style>
        /* Estilos CSS aquí */
    </style>
</head>
<body>
    <!-- Contenido HTML aquí -->
    <h1>¡Bienvenido a WebCraft!</h1>
    <p>Comienza a editar este código para crear tu proyecto.</p>
    
</body>
</html>';

$cssContent = "/* Agrega tus estilos CSS aquí */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 20px;
    line-height: 1.6;
    color: #333;
}

h1 {
    color: #0066cc;
}";

$jsContent = "// Agrega tu código JavaScript aquí
console.log('¡Bienvenido a WebCraft!');

// Ejemplo: Cambiar el contenido del título cuando se hace clic en él
document.addEventListener('DOMContentLoaded', function() {
    const heading = document.querySelector('h1');
    if (heading) {
        heading.addEventListener('click', function() {
            this.textContent = '¡Has hecho clic en el título!';
        });
    }
});";

// Si existe información del proyecto, usar esa en su lugar
if ($projectData) {
    $htmlContent = $projectData['html_content'];
    $cssContent = $projectData['css_content'];
    $jsContent = $projectData['js_content'];
}

// Obtener modo del editor (lesson, challenge, project)
$editorMode = isset($_GET['mode']) ? $_GET['mode'] : 'free';
$lessonId = isset($_GET['lesson_id']) ? (int)$_GET['lesson_id'] : 0;
$challengeId = isset($_GET['challenge_id']) ? (int)$_GET['challenge_id'] : 0;
$moduleId = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 0;

// Cargar plantilla específica si es necesario
if ($editorMode === 'lesson' && $lessonId > 0) {
    // Aquí cargaríamos la plantilla específica para la lección
    // Por ahora, dejamos los valores predeterminados
}
?>

<div class="editor-page-container">
    <!-- Barra de navegación del editor -->
    <div class="editor-navbar">
        <div class="editor-navbar-left">
            <a href="index.php" class="editor-logo">
                <i class="fas fa-code"></i> WebCraft Academy
            </a>
            
            <div class="project-info">
                <input type="text" id="projectTitle" class="project-title-input" placeholder="Título del proyecto" value="<?php echo $projectData ? htmlspecialchars($projectData['title']) : 'Nuevo Proyecto'; ?>">
            </div>
        </div>
        
        <div class="editor-navbar-right">
            <button id="saveProject" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar
            </button>
            
            <button id="runProject" class="btn btn-success">
                <i class="fas fa-play"></i> Ejecutar
            </button>
            
            <button id="downloadProject" class="btn btn-outline">
                <i class="fas fa-download"></i> Descargar
            </button>
            
            <?php if ($editorMode === 'lesson' && $lessonId > 0): ?>
            <a href="index.php?page=lessons&module_id=<?php echo $moduleId; ?>&lesson_id=<?php echo $lessonId; ?>" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Volver a la lección
            </a>
            <?php else: ?>
            <a href="index.php?page=dashboard" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Contenedor principal del editor -->
    <div class="editor-container">
        <!-- Panel izquierdo con archivos -->
        <div class="editor-sidebar">
            <div class="file-list">
                <div class="file-item active" data-file="html">
                    <i class="fab fa-html5"></i> HTML
                </div>
                <div class="file-item" data-file="css">
                    <i class="fab fa-css3-alt"></i> CSS
                </div>
                <div class="file-item" data-file="js">
                    <i class="fab fa-js"></i> JavaScript
                </div>
            </div>
            
            <?php if ($editorMode === 'lesson' && $lessonId > 0): ?>
            <div class="lesson-info">
                <h3>Instrucciones</h3>
                <div class="lesson-instructions" id="lessonInstructions">
                    <!-- Se cargará mediante AJAX -->
                    <p>Cargando instrucciones...</p>
                </div>
                
                <button id="checkLesson" class="btn btn-success btn-block">
                    <i class="fas fa-check-circle"></i> Verificar ejercicio
                </button>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Área principal del editor -->
        <div class="editor-main">
            <div class="editor-panels">
                <!-- Panel de edición HTML -->
                <div class="editor-panel active" id="htmlPanel">
                    <textarea id="htmlEditor"><?php echo htmlspecialchars($htmlContent); ?></textarea>
                </div>
                
                <!-- Panel de edición CSS -->
                <div class="editor-panel" id="cssPanel">
                    <textarea id="cssEditor"><?php echo htmlspecialchars($cssContent); ?></textarea>
                </div>
                
                <!-- Panel de edición JavaScript -->
                <div class="editor-panel" id="jsPanel">
                    <textarea id="jsEditor"><?php echo htmlspecialchars($jsContent); ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- Panel de vista previa -->
        <div class="preview-container">
            <div class="preview-header">
                <div class="preview-title">Vista Previa</div>
                
                <div class="preview-controls">
                    <button id="refreshPreview" class="btn-icon" title="Actualizar vista previa">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    
                    <div class="device-selector">
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
            </div>
            
            <div class="preview-frame-container">
                <iframe id="previewFrame" title="Vista previa"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Modal para guardar proyecto -->
<div class="modal" id="saveProjectModal">
    <div class="modal-backdrop"></div>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Guardar Proyecto</h3>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="saveProjectForm">
                    <div class="form-group">
                        <label for="saveTitle">Título</label>
                        <input type="text" id="saveTitle" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="saveDescription">Descripción (opcional)</label>
                        <textarea id="saveDescription" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="saveVisibility">Visibilidad</label>
                        <select id="saveVisibility" class="form-control">
                            <option value="0">Privado (solo yo)</option>
                            <option value="1">Público (todos)</option>
                        </select>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Script específico para el editor -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    let htmlEditor, cssEditor, jsEditor;
    let currentEditor = 'html';
    let autoRefresh = true;
    let projectId = <?php echo $projectId; ?>;
    
    // Inicializar editores CodeMirror
    function initEditors() {
        // Editor HTML
        htmlEditor = CodeMirror.fromTextArea(document.getElementById('htmlEditor'), {
            mode: 'htmlmixed',
            theme: 'material',
            lineNumbers: true,
            indentUnit: 4,
            autoCloseTags: true,
            autoCloseBrackets: true,
            matchBrackets: true,
            extraKeys: {
                'Ctrl-Space': 'autocomplete',
                'Ctrl-S': function() { saveProject(); return false; }
            }
        });
        
        // Editor CSS
        cssEditor = CodeMirror.fromTextArea(document.getElementById('cssEditor'), {
            mode: 'css',
            theme: 'material',
            lineNumbers: true,
            indentUnit: 4,
            autoCloseBrackets: true,
            matchBrackets: true,
            extraKeys: {
                'Ctrl-Space': 'autocomplete',
                'Ctrl-S': function() { saveProject(); return false; }
            }
        });
        
        // Editor JavaScript
        jsEditor = CodeMirror.fromTextArea(document.getElementById('jsEditor'), {
            mode: 'javascript',
            theme: 'material',
            lineNumbers: true,
            indentUnit: 4,
            autoCloseBrackets: true,
            matchBrackets: true,
            extraKeys: {
                'Ctrl-Space': 'autocomplete',
                'Ctrl-S': function() { saveProject(); return false; }
            }
        });
        
        // Evento para actualizar vista previa al cambiar el código
        htmlEditor.on('change', function() {
            if (autoRefresh) updatePreview();
        });
        
        cssEditor.on('change', function() {
            if (autoRefresh) updatePreview();
        });
        
        jsEditor.on('change', function() {
            if (autoRefresh) updatePreview();
        });
        
        // Iniciar vista previa
        updatePreview();
    }
    
    // Actualizar vista previa
    function updatePreview() {
        const htmlContent = htmlEditor.getValue();
        const cssContent = cssEditor.getValue();
        const jsContent = jsEditor.getValue();
        
        // Obtener el iframe y su documento
        const previewFrame = document.getElementById('previewFrame');
        const preview = previewFrame.contentDocument || previewFrame.contentWindow.document;
        
        // Crear contenido HTML completo
        const content = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style>${cssContent}</style>
            </head>
            <body>
                ${htmlContent}
                <script>${jsContent}</script>
            </body>
            </html>
        `;
        
        // Escribir al iframe
        preview.open();
        preview.write(content);
        preview.close();
    }
    
    // Cambiar entre archivos (HTML, CSS, JS)
    function switchFile(file) {
        // Ocultar todos los paneles
        document.querySelectorAll('.editor-panel').forEach(panel => {
            panel.classList.remove('active');
        });
        
        // Mostrar el panel seleccionado
        document.getElementById(file + 'Panel').classList.add('active');
        
        // Actualizar clases en la lista de archivos
        document.querySelectorAll('.file-item').forEach(item => {
            item.classList.remove('active');
        });
        
        document.querySelector(`.file-item[data-file="${file}"]`).classList.add('active');
        
        // Actualizar editor actual
        currentEditor = file;
        
        // Refrescar el editor visible para corregir cualquier problema de renderizado
        if (file === 'html') htmlEditor.refresh();
        if (file === 'css') cssEditor.refresh();
        if (file === 'js') jsEditor.refresh();
    }
    
    // Guardar proyecto
    function saveProject() {
        // Obtener datos actuales
        const title = document.getElementById('projectTitle').value || 'Proyecto sin título';
        
        // Abrir modal de guardado
        const modal = document.getElementById('saveProjectModal');
        modal.style.display = 'block';
        
        // Pre-llenar campos del formulario
        document.getElementById('saveTitle').value = title;
        
        if (projectId > 0) {
            // Si es un proyecto existente, pre-llenar con datos actuales
            const description = '<?php echo $projectData ? addslashes($projectData['description']) : ''; ?>';
            const isPublic = <?php echo $projectData && $projectData['is_public'] ? 'true' : 'false'; ?>;
            
            document.getElementById('saveDescription').value = description;
            document.getElementById('saveVisibility').value = isPublic ? '1' : '0';
        }
    }
    
    // Enviar proyecto al servidor
    function submitProject(formData) {
        // Mostrar indicador de carga
        const submitBtn = document.querySelector('#saveProjectForm button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Guardando...';
        submitBtn.disabled = true;
        
        // Preparar datos para enviar
        const projectData = {
            title: formData.title,
            description: formData.description,
            is_public: formData.visibility === '1',
            html_content: htmlEditor.getValue(),
            css_content: cssEditor.getValue(),
            js_content: jsEditor.getValue(),
            project_id: projectId
        };
        
        // Enviar al servidor
        fetch('includes/ajax/save_project.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(projectData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar UI
                document.getElementById('projectTitle').value = formData.title;
                
                // Si es un proyecto nuevo, actualizar URL
                if (projectId === 0 && data.project_id) {
                    projectId = data.project_id;
                    window.history.replaceState({}, '', `index.php?page=editor&project_id=${projectId}`);
                }
                
                // Cerrar modal
                document.getElementById('saveProjectModal').style.display = 'none';
                
                // Mostrar notificación
                showNotification('Proyecto guardado exitosamente', 'success');
            } else {
                throw new Error(data.message || 'Error al guardar el proyecto');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error: ' + error.message, 'error');
        })
        .finally(() => {
            // Restaurar botón
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    }
    
    // Descargar proyecto
    function downloadProject() {
        // Crear archivos para descargar
        const htmlContent = htmlEditor.getValue();
        const cssContent = cssEditor.getValue();
        const jsContent = jsEditor.getValue();
        
        // Descargar HTML
        downloadFile('index.html', htmlContent);
        
        // Descargar CSS
        downloadFile('styles.css', cssContent);
        
        // Descargar JS
        downloadFile('script.js', jsContent);
        
        showNotification('Archivos descargados correctamente', 'success');
    }
    
    // Función auxiliar para descargar archivos
    function downloadFile(filename, content) {
        const element = document.createElement('a');
        element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(content));
        element.setAttribute('download', filename);
        
        element.style.display = 'none';
        document.body.appendChild(element);
        
        element.click();
        
        document.body.removeChild(element);
    }
    
    // Mostrar notificación
    function showNotification(message, type) {
        // Crear elemento de notificación
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = message;
        
        // Añadir al DOM
        document.body.appendChild(notification);
        
        // Mostrar con animación
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Ocultar después de 3 segundos
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
    
    // Event Listeners
    
    // Cambiar entre archivos
    document.querySelectorAll('.file-item').forEach(item => {
        item.addEventListener('click', () => {
            const file = item.getAttribute('data-file');
            switchFile(file);
        });
    });
    
    // Botón de actualizar vista previa
    document.getElementById('refreshPreview').addEventListener('click', updatePreview);
    
    // Botón para ejecutar proyecto
    document.getElementById('runProject').addEventListener('click', updatePreview);
    
    // Botón para guardar proyecto
    document.getElementById('saveProject').addEventListener('click', saveProject);
    
    // Botón para descargar proyecto
    document.getElementById('downloadProject').addEventListener('click', downloadProject);
    
    // Formulario de guardado
    document.getElementById('saveProjectForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            title: document.getElementById('saveTitle').value,
            description: document.getElementById('saveDescription').value,
            visibility: document.getElementById('saveVisibility').value
        };
        
        submitProject(formData);
    });
    
    // Botones para cerrar modal
    document.querySelectorAll('[data-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('saveProjectModal').style.display = 'none';
        });
    });
    
    // Selector de dispositivo para vista previa
    document.querySelectorAll('.device-btn').forEach(button => {
        button.addEventListener('click', function() {
            const device = this.getAttribute('data-device');
            
            // Quitar clase activa de todos los botones
            document.querySelectorAll('.device-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Añadir clase activa a este botón
            this.classList.add('active');
            
            // Cambiar clase del contenedor de iframe
            const container = document.querySelector('.preview-frame-container');
            container.className = 'preview-frame-container';
            container.classList.add('device-' + device);
        });
    });
    
    // Verificar ejercicio (para modo lección)
    const checkLessonBtn = document.getElementById('checkLesson');
    if (checkLessonBtn) {
        checkLessonBtn.addEventListener('click', function() {
            // Implementar lógica para verificar ejercicio
            // Esta funcionalidad se desarrollará en una fase posterior
            showNotification('Funcionalidad en desarrollo', 'info');
        });
    }
    
    // Inicializar editores cuando se cargue CodeMirror
    if (typeof CodeMirror !== 'undefined') {
        initEditors();
    } else {
        // Si CodeMirror no está disponible, mostrar error
        console.error('ERROR: CodeMirror no está cargado');
        showNotification('Error al cargar el editor de código. Por favor, recarga la página.', 'error');
    }
});
</script>

<style>
/* Estilos específicos para la página del editor */
.editor-page-container {
    display: flex;
    flex-direction: column;
    height: calc(100vh - var(--header-height) - var(--footer-height));
    background-color: var(--gray-100);
}

/* Barra de navegación del editor */
.editor-navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    background-color: var(--dark-color);
    color: white;
}

.editor-navbar-left, .editor-navbar-right {
    display: flex;
    align-items: center;
}

.editor-logo {
    font-size: 1.2rem;
    font-weight: bold;
    color: white;
    text-decoration: none;
    margin-right: 20px;
}

.project-info {
    margin-left: 10px;
}

.project-title-input {
    background-color: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 4px;
    color: white;
    padding: 8px 12px;
    font-size: 1rem;
    width: 250px;
}

/* Contenedor principal del editor */
.editor-container {
    display: flex;
    flex: 1;
    overflow: hidden;
}

/* Barra lateral izquierda */
.editor-sidebar {
    width: 200px;
    background-color: var(--gray-800);
    color: var(--light-color);
    border-right: 1px solid var(--gray-700);
    overflow-y: auto;
}

.file-list {
    padding: 10px 0;
}

.file-item {
    padding: 10px 15px;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: background-color 0.2s;
}

.file-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.file-item.active {
    background-color: var(--primary-color);
    color: white;
}

.file-item i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

.lesson-info {
    padding: 15px;
    border-top: 1px solid var(--gray-700);
}

.lesson-info h3 {
    font-size: 1rem;
    margin-bottom: 10px;
}

.lesson-instructions {
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    padding: 10px;
    margin-bottom: 15px;
    font-size: 0.9rem;
    max-height: 300px;
    overflow-y: auto;
}

.btn-block {
    display: block;
    width: 100%;
}

/* Área principal del editor */
.editor-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background-color: var(--gray-900);
}

.editor-panels {
    flex: 1;
    position: relative;
    overflow: hidden;
}

.editor-panel {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: none;
}

.editor-panel.active {
    display: block;
}

/* Estilos para el editor CodeMirror */
.CodeMirror {
    height: 100%;
    font-family: 'Fira Code', monospace;
    font-size: 14px;
}

/* Panel de vista previa */
.preview-container {
    width: 40%;
    display: flex;
    flex-direction: column;
    background-color: white;
    border-left: 1px solid var(--gray-300);
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 15px;
    background-color: var(--gray-200);
    border-bottom: 1px solid var(--gray-300);
}

.preview-title {
    font-weight: 500;
    font-size: 0.9rem;
}

.preview-controls {
    display: flex;
    align-items: center;
}

.btn-icon {
    background: none;
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: var(--gray-600);
    transition: background-color 0.2s;
}

.btn-icon:hover {
    background-color: rgba(0, 0, 0, 0.1);
    color: var(--gray-800);
}

.device-selector {
    display: flex;
    margin-left: 10px;
}

.device-btn {
    margin: 0 2px;
}

.device-btn.active {
    color: var(--primary-color);
}

.preview-frame-container {
    flex: 1;
    overflow: auto;
    background-color: white;
}

#previewFrame {
    width: 100%;
    height: 100%;
    border: none;
    background-color: white;
}

/* Dispositivos responsivos */
.preview-frame-container.device-mobile {
    display: flex;
    justify-content: center;
    padding: 20px;
}

.preview-frame-container.device-mobile #previewFrame {
    width: 375px;
    height: 100%;
    border: 10px solid var(--gray-700);
    border-radius: 20px;
}

.preview-frame-container.device-tablet {
    display: flex;
    justify-content: center;
    padding: 20px;
}

.preview-frame-container.device-tablet #previewFrame {
    width: 768px;
    height: 100%;
    border: 10px solid var(--gray-700);
    border-radius: 10px;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1000;
}

.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-dialog {
    position: relative;
    width: 100%;
    max-width: 500px;
    margin: 50px auto;
    z-index: 1001;
}

.modal-content {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    margin: 0;
    font-size: 1.25rem;
}

.btn-close {
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    color: var(--gray-500);
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 15px 20px;
    border-top: 1px solid var(--gray-200);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid var(--gray-300);
    border-radius: 4px;
    font-family: inherit;
    font-size: 1rem;
}

textarea.form-control {
    min-height: 80px;
    resize: vertical;
}

/* Notificaciones */
.notification {
    position: fixed;
    bottom: 20px;
    right: 20px;
    padding: 12px 20px;
    background-color: white;
    border-left: 4px solid var(--primary-color);
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    transform: translateX(120%);
    transition: transform 0.3s ease;
}

.notification.show {
    transform: translateX(0);
}

.notification-success {
    border-left-color: var(--success-color);
}

.notification-error {
    border-left-color: var(--error-color);
}

.notification-info {
    border-left-color: var(--primary-color);
}

/* Tema oscuro */
[data-theme="dark"] .editor-sidebar {
    background-color: var(--gray-900);
    border-right-color: var(--gray-800);
}

[data-theme="dark"] .file-item:hover {
    background-color: rgba(255, 255, 255, 0.05);
}

[data-theme="dark"] .lesson-info {
    border-top-color: var(--gray-800);
}

[data-theme="dark"] .preview-container {
    background-color: var(--gray-800);
    border-left-color: var(--gray-700);
}

[data-theme="dark"] .preview-header {
    background-color: var(--gray-900);
    border-bottom-color: var(--gray-800);
}

[data-theme="dark"] #previewFrame {
    background-color: white; /* Mantenemos el fondo blanco para la vista previa */
}

[data-theme="dark"] .modal-content {
    background-color: var(--gray-800);
    color: var(--light-color);
}

[data-theme="dark"] .modal-header {
    border-bottom-color: var(--gray-700);
}

[data-theme="dark"] .modal-footer {
    border-top-color: var(--gray-700);
}

[data-theme="dark"] .form-control {
    background-color: var(--gray-700);
    border-color: var(--gray-600);
    color: var(--light-color);
}

/* Responsive */
@media (max-width: 992px) {
    .editor-container {
        flex-direction: column;
    }
    
    .editor-sidebar {
        width: 100%;
        height: auto;
        max-height: 200px;
        border-right: none;
        border-bottom: 1px solid var(--gray-700);
    }
    
    .preview-container {
        width: 100%;
        border-left: none;
        border-top: 1px solid var(--gray-300);
    }
}

@media (max-width: 768px) {
    .editor-navbar {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .editor-navbar-right {
        margin-top: 10px;
        width: 100%;
        overflow-x: auto;
        padding-bottom: 5px;
    }
}
</style>
