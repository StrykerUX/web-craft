/**
 * Profile JavaScript for WebCraft Academy
 * 
 * Este archivo maneja la funcionalidad interactiva de la página de perfil del usuario.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar componentes de la página de perfil
    initProfilePage();
});

/**
 * Inicializar todos los componentes de la página de perfil
 */
function initProfilePage() {
    // Inicializar tabs
    initTabs();
    
    // Inicializar toggles de contraseña
    initPasswordToggles();
    
    // Inicializar subida de avatar
    initAvatarUpload();
    
    // Inicializar modal de eliminación de cuenta
    initDeleteAccountModal();
    
    // Inicializar selector de tema
    initThemeSelector();
    
    // Inicializar animaciones
    initAnimations();
}

/**
 * Inicializar sistema de tabs
 */
function initTabs() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Desactivar todos los tabs
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Activar tab seleccionado
            button.classList.add('active');
            document.getElementById(button.dataset.tab + '-tab').classList.add('active');
            
            // Actualizar URL con hash para permitir enlace directo al tab
            window.history.replaceState(null, null, `#${button.dataset.tab}`);
        });
    });
    
    // Verificar si hay un hash en la URL para activar un tab específico
    if (window.location.hash) {
        const tabId = window.location.hash.substring(1);
        const tabButton = document.querySelector(`[data-tab="${tabId}"]`);
        
        if (tabButton) {
            tabButton.click();
        }
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
 * Inicializar subida de avatar
 */
function initAvatarUpload() {
    const avatarInput = document.getElementById('avatar');
    const hiddenSubmit = document.querySelector('.hidden-submit');
    const avatarImg = document.querySelector('.user-avatar-large img');
    
    if (avatarInput && hiddenSubmit) {
        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                // Para previsualizar el avatar antes de enviar (opcional)
                if (avatarImg) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        // Mostrar vista previa con animación
                        avatarImg.style.opacity = '0';
                        
                        setTimeout(() => {
                            avatarImg.src = e.target.result;
                            avatarImg.style.opacity = '1';
                        }, 300);
                    };
                    
                    reader.readAsDataURL(this.files[0]);
                }
                
                // Enviar formulario automáticamente
                hiddenSubmit.click();
            }
        });
    }
}

/**
 * Inicializar modal de eliminación de cuenta
 */
