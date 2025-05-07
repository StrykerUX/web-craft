<?php
// Verificar que el script se ejecuta dentro del contexto adecuado
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}
?>
<div class="container">
    <div class="footer-content">
        <div class="footer-logo">
            <img src="assets/images/webcraft-logo-white.svg" alt="WebCraft Academy" class="footer-logo-img">
            <div class="footer-tagline">Aprende desarrollo web de manera interactiva y divertida</div>
        </div>
        
        <div class="footer-nav">
            <div class="footer-column">
                <h4>Plataforma</h4>
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="index.php?page=modules">Módulos</a></li>
                    <li><a href="index.php?page=challenges">Desafíos</a></li>
                    <li><a href="index.php?page=forum">Foro</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h4>Recursos</h4>
                <ul>
                    <li><a href="index.php?page=faq">FAQ</a></li>
                    <li><a href="index.php?page=docs">Documentación</a></li>
                    <li><a href="index.php?page=blog">Blog</a></li>
                    <li><a href="index.php?page=support">Soporte</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h4>Legal</h4>
                <ul>
                    <li><a href="index.php?page=terms">Términos y Condiciones</a></li>
                    <li><a href="index.php?page=privacy">Política de Privacidad</a></li>
                    <li><a href="index.php?page=cookies">Política de Cookies</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h4>Comunidad</h4>
                <ul>
                    <li><a href="https://github.com/StrykerUX/web-craft" target="_blank" rel="noopener noreferrer">GitHub <i class="fas fa-external-link-alt"></i></a></li>
                    <li><a href="https://twitter.com/webcraftacademy" target="_blank" rel="noopener noreferrer">Twitter <i class="fas fa-external-link-alt"></i></a></li>
                    <li><a href="https://discord.gg/webcraftacademy" target="_blank" rel="noopener noreferrer">Discord <i class="fas fa-external-link-alt"></i></a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="footer-bottom">
        <div class="copyright">
            &copy; <?php echo date('Y'); ?> WebCraft Academy. Todos los derechos reservados.
        </div>
        
        <div class="version">
            v<?php echo APP_VERSION; ?>
        </div>
        
        <div class="footer-social">
            <a href="https://github.com/StrykerUX/web-craft" target="_blank" rel="noopener noreferrer" aria-label="GitHub">
                <i class="fab fa-github"></i>
            </a>
            <a href="https://twitter.com/webcraftacademy" target="_blank" rel="noopener noreferrer" aria-label="Twitter">
                <i class="fab fa-twitter"></i>
            </a>
            <a href="https://discord.gg/webcraftacademy" target="_blank" rel="noopener noreferrer" aria-label="Discord">
                <i class="fab fa-discord"></i>
            </a>
        </div>
    </div>
</div>
