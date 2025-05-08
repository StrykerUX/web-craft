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
let currentFile = 'html'; // Archivo actualmente abierto
let autoSaveEnabled = true; // Estado del guardado automático
let autoSaveTimer = null; // Temporizador para guardado automático
let lastSaved = null; // Última vez que se guardó

// Opciones comunes para editores CodeMirror
const commonEditorOptions = {
    lineNumbers: true,
    lineWrapping: true,
    autoCloseTags: true,
    autoCloseBrackets: true,
    styleActiveLine: true,
    foldGutter: true,
    gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
    extraKeys: {
        "Ctrl-Space": "autocomplete",
        "Alt-F": function(cm) {
            cm.execCommand("selectAll");
            cm.autoFormatRange(cm.getCursor(true), cm.getCursor(false));
            cm.setCursor(0);
        },
        "Ctrl-/": "toggleComment",
        "Ctrl-S": function() {
            saveProject();
            return false;
        },
        "F11": function(cm) {
            toggleFullscreen();
            return false;
        }
    }
};

// Inicializar editores al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    initializeEditors();
    setupEventListeners();
    updatePreview();
    
    // Configurar autoguardado
    if (autoSaveEnabled) {
        startAutoSave();
    }
});

/**
 * Inicializa los editores CodeMirror
 */
function initializeEditors() {
    // Editor HTML
    htmlEditor = CodeMirror.fromTextArea(document.getElementById('htmlCode'), {
        ...commonEditorOptions,
        mode: "htmlmixed",
        theme: "monokai",
        placeholder: "Escribe tu código HTML aquí..."
    });
    
    // Editor CSS
    cssEditor = CodeMirror.fromTextArea(document.getElementById('cssCode'), {
        ...commonEditorOptions,
        mode: "css",
        theme: "monokai",
        placeholder: "Escribe tu código CSS aquí..."
    });
    
    // Editor JavaScript
    jsEditor = CodeMirror.fromTextArea(document.getElementById('jsCode'), {
        ...commonEditorOptions,
        mode: "javascript",
        theme: "monokai",
        placeholder: "Escribe tu código JavaScript aquí..."
    });
    
    // Editor HTML para vista dividida
    splitHtmlEditor = CodeMirror.fromTextArea(document.getElementById('splitHtmlCode'), {
        ...commonEditorOptions,
        mode: "htmlmixed",
        theme: "monokai",
        placeholder: "Escribe tu código HTML aquí..."
    });
    
    // Sincronizar cambios entre editores principales y divididos
    htmlEditor.on('change', () => {
        const value = htmlEditor.getValue();
        if (splitHtmlEditor.getValue() !== value) {
            splitHtmlEditor.setValue(value);
        }
        updateCursorPosition(htmlEditor);
    });
    
    splitHtmlEditor.on('change', () => {
        const value = splitHtmlEditor.getValue();
        if (htmlEditor.getValue() !== value) {
            htmlEditor.setValue(value);
        }
        updateCursorPosition(splitHtmlEditor);
        updatePreview('split');
    });
    
    // Actualizar vista previa cuando cambia el código
    cssEditor.on('change', () => {
        updateCursorPosition(cssEditor);
    });
    
    jsEditor.on('change', () => {
        updateCursorPosition(jsEditor);
    });
    
    // Inicializar editores con los temas correctos
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    setEditorTheme(currentTheme === 'dark' ? 'monokai' : 'eclipse');
}

/**
 * Configura todos los event listeners
 */
