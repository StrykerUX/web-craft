/**
 * WebCraft Academy - Script Principal
 * 
 * Este archivo contiene scripts de uso general para toda la plataforma.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar todos los tooltips de Bootstrap
    initializeTooltips();
    
    // Manejar menú móvil
    setupMobileMenu();
    
    // Inicializar dropdowns
    setupDropdowns();
    
    // Añadir clase activa a links de navegación
    highlightActiveNavLinks();
    
    // Animaciones para elementos de la página
    initializeAnimations();
});

/**
 * Inicializa los tooltips de Bootstrap
 */
function initializeTooltips() {
    // Inicializar tooltips si Bootstrap está disponible
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

/**
 * Configura el menú móvil
 */
function setupMobileMenu() {
    const menuToggle = document.getElementById('menu-toggle');
    const navbarNav = document.getElementById('navbar-nav');
    
    if (menuToggle && navbarNav) {
        menuToggle.addEventListener('click', function() {
            navbarNav.classList.toggle('active');
            
            // Cambiar ícono del botón
            const icon = menuToggle.querySelector('i');
            if (icon) {
                if (navbarNav.classList.contains('active')) {
                    icon.className = 'fas fa-times';
                    menuToggle.setAttribute('aria-label', 'Cerrar menú');
                } else {
                    icon.className = 'fas fa-bars';
                    menuToggle.setAttribute('aria-label', 'Abrir menú');
                }
            }
        });
        
        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!menuToggle.contains(e.target) && !navbarNav.contains(e.target) && navbarNav.classList.contains('active')) {
                navbarNav.classList.remove('active');
                
                const icon = menuToggle.querySelector('i');
                if (icon) {
                    icon.className = 'fas fa-bars';
                    menuToggle.setAttribute('aria-label', 'Abrir menú');
                }
            }
        });
    }
}

/**
 * Configura los dropdowns personalizados
 */
function setupDropdowns() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            
            const dropdownMenu = this.nextElementSibling;
            if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                dropdownMenu.classList.toggle('show');
                this.setAttribute('aria-expanded', dropdownMenu.classList.contains('show'));
                
                // Cerrar otros dropdowns
                document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                    if (menu !== dropdownMenu) {
                        menu.classList.remove('show');
                        const otherToggle = menu.previousElementSibling;
                        if (otherToggle) {
                            otherToggle.setAttribute('aria-expanded', 'false');
                        }
                    }
                });
            }
        });
    });
    
    // Cerrar dropdowns al hacer clic fuera
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
            menu.classList.remove('show');
            const toggle = menu.previousElementSibling;
            if (toggle) {
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    });
    
    // Evitar cierre al hacer clic dentro de dropdown
    document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
        menu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
}

/**
 * Resalta los enlaces de navegación activos
 */
function highlightActiveNavLinks() {
    // Obtener la URL actual
    const currentPath = window.location.pathname;
    const currentSearch = window.location.search;
    
    // Verificar enlaces de navegación
    document.querySelectorAll('.navbar-nav .nav-link, .footer-links a').forEach(function(link) {
        const href = link.getAttribute('href');
        
        // Si el enlace coincide con la URL actual o con el parámetro "page"
        if (href === currentPath || 
            (currentSearch.includes('page=') && href.includes('page=') && 
             href.split('page=')[1] === currentSearch.split('page=')[1].split('&')[0])) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
}

/**
 * Inicializa animaciones para distintos elementos
 */
function initializeAnimations() {
    // Animaciones con GSAP si está disponible
    if (typeof gsap !== 'undefined') {
        // Animación para hero section
        if (document.querySelector('.hero-section')) {
            gsap.from('.hero-title', { duration: 0.8, y: 30, opacity: 0, ease: 'power3.out' });
            gsap.from('.hero-subtitle', { duration: 0.8, y: 30, opacity: 0, ease: 'power3.out', delay: 0.2 });
            gsap.from('.hero-cta', { duration: 0.8, y: 30, opacity: 0, ease: 'power3.out', delay: 0.4 });
            gsap.from('.hero-stats .stat-item', { 
                duration: 0.6, 
                y: 20, 
                opacity: 0, 
                ease: 'power3.out',
                stagger: 0.1,
                delay: 0.6
            });
            gsap.from('.code-editor-mockup', { duration: 1, x: 30, opacity: 0, ease: 'power3.out', delay: 0.3 });
        }
        
        // Animación para cards
        gsap.from('.feature-card, .module-card, .action-card', {
            duration: 0.6,
            y: 30,
            opacity: 0,
            ease: 'power3.out',
            stagger: 0.1,
            scrollTrigger: {
                trigger: '.feature-card, .module-card, .action-card',
                start: 'top bottom-=100px',
                toggleActions: 'play none none none'
            }
        });
    }
}
