<?php
/**
 * Página de módulos de WebCraft Academy
 * 
 * Esta página muestra todos los módulos disponibles en la plataforma
 * y permite al usuario acceder a ellos.
 */

// Verificar si se está accediendo directamente
if (!defined('WEBCRAFT')) {
    header('Location: index.php');
    exit;
}

// Incluir cargador de módulos
require_once 'modules/modulo-loader.php';

// Obtener todos los módulos
$modulos = getModulos();

// Obtener progreso del usuario si está logueado
$progreso = [];
if (isUserLoggedIn()) {
    $progreso = getUserProgress($_SESSION['user_id']);
}

// Obtener recomendación de próxima lección
$recomendacion = null;
if (isUserLoggedIn()) {
    $recomendacion = getNextRecommendedLesson($_SESSION['user_id']);
}
?>

<div class="modules-container">
    <div class="modules-header">
        <h1>Módulos de Aprendizaje</h1>
        <p>Selecciona un módulo para comenzar o continuar tu aprendizaje. Cada módulo contiene una serie de lecciones enfocadas en un tema específico.</p>
    </div>
    
    <?php if ($recomendacion): ?>
    <div class="recommendation-card">
        <div class="recommendation-icon">
            <i class="fas fa-lightbulb"></i>
        </div>
        <div class="recommendation-content">
            <h3>Continúa tu aprendizaje</h3>
            <p>Te recomendamos seguir con:</p>
            <h4><?php echo htmlspecialchars($recomendacion['titulo']); ?></h4>
            <p class="recommendation-module"><?php echo htmlspecialchars($recomendacion['modulo_titulo']); ?></p>
        </div>
        <a href="index.php?page=lessons&module_id=<?php echo $recomendacion['modulo_id']; ?>&lesson_id=<?php echo $recomendacion['id']; ?>" class="btn btn-primary">
            Continuar <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    <?php endif; ?>
    
    <div class="modules-grid">
        <?php foreach ($modulos as $modulo): 
            // Calcular progreso
            $porcentajeProgreso = 0;
            if (isset($progreso[$modulo['id']])) {
                $porcentajeProgreso = $progreso[$modulo['id']]['porcentaje'];
            }
            
            // Determinar el estado del módulo
            $estadoModulo = 'locked';
            if ($modulo['id'] == 1 || (isset($modulo['requisitos_previos']) && empty($modulo['requisitos_previos']))) {
                $estadoModulo = 'available';
            } elseif (isset($modulo['requisitos_previos'])) {
                $requisitosCompletados = true;
                foreach ($modulo['requisitos_previos'] as $requisitoId) {
                    if (!isset($progreso[$requisitoId]) || $progreso[$requisitoId]['porcentaje'] < 100) {
                        $requisitosCompletados = false;
                        break;
                    }
                }
                if ($requisitosCompletados) {
                    $estadoModulo = 'available';
                }
            }
            
            // Si hay progreso, marcar como disponible
            if ($porcentajeProgreso > 0) {
                $estadoModulo = 'available';
            }
            
            // Si el progreso es 100%, marcar como completado
            if ($porcentajeProgreso == 100) {
                $estadoModulo = 'completed';
            }
        ?>
        <div class="module-card <?php echo $estadoModulo; ?>">
            <div class="module-card-header">
                <div class="module-icon">
                    <?php 
                    $iconClass = 'fas fa-code';
                    switch ($modulo['id']) {
                        case 1: $iconClass = 'fab fa-html5'; break;
                        case 2: $iconClass = 'fab fa-css3-alt'; break;
                        case 3: $iconClass = 'fab fa-js'; break;
                        case 4: $iconClass = 'fab fa-jquery'; break;
                        case 5: $iconClass = 'fas fa-film'; break; // GSAP (animaciones)
                        case 6: $iconClass = 'fab fa-php'; break;
                    }
                    ?>
                    <i class="<?php echo $iconClass; ?>"></i>
                </div>
                <div class="module-title">
                    <h2><?php echo htmlspecialchars($modulo['titulo']); ?></h2>
                    <?php if ($estadoModulo == 'completed'): ?>
                    <span class="badge badge-success">
                        <i class="fas fa-check-circle"></i> Completado
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="module-card-body">
                <p class="module-description"><?php echo htmlspecialchars($modulo['descripcion']); ?></p>
                
                <div class="module-meta">
                    <span class="module-duration">
                        <i class="far fa-clock"></i> <?php echo $modulo['duracion_estimada']; ?>
                    </span>
                    <span class="module-level">
                        <i class="fas fa-signal"></i> <?php echo $modulo['nivel']; ?>
                    </span>
                    <span class="module-lessons">
                        <i class="fas fa-book"></i> <?php echo count($modulo['lecciones']); ?> lecciones
                    </span>
                </div>
                
                <?php if ($estadoModulo != 'locked' && isUserLoggedIn()): ?>
                <div class="module-progress">
                    <div class="progress-label">
                        <span>Progreso: <?php echo $porcentajeProgreso; ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress" style="width: <?php echo $porcentajeProgreso; ?>%"></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="module-card-footer">
                <?php if ($estadoModulo == 'locked'): ?>
                <div class="module-status">
                    <i class="fas fa-lock"></i> Bloqueado
                    <p class="lock-message">Completa los módulos anteriores para desbloquear</p>
                </div>
                <?php else: ?>
                <a href="index.php?page=lessons&module_id=<?php echo $modulo['id']; ?>" class="btn btn-primary">
                    <?php if ($porcentajeProgreso > 0 && $porcentajeProgreso < 100): ?>
                    <i class="fas fa-play"></i> Continuar
                    <?php elseif ($porcentajeProgreso == 100): ?>
                    <i class="fas fa-redo"></i> Repasar
                    <?php else: ?>
                    <i class="fas fa-play"></i> Comenzar
                    <?php endif; ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="modules-roadmap">
        <h2>Ruta de Aprendizaje</h2>
        <p>Para obtener los mejores resultados, te recomendamos seguir los módulos en este orden:</p>
        
        <div class="roadmap-visualization">
            <?php
            $totalModulos = count($modulos);
            $modulosPorFila = min(3, $totalModulos);
            $filas = ceil($totalModulos / $modulosPorFila);
            
            $indice = 0;
            for ($i = 0; $i < $filas; $i++):
            ?>
            <div class="roadmap-row">
                <?php for ($j = 0; $j < $modulosPorFila && $indice < $totalModulos; $j++, $indice++): 
                    $modulo = $modulos[$indice];
                    $completado = isset($progreso[$modulo['id']]) && $progreso[$modulo['id']]['porcentaje'] == 100;
                ?>
                <div class="roadmap-item <?php echo $completado ? 'completed' : ''; ?>">
                    <div class="roadmap-node">
                        <?php if ($completado): ?>
                        <i class="fas fa-check-circle"></i>
                        <?php else: ?>
                        <span class="node-number"><?php echo $indice + 1; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="roadmap-content">
                        <h3><?php echo htmlspecialchars($modulo['titulo']); ?></h3>
                        <p><?php 
                            $descripcionCorta = strlen($modulo['descripcion']) > 60 ? 
                                substr($modulo['descripcion'], 0, 60) . '...' : 
                                $modulo['descripcion'];
                            echo htmlspecialchars($descripcionCorta); 
                        ?></p>
                    </div>
                </div>
                <?php if ($j < $modulosPorFila - 1 && $indice < $totalModulos - 1): ?>
                <div class="roadmap-connector"></div>
                <?php endif; ?>
                <?php endfor; ?>
            </div>
            <?php if ($i < $filas - 1): ?>
            <div class="roadmap-vertical-connector"></div>
            <?php endif; ?>
            <?php endfor; ?>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para la página de módulos */