function initDeleteAccountModal() {
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    const deleteAccountModal = document.getElementById('deleteAccountModal');
    const modalClose = document.querySelector('.modal-close');
    const modalCancel = document.querySelector('.modal-cancel');
    const confirmDeleteAccount = document.getElementById('confirmDeleteAccount');
    const confirmUsername = document.getElementById('confirm_username');
    const expectedUsername = document.querySelector('.username')?.textContent.trim();
    
    if (deleteAccountBtn && deleteAccountModal) {
        // Abrir modal
        deleteAccountBtn.addEventListener('click', function() {
            deleteAccountModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        // Cerrar modal
        if (modalClose) {
            modalClose.addEventListener('click', function() {
                deleteAccountModal.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
        
        // Cancelar
        if (modalCancel) {
            modalCancel.addEventListener('click', function() {
                deleteAccountModal.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
        
        // Verificar nombre de usuario
        if (confirmUsername && confirmDeleteAccount && expectedUsername) {
            confirmUsername.addEventListener('input', function() {
                confirmDeleteAccount.disabled = (this.value !== expectedUsername);
            });
        }
        
        // Confirmar eliminación
        if (confirmDeleteAccount) {
            confirmDeleteAccount.addEventListener('click', function() {
                if (confirmUsername.value === expectedUsername) {
                    // Mostrar notificación de confirmación
                    showNotification('Eliminando cuenta...', 'info');
                    
                    // Enviar solicitud para eliminar cuenta
                    const formData = new FormData();
                    formData.append('action', 'delete_account');
                    formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
                    
                    fetch('includes/ajax/delete_account.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Cuenta eliminada exitosamente', 'success');
                            
                            // Redirigir después de 2 segundos
                            setTimeout(() => {
                                window.location.href = 'index.php';
                            }, 2000);
                        } else {
                            showNotification(data.message || 'Error al eliminar la cuenta', 'error');
                            deleteAccountModal.classList.remove('active');
                            document.body.style.overflow = '';
                        }
                    })
                    .catch(error => {
                        showNotification('Error al procesar la solicitud', 'error');
                        deleteAccountModal.classList.remove('active');
                        document.body.style.overflow = '';
                    });
                }
            });
        }
        
        // Cerrar modal con Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && deleteAccountModal.classList.contains('active')) {
                deleteAccountModal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
        
        // Cerrar modal haciendo clic fuera
        deleteAccountModal.addEventListener('click', function(e) {
            if (e.target === deleteAccountModal) {
                deleteAccountModal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
}

/**
 * Inicializar selector de tema
 */
function initThemeSelector() {
    const themeOptions = document.querySelectorAll('input[name="theme"]');
    
    themeOptions.forEach(option => {
        option.addEventListener('change', function() {
            if (this.checked) {
                // Aplicar tema inmediatamente para vista previa
                applyTheme(this.value);
            }
        });
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
 * Inicializar animaciones
 */
function initAnimations() {
    // Animar entrada de elementos
    const elements = [
        { selector: '.profile-sidebar', delay: 0 },
        { selector: '.tab-list', delay: 100 },
        { selector: '.tab-content.active', delay: 200 }
    ];
    
    elements.forEach(element => {
        const el = document.querySelector(element.selector);
        
        if (el) {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            }, element.delay);
        }
    });
}

/**
 * Mostrar notificación
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - Tipo de notificación (success, error, info)
 */
function showNotification(message, type = 'info') {
    // Verificar si ya existe el contenedor de notificaciones
    let notificationsContainer = document.getElementById('notifications-container');
    
    if (!notificationsContainer) {
        // Crear contenedor
        notificationsContainer = document.createElement('div');
        notificationsContainer.id = 'notifications-container';
        document.body.appendChild(notificationsContainer);
        
        // Estilos para el contenedor
        notificationsContainer.style.position = 'fixed';
        notificationsContainer.style.top = '20px';
        notificationsContainer.style.right = '20px';
        notificationsContainer.style.zIndex = '9999';
        notificationsContainer.style.display = 'flex';
        notificationsContainer.style.flexDirection = 'column';
        notificationsContainer.style.gap = '10px';
    }
    
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    // Estilos para la notificación
    notification.style.padding = '15px 20px';
    notification.style.borderRadius = '4px';
    notification.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.1)';
    notification.style.display = 'flex';
    notification.style.alignItems = 'center';
    notification.style.minWidth = '280px';
    notification.style.maxWidth = '350px';
    notification.style.transform = 'translateX(100%)';
    notification.style.opacity = '0';
    notification.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
    
    // Colores según tipo
    if (type === 'success') {
        notification.style.backgroundColor = '#e8f5e9';
        notification.style.color = '#2e7d32';
        notification.style.borderLeft = '4px solid #2e7d32';
    } else if (type === 'error') {
        notification.style.backgroundColor = '#ffebee';
        notification.style.color = '#c62828';
        notification.style.borderLeft = '4px solid #c62828';
    } else {
        notification.style.backgroundColor = '#e3f2fd';
        notification.style.color = '#1565c0';
        notification.style.borderLeft = '4px solid #1565c0';
    }
    
    // Icono según tipo
    let icon = 'info-circle';
    if (type === 'success') icon = 'check-circle';
    if (type === 'error') icon = 'exclamation-circle';
    
    notification.innerHTML = `
        <i class="fas fa-${icon}" style="margin-right: 10px; font-size: 1.2rem;"></i>
        <span style="flex: 1;">${message}</span>
        <button class="close-notification" style="background: none; border: none; cursor: pointer; opacity: 0.7; font-size: 1rem;">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Agregar al contenedor
    notificationsContainer.appendChild(notification);
    
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
    const closeTimeout = setTimeout(() => {
        closeNotification(notification);
    }, 5000);
    
    // Detener auto-cierre al pasar el ratón
    notification.addEventListener('mouseenter', function() {
        clearTimeout(closeTimeout);
    });
    
    // Reanudar auto-cierre al quitar el ratón
    notification.addEventListener('mouseleave', function() {
        setTimeout(() => {
            closeNotification(notification);
        }, 3000);
    });
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