function setupEventListeners() {
    // Cambiar entre archivos en la barra lateral
    document.querySelectorAll('.file-list .file').forEach(fileItem => {
        fileItem.addEventListener('click', () => {
            const fileType = fileItem.getAttribute('data-file');
            switchFile(fileType);
        });
    });
    
    // Cambiar entre pestañas (editor, vista previa, dividida)
    document.querySelectorAll('.tab-btn').forEach(tab => {
        tab.addEventListener('click', () => {
            const tabName = tab.getAttribute('data-tab');
            switchTab(tabName);
        });
    });
    
    // Botón de actualizar vista previa
    document.getElementById('refreshPreview').addEventListener('click', () => {
        updatePreview();
    });
    
    // Controles de tamaño de vista previa
    document.querySelectorAll('.resize-control').forEach(control => {
        control.addEventListener('click', () => {
            const size = control.getAttribute('data-size');
            setPreviewSize(size);
        });
    });
    
    // Botón de guardar proyecto
    document.getElementById('saveProject').addEventListener('click', saveProject);
    
    // Formulario de guardar proyecto (modal)
    document.getElementById('saveProjectForm').addEventListener('submit', (e) => {
        e.preventDefault();
        saveProjectToServer();
    });
    
    // Botón de descargar proyecto
    document.getElementById('downloadProject').addEventListener('click', downloadProject);
    
    // Cambiar tema del editor
    document.getElementById('editorTheme').addEventListener('change', (e) => {
        setEditorTheme(e.target.value);
    });
    
    // Botón de pantalla completa
    document.getElementById('toggleFullscreen').addEventListener('click', toggleFullscreen);
    
    // Botón de ayuda
    document.getElementById('showHelp').addEventListener('click', () => {
        showModal('helpModal');
    });
    
    // Enlaces de recursos
    document.querySelectorAll('.resource-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const resourceType = link.getAttribute('data-resource');
            showResource(resourceType);
        });
    });
    
    // Cerrar modales
    document.querySelectorAll('.modal-close, .modal-cancel').forEach(btn => {
        btn.addEventListener('click', () => {
            closeAllModals();
        });
    });
    
    // Cerrar modales al hacer clic en el fondo
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeAllModals();
            }
        });
    });
    
    // Si estamos en modo lección, configurar el botón de verificar ejercicio
    const checkExerciseBtn = document.getElementById('checkExercise');
    if (checkExerciseBtn) {
        checkExerciseBtn.addEventListener('click', checkExercise);
    }
    
    // Sincronizar cambios de título y descripción
    const projectTitle = document.getElementById('projectTitle');
    const projectDescription = document.getElementById('projectDescription');
    
    if (projectTitle) {
        projectTitle.addEventListener('input', () => {
            const modalProjectTitle = document.getElementById('modalProjectTitle');
            if (modalProjectTitle) {
                modalProjectTitle.value = projectTitle.value;
            }
        });
    }
    
    if (projectDescription) {
        projectDescription.addEventListener('input', () => {
            const modalProjectDescription = document.getElementById('modalProjectDescription');
            if (modalProjectDescription) {
                modalProjectDescription.value = projectDescription.value;
            }
        });
    }
}

/**
 * Cambia entre los diferentes archivos (HTML, CSS, JS)
 */
function switchFile(fileType) {
    // Desactivar todos los archivos y editores
    document.querySelectorAll('.file-list .file').forEach(item => {
        item.classList.remove('active');
    });
    
    document.querySelectorAll('.code-editor').forEach(editor => {
        editor.classList.remove('active');
    });
    
    // Activar el archivo y editor seleccionado
    document.querySelector(`.file-list .file[data-file="${fileType}"]`).classList.add('active');
    document.getElementById(`${fileType}Editor`).classList.add('active');
    
    currentFile = fileType;
    
    // Actualizar la vista dividida si está activa
    if (document.getElementById('splitPanel').classList.contains('active')) {
        switchSplitEditor(fileType);
    }
}

/**
 * Cambia el editor en la vista dividida
 */
function switchSplitEditor(fileType) {
    // Implementación futura para cambiar entre editores en vista dividida
    // Por ahora solo mostramos HTML en la vista dividida
}

/**
 * Cambia entre las pestañas de editor, vista previa y vista dividida
 */
function switchTab(tabName) {
    // Desactivar todas las pestañas y paneles
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    document.querySelectorAll('.editor-panel').forEach(panel => {
        panel.classList.remove('active');
    });
    
    // Activar la pestaña y panel seleccionado
    document.querySelector(`.tab-btn[data-tab="${tabName}"]`).classList.add('active');
    document.getElementById(`${tabName}Panel`).classList.add('active');
    
    // Si es vista previa o dividida, actualizar la previsualizacion
    if (tabName === 'preview' || tabName === 'split') {
        updatePreview(tabName);
    }
    
    // Actualizar la posición del cursor en el editor activo
    if (tabName === 'editor') {
        updateCursorPosition(getActiveEditor());
    } else if (tabName === 'split') {
        updateCursorPosition(splitHtmlEditor);
    }
}

/**
 * Actualiza la vista previa con el código actual
 */
