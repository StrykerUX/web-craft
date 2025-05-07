<?php
/**
 * Página de inicio para WebCraft Academy
 * 
 * Esta página muestra la landing page principal para usuarios no autenticados
 * y una vista de resumen para usuarios autenticados.
 */

// Prevenir acceso directo a este archivo
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Verificar si el usuario está autenticado
$isAuthenticated = isAuthenticated();
?>

<?php if (!$isAuthenticated): ?>
    <!-- Versión para usuarios no autenticados -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Aprende desarrollo web de forma <span class="highlight">interactiva</span> y <span class="highlight">divertida</span></h1>
                <p class="hero-subtitle">WebCraft Academy es una plataforma educativa diseñada para enseñarte desarrollo web a través de un enfoque práctico y gamificado. Aprende HTML, CSS, JavaScript, jQuery, GSAP y PHP construyendo proyectos reales.</p>
                <div class="hero-cta">
                    <a href="index.php?page=register" class="btn btn-primary btn-lg">Empieza gratis</a>
                    <a href="index.php?page=features" class="btn btn-outline btn-lg">Conoce más</a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-value">6</span>
                        <span class="stat-label">Módulos</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">100+</span>
                        <span class="stat-label">Lecciones</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">50+</span>
                        <span class="stat-label">Desafíos</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">24/7</span>
                        <span class="stat-label">Soporte</span>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <div class="code-editor-mockup">
                    <div class="editor-header">
                        <div class="editor-controls">
                            <span class="control red"></span>
                            <span class="control yellow"></span>
                            <span class="control green"></span>
                        </div>
                        <div class="editor-title">index.html</div>
                    </div>
                    <div class="editor-content">
                        <pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="es"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;Mi Primer Proyecto&lt;/title&gt;
    &lt;link rel="stylesheet" href="styles.css"&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;header class="header"&gt;
        &lt;h1 class="title"&gt;¡Hola Mundo!&lt;/h1&gt;
        &lt;p class="subtitle"&gt;Mi primer sitio web&lt;/p&gt;
    &lt;/header&gt;

    &lt;main class="content"&gt;
        &lt;p&gt;Estoy aprendiendo a programar con &lt;span class="highlight"&gt;WebCraft Academy&lt;/span&gt;.&lt;/p&gt;

        &lt;button id="changeBtn"&gt;Cambiar color&lt;/button&gt;
    &lt;/main&gt;

    &lt;script src="script.js"&gt;&lt;/script&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="features-section">
        <div class="container">
            <h2 class="section-title">¿Por qué elegir WebCraft Academy?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-code"></i>
                    </div>
                    <h3 class="feature-title">Aprende haciendo</h3>
                    <p class="feature-description">80% práctica, 20% teoría. Programa en un editor de código en vivo mientras aprendes, con feedback inmediato.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <h3 class="feature-title">Gamificación</h3>
                    <p class="feature-description">Sube de nivel, gana insignias y desbloquea logros mientras desarrollas tus habilidades como programador web.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <h3 class="feature-title">Proyectos reales</h3>
                    <p class="feature-description">Construye sitios y aplicaciones web funcionales que puedes agregar a tu portafolio para demostrar tus habilidades.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="feature-title">Comunidad</h3>
                    <p class="feature-description">Únete a una comunidad de estudiantes y mentores para colaborar, compartir recursos y resolver dudas.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h3 class="feature-title">Certificación</h3>
                    <p class="feature-description">Obtén certificados al completar módulos que validan tus conocimientos y habilidades en desarrollo web.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3 class="feature-title">Acceso móvil</h3>
                    <p class="feature-description">Aprende desde cualquier dispositivo. Nuestra plataforma se adapta a escritorio, tablet y móvil para que aprendas donde sea.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="modules-overview-section">
        <div class="container">
            <h2 class="section-title">Ruta de aprendizaje</h2>
            <p class="section-subtitle">Aprende a desarrollar sitios web profesionales desde cero con estos 6 módulos.</p>
            
            <div class="modules-timeline">
                <div class="timeline-module">
                    <div class="module-number">1</div>
                    <div class="module-content">
                        <h3><i class="fab fa-html5"></i> Fundamentos HTML</h3>
                        <p>Estructura básica de documentos, etiquetas, elementos semánticos y formularios.</p>
                        <ul class="module-topics">
                            <li>Estructura básica de documentos</li>
                            <li>Elementos semánticos (header, footer, nav, etc.)</li>
                            <li>Formularios y elementos interactivos</li>
                        </ul>
                    </div>
                </div>
                
                <div class="timeline-module">
                    <div class="module-number">2</div>
                    <div class="module-content">
                        <h3><i class="fab fa-css3-alt"></i> Estilización con CSS</h3>
                        <p>Selectores, box model, flexbox, grid, diseño responsive y animaciones.</p>
                        <ul class="module-topics">
                            <li>Box Model y layout</li>
                            <li>Flexbox y Grid</li>
                            <li>Responsive Design</li>
                        </ul>
                    </div>
                </div>
                
                <div class="timeline-module">
                    <div class="module-number">3</div>
                    <div class="module-content">
                        <h3><i class="fab fa-js"></i> Interactividad con JavaScript</h3>
                        <p>Variables, funciones, eventos, manipulación del DOM y fetch API.</p>
                        <ul class="module-topics">
                            <li>Manipulación del DOM</li>
                            <li>Eventos y funciones</li>
                            <li>Fetch API y promesas</li>
                        </ul>
                    </div>
                </div>
                
                <div class="timeline-module">
                    <div class="module-number">4</div>
                    <div class="module-content">
                        <h3><i class="fab fa-js"></i> Mejoras con jQuery</h3>
                        <p>Selectores simplificados, manipulación del DOM, eventos y AJAX.</p>
                        <ul class="module-topics">
                            <li>Manipulación del DOM simplificada</li>
                            <li>Eventos y animaciones</li>
                            <li>AJAX para carga dinámica</li>
                        </ul>
                    </div>
                </div>
                
                <div class="timeline-module">
                    <div class="module-number">5</div>
                    <div class="module-content">
                        <h3><i class="fas fa-magic"></i> Animaciones con GSAP</h3>
                        <p>Tweens, timelines, ScrollTrigger y animaciones avanzadas.</p>
                        <ul class="module-topics">
                            <li>Timelines y tweens</li>
                            <li>ScrollTrigger</li>
                            <li>Interacciones complejas</li>
                        </ul>
                    </div>
                </div>
                
                <div class="timeline-module">
                    <div class="module-number">6</div>
                    <div class="module-content">
                        <h3><i class="fab fa-php"></i> Backend con PHP</h3>
                        <p>Sintaxis, estructuras de control, formularios y base de datos.</p>
                        <ul class="module-topics">
                            <li>Procesamiento de formularios</li>
                            <li>Conexión con bases de datos</li>
                            <li>Sesiones y cookies</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="modules-cta">
                <a href="index.php?page=register" class="btn btn-primary">Comenzar ahora</a>
                <a href="index.php?page=modules" class="btn btn-outline">Ver detalles de los módulos</a>
            </div>
        </div>
    </section>

    <section class="testimonials-section">
        <div class="container">
            <h2 class="section-title">Lo que dicen nuestros estudiantes</h2>
            
            <div class="testimonials-slider">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"WebCraft Academy transformó mi forma de aprender a programar. El enfoque práctico y la gamificación me mantuvieron motivado para completar todos los módulos. ¡Ahora trabajo como desarrollador frontend!"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <img src="assets/images/testimonials/user1.jpg" alt="Avatar de Carlos">
                        </div>
                        <div class="author-info">
                            <h4 class="author-name">Carlos Rodríguez</h4>
                            <p class="author-title">Desarrollador Frontend</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"Lo que más me gustó es que cada concepto lo aplicas inmediatamente en proyectos reales. No es solo teoría, sino que aprendes construyendo cosas que puedes mostrar en tu portafolio."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <img src="assets/images/testimonials/user2.jpg" alt="Avatar de María">
                        </div>
                        <div class="author-info">
                            <h4 class="author-name">María Gómez</h4>
                            <p class="author-title">Diseñadora Web</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"Como profesor de informática, recomiendo WebCraft Academy a mis estudiantes. La plataforma combina perfectamente diversión y aprendizaje efectivo, con un sistema de progresión que los mantiene enganchados."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <img src="assets/images/testimonials/user3.jpg" alt="Avatar de Miguel">
                        </div>
                        <div class="author-info">
                            <h4 class="author-name">Miguel Álvarez</h4>
                            <p class="author-title">Profesor de Informática</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="testimonials-nav">
                <button class="nav-prev" aria-label="Testimonio anterior"><i class="fas fa-arrow-left"></i></button>
                <div class="nav-dots">
                    <button class="dot active" aria-label="Ir al testimonio 1"></button>
                    <button class="dot" aria-label="Ir al testimonio 2"></button>
                    <button class="dot" aria-label="Ir al testimonio 3"></button>
                </div>
                <button class="nav-next" aria-label="Testimonio siguiente"><i class="fas fa-arrow-right"></i></button>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">¿Listo para empezar tu viaje como desarrollador web?</h2>
                <p class="cta-text">Regístrate hoy y obtén acceso a todos los módulos básicos de forma gratuita.</p>
                <div class="cta-buttons">
                    <a href="index.php?page=register" class="btn btn-primary btn-lg">Crear cuenta gratis</a>
                    <a href="index.php?page=login" class="btn btn-outline btn-lg">Iniciar sesión</a>
                </div>
            </div>
        </div>
    </section>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Slider de testimonios
        const slider = document.querySelector('.testimonials-slider');
        const navDots = document.querySelectorAll('.nav-dots .dot');
        const prevBtn = document.querySelector('.nav-prev');
        const nextBtn = document.querySelector('.nav-next');
        
        let currentSlide = 0;
        const totalSlides = document.querySelectorAll('.testimonial-card').length;
        
        function updateSlider() {
            slider.style.transform = `translateX(-${currentSlide * 100}%)`;
            
            // Actualizar dots
            navDots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentSlide);
            });
        }
        
        // Event listeners para navegación
        prevBtn.addEventListener('click', () => {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            updateSlider();
        });
        
        nextBtn.addEventListener('click', () => {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateSlider();
        });
        
        // Event listeners para dots
        navDots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentSlide = index;
                updateSlider();
            });
        });
        
        // Auto-rotación del slider
        let sliderInterval = setInterval(() => {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateSlider();
        }, 5000);
        
        // Detener auto-rotación al interactuar con el slider
        slider.addEventListener('mouseenter', () => {
            clearInterval(sliderInterval);
        });
        
        slider.addEventListener('mouseleave', () => {
            sliderInterval = setInterval(() => {
                currentSlide = (currentSlide + 1) % totalSlides;
                updateSlider();
            }, 5000);
        });
    });
    </script>
<?php else: ?>
    <!-- Versión para usuarios autenticados -->
    <div class="container">
        <div class="welcome-back-section">
            <h1>Bienvenido de nuevo, <?php echo htmlspecialchars(getCurrentUser()['display_name'] ?? getCurrentUser()['username']); ?></h1>
            <p>Continúa tu viaje de aprendizaje desde donde lo dejaste.</p>
            
            <div class="quick-actions">
                <a href="index.php?page=dashboard" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <div class="action-info">
                        <h3>Dashboard</h3>
                        <p>Revisa tu progreso y estadísticas</p>
                    </div>
                </a>
                
                <a href="index.php?page=modules" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="action-info">
                        <h3>Continuar aprendiendo</h3>
                        <p>Retoma tus lecciones</p>
                    </div>
                </a>
                
                <a href="index.php?page=challenges" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="action-info">
                        <h3>Desafíos diarios</h3>
                        <p>Pon a prueba tus habilidades</p>
                    </div>
                </a>
                
                <a href="index.php?page=forum" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="action-info">
                        <h3>Foro</h3>
                        <p>Conecta con la comunidad</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>