.modules-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.modules-header {
    text-align: center;
    margin-bottom: 40px;
}

.modules-header h1 {
    font-size: 2.5rem;
    margin-bottom: 15px;
    color: var(--primary-color);
}

.modules-header p {
    font-size: 1.1rem;
    color: var(--gray-600);
    max-width: 800px;
    margin: 0 auto;
}

.recommendation-card {
    display: flex;
    align-items: center;
    background-color: var(--light-color);
    border-left: 5px solid var(--primary-color);
    border-radius: var(--border-radius);
    padding: 20px;
    margin-bottom: 40px;
    box-shadow: var(--shadow-sm);
}

.recommendation-icon {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-right: 20px;
}

.recommendation-content {
    flex: 1;
}

.recommendation-content h3 {
    font-size: 1.4rem;
    margin-bottom: 5px;
    color: var(--gray-800);
}

.recommendation-content h4 {
    font-size: 1.2rem;
    margin: 10px 0 5px;
    color: var(--primary-color);
}

.recommendation-module {
    font-size: 0.9rem;
    color: var(--gray-600);
    margin-top: 0;
}

.modules-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 30px;
    margin-bottom: 50px;
}

.module-card {
    background-color: white;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow-md);
    transition: transform var(--transition-normal), box-shadow var(--transition-normal);
    display: flex;
    flex-direction: column;
}

.module-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.module-card.locked {
    opacity: 0.7;
    filter: grayscale(30%);
}

.module-card-header {
    display: flex;
    align-items: center;
    padding: 20px;
    background-color: var(--gray-100);
    border-bottom: 1px solid var(--gray-200);
}

.module-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-right: 15px;
}

.module-card.completed .module-icon {
    background-color: var(--success-color);
}

.module-card.locked .module-icon {
    background-color: var(--gray-500);
}

.module-title {
    flex: 1;
}

.module-title h2 {
    font-size: 1.3rem;
    margin: 0 0 5px;
    font-weight: 600;
}

.badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.badge-success {
    background-color: rgba(6, 214, 160, 0.15);
    color: var(--success-color);
}

.module-card-body {
    padding: 20px;
    flex: 1;
}

.module-description {
    font-size: 0.95rem;
    color: var(--gray-700);
    margin-bottom: 15px;
}

