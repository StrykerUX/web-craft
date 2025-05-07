/**
 * Sistema de notificaciones para WebCraft Academy
 * 
 * Este componente proporciona un sistema de notificaciones reutilizable
 * que puede ser utilizado en toda la aplicación.
 */

// Crear espacio de nombres para WebCraft
if (typeof WebCraft === 'undefined') {
    WebCraft = {};
}

// Sistema de notificaciones
WebCraft.Notifications = (function() {
    /**
     * Mostrar una notificación
     * @param {string} message - Mensaje a mostrar
     * @param {object} options - Opciones de configuración
     * @param {string} options.type - Tipo de notificación (success, error, info, warning)
     * @param {number} options.duration - Duración en milisegundos (0 para no auto-cerrar)
     * @param {boolean} options.dismissible - Si se puede cerrar manualmente
     * @param {function} options.onClose - Callback al cerrar la notificación
     * @return {HTMLElement} El elemento de notificación creado
     */
    function show(message, options = {}) {
        // Opciones predeterminadas
        const defaults = {
            type: 'info',
            duration: 5000,
            dismissible: true,
            onClose: null
        };
        
        // Combinar opciones predeterminadas con las proporcionadas
        const settings = Object.assign({}, defaults, options);
        
        // Verificar si ya existe el contenedor de notificaciones
        let container = document.getElementById('notifications-container');
        
        if (!container) {
            // Crear contenedor
            container = document.createElement('div');
            container.id = 'notifications-container';
            document.body.appendChild(container);
            
            // Agregar estilos al contenedor
            Object.assign(container.style, {
                position: 'fixed',
                top: '20px',
                right: '20px',
                zIndex: '9999',
                display: 'flex',
                flexDirection: 'column',
                gap: '10px',
                maxWidth: '350px',
                width: '100%'
            });
        }
        
        // Crear elemento de notificación
        const notification = document.createElement('div');
        notification.className = `notification notification-${settings.type}`;
        
        // Estilos base para la notificación
        Object.assign(notification.style, {
            padding: '15px 20px',
            borderRadius: '4px',
            boxShadow: '0 4px 8px rgba(0, 0, 0, 0.1)',
            display: 'flex',
            alignItems: 'center',
            width: '100%',
            transform: 'translateX(100%)',
            opacity: '0',
            transition: 'transform 0.3s ease, opacity 0.3s ease'
        });
        
        // Estilos según tipo
        const styles = {
            success: {
                backgroundColor: '#e8f5e9',
                color: '#2e7d32',
                borderLeft: '4px solid #2e7d32'
            },
            error: {
                backgroundColor: '#ffebee',
                color: '#c62828',
                borderLeft: '4px solid #c62828'
            },
            warning: {
                backgroundColor: '#fff8e1',
                color: '#f57f17',
                borderLeft: '4px solid #f57f17'
            },
            info: {
                backgroundColor: '#e3f2fd',
                color: '#1565c0',
                borderLeft: '4px solid #1565c0'
            }
        };
        
        // Aplicar estilos según tipo
        Object.assign(notification.style, styles[settings.type] || styles.info);
        
        // Icono según tipo
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        
        const icon = icons[settings.type] || icons.info;
        
        // Contenido de la notificación
        let content = `
            <i class="fas fa-${icon}" style="margin-right: 10px; font-size: 1.2rem;"></i>
            <span style="flex: 1;">${message}</span>
        `;
        
        // Agregar botón para cerrar si es dismissible
        if (settings.dismissible) {
            content += `
                <button class="close-notification" style="background: none; border: none; cursor: pointer; opacity: 0.7; font-size: 1rem;">
                    <i class="fas fa-times"></i>
                </button>
            `;
        }
        
        notification.innerHTML = content;
        
        // Agregar al contenedor
        container.appendChild(notification);
        
        // Animar entrada
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
            notification.style.opacity = '1';
        }, 10);
        
        // Cerrar notificación
        const close = () => {
            notification.style.transform = 'translateX(100%)';
            notification.style.opacity = '0';
            
            setTimeout(() => {
                notification.remove();
                
                // Ejecutar callback si existe
                if (typeof settings.onClose === 'function') {
                    settings.onClose();
                }
                
                // Eliminar contenedor si no hay más notificaciones
                if (container.children.length === 0) {
                    container.remove();
                }
            }, 300);
        };
        
        // Agregar evento para cerrar si es dismissible
        if (settings.dismissible) {
            notification.querySelector('.close-notification').addEventListener('click', close);
        }
        
        // Auto-cerrar si la duración es > 0
        let closeTimeout;
        
        if (settings.duration > 0) {
            closeTimeout = setTimeout(close, settings.duration);
            
            // Pausar el temporizador cuando el ratón está sobre la notificación
            notification.addEventListener('mouseenter', () => {
                clearTimeout(closeTimeout);
            });
            
            // Reiniciar el temporizador cuando el ratón sale de la notificación
            notification.addEventListener('mouseleave', () => {
                closeTimeout = setTimeout(close, settings.duration);
            });
        }
        
        // Devolver el elemento y la función de cierre
        return {
            element: notification,
            close: close
        };
    }
    
    /**
     * Mostrar notificación de éxito
     * @param {string} message - Mensaje a mostrar
     * @param {object} options - Opciones de configuración
     * @return {HTMLElement} El elemento de notificación creado
     */
    function success(message, options = {}) {
        return show(message, Object.assign({}, options, { type: 'success' }));
    }
    
    /**
     * Mostrar notificación de error
     * @param {string} message - Mensaje a mostrar
     * @param {object} options - Opciones de configuración
     * @return {HTMLElement} El elemento de notificación creado
     */
    function error(message, options = {}) {
        return show(message, Object.assign({}, options, { type: 'error' }));
    }
    
    /**
     * Mostrar notificación de advertencia
     * @param {string} message - Mensaje a mostrar
     * @param {object} options - Opciones de configuración
     * @return {HTMLElement} El elemento de notificación creado
     */
    function warning(message, options = {}) {
        return show(message, Object.assign({}, options, { type: 'warning' }));
    }
    
    /**
     * Mostrar notificación de información
     * @param {string} message - Mensaje a mostrar
     * @param {object} options - Opciones de configuración
     * @return {HTMLElement} El elemento de notificación creado
     */
    function info(message, options = {}) {
        return show(message, Object.assign({}, options, { type: 'info' }));
    }
    
    /**
     * Cerrar todas las notificaciones
     */
    function closeAll() {
        const container = document.getElementById('notifications-container');
        
        if (container) {
            const notifications = container.querySelectorAll('.notification');
            
            notifications.forEach(notification => {
                notification.style.transform = 'translateX(100%)';
                notification.style.opacity = '0';
            });
            
            setTimeout(() => {
                container.remove();
            }, 300);
        }
    }
    
    // Exponer API pública
    return {
        show: show,
        success: success,
        error: error,
        warning: warning,
        info: info,
        closeAll: closeAll
    };
})();