function updatePreview(viewType = 'preview') {
    const htmlContent = htmlEditor.getValue();
    const cssContent = cssEditor.getValue();
    const jsContent = jsEditor.getValue();
    
    const previewFrame = viewType === 'split' 
        ? document.getElementById('splitPreviewFrame') 
        : document.getElementById('previewFrame');
    
    // Escribir el contenido en el iframe
    const frameContent = `
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
    
    const frame = previewFrame.contentWindow || previewFrame.contentDocument.document || previewFrame.contentDocument;
    frame.document.open();
    frame.document.write(frameContent);
    frame.document.close();
}

/**
 * Cambia el tamaño de la vista previa (móvil, tablet, escritorio)
 */
function setPreviewSize(size) {
    // Eliminar clases activas de los controles
    document.querySelectorAll('.resize-control').forEach(control => {
        control.classList.remove('active');
    });
    
    // Activar el control seleccionado
    document.querySelector(`.resize-control[data-size="${size}"]`).classList.add('active');
    
    // Aplicar clase de tamaño a los contenedores de vista previa
    const previewContainer = document.querySelector('.preview-container');
    const splitPreviewContainer = document.querySelector('.split-preview');
    
    // Eliminar clases de tamaño anteriores
    previewContainer.classList.remove('mobile', 'tablet');
    splitPreviewContainer.classList.remove('mobile', 'tablet');
    
    // Añadir clase de tamaño si no es desktop
    if (size !== 'desktop') {
        previewContainer.classList.add(size);
        splitPreviewContainer.classList.add(size);
    }
}

/**
 * Guarda el proyecto (muestra el modal de guardado)
 */
function saveProject() {
    const projectTitle = document.getElementById('projectTitle').value;
    const projectDescription = document.getElementById('projectDescription').value;
    
    // Rellenar el formulario del modal con los datos actuales
    document.getElementById('modalProjectTitle').value = projectTitle;
    document.getElementById('modalProjectDescription').value = projectDescription;
    
    // Si ya existe un ID de proyecto (estamos editando), usar visibilidad existente
    if (projectData && projectData.is_public !== undefined) {
        const visibilitySelect = document.getElementById('projectVisibility');
        visibilitySelect.value = projectData.is_public ? 'public' : 'private';
    }
    
    // Mostrar el modal de guardado
    showModal('saveProjectModal');
}

/**
 * Guarda el proyecto en el servidor
 */
function saveProjectToServer() {
    const title = document.getElementById('modalProjectTitle').value;
    const description = document.getElementById('modalProjectDescription').value;
    const isPublic = document.getElementById('projectVisibility').value === 'public';
    
    // Obtener el contenido actual de los editores
    const htmlContent = htmlEditor.getValue();
    const cssContent = cssEditor.getValue();
    const jsContent = jsEditor.getValue();
    
    // Validar que al menos haya un título
    if (!title.trim()) {
        alert('Por favor, ingresa un título para tu proyecto.');
        return;
    }
    
    // Datos a enviar al servidor
    const projectData = {
        title,
        description,
        html_content: htmlContent,
        css_content: cssContent,
        js_content: jsContent,
        is_public: isPublic,
        project_id: window.projectData ? window.projectData.project_id : null
    };
    
    // Mostrar indicador de guardado
    const saveBtn = document.querySelector('#saveProjectForm button[type="submit"]');
    const originalBtnText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    saveBtn.disabled = true;
    
    // Enviar datos al servidor
    fetch('../includes/ajax/save_project.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(projectData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cerrar el modal
            closeAllModals();
            
            // Actualizar la UI
            document.getElementById('projectTitle').value = title;
            document.getElementById('projectDescription').value = description;
            
            // Actualizar timestamp de último guardado
            lastSaved = new Date();
            updateLastSavedStatus();
            
            // Si es un proyecto nuevo, redirigir a la URL con el ID
            if (!window.projectData && data.project_id) {
                window.location.href = `index.php?project_id=${data.project_id}`;
            } else {
                // Mostrar mensaje de éxito
                showNotification('Proyecto guardado correctamente', 'success');
            }
        } else {
            throw new Error(data.message || 'Error al guardar el proyecto');
        }
    })
    .catch(error => {
        console.error('Error al guardar el proyecto:', error);
        showNotification('Error al guardar el proyecto: ' + error.message, 'error');
    })
    .finally(() => {
        // Restaurar el botón
        saveBtn.innerHTML = originalBtnText;
        saveBtn.disabled = false;
    });
}

/**
 * Descarga el proyecto como archivo ZIP
 */
function downloadProject() {
    // Esta función requeriría una librería para crear ZIPs en el cliente
    // o usar un endpoint en el servidor para crear el ZIP
    
    // Por ahora, simplemente descargamos los archivos individuales
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
 * Auxiliar para descargar un archivo
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
 * Cambia el tema del editor
 */
function setEditorTheme(theme) {
    // Actualizar los editores con el nuevo tema
    htmlEditor.setOption('theme', theme);
    cssEditor.setOption('theme', theme);
    jsEditor.setOption('theme', theme);
    splitHtmlEditor.setOption('theme', theme);
    
    // Actualizar el selector de tema
    document.getElementById('editorTheme').value = theme;
}

/**
 * Actualiza la posición del cursor en la barra de estado
 */
function updateCursorPosition(editor) {
    if (!editor) return;
    
    const cursor = editor.getCursor();
    const line = cursor.line + 1;
    const ch = cursor.ch + 1;
    
    document.getElementById('cursorPosition').textContent = `Línea: ${line}, Columna: ${ch}`;
}

/**
 * Alterna pantalla completa del editor
 */
function toggleFullscreen() {
    const editorElement = document.documentElement;
    
    if (!document.fullscreenElement) {
        if (editorElement.requestFullscreen) {
            editorElement.requestFullscreen();
        } else if (editorElement.mozRequestFullScreen) {
            editorElement.mozRequestFullScreen();
        } else if (editorElement.webkitRequestFullscreen) {
            editorElement.webkitRequestFullscreen();
        } else if (editorElement.msRequestFullscreen) {
            editorElement.msRequestFullscreen();
        }
        
        document.getElementById('toggleFullscreen').innerHTML = '<i class="fas fa-compress"></i>';
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
        
        document.getElementById('toggleFullscreen').innerHTML = '<i class="fas fa-expand"></i>';
    }
}

/**
 * Muestra un modal por su ID
 */
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
    }
}

/**
 * Cierra todos los modales
 */
function closeAllModals() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.style.display = 'none';
    });
}

/**
 * Muestra un recurso en el modal de recursos
 */
function showResource(resourceType) {
    const resourceTitle = document.getElementById('resourceTitle');
    const resourceContent = document.getElementById('resourceContent');
    
    let title = 'Recurso';
    let content = '<p>Contenido no disponible.</p>';
    
    switch (resourceType) {
        case 'html-cheatsheet':
            title = 'HTML Cheatsheet';
            content = `
                <h3>Estructura básica</h3>
                <pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="es"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;Título de la página&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;!-- Contenido aquí --&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
                
                <h3>Elementos de texto</h3>
                <ul>
                    <li><code>&lt;h1&gt;</code> a <code>&lt;h6&gt;</code> - Encabezados</li>
                    <li><code>&lt;p&gt;</code> - Párrafo</li>
                    <li><code>&lt;br&gt;</code> - Salto de línea</li>
                    <li><code>&lt;hr&gt;</code> - Línea horizontal</li>
                    <li><code>&lt;strong&gt;</code> - Texto fuerte (negrita)</li>
                    <li><code>&lt;em&gt;</code> - Énfasis (cursiva)</li>
                </ul>
                
                <h3>Listas</h3>
                <pre><code>&lt;!-- Lista no ordenada --&gt;
&lt;ul&gt;
    &lt;li&gt;Elemento 1&lt;/li&gt;
    &lt;li&gt;Elemento 2&lt;/li&gt;
&lt;/ul&gt;

&lt;!-- Lista ordenada --&gt;
&lt;ol&gt;
    &lt;li&gt;Primer elemento&lt;/li&gt;
    &lt;li&gt;Segundo elemento&lt;/li&gt;
&lt;/ol&gt;</code></pre>
                
                <h3>Enlaces e imágenes</h3>
                <pre><code>&lt;!-- Enlace --&gt;
&lt;a href="https://ejemplo.com"&gt;Texto del enlace&lt;/a&gt;

&lt;!-- Imagen --&gt;
&lt;img src="ruta/a/imagen.jpg" alt="Texto alternativo"&gt;</code></pre>
                
                <h3>Elementos semánticos</h3>
                <ul>
                    <li><code>&lt;header&gt;</code> - Encabezado de página o sección</li>
                    <li><code>&lt;nav&gt;</code> - Navegación</li>
                    <li><code>&lt;main&gt;</code> - Contenido principal</li>
                    <li><code>&lt;section&gt;</code> - Sección de contenido</li>
                    <li><code>&lt;article&gt;</code> - Contenido independiente</li>
                    <li><code>&lt;aside&gt;</code> - Contenido relacionado</li>
                    <li><code>&lt;footer&gt;</code> - Pie de página</li>
                </ul>
            `;
            break;
            
        case 'css-cheatsheet':
            title = 'CSS Cheatsheet';
            content = `
                <h3>Selectores básicos</h3>
                <pre><code>/* Selector de elemento */
p {
    color: blue;
}

/* Selector de clase */
.mi-clase {
    font-size: 16px;
}

/* Selector de ID */
#mi-id {
    background-color: yellow;
}

