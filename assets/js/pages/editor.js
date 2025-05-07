/**
 * WebCraft Academy - Editor de Código Interactivo
 * 
 * Este archivo implementa la funcionalidad del editor de código con CodeMirror,
 * vista previa en tiempo real y sistema de guardado de proyectos.
 */

// Variables globales para los editores
let htmlEditor, cssEditor, jsEditor;
let previewUpdateTimeout;
const UPDATE_DELAY = 1000; // Retraso en ms para actualizar la vista previa

// Inicializar la aplicación cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    initCodeEditors();
    setupEventListeners();
    updatePreview();
});

/**
 * Inicializa los editores de código con CodeMirror
 */
function initCodeEditors() {
    // Opciones comunes para todos los editores
    const commonOptions = {
        lineNumbers: true,
        autoCloseTags: true,
        autoCloseBrackets: true,
        theme: 'default',
        indentUnit: 4,
        smartIndent: true,
        tabSize: 4,
        indentWithTabs: false,
        extraKeys: {
            'Ctrl-Space': 'autocomplete'
        }
    };

    // Inicializar editor HTML
    htmlEditor = CodeMirror.fromTextArea(document.getElementById('html-code'), {
        ...commonOptions,
        mode: 'htmlmixed',
        extraKeys: {
            ...commonOptions.extraKeys,
            'Tab': function(cm) {
                var spaces = Array(cm.getOption('indentUnit') + 1).join(' ');
                cm.replaceSelection(spaces);
            }
        }
    });

    // Inicializar editor CSS
    cssEditor = CodeMirror.fromTextArea(document.getElementById('css-code'), {
        ...commonOptions,
        mode: 'css'
    });

    // Inicializar editor JavaScript
    jsEditor = CodeMirror.fromTextArea(document.getElementById('js-code'), {
        ...commonOptions,
        mode: 'javascript'
    });

    // Aplicar tema según preferencia del usuario
    applyEditorTheme();

    // Configurar eventos de cambio para actualizar la vista previa
    htmlEditor.on('change', schedulePreviewUpdate);
    cssEditor.on('change', schedulePreviewUpdate);
    jsEditor.on('change', schedulePreviewUpdate);
}

/**
 * Programa la actualización de la vista previa con un retraso
 * para evitar actualizaciones constantes mientras se escribe
 */
function schedulePreviewUpdate() {
    clearTimeout(previewUpdateTimeout);
    previewUpdateTimeout = setTimeout(updatePreview, UPDATE_DELAY);
}

/**
 * Actualiza la vista previa combinando HTML, CSS y JS
 */
