/**
 * WebCraft Academy - Theme Switcher
 * 
 * Este script maneja el cambio entre temas claro y oscuro
 * y la detección automática de preferencias del usuario.
 */

// Función de inicialización que se ejecuta cuando el DOM está listo
document.addEventListener('DOMContentLoaded', () => {
    initThemeSwitch();
});

/**
 * Inicializa el sistema de cambio de tema
 */
function initThemeSwitch() {
    // Obtener el botón de cambio de tema
    const themeToggle = document.getElementById('theme-toggle');
    
    if (!themeToggle) {
        console.warn('Botón de cambio de tema no encontrado');
        return;
    }
    
    // Agregar evento de clic al botón
    themeToggle.addEventListener('click', toggleTheme);
    
    // Verificar preferencia guardada o usar preferencia del sistema
    checkThemePreference();
    
    // Escuchar cambios en la preferencia del sistema
    listenForSystemPreferenceChanges();
}

/**
 * Cambia entre los temas claro y oscuro
 */
function toggleTheme() {
    // Obtener el tema actual
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    
    // Cambiar al tema opuesto
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    
    // Aplicar el nuevo tema
    setTheme(newTheme);
    
    // Guardar la preferencia en localStorage
    localStorage.setItem('theme', newTheme);
    
    // Registro para depuración
    console.log(`Tema cambiado a: ${newTheme}`);
}

/**
 * Verifica la preferencia de tema guardada o la configuración del sistema
 */
function checkThemePreference() {
    // Verificar si hay una preferencia guardada
    const savedTheme = localStorage.getItem('theme');
    
    // Si hay una preferencia guardada, usarla
    if (savedTheme) {
        setTheme(savedTheme);
        return;
    }
    
    // Si no hay preferencia guardada, verificar la configuración del sistema
    const prefersDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const systemTheme = prefersDarkMode ? 'dark' : 'light';
    
    // Aplicar el tema según la preferencia del sistema
    setTheme(systemTheme);
}

/**
 * Escucha cambios en la preferencia de tema del sistema
 */
function listenForSystemPreferenceChanges() {
    // Media query para detectar preferencia de tema oscuro
    const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    
    // Agregar event listener para cambios
    darkModeMediaQuery.addEventListener('change', (e) => {
        // Solo actualizar si el usuario no ha establecido una preferencia manualmente
        if (!localStorage.getItem('theme')) {
            const systemTheme = e.matches ? 'dark' : 'light';
            setTheme(systemTheme);
            console.log(`Tema ajustado automáticamente a: ${systemTheme} (preferencia del sistema)`);
        }
    });
}

/**
 * Aplica el tema especificado
 * @param {string} theme - El tema a aplicar ('light' o 'dark')
 */
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    
    // Si el tema es oscuro, establecer meta theme-color para navegadores móviles
    const metaThemeColor = document.querySelector('meta[name="theme-color"]');
    if (metaThemeColor) {
        metaThemeColor.setAttribute('content', theme === 'dark' ? '#121212' : '#ffffff');
    }
}