/* Selector de atributo */
[type="text"] {
    border: 1px solid gray;
}

/* Selector descendiente */
div p {
    margin: 10px;
}

/* Selector hijo directo */
ul > li {
    list-style: square;
}</code></pre>
                
                <h3>Modelo de caja</h3>
                <pre><code>/* Modelo de caja completo */
.elemento {
    /* Contenido */
    width: 200px;
    height: 100px;
    
    /* Relleno interno */
    padding: 20px;
    /* O por lados: padding-top, padding-right, padding-bottom, padding-left */
    
    /* Bordes */
    border: 1px solid black;
    /* O específico: border-width, border-style, border-color */
    
    /* Margen externo */
    margin: 10px;
    /* O por lados: margin-top, margin-right, margin-bottom, margin-left */
    
    /* Cambiar el modelo de caja */
    box-sizing: border-box; /* Incluye padding y border en width/height */
}</code></pre>
                
                <h3>Flexbox</h3>
                <pre><code>/* Contenedor flex */
.contenedor {
    display: flex;
    flex-direction: row; /* o column, row-reverse, column-reverse */
    justify-content: space-between; /* o flex-start, flex-end, center, space-around */
    align-items: center; /* o flex-start, flex-end, stretch, baseline */
    flex-wrap: wrap; /* o nowrap, wrap-reverse */
}

