/**
 * Dashboard JavaScript for WebCraft Academy
 * 
 * Este archivo maneja la funcionalidad interactiva del dashboard del usuario.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar componentes del dashboard
    initDashboard();
});

/**
 * Inicializar todos los componentes del dashboard
 */
function initDashboard() {
    // Inicializar panel de configuración
    initSettingsPanel();
    
    // Inicializar acordeón de módulos
    initModulesAccordion();
    
    // Inicializar consejos
    initTips();
    
    // Inicializar toggles de contraseñas
    initPasswordToggles();
    
    // Inicializar animaciones
    initAnimations();
}

/**
 * Inicializar el panel de configuración
 */
function initSettingsPanel() {
    // Elementos del DOM
    const settingsButton = document.querySelector('.user-settings-button');
    const settingsPanel = document.getElementById('settingsPanel');
    const closeSettings = document.getElementById('closeSettings');
    const cancelSettings = document.getElementById('cancelSettings');
    const saveSettings = document.getElementById('saveSettings');
    const themeOptions = document.querySelectorAll('input[name="theme"]');
    
    // Mostrar panel de configuración
    if (settingsButton) {
        settingsButton.addEventListener('click', function() {
            settingsPanel.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }
    
    // Cerrar panel de configuración
    if (closeSettings) {
        closeSettings.addEventListener('click', function() {
            settingsPanel.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
    
    // Cancelar cambios
    if (cancelSettings) {
        cancelSettings.addEventListener('click', function() {
            settingsPanel.classList.remove('active');
            document.body.style.overflow = '';
            // Reiniciar formulario a valores originales
            document.querySelectorAll('.settings-panel form').forEach(form => form.reset());
        });
    }
    
    // Guardar cambios
    if (saveSettings) {
        saveSettings.addEventListener('click', function() {
            // Obtener tema seleccionado
            let selectedTheme = 'system';
            themeOptions.forEach(option => {
                if (option.checked) {
                    selectedTheme = option.value;
                }
            });
            
            // Guardar tema en localStorage
            localStorage.setItem('theme', selectedTheme);
            
            // Aplicar tema
            applyTheme(selectedTheme);
            
            // Guardar configuración en el servidor
            saveUserSettings({
                theme_preference: selectedTheme,
                font_size: document.getElementById('fontSize')?.value || 'normal',
                contrast: document.getElementById('contrast')?.value || 'normal',
                reduce_motion: document.getElementById('reduceMotion')?.checked || false,
                email_notifications: document.getElementById('emailNotifications')?.checked || false,
                achievement_notifications: document.getElementById('achievementNotifications')?.checked || false
            });
            
            // Cerrar panel
            settingsPanel.classList.remove('active');
            document.body.style.overflow = '';
            
            // Mostrar notificación
            showNotification('Configuración guardada exitosamente', 'success');
        });
    }
    
    // Cerrar panel con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && settingsPanel.classList.contains('active')) {
            settingsPanel.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
    
    // Cerrar panel haciendo clic fuera
    document.addEventListener('click', function(e) {
        if (settingsPanel.classList.contains('active') && 
            !settingsPanel.contains(e.target) && 
            e.target !== settingsButton) {
            settingsPanel.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
}

/**
 * Aplicar tema seleccionado
 * @param {string} theme - Tema a aplicar (light, dark, system)
 */
function applyTheme(theme) {
    if (theme === 'system') {
        // Detectar preferencia del sistema
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
    } else {
        document.documentElement.setAttribute('data-theme', theme);
    }
}

/**
 * Guardar configuración de usuario en el servidor
 * @param {Object} settings - Configuración a guardar
 */
function saveUserSettings(settings) {
    // Enviar configuración al servidor mediante AJAX
    fetch('includes/ajax/save_user_settings.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(settings)
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Error al guardar configuración:', data.message);
            showNotification('Error al guardar configuración', 'error');
        }
    })
    .catch(error => {
        console.error('Error al guardar configuración:', error);
        showNotification('Error al guardar configuración', 'error');
    });
}

/**
 * Inicializar acordeón de módulos
 */
function initModulesAccordion() {
    const accordionTitle = document.querySelector('.accordion-title');
    const accordionContent = document.querySelector('.accordion-content');
    
    if (accordionTitle && accordionContent) {
        // Verificar estado guardado
        const isCollapsed = localStorage.getItem('modulesAccordionCollapsed') === 'true';
        
        // Aplicar estado inicial
        if (isCollapsed) {
            accordionTitle.classList.add('collapsed');
            accordionContent.style.display = 'none';
        }
        
        // Alternar acordeón al hacer clic
        accordionTitle.addEventListener('click', function() {
            this.classList.toggle('collapsed');
            
            if (this.classList.contains('collapsed')) {
                // Colapsar con animación
                const contentHeight = accordionContent.scrollHeight;
                accordionContent.style.height = contentHeight + 'px';
                
                // Forzar reflow
                accordionContent.offsetHeight;
                
                accordionContent.style.height = '0';
                accordionContent.style.overflow = 'hidden';
                
                // Después de la animación
                setTimeout(() => {
                    accordionContent.style.display = 'none';
                    accordionContent.style.height = '';
                    accordionContent.style.overflow = '';
                }, 300);
                
                localStorage.setItem('modulesAccordionCollapsed', 'true');
            } else {
                // Expandir con animación
                accordionContent.style.display = 'block';
                accordionContent.style.height = '0';
                accordionContent.style.overflow = 'hidden';
                
                // Forzar reflow
                accordionContent.offsetHeight;
                
                accordionContent.style.height = accordionContent.scrollHeight + 'px';
                
                // Después de la animación
                setTimeout(() => {
                    accordionContent.style.height = '';
                    accordionContent.style.overflow = '';
                }, 300);
                
                localStorage.setItem('modulesAccordionCollapsed', 'false');
            }
        });
    }
}

/**
 * Inicializar consejos
 */
function initTips() {
    const tips = [
        "Completa desafíos para ganar experiencia adicional y desbloquear logros especiales.",
        "Mantén una racha de estudio diario para obtener bonificaciones de XP.",
        "Revisa los proyectos de otros estudiantes para inspirarte y aprender nuevas técnicas.",
        "Utiliza el editor de código para experimentar con los conceptos que vayas aprendiendo.",
        "Participa en el foro para resolver dudas y ayudar a otros estudiantes.",
        "Personaliza tu experiencia de aprendizaje en la sección de configuración.",
        "Prueba a modificar los ejemplos de código para entender mejor cómo funcionan.",
        "Completa todos los ejercicios prácticos para dominar cada concepto.",
        "Revisa tus proyectos anteriores y mejóralos con tus nuevos conocimientos.",
        "Utiliza las herramientas de inspección del navegador para analizar sitios web."
    ];
    
    const tipElement = document.querySelector('.tip-content p');
    const refreshButton = document.querySelector('.refresh-tips');
    
    if (tipElement && refreshButton) {
        // Mostrar consejo aleatorio al cargar
        tipElement.textContent = tips[Math.floor(Math.random() * tips.length)];
        
        // Cambiar consejo al hacer clic en refrescar
        refreshButton.addEventListener('click', function() {
            // Animación de fade
            tipElement.style.opacity = '0';
            
            setTimeout(() => {
                tipElement.textContent = tips[Math.floor(Math.random() * tips.length)];
                tipElement.style.opacity = '1';
            }, 300);
        });
    }
}

/**
 * Inicializar toggles de contraseña
 */
function initPasswordToggles() {
    const toggles = document.querySelectorAll('.password-toggle');
    
    toggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
}

/**
 * Inicializar animaciones
 */
function initAnimations() {
    // Animación de tarjetas al cargar la página
    const cards = document.querySelectorAll('.dashboard-card');
    
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 + (index * 50));
    });
    
    // Animación de progreso de barras
    const progressBars = document.querySelectorAll('.progress-fill');
    
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0';
        
        setTimeout(() => {
            bar.style.transition = 'width 1s ease';
            bar.style.width = width;
        }, 500);
    });
}

/**
 * Mostrar notificación
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - Tipo de notificación (success, error, info)
 */
function showNotification(message, type = 'info') {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    // Icono según tipo
    let icon = 'info-circle';
    if (type === 'success') icon = 'check-circle';
    if (type === 'error') icon = 'exclamation-circle';
    
    notification.innerHTML = `
        <i class="fas fa-${icon}"></i>
        <span>${message}</span>
        <button class="close-notification">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Agregar al DOM
    document.body.appendChild(notification);
    
    // Animar entrada
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
        notification.style.opacity = '1';
    }, 10);
    
    // Agregar evento para cerrar
    notification.querySelector('.close-notification').addEventListener('click', function() {
        closeNotification(notification);
    });
    
    // Auto-cerrar después de 5 segundos
    setTimeout(() => {
        closeNotification(notification);
    }, 5000);
}

/**
 * Cerrar notificación
 * @param {HTMLElement} notification - Elemento de notificación
 */
function closeNotification(notification) {
    notification.style.transform = 'translateX(100%)';
    notification.style.opacity = '0';
    
    setTimeout(() => {
        notification.remove();
    }, 300);
}
