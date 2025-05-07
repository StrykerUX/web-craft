/**
 * WebCraft Academy - Script Principal
 * 
 * Este archivo contiene las funcionalidades básicas de la plataforma,
 * incluyendo interacciones de UI y comportamientos generales.
 */

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar todos los componentes de la UI
    initUI();
    
    // Manejar formularios si existen
    initForms();
    
    // Inicializar navegación móvil
    initMobileNavigation();
    
    // Inicializar dropdowns
    initDropdowns();
    
    // Inicializar visibilidad de contraseña
    initPasswordToggles();
    
    // Inicializar carrusel de testimonios si existe
    initTestimonialsCarousel();
});

/**
 * Inicializa componentes generales de la UI
 */
function initUI() {
    console.log('Inicializando UI de WebCraft Academy...');
    
    // Asegurarse de que los enlaces externos abran en una nueva pestaña
    document.querySelectorAll('a[href^="http"]').forEach(link => {
        if (!link.hasAttribute('target')) {
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener noreferrer');
        }
    });
    
    // Agregar clases activas a elementos de navegación
    highlightActiveNavItems();
}

/**
 * Resalta los elementos de navegación activos según la URL actual
 */
function highlightActiveNavItems() {
    const currentPath = window.location.pathname;
    const searchParams = new URLSearchParams(window.location.search);
    const currentPage = searchParams.get('page') || 'home';
    
    // Resaltar elementos de navegación principal
    document.querySelectorAll('.nav-item').forEach(item => {
        const link = item.querySelector('a');
        if (link) {
            const linkParams = new URLSearchParams(new URL(link.href, window.location.origin).search);
            const linkPage = linkParams.get('page') || 'home';
            
            if (linkPage === currentPage) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        }
    });
}

/**
 * Inicializa el comportamiento de formularios
 */
function initForms() {
    // Manejar envío de formularios con funcionalidad AJAX si es necesario
    document.querySelectorAll('form').forEach(form => {
        // Validación de formularios del lado del cliente
        form.addEventListener('submit', (e) => {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Función simple de validación de formularios
 * @param {HTMLFormElement} form - El formulario a validar
 * @returns {boolean} - True si el formulario es válido, False si no
 */
function validateForm(form) {
    let isValid = true;
    
    // Validar campos requeridos
    form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            showFieldError(field, 'Este campo es obligatorio');
        } else {
            clearFieldError(field);
        }
    });
    
    // Validar emails
    form.querySelectorAll('input[type="email"]').forEach(field => {
        if (field.value.trim() && !validateEmail(field.value)) {
            isValid = false;
            showFieldError(field, 'Por favor, introduce un email válido');
        }
    });
    
    // Validar coincidencia de contraseñas si hay campos de confirmación
    const passwordField = form.querySelector('input[name="password"]');
    const confirmField = form.querySelector('input[name="confirm_password"]');
    
    if (passwordField && confirmField && passwordField.value !== confirmField.value) {
        isValid = false;
        showFieldError(confirmField, 'Las contraseñas no coinciden');
    }
    
    return isValid;
}

/**
 * Muestra un mensaje de error para un campo de formulario
 * @param {HTMLElement} field - El campo con error
 * @param {string} message - El mensaje de error a mostrar
 */
function showFieldError(field, message) {
    // Eliminar cualquier mensaje de error anterior
    clearFieldError(field);
    
    // Agregar clase de error al campo
    field.classList.add('is-invalid');
    
    // Crear y mostrar mensaje de error
    const errorElement = document.createElement('div');
    errorElement.className = 'invalid-feedback';
    errorElement.textContent = message;
    
    // Insertar después del campo
    field.parentNode.appendChild(errorElement);
}

/**
 * Elimina mensajes de error de un campo
 * @param {HTMLElement} field - El campo a limpiar
 */
function clearFieldError(field) {
    field.classList.remove('is-invalid');
    
    // Eliminar mensajes de error previos
    const parent = field.parentNode;
    const errorElements = parent.querySelectorAll('.invalid-feedback');
    errorElements.forEach(el => el.remove());
}

/**
 * Valida una dirección de email
 * @param {string} email - El email a validar
 * @returns {boolean} - True si es válido, False si no
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Inicializa la navegación móvil
 */
function initMobileNavigation() {
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileToggle && navMenu) {
        mobileToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            mobileToggle.classList.toggle('active');
        });
        
        // Cerrar menú al hacer clic en un elemento
        navMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                mobileToggle.classList.remove('active');
            });
        });
    }
}

/**
 * Inicializa los dropdowns de la interfaz
 */
