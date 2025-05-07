/**
 * Navegación de módulos para WebCraft Academy
 * 
 * Este componente proporciona una navegación interactiva entre los módulos
 * educativos de la plataforma.
 */

// Crear espacio de nombres para WebCraft si no existe
if (typeof WebCraft === 'undefined') {
    WebCraft = {};
}

// Componente de navegación de módulos
WebCraft.ModuleNavigation = (function() {
    // Almacenar estado del componente
    const state = {
        modules: [],
        currentModuleId: null,
        currentLessonId: null,
        expanded: true,
        container: null,
        onModuleSelect: null,
        onLessonSelect: null
    };
    
    /**
     * Inicializar el componente
     * @param {object} options - Opciones de configuración
     * @param {string} options.containerId - ID del contenedor donde se renderizará la navegación
     * @param {Function} options.onModuleSelect - Callback cuando se selecciona un módulo
     * @param {Function} options.onLessonSelect - Callback cuando se selecciona una lección
     * @param {boolean} options.expanded - Si debe iniciar expandido
     */
    function init(options = {}) {
        // Guardar opciones en el estado
        state.onModuleSelect = options.onModuleSelect || function() {};
        state.onLessonSelect = options.onLessonSelect || function() {};
        state.expanded = options.expanded !== false;
        
        // Obtener contenedor
        state.container = document.getElementById(options.containerId);
        
        if (!state.container) {
            console.error('Contenedor no encontrado:', options.containerId);
            return;
        }
        
        // Agregar clase al contenedor
        state.container.classList.add('module-navigation');
        
        // Cargar datos de módulos
        loadModules();
    }
    
    /**
     * Cargar datos de módulos desde el servidor
     */
    function loadModules() {
        // Mostrar indicador de carga
        state.container.innerHTML = '<div class="module-nav-loading"><i class="fas fa-spinner fa-spin"></i> Cargando módulos...</div>';
        
        // Realizar petición AJAX
        fetch('includes/ajax/get_modules.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    state.modules = data.modules;
                    render();
                } else {
                    state.container.innerHTML = `<div class="module-nav-error"><i class="fas fa-exclamation-circle"></i> ${data.message || 'Error al cargar los módulos'}</div>`;
                }
            })
            .catch(error => {
                console.error('Error al cargar módulos:', error);
                state.container.innerHTML = '<div class="module-nav-error"><i class="fas fa-exclamation-circle"></i> Error al cargar los módulos</div>';
            });
    }
    
    /**
     * Renderizar la navegación de módulos
     */
    function render() {
        // Limpiar contenedor
        state.container.innerHTML = '';
        
        // Crear encabezado
        const header = document.createElement('div');
        header.className = 'module-nav-header';
        
        const title = document.createElement('h3');
        title.className = 'module-nav-title';
        title.textContent = 'Módulos de Aprendizaje';
        
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'module-nav-toggle';
        toggleBtn.innerHTML = state.expanded ? '<i class="fas fa-chevron-up"></i>' : '<i class="fas fa-chevron-down"></i>';
        toggleBtn.setAttribute('aria-label', state.expanded ? 'Colapsar navegación' : 'Expandir navegación');
        toggleBtn.addEventListener('click', toggleExpanded);
        
        header.appendChild(title);
        header.appendChild(toggleBtn);
        state.container.appendChild(header);
        
        // Crear contenedor de lista
        const listContainer = document.createElement('div');
        listContainer.className = 'module-nav-list-container';
        listContainer.style.display = state.expanded ? 'block' : 'none';
        
        // Verificar si hay módulos
        if (state.modules.length === 0) {
            const emptyMessage = document.createElement('div');
            emptyMessage.className = 'module-nav-empty';
            emptyMessage.innerHTML = '<i class="fas fa-info-circle"></i> No hay módulos disponibles';
            listContainer.appendChild(emptyMessage);
        } else {
            // Crear lista de módulos
            const moduleList = document.createElement('ul');
            moduleList.className = 'module-nav-list';
            
            state.modules.forEach(module => {
                // Elemento de módulo
                const moduleItem = document.createElement('li');
                moduleItem.className = 'module-nav-item';
                if (module.module_id === state.currentModuleId) {
                    moduleItem.classList.add('active');
                }
                
                // Encabezado del módulo (clickeable)
                const moduleHeader = document.createElement('div');
                moduleHeader.className = 'module-nav-module-header';
                moduleHeader.addEventListener('click', () => selectModule(module.module_id));
                
                // Icono del módulo
                const moduleIcon = document.createElement('i');
                moduleIcon.className = module.icon || 'fas fa-book';
                
                // Título del módulo
                const moduleTitle = document.createElement('span');
                moduleTitle.className = 'module-nav-module-title';
                moduleTitle.textContent = module.title;
                
                // Progreso del módulo
                const moduleProgress = document.createElement('div');
                moduleProgress.className = 'module-nav-progress';
                
                const progressBar = document.createElement('div');
                progressBar.className = 'module-nav-progress-bar';
                
                const progressFill = document.createElement('div');
                progressFill.className = 'module-nav-progress-fill';
                progressFill.style.width = `${calculateProgress(module)}%`;
                
                progressBar.appendChild(progressFill);
                moduleProgress.appendChild(progressBar);
                
                // Armar encabezado del módulo
                moduleHeader.appendChild(moduleIcon);
                moduleHeader.appendChild(moduleTitle);
                moduleHeader.appendChild(moduleProgress);
                
                // Expandir/colapsar lecciones
                const expandBtn = document.createElement('button');
                expandBtn.className = 'module-nav-expand';
                expandBtn.innerHTML = module.expanded ? '<i class="fas fa-chevron-down"></i>' : '<i class="fas fa-chevron-right"></i>';
                expandBtn.setAttribute('aria-label', module.expanded ? 'Colapsar lecciones' : 'Expandir lecciones');
                expandBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleModuleLessons(module.module_id);
                });
                
                moduleHeader.appendChild(expandBtn);
                moduleItem.appendChild(moduleHeader);
                
                // Lista de lecciones (si tiene)
                if (module.lessons && module.lessons.length > 0) {
                    const lessonsList = document.createElement('ul');
                    lessonsList.className = 'module-nav-lessons-list';
                    lessonsList.style.display = module.expanded ? 'block' : 'none';
                    
                    module.lessons.forEach(lesson => {
                        const lessonItem = document.createElement('li');
                        lessonItem.className = 'module-nav-lesson';
                        if (lesson.lesson_id === state.currentLessonId) {
                            lessonItem.classList.add('active');
                        }
                        if (lesson.completed) {
                            lessonItem.classList.add('completed');
                        }
                        
                        const lessonLink = document.createElement('a');
                        lessonLink.className = 'module-nav-lesson-link';
                        lessonLink.href = `index.php?page=lessons&lesson_id=${lesson.lesson_id}`;
                        lessonLink.textContent = lesson.title;
                        lessonLink.addEventListener('click', (e) => {
                            e.preventDefault();
                            selectLesson(lesson.lesson_id);
                        });
                        
                        const lessonStatus = document.createElement('span');
                        lessonStatus.className = 'module-nav-lesson-status';
                        lessonStatus.innerHTML = lesson.completed ? 
                            '<i class="fas fa-check-circle"></i>' : 
                            '<i class="far fa-circle"></i>';
                        
                        lessonItem.appendChild(lessonStatus);
                        lessonItem.appendChild(lessonLink);
                        lessonsList.appendChild(lessonItem);
                    });
                    
                    moduleItem.appendChild(lessonsList);
                }
                
                moduleList.appendChild(moduleItem);
            });
            
            listContainer.appendChild(moduleList);
        }
        
        state.container.appendChild(listContainer);
    }
    
    /**
     * Calcular el porcentaje de progreso de un módulo
     * @param {object} module - Objeto del módulo
     * @return {number} Porcentaje de progreso
     */
    function calculateProgress(module) {
        if (!module.lessons || module.lessons.length === 0) {
            return 0;
        }
        
        const completedLessons = module.lessons.filter(lesson => lesson.completed).length;
        return Math.round((completedLessons / module.lessons.length) * 100);
    }
    
    /**
     * Alternar estado de expansión de la navegación
     */
    function toggleExpanded() {
        state.expanded = !state.expanded;
        render();
    }
    
    /**
     * Alternar visibilidad de lecciones de un módulo
     * @param {number} moduleId - ID del módulo
     */
    function toggleModuleLessons(moduleId) {
        state.modules = state.modules.map(module => {
            if (module.module_id === moduleId) {
                return {
                    ...module,
                    expanded: !module.expanded
                };
            }
            return module;
        });
        
        render();
    }
    
    /**
     * Seleccionar un módulo
     * @param {number} moduleId - ID del módulo
     */
    function selectModule(moduleId) {
        state.currentModuleId = moduleId;
        
        // Expandir módulo seleccionado y colapsar otros
        state.modules = state.modules.map(module => ({
            ...module,
            expanded: module.module_id === moduleId
        }));
        
        render();
        
        // Llamar al callback
        if (typeof state.onModuleSelect === 'function') {
            const selectedModule = state.modules.find(m => m.module_id === moduleId);
            state.onModuleSelect(selectedModule);
        }
    }
    
    /**
     * Seleccionar una lección
     * @param {number} lessonId - ID de la lección
     */
    function selectLesson(lessonId) {
        state.currentLessonId = lessonId;
        
        render();
        
        // Llamar al callback
        if (typeof state.onLessonSelect === 'function') {
            let selectedLesson = null;
            let selectedModule = null;
            
            for (const module of state.modules) {
                if (module.lessons) {
                    const lesson = module.lessons.find(l => l.lesson_id === lessonId);
                    if (lesson) {
                        selectedLesson = lesson;
                        selectedModule = module;
                        break;
                    }
                }
            }
            
            if (selectedLesson && selectedModule) {
                state.onLessonSelect(selectedLesson, selectedModule);
            }
        }
    }
    
    /**
     * Actualizar datos de módulos
     * @param {Array} modules - Nuevos datos de módulos
     */
    function updateModules(modules) {
        state.modules = modules;
        render();
    }
    
    /**
     * Marcar una lección como completada
     * @param {number} lessonId - ID de la lección
     * @param {boolean} completed - Estado de completado
     */
    function markLessonCompleted(lessonId, completed = true) {
        // Actualizar estado local
        state.modules = state.modules.map(module => {
            if (module.lessons) {
                const updatedLessons = module.lessons.map(lesson => {
                    if (lesson.lesson_id === lessonId) {
                        return {
                            ...lesson,
                            completed: completed
                        };
                    }
                    return lesson;
                });
                
                return {
                    ...module,
                    lessons: updatedLessons
                };
            }
            return module;
        });
        
        render();
        
        // Enviar actualización al servidor
        fetch('includes/ajax/update_lesson_progress.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                lesson_id: lessonId,
                completed: completed
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Error al actualizar progreso:', data.message);
            }
        })
        .catch(error => {
            console.error('Error al actualizar progreso:', error);
        });
    }
    
    // Exponer API pública
    return {
        init: init,
        updateModules: updateModules,
        selectModule: selectModule,
        selectLesson: selectLesson,
        markLessonCompleted: markLessonCompleted
    };
})();
