/**
 * JavaScript para las páginas de lecciones de WebCraft Academy
 * 
 * Este archivo maneja la funcionalidad interactiva específica para las lecciones.
 */

// Crear espacio de nombres para WebCraft si no existe
if (typeof WebCraft === 'undefined') {
    WebCraft = {};
}

// Inicialización específica para la página de lecciones
WebCraft.initLessonsPage = function() {
    // Inicializar elementos de código
    WebCraft.initCodeBlocks();
    
    // Inicializar mensajes de completado
    WebCraft.initCompletionMessages();
    
    // Inicializar trackeo de tiempo
    WebCraft.initTimeTracking();
    
    // Inicializar navegación con teclado
    WebCraft.initKeyboardNavigation();
    
    // Inicializar anclas de tabla de contenidos
    WebCraft.initTableOfContents();
    
    // Aplicar sintaxis highlighting si CodeMirror está disponible
    if (typeof CodeMirror !== 'undefined') {
        WebCraft.applySyntaxHighlighting();
    }
};

// Inicializar bloques de código con funcionalidad de copiar
WebCraft.initCodeBlocks = function() {
    const codeBlocks = document.querySelectorAll('.lesson-body pre code');
    
    codeBlocks.forEach(block => {
        // Crear botón de copiar
        const copyButton = document.createElement('button');
        copyButton.className = 'copy-code-button';
        copyButton.innerHTML = '<i class="fas fa-copy"></i>';
        copyButton.setAttribute('aria-label', 'Copiar código');
        copyButton.setAttribute('title', 'Copiar código');
        
        // Encontrar el contenedor pre
        const preBlock = block.parentElement;
        
        // Hacer que el contenedor pre sea relativo para posicionar el botón
        preBlock.style.position = 'relative';
        
        // Agregar el botón
        preBlock.appendChild(copyButton);
        
        // Agregar evento al botón
        copyButton.addEventListener('click', function() {
            // Crear elemento textarea temporal
            const textArea = document.createElement('textarea');
            textArea.value = block.textContent;
            
            // Hacer invisible el textarea y agregarlo al DOM
            textArea.style.position = 'absolute';
            textArea.style.left = '-9999px';
            document.body.appendChild(textArea);
            
            // Seleccionar y copiar el texto
            textArea.select();
            document.execCommand('copy');
            
            // Eliminar el textarea
            document.body.removeChild(textArea);
            
            // Cambiar texto del botón temporalmente
            copyButton.innerHTML = '<i class="fas fa-check"></i>';
            
            // Volver al texto original después de 2 segundos
            setTimeout(() => {
                copyButton.innerHTML = '<i class="fas fa-copy"></i>';
            }, 2000);
        });
    });
};

// Inicializar mensajes de completado
WebCraft.initCompletionMessages = function() {
    const completionMessage = document.querySelector('.completion-message');
    const closeButton = document.querySelector('.close-message');
    
    if (completionMessage && closeButton) {
        closeButton.addEventListener('click', function() {
            WebCraft.closeCompletionMessage(completionMessage);
        });
        
        // Auto-cerrar después de 5 segundos
        setTimeout(() => {
            WebCraft.closeCompletionMessage(completionMessage);
        }, 5000);
    }
};

// Cerrar mensaje de completado con animación
WebCraft.closeCompletionMessage = function(message) {
    message.style.opacity = '0';
    setTimeout(() => {
        message.style.display = 'none';
    }, 300);
};

// Inicializar trackeo de tiempo
WebCraft.initTimeTracking = function() {
    // Almacenar tiempo de inicio
    WebCraft.lessonStartTime = new Date();
    
    // Actualizar tiempo regularmente
    WebCraft.timeInterval = setInterval(() => {
        const timeSpent = Math.floor((new Date() - WebCraft.lessonStartTime) / 1000); // Tiempo en segundos
        
        // Actualizar tiempo en formulario si existe
        const timeInput = document.querySelector('input[name="time_spent"]');
        if (timeInput) {
            timeInput.value = timeSpent;
        }
    }, 5000); // Actualizar cada 5 segundos
    
    // Limpiar intervalo al salir de la página
    window.addEventListener('beforeunload', () => {
        clearInterval(WebCraft.timeInterval);
    });
};

// Inicializar navegación con teclado
WebCraft.initKeyboardNavigation = function() {
    // Obtener enlaces de navegación
    const prevLink = document.querySelector('.lesson-prev .nav-link');
    const nextLink = document.querySelector('.lesson-next .nav-link');
    
    // Agregar eventos de teclado
    document.addEventListener('keydown', function(e) {
        // Alt + Flecha izquierda para la lección anterior
        if (e.altKey && e.key === 'ArrowLeft' && prevLink) {
            window.location.href = prevLink.getAttribute('href');
        }
        
        // Alt + Flecha derecha para la siguiente lección
        if (e.altKey && e.key === 'ArrowRight' && nextLink) {
            window.location.href = nextLink.getAttribute('href');
        }
    });
};

// Inicializar tabla de contenidos
WebCraft.initTableOfContents = function() {
    const lessonBody = document.querySelector('.lesson-body');
    const tocContainer = document.querySelector('.table-of-contents');
    
    if (lessonBody && tocContainer) {
        // Obtener todos los encabezados
        const headings = lessonBody.querySelectorAll('h2, h3, h4');
        
        if (headings.length > 0) {
            // Crear lista
            const toc = document.createElement('ul');
            toc.className = 'toc-list';
            
            headings.forEach((heading, index) => {
                // Asignar id al encabezado si no tiene
                if (!heading.id) {
                    heading.id = 'heading-' + index;
                }
                
                // Crear elemento de lista
                const listItem = document.createElement('li');
                listItem.className = 'toc-item toc-' + heading.tagName.toLowerCase();
                
                // Crear enlace
                const link = document.createElement('a');
                link.href = '#' + heading.id;
                link.textContent = heading.textContent;
                
                // Agregar enlace a elemento de lista
                listItem.appendChild(link);
                
                // Agregar elemento de lista a TOC
                toc.appendChild(listItem);
            });
            
            // Agregar TOC al contenedor
            tocContainer.appendChild(toc);
        }
    }
};

// Aplicar syntax highlighting
WebCraft.applySyntaxHighlighting = function() {
    // Obtener todos los bloques de código
    const codeBlocks = document.querySelectorAll('.lesson-body pre code');
    
    codeBlocks.forEach(block => {
        // Detectar lenguaje de programación
        const language = block.className.replace('language-', '');
        
        // Obtener el contenedor pre
        const preBlock = block.parentElement;
        
        // Obtener el código
        const code = block.textContent;
        
        // Crear editor de solo lectura
        const editor = CodeMirror(function(elt) {
            // Reemplazar el bloque pre con el editor
            preBlock.parentNode.replaceChild(elt, preBlock);
        }, {
            value: code,
            mode: language,
            theme: 'default',
            lineNumbers: true,
            readOnly: true
        });
    });
};

// Registrar inicialización
document.addEventListener('DOMContentLoaded', function() {
    // La inicialización se llamará desde main.js
});
