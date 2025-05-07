/**
 * JavaScript principal para WebCraft Academy
 * 
 * Este archivo inicializa la funcionalidad común para todas las páginas
 * y carga los componentes necesarios.
 */

// Crear espacio de nombres para WebCraft si no existe
if (typeof WebCraft === 'undefined') {
    WebCraft = {};
}

// Inicialización principal
WebCraft.init = function() {
    // Detectar tema
    WebCraft.initTheme();
    
    // Inicializar componentes comunes
    WebCraft.initCommonComponents();
    
    // Inicializar funcionalidad específica para la página actual
    WebCraft.initCurrentPage();
};

// Inicialización del tema
WebCraft.initTheme = function() {
    // Detectar preferencia de tema del sistema
    const prefersDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Obtener tema guardado o usar el detectado del sistema
    const savedTheme = localStorage.getItem('theme') || (prefersDarkMode ? 'dark' : 'light');
    
    // Aplicar tema
    WebCraft.applyTheme(savedTheme);
    
    // Escuchar cambios en la preferencia del sistema
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        if (localStorage.getItem('theme') === 'system' || !localStorage.getItem('theme')) {
            WebCraft.applyTheme(e.matches ? 'dark' : 'light');
        }
    });
};

// Aplicar tema
WebCraft.applyTheme = function(theme) {
    if (theme === 'system') {
        // Detectar preferencia del sistema
        const prefersDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.setAttribute('data-theme', prefersDarkMode ? 'dark' : 'light');
    } else {
        document.documentElement.setAttribute('data-theme', theme);
    }
};

// Inicializar componentes comunes
WebCraft.initCommonComponents = function() {
    // Inicializar toggles de contraseña
    WebCraft.initPasswordToggles();
    
    // Inicializar dropdowns
    WebCraft.initDropdowns();
    
    // Inicializar tooltips
    WebCraft.initTooltips();
};

// Inicializar toggles de contraseña
WebCraft.initPasswordToggles = function() {
    const toggles = document.querySelectorAll('.password-toggle');
    
    toggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            
            if (input && input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else if (input) {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
};

// Inicializar dropdowns
WebCraft.initDropdowns = function() {
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.dropdown-trigger');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (trigger && menu) {
            // Abrir/cerrar al hacer clic
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                menu.classList.toggle('active');
                
                // Si está activo, agregar event listener para cerrar al hacer clic fuera
                if (menu.classList.contains('active')) {
                    document.addEventListener('click', closeDropdown);
                } else {
                    document.removeEventListener('click', closeDropdown);
                }
            });
            
            // Función para cerrar dropdown
            function closeDropdown(e) {
                if (!menu.contains(e.target)) {
                    menu.classList.remove('active');
                    document.removeEventListener('click', closeDropdown);
                }
            }
        }
    });
};

// Inicializar tooltips
WebCraft.initTooltips = function() {
    const tooltipTriggers = document.querySelectorAll('[data-tooltip]');
    
    tooltipTriggers.forEach(trigger => {
        const tooltipText = trigger.getAttribute('data-tooltip');
        
        // Crear elemento de tooltip
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = tooltipText;
        
        // Agregar al documento
        document.body.appendChild(tooltip);
        
        // Mostrar tooltip al pasar el ratón
        trigger.addEventListener('mouseenter', function(e) {
            const rect = trigger.getBoundingClientRect();
            
            tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5 + window.scrollY) + 'px';
            tooltip.classList.add('active');
        });
        
        // Ocultar tooltip al quitar el ratón
        trigger.addEventListener('mouseleave', function() {
            tooltip.classList.remove('active');
        });
    });
};

// Inicializar funcionalidad específica para la página actual
WebCraft.initCurrentPage = function() {
    // Obtener nombre de página del atributo de clase del body
    const body = document.body;
    const pageClass = Array.from(body.classList).find(cls => cls.startsWith('page-'));
    const pageName = pageClass ? pageClass.replace('page-', '') : null;
    
    // Ejecutar inicialización específica según la página
    if (pageName && typeof WebCraft[`init${pageName.charAt(0).toUpperCase() + pageName.slice(1)}Page`] === 'function') {
        WebCraft[`init${pageName.charAt(0).toUpperCase() + pageName.slice(1)}Page`]();
    }
};

// Cargar componentes comunes
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar la aplicación
    WebCraft.init();
});
