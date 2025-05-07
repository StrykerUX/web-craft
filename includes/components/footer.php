<?php
/**
 * Componente de footer para WebCraft Academy
 * 
 * Este componente contiene el pie de página con enlaces y créditos.
 * Actualizado para funcionar con Bootstrap 5.
 */

// Prevenir acceso directo a este archivo
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Obtener el año actual para el copyright
$currentYear = date('Y');
?>

<div class="container">
    <div class="row footer-content">
        <!-- Columna de información principal -->
        <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
            <div class="footer-about">
                <h3>WebCraft Academy</h3>
                <p>Plataforma educativa interactiva para aprender desarrollo web mediante un enfoque práctico y gamificado.</p>
                
                <!-- Redes sociales -->
                <div class="footer-social">
                    <a href="#" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" aria-label="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" aria-label="GitHub">
                        <i class="fab fa-github"></i>
                    </a>
                    <a href="#" aria-label="Discord">
                        <i class="fab fa-discord"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Columna de Aprendizaje -->
        <div class="col-lg-2 col-md-6 col-6 mb-4 mb-lg-0">
            <div class="footer-links">
                <h4>Aprendizaje</h4>
                <ul>
                    <li><a href="index.php?page=modules">Módulos</a></li>
                    <li><a href="index.php?page=lessons">Lecciones</a></li>
                    <li><a href="index.php?page=challenges">Desafíos</a></li>
                    <li><a href="index.php?page=projects">Proyectos</a></li>
                    <li><a href="index.php?page=forum">Foro</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Columna de Recursos -->
        <div class="col-lg-2 col-md-6 col-6 mb-4 mb-lg-0">
            <div class="footer-links">
                <h4>Recursos</h4>
                <ul>
                    <li><a href="index.php?page=blog">Blog</a></li>
                    <li><a href="index.php?page=tutorials">Tutoriales</a></li>
                    <li><a href="index.php?page=documentation">Documentación</a></li>
                    <li><a href="index.php?page=cheatsheets">Cheatsheets</a></li>
                    <li><a href="index.php?page=roadmap">Ruta de aprendizaje</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Columna de Empresa -->
        <div class="col-lg-2 col-md-6 col-6 mb-4 mb-lg-0">
            <div class="footer-links">
                <h4>Empresa</h4>
                <ul>
                    <li><a href="index.php?page=about">Acerca de</a></li>
                    <li><a href="index.php?page=team">Equipo</a></li>
                    <li><a href="index.php?page=pricing">Planes</a></li>
                    <li><a href="index.php?page=contact">Contacto</a></li>
                    <li><a href="index.php?page=careers">Empleo</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Columna de Soporte -->
        <div class="col-lg-2 col-md-6 col-6 mb-4 mb-lg-0">
            <div class="footer-links">
                <h4>Soporte</h4>
                <ul>
                    <li><a href="index.php?page=help">Ayuda</a></li>
                    <li><a href="index.php?page=faqs">FAQs</a></li>
                    <li><a href="index.php?page=terms">Términos</a></li>
                    <li><a href="index.php?page=privacy">Privacidad</a></li>
                    <li><a href="index.php?page=cookies">Cookies</a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Línea separadora -->
    <hr class="footer-divider">
    
    <!-- Pie de página con copyright -->
    <div class="row footer-bottom">
        <div class="col-md-6 mb-3 mb-md-0">
            <p class="copyright mb-0">
                &copy; <?php echo $currentYear; ?> WebCraft Academy. Todos los derechos reservados.
            </p>
        </div>
        <div class="col-md-6 text-md-end">
            <p class="version mb-0">
                Versión 1.0.0
            </p>
        </div>
    </div>
</div>
