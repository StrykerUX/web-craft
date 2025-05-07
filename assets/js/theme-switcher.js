/**
 * WebCraft Academy - Theme Switcher
 * 
 * Este script maneja el cambio entre tema claro y oscuro
 * y guarda la preferencia del usuario en localStorage.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Elementos DOM
    const themeToggle = document.getElementById('theme-toggle');
    const htmlElement = document.documentElement;
    
    // Detectar preferencia del sistema
    const prefersDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Obtener tema guardado o usar el del sistema
    let currentTheme = localStorage.getItem('theme') || (prefersDarkMode ? 'dark' : 'light');
    
    // Aplicar tema inicial
    applyTheme(currentTheme);
    
    // Evento para cambiar tema
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            // Cambiar entre temas
            currentTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            // Guardar preferencia
            localStorage.setItem('theme', currentTheme);
            
            // Aplicar tema
            applyTheme(currentTheme);
        });
    }
    
    // Función para aplicar tema
    function applyTheme(theme) {
        // Establecer atributo en HTML
        htmlElement.setAttribute('data-theme', theme);
        
        // Actualizar ícono si existe el botón
        if (themeToggle) {
            const icon = themeToggle.querySelector('i') || themeToggle;
            
            if (theme === 'dark') {
                // Cambiar al ícono de sol para tema oscuro
                if (icon.classList) {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                }
                themeToggle.setAttribute('aria-label', 'Cambiar a tema claro');
            } else {
                // Cambiar al ícono de luna para tema claro
                if (icon.classList) {
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                }
                themeToggle.setAttribute('aria-label', 'Cambiar a tema oscuro');
            }
        }
        
        // Disparar evento personalizado para notificar a otros scripts
        const themeEvent = new CustomEvent('themeChanged', { detail: { theme } });
        document.dispatchEvent(themeEvent);
    }
    
    // Escuchar cambios en la preferencia del sistema
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        // Solo cambiar automáticamente si el usuario no ha establecido preferencia
        if (!localStorage.getItem('theme')) {
            const newTheme = e.matches ? 'dark' : 'light';
            applyTheme(newTheme);
            currentTheme = newTheme;
        }
    });
});
