<?php
// Verificar que el script se ejecuta dentro del contexto adecuado
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}
?>
<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Aprende desarrollo web<br>de forma práctica y divertida</h1>
            <p class="hero-subtitle">WebCraft Academy te enseña a construir sitios web asombrosos a través de proyectos reales y desafíos interactivos.</p>
            
            <div class="hero-cta">
                <a href="index.php?page=register" class="btn btn-primary btn-lg">Comenzar ahora</a>
                <a href="#como-funciona" class="btn btn-outline btn-lg">Cómo funciona</a>
            </div>
            
            <div class="hero-features">
                <div class="feature">
                    <i class="fas fa-code"></i>
                    <span>Aprende haciendo</span>
                </div>
                <div class="feature">
                    <i class="fas fa-gamepad"></i>
                    <span>Sistema gamificado</span>
                </div>
                <div class="feature">
                    <i class="fas fa-users"></i>
                    <span>Comunidad activa</span>
                </div>
                <div class="feature">
                    <i class="fas fa-certificate"></i>
                    <span>Proyectos reales</span>
                </div>
            </div>
        </div>
        
        <div class="hero-image">
            <img src="assets/images/hero-illustration.svg" alt="WebCraft Academy - Editor de código interactivo" class="main-illustration">
            <!-- Elementos decorativos -->
            <div class="code-element html-tag"><span>&lt;html&gt;</span></div>
            <div class="code-element css-rule"><span>.awesome {}</span></div>
            <div class="code-element js-function"><span>function()</span></div>
        </div>
    </div>
    
    <!-- Elementos decorativos de fondo -->
    <div class="hero-bg-element element-1"></div>
    <div class="hero-bg-element element-2"></div>
    <div class="hero-bg-element element-3"></div>
</section>

<!-- Características Section -->
<section class="features-section" id="como-funciona">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Cómo funciona WebCraft Academy</h2>
            <p class="section-subtitle">Una experiencia educativa diseñada para hacer el aprendizaje efectivo y entretenido</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-hands-on"></i>
                </div>
                <h3>Aprendizaje Práctico</h3>
                <p>80% práctica, 20% teoría. Aprende escribiendo código real desde el primer día en nuestro editor interactivo.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3>Gamificación</h3>
                <p>Gana puntos, sube de nivel y desbloquea insignias mientras dominas nuevas habilidades y conceptos.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <h3>Proyectos Reales</h3>
                <p>Construye proyectos genuinos que puedes agregar a tu portafolio profesional mientras aprendes.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-comment-dots"></i>
                </div>
                <h3>Feedback Instantáneo</h3>
                <p>Recibe retroalimentación inmediata sobre tu código y aprende de tus errores en tiempo real.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Comunidad Activa</h3>
                <p>Forma parte de una comunidad de estudiantes y desarrolladores que aprenden juntos.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-road"></i>
                </div>
                <h3>Progresión Clara</h3>
                <p>Sigue un camino de aprendizaje estructurado desde lo más básico hasta conceptos avanzados.</p>
            </div>
        </div>
    </div>
</section>