.module-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 20px;
    font-size: 0.85rem;
    color: var(--gray-600);
}

.module-meta span {
    display: flex;
    align-items: center;
}

.module-meta i {
    margin-right: 5px;
}

.module-progress {
    margin-top: 10px;
}

.progress-label {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    margin-bottom: 5px;
    color: var(--gray-700);
}

.progress-bar {
    height: 8px;
    background-color: var(--gray-200);
    border-radius: 4px;
    overflow: hidden;
}

.progress {
    height: 100%;
    background-color: var(--primary-color);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.module-card.completed .progress {
    background-color: var(--success-color);
}

.module-card-footer {
    padding: 15px 20px;
    border-top: 1px solid var(--gray-200);
    display: flex;
    justify-content: center;
}

.module-status {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    color: var(--gray-600);
}

.module-status i {
    font-size: 1.2rem;
    margin-bottom: 5px;
}

.lock-message {
    font-size: 0.8rem;
    margin: 5px 0 0;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 20px;
    border-radius: var(--border-radius);
    font-weight: 500;
    font-size: 1rem;
    cursor: pointer;
    transition: all var(--transition-fast);
    text-decoration: none;
    border: none;
}

.btn i {
    margin-right: 8px;
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background-color: rgba(67, 97, 238, 0.9);
}

/* Roadmap */
.modules-roadmap {
    margin-top: 60px;
    padding-top: 40px;
    border-top: 1px solid var(--gray-200);
}

.modules-roadmap h2 {
    text-align: center;
    margin-bottom: 10px;
    font-size: 1.8rem;
    color: var(--gray-800);
}

.modules-roadmap p {
    text-align: center;
    margin-bottom: 40px;
    color: var(--gray-600);
}

.roadmap-visualization {
    max-width: 900px;
    margin: 0 auto;
}

.roadmap-row {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 30px;
}

.roadmap-item {
    display: flex;
    align-items: center;
    background-color: white;
    padding: 15px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-sm);
    max-width: 250px;
    transition: transform 0.2s ease;
}

.roadmap-item:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}

.roadmap-item.completed {
    border-left: 3px solid var(--success-color);
}

.roadmap-node {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-weight: bold;
}

.roadmap-item.completed .roadmap-node {
    background-color: var(--success-color);
}

.roadmap-content h3 {
    font-size: 1rem;
    margin: 0 0 5px;
    color: var(--gray-800);
}

.roadmap-content p {
    font-size: 0.8rem;
    margin: 0;
    color: var(--gray-600);
    text-align: left;
}

.roadmap-connector {
    flex: 1;
    height: 2px;
    background-color: var(--gray-300);
    max-width: 50px;
    min-width: 20px;
    position: relative;
}

.roadmap-connector::after {
    content: "";
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-top: 5px solid transparent;
    border-bottom: 5px solid transparent;
    border-left: 5px solid var(--gray-300);
}

.roadmap-vertical-connector {
    width: 2px;
    height: 30px;
    background-color: var(--gray-300);
    margin: 0 auto;
    position: relative;
}

.roadmap-vertical-connector::after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid var(--gray-300);
}

/* Responsive */
@media (max-width: 768px) {
    .modules-grid {
        grid-template-columns: 1fr;
    }
    
    .recommendation-card {
        flex-direction: column;
        text-align: center;
    }
    
    .recommendation-icon {
        margin-right: 0;
        margin-bottom: 15px;
    }
    
    .roadmap-row {
        flex-direction: column;
    }
    
    .roadmap-connector {
        display: none;
    }
    
    .roadmap-item {
        width: 100%;
        max-width: 100%;
        margin-bottom: 15px;
    }
}

/* Tema oscuro */
[data-theme="dark"] .module-card {
    background-color: var(--gray-800);
}

[data-theme="dark"] .module-card-header {
    background-color: var(--gray-900);
    border-bottom: 1px solid var(--gray-700);
}

[data-theme="dark"] .module-title h2,
[data-theme="dark"] .module-description,
[data-theme="dark"] .roadmap-content h3 {
    color: var(--light-color);
}

[data-theme="dark"] .module-meta,
[data-theme="dark"] .progress-label,
[data-theme="dark"] .roadmap-content p {
    color: var(--gray-400);
}

[data-theme="dark"] .module-card-footer {
    border-top: 1px solid var(--gray-700);
}

[data-theme="dark"] .progress-bar {
    background-color: var(--gray-700);
}

[data-theme="dark"] .modules-roadmap {
    border-top: 1px solid var(--gray-700);
}

[data-theme="dark"] .modules-roadmap h2 {
    color: var(--light-color);
}

[data-theme="dark"] .roadmap-item {
    background-color: var(--gray-800);
}

[data-theme="dark"] .recommendation-card {
    background-color: var(--gray-800);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animación de entrada para las tarjetas de módulos
    const moduleCards = document.querySelectorAll('.module-card');
    
    moduleCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        }, index * 100);
    });
});
</script>