function initDropdowns() {
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const dropdown = toggle.closest('.dropdown');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            // Cerrar todos los demás dropdowns
            document.querySelectorAll('.dropdown-menu.show').forEach(openMenu => {
                if (openMenu !== menu) {
                    openMenu.classList.remove('show');
                }
            });
            
            // Alternar el dropdown actual
            menu.classList.toggle('show');
        });
    });
    
    // Cerrar dropdowns al hacer clic fuera
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });
}

/**
 * Inicializa los toggles de visibilidad de contraseñas
 */
function initPasswordToggles() {
    document.querySelectorAll('.password-toggle').forEach(toggle => {
        toggle.addEventListener('click', () => {
            const passwordField = toggle.closest('.input-icon-wrapper').querySelector('input');
            const icon = toggle.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                toggle.setAttribute('aria-label', 'Ocultar contraseña');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                toggle.setAttribute('aria-label', 'Ver contraseña');
            }
        });
    });
}

/**
 * Inicializa el carrusel de testimonios
 */
function initTestimonialsCarousel() {
    const slider = document.querySelector('.testimonials-slider');
    
    if (!slider) return;
    
    const testimonials = slider.querySelectorAll('.testimonial');
    const indicators = document.querySelectorAll('.nav-indicators .indicator');
    const prevButton = document.querySelector('.nav-prev');
    const nextButton = document.querySelector('.nav-next');
    
    if (testimonials.length === 0) return;
    
    let currentIndex = 0;
    
    // Función para mostrar un testimonio específico
    function showTestimonial(index) {
        // Ocultar todos los testimonios
        testimonials.forEach(testimonial => {
            testimonial.style.display = 'none';
        });
        
        // Quitar clase activa de todos los indicadores
        indicators.forEach(indicator => {
            indicator.classList.remove('active');
        });
        
        // Mostrar el testimonio actual
        testimonials[index].style.display = 'block';
        
        // Activar el indicador correspondiente
        if (indicators[index]) {
            indicators[index].classList.add('active');
        }
        
        // Actualizar índice actual
        currentIndex = index;
    }
    
    // Mostrar el primer testimonio al cargar
    showTestimonial(0);
    
    // Configurar botones de navegación
    if (prevButton) {
        prevButton.addEventListener('click', () => {
            const newIndex = (currentIndex - 1 + testimonials.length) % testimonials.length;
            showTestimonial(newIndex);
        });
    }
    
    if (nextButton) {
        nextButton.addEventListener('click', () => {
            const newIndex = (currentIndex + 1) % testimonials.length;
            showTestimonial(newIndex);
        });
    }
    
    // Configurar indicadores
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            showTestimonial(index);
        });
    });
}

/**
 * Muestra mensajes de alerta personalizados
 * @param {string} message - El mensaje a mostrar
 * @param {string} type - El tipo de alerta (success, error, info, warning)
 * @param {number} duration - Duración en milisegundos antes de que desaparezca
 */
function showAlert(message, type = 'info', duration = 3000) {
    // Crear el elemento de alerta
    const alertElement = document.createElement('div');
    alertElement.className = `alert-toast alert-${type}`;
    alertElement.textContent = message;
    
    // Agregar al cuerpo del documento
    document.body.appendChild(alertElement);
    
    // Mostrar con animación
    setTimeout(() => {
        alertElement.classList.add('show');
    }, 10);
    
    // Ocultar después de la duración especificada
    setTimeout(() => {
        alertElement.classList.remove('show');
        // Eliminar del DOM después de la animación
        setTimeout(() => {
            document.body.removeChild(alertElement);
        }, 300);
    }, duration);
}

/**
 * Función de utilidad para hacer peticiones AJAX
 * @param {string} url - URL a la que hacer la petición
 * @param {Object} options - Opciones para la petición
 * @returns {Promise} - Promise con la respuesta
 */
function ajaxRequest(url, options = {}) {
    // Opciones por defecto
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    // Combinar opciones
    const finalOptions = { ...defaultOptions, ...options };
    
    // Si hay datos para enviar y es un objeto, convertirlo a JSON
    if (finalOptions.data && typeof finalOptions.data === 'object' && !(finalOptions.data instanceof FormData)) {
        finalOptions.body = JSON.stringify(finalOptions.data);
        delete finalOptions.data;
    } else if (finalOptions.data instanceof FormData) {
        finalOptions.body = finalOptions.data;
        delete finalOptions.data;
        // Eliminar Content-Type para que el navegador establezca el boundary correcto
        delete finalOptions.headers['Content-Type'];
    }
    
    // Realizar la petición
    return fetch(url, finalOptions)
        .then(response => {
            // Verificar si la respuesta es exitosa
            if (!response.ok) {
                throw new Error(`Error ${response.status}: ${response.statusText}`);
            }
            
            // Intentar parsear como JSON
            return response.json()
                .catch(() => {
                    // Si no es JSON, devolver el texto
                    return response.text();
                });
        });
}
