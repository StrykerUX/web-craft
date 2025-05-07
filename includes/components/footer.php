<?php
/**
 * Componente de pie de página para WebCraft Academy
 * 
 * Este componente contiene los enlaces del footer, información de contacto,
 * redes sociales y derechos de autor.
 */

// Prevenir acceso directo a este archivo
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Año actual para copyright
$currentYear = date('Y');
?>

<div class="container">
    <div class="footer-grid">
        <!-- Sección Acerca de -->
        <div class="footer-about">
            <h3>WebCraft Academy</h3>
            <p>Plataforma educativa interactiva para aprender desarrollo web mediante un enfoque práctico y gamificado.</p>
            <div class="footer-social">
                <a href="#" class="social-icon" aria-label="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="social-icon" aria-label="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="social-icon" aria-label="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="social-icon" aria-label="GitHub">
                    <i class="fab fa-github"></i>
                </a>
                <a href="#" class="social-icon" aria-label="LinkedIn">
                    <i class="fab fa-linkedin-in"></i>
                </a>
            </div>
        </div>

        <!-- Enlaces de navegación -->
        <div class="footer-links">
            <h4>Aprendizaje</h4>
            <ul>
                <li><a href="index.php?page=modules">Módulos</a></li>
                <li><a href="index.php?page=challenges">Desafíos</a></li>
                <li><a href="index.php?page=projects">Proyectos</a></li>
                <li><a href="index.php?page=forum">Foro</a></li>
                <li><a href="index.php?page=resources">Recursos</a></li>
            </ul>
        </div>

        <!-- Enlaces de recursos -->
        <div class="footer-links">
            <h4>Recursos</h4>
            <ul>
                <li><a href="index.php?page=blog">Blog</a></li>
                <li><a href="index.php?page=documentation">Documentación</a></li>
                <li><a href="index.php?page=tutorials">Tutoriales</a></li>
                <li><a href="index.php?page=faq">Preguntas frecuentes</a></li>
                <li><a href="index.php?page=roadmap">Roadmap</a></li>
            </ul>
        </div>

        <!-- Enlaces de Empresa -->
        <div class="footer-links">
            <h4>Empresa</h4>
            <ul>
                <li><a href="index.php?page=about">Acerca de</a></li>
                <li><a href="index.php?page=contact">Contacto</a></li>
                <li><a href="index.php?page=terms">Términos de servicio</a></li>
                <li><a href="index.php?page=privacy">Política de privacidad</a></li>
                <li><a href="index.php?page=careers">Empleo</a></li>
            </ul>
        </div>
    </div>

    <!-- Parte inferior del footer -->
    <div class="footer-bottom">
        <p>&copy; <?php echo $currentYear; ?> WebCraft Academy. Todos los derechos reservados.</p>
        <p>Desarrollado con <i class="fas fa-heart"></i> por StrykerUX</p>
    </div>
</div>