<!-- Módulos Section -->
<section class="modules-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Explora nuestros módulos de aprendizaje</h2>
            <p class="section-subtitle">Un plan de estudios completo para convertirte en desarrollador web full-stack</p>
        </div>
        
        <div class="modules-grid">
            <?php
            // Obtener módulos desde la base de datos
            try {
                $stmt = getDbConnection()->query("SELECT module_id, module_name, module_description, icon_class FROM modules WHERE is_active = TRUE ORDER BY module_order");
                $modules = $stmt->fetchAll();
            } catch (PDOException $e) {
                $modules = [];
                if (defined('DEV_MODE') && DEV_MODE) {
                    echo "<!-- Error: " . $e->getMessage() . " -->";
                }
            }
            
            // Mostrar módulos
            if (isset($modules) && !empty($modules)):
                foreach ($modules as $module):
            ?>
            <div class="module-card">
                <div class="module-icon">
                    <i class="<?php echo htmlspecialchars($module['icon_class']); ?>"></i>
                </div>
                <div class="module-content">
                    <h3><?php echo htmlspecialchars($module['module_name']); ?></h3>
                    <p><?php echo htmlspecialchars($module['module_description']); ?></p>
                    <a href="index.php?page=modules&module_id=<?php echo $module['module_id']; ?>" class="btn btn-outline btn-sm">Ver módulo</a>
                </div>
            </div>
            <?php 
                endforeach; 
            else: 
                // Mostrar módulos de muestra si no hay datos en la BD 
            ?>
            <div class="module-card">
                <div class="module-icon">
                    <i class="fab fa-html5"></i>
                </div>
                <div class="module-content">
                    <h3>Fundamentos HTML</h3>
                    <p>Introducción a etiquetas, estructura básica de documentos, anatomía de una página web, elementos semánticos y formularios.</p>
                    <a href="index.php?page=modules&module_id=1" class="btn btn-outline btn-sm">Ver módulo</a>
                </div>
            </div>
            
            <div class="module-card">
                <div class="module-icon">
                    <i class="fab fa-css3-alt"></i>
                </div>
                <div class="module-content">
                    <h3>Estilización con CSS</h3>
                    <p>Selectores y especificidad, Box Model y layout, Flexbox y Grid, Responsive Design, Animaciones y transiciones, Variables CSS.</p>
                    <a href="index.php?page=modules&module_id=2" class="btn btn-outline btn-sm">Ver módulo</a>
                </div>
            </div>
            
            <div class="module-card">
                <div class="module-icon">
                    <i class="fab fa-js"></i>
                </div>
                <div class="module-content">
                    <h3>Interactividad con JavaScript</h3>
                    <p>Variables y tipos de datos, Funciones y eventos, Manipulación del DOM, Validación de formularios, Local Storage, Fetch API.</p>
                    <a href="index.php?page=modules&module_id=3" class="btn btn-outline btn-sm">Ver módulo</a>
                </div>
            </div>
            
            <div class="module-card">
                <div class="module-icon">
                    <i class="fas fa-code"></i>
                </div>
                <div class="module-content">
                    <h3>Mejoras con jQuery</h3>
                    <p>Selectores simplificados, Manipulación del DOM, Eventos y animaciones, AJAX para carga dinámica.</p>
                    <a href="index.php?page=modules&module_id=4" class="btn btn-outline btn-sm">Ver módulo</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="view-all-modules">
            <a href="index.php?page=modules" class="btn btn-primary">Ver todos los módulos</a>
        </div>
    </div>
</section>

<!-- Testimonios Section -->
<section class="testimonials-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Lo que dicen nuestros estudiantes</h2>
            <p class="section-subtitle">Historias de éxito de quienes han aprendido con WebCraft Academy</p>
        </div>
        
        <div class="testimonials-slider">
            <div class="testimonial">
                <div class="testimonial-content">
                    <p>"WebCraft Academy transformó mi forma de aprender programación. Los proyectos prácticos me ayudaron a entender conceptos que antes me parecían imposibles."</p>
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="assets/images/testimonials/user1.jpg" alt="Ana García">
                    </div>
                    <div class="author-info">
                        <h4>Ana García</h4>
                        <p>Desarrolladora Front-end</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial">
                <div class="testimonial-content">
                    <p>"Pasé de no saber nada de código a conseguir mi primer trabajo como desarrollador web en 6 meses gracias a los proyectos que construí en WebCraft."</p>
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="assets/images/testimonials/user2.jpg" alt="Carlos Rodríguez">
                    </div>
                    <div class="author-info">
                        <h4>Carlos Rodríguez</h4>
                        <p>Desarrollador Full-Stack</p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial">
                <div class="testimonial-content">
                    <p>"El sistema de gamificación hace que quiera seguir aprendiendo. He intentado muchos cursos antes, pero este es el primero que me mantiene motivada constantemente."</p>
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="assets/images/testimonials/user3.jpg" alt="Lucía Martínez">
                    </div>
                    <div class="author-info">
                        <h4>Lucía Martínez</h4>
                        <p>Estudiante de Diseño Web</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="testimonials-navigation">
            <button class="nav-prev" aria-label="Anterior testimonio"><i class="fas fa-chevron-left"></i></button>
            <div class="nav-indicators">
                <span class="indicator active"></span>
                <span class="indicator"></span>
                <span class="indicator"></span>
            </div>
            <button class="nav-next" aria-label="Siguiente testimonio"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>¿Listo para comenzar tu viaje en desarrollo web?</h2>
            <p>Únete a miles de estudiantes que ya están construyendo el futuro de la web con WebCraft Academy.</p>
            <div class="cta-buttons">
                <a href="index.php?page=register" class="btn btn-primary btn-lg">Crear cuenta gratuita</a>
                <a href="index.php?page=modules" class="btn btn-outline btn-lg">Explorar módulos</a>
            </div>
        </div>
    </div>
    
    <!-- Elementos decorativos de fondo -->
    <div class="cta-bg-element element-1"></div>
    <div class="cta-bg-element element-2"></div>
</section>