function updatePreview() {
    const iframe = document.getElementById('preview-iframe');
    const htmlContent = htmlEditor.getValue();
    const cssContent = cssEditor.getValue();
    const jsContent = jsContent = jsEditor.getValue();

    // Crear documento combinado para el iframe
    const previewContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>${cssContent}</style>
        </head>
        <body>
            ${htmlContent}
            <script>
            // Capturar console.log para mostrar en la consola del editor
            (function() {
                const originalConsole = console;
                console = {
                    log: function() {
                        sendToEditorConsole('log', arguments);
                        originalConsole.log.apply(originalConsole, arguments);
                    },
                    warn: function() {
                        sendToEditorConsole('warn', arguments);
                        originalConsole.warn.apply(originalConsole, arguments);
                    },
                    error: function() {
                        sendToEditorConsole('error', arguments);
                        originalConsole.error.apply(originalConsole, arguments);
                    }
                };
                
                function sendToEditorConsole(type, args) {
                    const argsArray = Array.from(args).map(item => {
                        if (typeof item === 'object') {
                            try {
                                return JSON.stringify(item);
                            } catch (e) {
                                return String(item);
                            }
                        }
                        return String(item);
                    });
                    
                    window.parent.postMessage({
                        type: 'console',
                        logType: type,
                        content: argsArray.join(' ')
                    }, '*');
                }
                
                // Manejo de errores globales
                window.onerror = function(message, source, lineno, colno, error) {
                    sendToEditorConsole('error', [`Error: ${message} (Línea: ${lineno}, Columna: ${colno})`]);
                    return true;
                };
            })();
            
            // Código del usuario
            ${jsContent}
            </script>
        </body>
        </html>
    `;

    // Actualizar el contenido del iframe
    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
    iframeDoc.open();
    iframeDoc.write(previewContent);
    iframeDoc.close();
}

/**
 * Configura todos los event listeners necesarios para la interfaz
 */
function setupEventListeners() {
    // Manejo de tabs del editor
    const tabButtons = document.querySelectorAll('.tab-btn');
    const editorPanels = document.querySelectorAll('.code-editor');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            
            // Desactivar todos los tabs y editores
            tabButtons.forEach(btn => btn.classList.remove('active'));
            editorPanels.forEach(panel => panel.classList.remove('active'));
            
            // Activar el tab y editor seleccionado
            this.classList.add('active');
            document.getElementById(`${target}-editor`) || document.getElementById(`${target}-panel`).classList.add('active');
            
            // Si se selecciona la vista previa, actualizarla
            if (target === 'preview') {
                updatePreview();
            }
            
            // Refrescar el editor activo para solucionar problemas de visualización
            if (target === 'html') htmlEditor.refresh();
            if (target === 'css') cssEditor.refresh();
            if (target === 'js') jsEditor.refresh();
        });
    });
    
    // Botón ejecutar
    document.getElementById('run-project').addEventListener('click', updatePreview);
    
    // Botón guardar
    document.getElementById('save-project').addEventListener('click', function() {
        // Rellenar modal con datos actuales
        document.getElementById('modal-project-title').value = document.getElementById('project-title').value;
        document.getElementById('modal-project-description').value = document.getElementById('project-description').value;
        document.getElementById('modal-project-public').checked = document.getElementById('project-public').checked;
        
        // Mostrar modal
        document.getElementById('save-modal').style.display = 'block';
    });
    
    // Cerrar modal
    document.querySelector('.close-modal').addEventListener('click', function() {
        document.getElementById('save-modal').style.display = 'none';
    });
    
    document.querySelector('.cancel-save').addEventListener('click', function() {
        document.getElementById('save-modal').style.display = 'none';
    });
    
    // Formulario de guardado
    document.getElementById('save-project-form').addEventListener('submit', function(e) {
        e.preventDefault();
        saveProject();
    });
    
    // Limpiar consola
    document.getElementById('clear-console').addEventListener('click', function() {
        document.getElementById('console-output').innerHTML = '';
    });
    
    // Escuchar mensajes desde el iframe (consola)
    window.addEventListener('message', function(event) {
        if (event.data && event.data.type === 'console') {
            appendToConsole(event.data.logType, event.data.content);
        }
    });
}

/**
 * Guarda el proyecto actual
 */
function saveProject() {
    const projectId = document.getElementById('project-id').value;
    const title = document.getElementById('modal-project-title').value;
    const description = document.getElementById('modal-project-description').value;
    const isPublic = document.getElementById('modal-project-public').checked ? 1 : 0;
    const htmlContent = htmlEditor.getValue();
    const cssContent = cssEditor.getValue();
    const jsContent = jsEditor.getValue();
    
    // Actualizar interfaz con los valores del modal
    document.getElementById('project-title').value = title;
    document.getElementById('project-description').value = description;
    document.getElementById('project-public').checked = isPublic;
    
    // Preparar datos para enviar
    const projectData = {
        project_id: projectId,
        title: title,
        description: description,
        is_public: isPublic,
        html_content: htmlContent,
        css_content: cssContent,
        js_content: jsContent
    };
    
    // Enviar datos al servidor mediante AJAX
    fetch('includes/api/save_project.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(projectData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar ID de proyecto si es nuevo
            if (data.project_id) {
                document.getElementById('project-id').value = data.project_id;
                
                // Actualizar URL sin recargar
                const newUrl = `index.php?page=editor&project_id=${data.project_id}`;
                window.history.pushState({ path: newUrl }, '', newUrl);
            }
            
            showSystemMessage('Proyecto guardado correctamente', 'success');
        } else {
            showSystemMessage('Error al guardar: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showSystemMessage('Error de conexión: ' + error, 'error');
    })
    .finally(() => {
        // Cerrar modal
        document.getElementById('save-modal').style.display = 'none';
    });
}

/**
 * Muestra un mensaje de sistema temporal
 */
function showSystemMessage(message, type = 'info') {
    const messageElement = document.getElementById('system-message');
    messageElement.textContent = message;
    messageElement.className = 'system-message ' + type;
    messageElement.classList.remove('hidden');
    
    // Ocultar después de 3 segundos
    setTimeout(() => {
        messageElement.classList.add('hidden');
    }, 3000);
}

/**
 * Añade un mensaje a la consola
 */
function appendToConsole(type, content) {
    const consoleOutput = document.getElementById('console-output');
    const messageElement = document.createElement('div');
    messageElement.className = 'console-message ' + type;
    messageElement.textContent = content;
    consoleOutput.appendChild(messageElement);
    
    // Hacer scroll hasta el final
    consoleOutput.scrollTop = consoleOutput.scrollHeight;
}

/**
 * Aplica el tema del editor según la preferencia del sistema/usuario
 */
function applyEditorTheme() {
    // Detectar tema
    const isDarkMode = document.documentElement.getAttribute('data-theme') === 'dark';
    const theme = isDarkMode ? 'darcula' : 'default';
    
    // Aplicar tema a todos los editores
    if (htmlEditor) htmlEditor.setOption('theme', theme);
    if (cssEditor) cssEditor.setOption('theme', theme);
    if (jsEditor) jsEditor.setOption('theme', theme);
}
