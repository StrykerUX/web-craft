/**
 * WebCraft Academy - Script del Editor de Código
 * 
 * Este archivo contiene toda la lógica para el funcionamiento
 * del editor de código interactivo, incluyendo la integración
 * con CodeMirror, la vista previa en tiempo real y el sistema
 * de guardado.
 */

// Instancias de CodeMirror
let htmlEditor, cssEditor, jsEditor;
let splitHtmlEditor; // Editor para vista dividida
let currentEditor = 'html'; // Archivo actualmente abierto
let projectId = 0; // ID del proyecto actualmente abierto
let autoSaveEnabled = true; // Estado del guardado automático
let autoSaveTimer = null; // Temporizador para guardado automático
let lastSaved = null; // Última vez que se guardó

// Inicializar cuando el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar editores
    initEditors();
    
    // Actualizar la vista previa inicialmente
    updatePreview();
    
    // Establecer eventos para los archivos
    setupEventListeners();
    
    // Si hay un proyecto ID (establecido en index.php), actualizarlo
    if (typeof projectData !== 'undefined' && projectData && projectData.project_id) {
        projectId = projectData.project_id;
    }
});

/**
 * Inicializa los editores CodeMirror
 */
function initEditors() {
    // Editor HTML
    htmlEditor = CodeMirror.fromTextArea(document.getElementById('htmlCode'), {
        mode: "htmlmixed",
        theme: "monokai",
        lineNumbers: true,
        autoCloseTags: true,
        autoCloseBrackets: true,
        matchBrackets: true,
        lineWrapping: true,
        extraKeys: {
            "Ctrl-Space": "autocomplete",
            "Ctrl-S": function() {
                saveProject();
                return false;
            }
        }
    });
    
    // Editor CSS
    cssEditor = CodeMirror.fromTextArea(document.getElementById('cssCode'), {
        mode: "css",
        theme: "monokai",
        lineNumbers: true,
        autoCloseBrackets: true,
        matchBrackets: true,
        lineWrapping: true,
        extraKeys: {
            "Ctrl-Space": "autocomplete",
            "Ctrl-S": function() {
                saveProject();
                return false;
            }
        }
    });
    
    // Editor JavaScript
    jsEditor = CodeMirror.fromTextArea(document.getElementById('jsCode'), {
        mode: "javascript",
        theme: "monokai",
        lineNumbers: true,
        autoCloseBrackets: true,
        matchBrackets: true,
        lineWrapping: true,
        extraKeys: {
            "Ctrl-Space": "autocomplete",
            "Ctrl-S": function() {
                saveProject();
                return false;
            }
        }
    });
    
    // Actualizar vista previa cuando el código cambie
    htmlEditor.on('change', function() {
        if (document.getElementById('autoRefresh').checked) {
            updatePreview();
        }
    });
    
    cssEditor.on('change', function() {
        if (document.getElementById('autoRefresh').checked) {
            updatePreview();
        }
    });
    
    jsEditor.on('change', function() {
        if (document.getElementById('autoRefresh').checked) {
            updatePreview();
        }
    });
}

/**
 * Configura los event listeners necesarios
 */
function setupEventListeners() {
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
}

/**
 * Actualiza la vista previa con el código actual
 */
function updatePreview() {
    // Obtener contenido de los editores
    const htmlContent = htmlEditor.getValue();
    const cssContent = cssEditor.getValue();
    const jsContent = jsEditor.getValue();
    
    // Construir contenido completo del documento
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
    
    // Obtener el iframe de vista previa
    const preview = document.getElementById('previewFrame').contentWindow;
    
    // Escribir al iframe
    preview.document.open();
    preview.document.write(content);
    preview.document.close();
}

/**
 * Cambiar entre archivos (HTML, CSS, JS)
 */
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

/**
 * Guardar proyecto
 */
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
        const description = document.getElementById('projectDescription').value || '';
        const isPublic = false; // Por defecto privado
        
        document.getElementById('saveDescription').value = description;
        document.getElementById('saveVisibility').value = isPublic ? '1' : '0';
    }
}

/**
 * Enviar proyecto al servidor
 */
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

/**
 * Descargar proyecto
 */
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

/**
 * Función auxiliar para descargar archivos
 */
function downloadFile(filename, content) {
    const element = document.createElement('a');
    element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(content));
    element.setAttribute('download', filename);
    element.style.display = 'none';
    document.body.appendChild(element);
    element.click();
    document.body.removeChild(element);
}

/**
 * Mostrar notificación
 */
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