/* Elementos flex */
.item {
    flex: 1; /* shorthand para flex-grow, flex-shrink, flex-basis */
    order: 2; /* cambia el orden de aparición */
    align-self: flex-start; /* sobreescribe align-items para este elemento */
}</code></pre>
                
                <h3>Grid</h3>
                <pre><code>/* Contenedor grid */
.grid {
    display: grid;
    grid-template-columns: 1fr 2fr 1fr; /* tres columnas */
    grid-template-rows: 100px auto; /* dos filas */
    gap: 10px; /* espacio entre elementos */
}

/* Elemento grid */
.grid-item {
    grid-column: 1 / 3; /* desde línea 1 hasta línea 3 */
    grid-row: 2; /* en la segunda fila */
}</code></pre>
                
                <h3>Media Queries</h3>
                <pre><code>/* Responsive design */
@media (max-width: 768px) {
    /* Estilos para pantallas menores a 768px */
    .contenedor {
        flex-direction: column;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    /* Estilos para tablets */
}</code></pre>
            `;
            break;
            
        case 'js-cheatsheet':
            title = 'JavaScript Cheatsheet';
            content = `
                <h3>Variables y tipos de datos</h3>
                <pre><code>// Declaración de variables
let nombre = "Juan"; // String
const edad = 25;     // Number
var activo = true;   // Boolean

// Arrays
let colores = ["rojo", "verde", "azul"];

// Objetos
let persona = {
    nombre: "María",
    edad: 30,
    saludar: function() {
        console.log("Hola, soy " + this.nombre);
    }
};</code></pre>
                
                <h3>Operadores</h3>
                <pre><code>// Aritméticos
let suma = 5 + 3;       // 8
let resta = 10 - 4;     // 6
let multiplicacion = 3 * 4; // 12
let division = 8 / 2;   // 4
let modulo = 7 % 3;     // 1 (resto de la división)

// Comparación
let igual = 5 == "5";        // true (compara valor)
let estrictamenteIgual = 5 === "5"; // false (compara valor y tipo)
let mayor = 7 > 3;           // true
let menorIgual = 4 <= 4;     // true</code></pre>
                
                <h3>Condicionales</h3>
                <pre><code>// if, else if, else
if (edad < 18) {
    console.log("Menor de edad");
} else if (edad >= 18 && edad < 65) {
    console.log("Adulto");
} else {
    console.log("Adulto mayor");
}

// Operador ternario
let mensaje = edad >= 18 ? "Adulto" : "Menor";</code></pre>
                
                <h3>Bucles</h3>
                <pre><code>// for
for (let i = 0; i < 5; i++) {
    console.log(i); // 0, 1, 2, 3, 4
}

// while
let contador = 0;
while (contador < 3) {
    console.log(contador); // 0, 1, 2
    contador++;
}

// for...of (iteración de arrays)
for (let color of colores) {
    console.log(color); // "rojo", "verde", "azul"
}

// for...in (iteración de propiedades de objetos)
for (let prop in persona) {
    console.log(prop + ": " + persona[prop]);
}</code></pre>
                
                <h3>Funciones</h3>
                <pre><code>// Declaración de función
function sumar(a, b) {
    return a + b;
}

// Expresión de función
const multiplicar = function(a, b) {
    return a * b;
};

// Arrow function
const dividir = (a, b) => a / b;</code></pre>
                
                <h3>Manipulación del DOM</h3>
                <pre><code>// Seleccionar elementos
const elemento = document.getElementById("miElemento");
const parrafos = document.getElementsByTagName("p");
const botones = document.getElementsByClassName("btn");
const enlaces = document.querySelectorAll("a.link");

// Modificar contenido
elemento.textContent = "Nuevo texto";
elemento.innerHTML = "<strong>HTML</strong> nuevo";

// Modificar estilos
elemento.style.color = "red";
elemento.style.fontSize = "18px";

// Añadir/quitar clases
elemento.classList.add("destacado");
elemento.classList.remove("oculto");
elemento.classList.toggle("seleccionado");

// Eventos
elemento.addEventListener("click", function(evento) {
    console.log("Elemento clickeado");
});</code></pre>
            `;
            break;
    }
    
    // Actualizar el modal y mostrarlo
    resourceTitle.textContent = title;
    resourceContent.innerHTML = content;
    showModal('resourceModal');
}

/**
 * Inicia el autoguardado
 */
function startAutoSave() {
    // Guardar cada 30 segundos
    autoSaveTimer = setInterval(() => {
        // Si el proyecto tiene ID (ya está guardado en el servidor)
        if (window.projectData && window.projectData.project_id) {
            saveProjectToLocalStorage();
            
            // Actualizar la UI
            updateLastSavedStatus();
        }
    }, 30000);
    
    // Actualizar indicador de estado
    document.querySelector('#autoSaveStatus .status-indicator').textContent = 'Activado';
    document.querySelector('#autoSaveStatus .status-indicator').style.color = 'var(--success-color)';
}

/**
 * Detiene el autoguardado
 */
function stopAutoSave() {
    if (autoSaveTimer) {
        clearInterval(autoSaveTimer);
        autoSaveTimer = null;
    }
    
    // Actualizar indicador de estado
    document.querySelector('#autoSaveStatus .status-indicator').textContent = 'Desactivado';
    document.querySelector('#autoSaveStatus .status-indicator').style.color = 'var(--error-color)';
}

/**
 * Guarda el proyecto en localStorage
 */
function saveProjectToLocalStorage() {
    // Solo guardamos localmente si no hay función de guardado en servidor
    // O como respaldo mientras desarrollamos
    
    const projectData = {
        title: document.getElementById('projectTitle').value,
        description: document.getElementById('projectDescription').value,
        html_content: htmlEditor.getValue(),
        css_content: cssEditor.getValue(),
        js_content: jsEditor.getValue(),
        last_saved: new Date().toISOString()
    };
    
    try {
        localStorage.setItem('webcraft_project_backup', JSON.stringify(projectData));
        lastSaved = new Date();
    } catch (error) {
        console.error('Error al guardar en localStorage:', error);
    }
}

/**
 * Actualiza el estado de la última vez guardado
 */
function updateLastSavedStatus() {
    const lastSavedElement = document.getElementById('lastSaved');
    
    if (lastSaved) {
        const timeAgo = getTimeAgo(lastSaved);
        lastSavedElement.textContent = `Último guardado: ${timeAgo}`;
    } else {
        lastSavedElement.textContent = 'Último guardado: Nunca';
    }
}

/**
 * Obtiene un texto descriptivo de cuánto tiempo ha pasado
 */
function getTimeAgo(date) {
    const now = new Date();
    const diffMs = now - date;
    const diffSec = Math.floor(diffMs / 1000);
    
    if (diffSec < 60) {
        return 'hace menos de un minuto';
    }
    
    const diffMin = Math.floor(diffSec / 60);
    if (diffMin < 60) {
        return `hace ${diffMin} ${diffMin === 1 ? 'minuto' : 'minutos'}`;
    }
    
    const diffHour = Math.floor(diffMin / 60);
    if (diffHour < 24) {
        return `hace ${diffHour} ${diffHour === 1 ? 'hora' : 'horas'}`;
    }
    
    const diffDay = Math.floor(diffHour / 24);
    return `hace ${diffDay} ${diffDay === 1 ? 'día' : 'días'}`;
}

/**
 * Muestra una notificación al usuario
 */
function showNotification(message, type = 'info') {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas ${getIconForNotificationType(type)}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close">&times;</button>
    `;
    
    // Añadir al DOM
    document.body.appendChild(notification);
    
    // Mostrar con animación
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Configurar cierre automático
    const autoCloseTimeout = setTimeout(() => {
        closeNotification(notification);
    }, 5000);
    
    // Configurar cierre manual
    notification.querySelector('.notification-close').addEventListener('click', () => {
        clearTimeout(autoCloseTimeout);
        closeNotification(notification);
    });
}

