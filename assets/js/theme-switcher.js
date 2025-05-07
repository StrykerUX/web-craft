/**
 * WebCraft Academy - Controlador de Tema
 * 
 * Este script maneja la funcionalidad de cambio entre tema claro y oscuro,
 * respetando las preferencias del usuario y del sistema.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Obtener elementos del DOM
    const themeToggle = document.querySelector('.theme-toggle');
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
    
    // Función para configurar tema
    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        
        // Actualizar ícono del botón según el tema
        if (themeToggle) {
            const icon = themeToggle.querySelector('i');
            if (icon) {
                if (theme === 'dark') {
                    icon.className = 'fas fa-sun';
                    themeToggle.setAttribute('title', 'Cambiar a tema claro');
                    themeToggle.setAttribute('aria-label', 'Cambiar a tema claro');
                } else {
                    icon.className = 'fas fa-moon';
                    themeToggle.setAttribute('title', 'Cambiar a tema oscuro');
                    themeToggle.setAttribute('aria-label', 'Cambiar a tema oscuro');
                }
            }
        }
    }
    
    // Obtener tema guardado o usar preferencia del sistema
    const savedTheme = localStorage.getItem('theme');
    
    if (savedTheme) {
        // Usar tema guardado si existe
        setTheme(savedTheme);
    } else if (prefersDarkScheme.matches) {
        // Si no hay tema guardado pero el sistema prefiere oscuro
        setTheme('dark');
    } else {
        // Por defecto usar tema claro
        setTheme('light');
    }
    
    // Escuchar eventos de clic en el botón de cambio de tema
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            setTheme(newTheme);
        });
    }
    
    // Escuchar cambios en las preferencias del sistema
    prefersDarkScheme.addEventListener('change', function(e) {
        // Solo cambiar automáticamente si el usuario no ha establecido una preferencia
        if (!localStorage.getItem('theme')) {
            const newTheme = e.matches ? 'dark' : 'light';
            setTheme(newTheme);
        }
    });
});