/**
 * Cierra una notificación con animación
 */
function closeNotification(notification) {
    notification.classList.remove('show');
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 300);
}

/**
 * Retorna el icono apropiado para el tipo de notificación
 */
function getIconForNotificationType(type) {
    switch (type) {
        case 'success': return 'fa-check-circle';
        case 'error': return 'fa-exclamation-circle';
        case 'warning': return 'fa-exclamation-triangle';
        default: return 'fa-info-circle';
    }
}

/**
 * Obtiene el editor actualmente activo
 */
function getActiveEditor() {
    switch (currentFile) {
        case 'html': return htmlEditor;
        case 'css': return cssEditor;
        case 'js': return jsEditor;
        default: return htmlEditor;
    }
}

/**
 * Verifica el ejercicio en modo lección
 */
function checkExercise() {
    // Esta función se implementará según las reglas de verificación específicas de cada lección
    if (!window.lessonId) return;
    
    // Por ahora mostramos un mensaje de éxito genérico
    showNotification('¡Ejercicio completado correctamente!', 'success');
    
    // En una implementación real, enviaríamos el código al servidor para verificación
    // y actualizaríamos el progreso del usuario
    const htmlContent = htmlEditor.getValue();
    const cssContent = cssEditor.getValue();
    const jsContent = jsEditor.getValue();
    
    console.log('Verificando ejercicio de lección ID:', window.lessonId);
    
    // Aquí iría la lógica para enviar al servidor
    // fetch('../includes/ajax/check_exercise.php', {...})
}

// Estilos CSS para notificaciones
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 15px;
        background-color: white;
        border-left: 4px solid #2196F3;
        border-radius: 4px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        display: flex;
        align-items: center;
        justify-content: space-between;
        max-width: 350px;
        z-index: 9999;
        transform: translateX(120%);
        transition: transform 0.3s ease;
    }
    
    .notification.show {
        transform: translateX(0);
    }
    
    .notification-content {
        display: flex;
        align-items: center;
    }
    
    .notification-content i {
        margin-right: 10px;
        font-size: 18px;
    }
    
    .notification-success {
        border-left-color: #4CAF50;
    }
    
    .notification-error {
        border-left-color: #F44336;
    }
    
    .notification-warning {
        border-left-color: #FFC107;
    }
    
    .notification-success i {
        color: #4CAF50;
    }
    
    .notification-error i {
        color: #F44336;
    }
    
    .notification-warning i {
        color: #FFC107;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: #999;
        cursor: pointer;
        font-size: 18px;
        padding: 0;
        margin-left: 10px;
    }
    
    .notification-close:hover {
        color: #333;
    }
    
    [data-theme="dark"] .notification {
        background-color: #333;
        color: #eee;
    }
    
    [data-theme="dark"] .notification-close:hover {
        color: #fff;
    }
`;

document.head.appendChild(notificationStyles);
