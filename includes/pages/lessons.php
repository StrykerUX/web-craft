<?php
/**
 * Página de lecciones de WebCraft Academy
 * 
 * Esta página muestra el contenido de una lección específica o
 * lista todas las lecciones de un módulo.
 */

// Verificar si se está accediendo directamente
if (!defined('WEBCRAFT')) {
    header('Location: index.php');
    exit;
}

// Incluir cargador de módulos
require_once 'modules/modulo-loader.php';

// Obtener parámetros
$moduloId = isset($_GET['module_id']) ? (int)$_GET['module_id'] : null;
$leccionId = isset($_GET['lesson_id']) ? (int)$_GET['lesson_id'] : null;

// Si no se especifica módulo, redirigir a la lista de módulos
if (!$moduloId) {
    header('Location: index.php?page=modules');
    exit;
}

// Obtener información del módulo
$modulo = getModulo($moduloId);

// Si el módulo no existe, mostrar error
if (!$modulo) {
    echo '<div class="error-container">';
    echo '<h1>Módulo no encontrado</h1>';
    echo '<p>Lo sentimos, el módulo solicitado no existe.</p>';
    echo '<a href="index.php?page=modules" class="btn btn-primary">Ver todos los módulos</a>';
    echo '</div>';
    exit;
}

// Si se especifica una lección, mostrar su contenido
if ($leccionId) {
    $leccion = getLeccion($moduloId, $leccionId);
    
    // Si la lección no existe, mostrar error
    if (!$leccion) {
        echo '<div class="error-container">';
        echo '<h1>Lección no encontrada</h1>';
        echo '<p>Lo sentimos, la lección solicitada no existe.</p>';
        echo '<a href="index.php?page=lessons&module_id=' . $moduloId . '" class="btn btn-primary">Ver todas las lecciones del módulo</a>';
        echo '</div>';
        exit;
    }
    
    // Verificar si el usuario tiene acceso a esta lección
    // (podría requerir completar lecciones previas)
    $tieneAcceso = true;
    
    if (isset($leccion['requisitos_previos']) && !empty($leccion['requisitos_previos'])) {
        foreach ($leccion['requisitos_previos'] as $requisitoId) {
            if (!hasCompletedLesson($_SESSION['user_id'], $requisitoId)) {
                $tieneAcceso = false;
                break;
            }
        }
    }
    
    // Si no tiene acceso, mostrar mensaje
    if (!$tieneAcceso) {
        echo '<div class="lesson-locked">';
        echo '<h1>Lección bloqueada</h1>';
        echo '<p>Debes completar las lecciones anteriores para acceder a esta lección.</p>';
        echo '<a href="index.php?page=lessons&module_id=' . $moduloId . '" class="btn btn-primary">Ver todas las lecciones del módulo</a>';
        echo '</div>';
        exit;
    }
    
    // Verificar si la lección está completada
    $completada = hasCompletedLesson($_SESSION['user_id'], $leccionId);
    
    // Mostrar contenido de la lección
    ?>
    
    <div class="lesson-container">
        <div class="lesson-header">
            <div class="module-nav">
                <a href="index.php?page=modules" class="btn-link">
                    <i class="fas fa-th-large"></i> Módulos
                </a>
                <span class="separator">/</span>
                <a href="index.php?page=lessons&module_id=<?php echo $moduloId; ?>" class="btn-link">
                    <?php echo htmlspecialchars($modulo['titulo']); ?>
                </a>
            </div>
            
            <h1 class="lesson-title">
                <?php echo htmlspecialchars($leccion['titulo']); ?>
                <?php if ($completada): ?>
                <span class="badge badge-success">
                    <i class="fas fa-check-circle"></i> Completada
                </span>
                <?php endif; ?>
            </h1>
            
            <div class="lesson-meta">
                <span class="lesson-duration">
                    <i class="far fa-clock"></i> <?php echo $leccion['duracion_estimada']; ?> minutos
                </span>
                <span class="lesson-xp">
                    <i class="fas fa-star"></i> <?php echo $leccion['xp_recompensa']; ?> XP
                </span>
                <span class="lesson-level">
                    <i class="fas fa-signal"></i> <?php echo $leccion['nivel']; ?>
                </span>
            </div>
        </div>
        
        <div class="lesson-tabs">
            <button class="tab-btn active" data-tab="theory">Teoría</button>
            <button class="tab-btn" data-tab="practice">Práctica</button>
            <button class="tab-btn" data-tab="exercise">Ejercicio</button>
            <button class="tab-btn" data-tab="resources">Recursos</button>
        </div>
        
        <div class="lesson-content">
            <!-- Pestaña de Teoría -->
            <div class="tab-panel active" id="theory-panel">
                <div class="theory-content">
                    <?php if (isset($leccion['contenido']['teoria']['introduccion'])): ?>
                    <div class="theory-intro">
                        <?php echo parseMarkdown($leccion['contenido']['teoria']['introduccion']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($leccion['contenido']['teoria']['secciones'])): ?>
                    <div class="theory-sections">
                        <?php foreach ($leccion['contenido']['teoria']['secciones'] as $seccion): ?>
                        <div class="theory-section">
                            <h2><?php echo htmlspecialchars($seccion['titulo']); ?></h2>
                            <?php echo parseMarkdown($seccion['contenido']); ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Pestaña de Práctica -->
            <div class="tab-panel" id="practice-panel">
                <div class="practice-content">
                    <?php if (isset($leccion['contenido']['practica']['instrucciones'])): ?>
                    <div class="practice-intro">
                        <?php echo parseMarkdown($leccion['contenido']['practica']['instrucciones']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($leccion['contenido']['practica']['pasos'])): ?>
                    <div class="practice-steps">
                        <?php foreach ($leccion['contenido']['practica']['pasos'] as $paso): ?>
                        <div class="practice-step">
                            <div class="step-header">
                                <div class="step-number"><?php echo $paso['paso']; ?></div>
                                <h3><?php echo htmlspecialchars($paso['titulo']); ?></h3>
                            </div>
                            
                            <div class="step-body">
                                <p><?php echo htmlspecialchars($paso['descripcion']); ?></p>
                                
                                <?php if (isset($paso['codigo'])): ?>
                                <div class="code-example">
                                    <pre><code class="language-html"><?php echo htmlspecialchars($paso['codigo']); ?></code></pre>
                                    <button class="btn-copy" data-clipboard-text="<?php echo htmlspecialchars($paso['codigo']); ?>">
                                        <i class="far fa-copy"></i> Copiar
                                    </button>
                                    <button class="btn-practice" data-code="<?php echo htmlspecialchars($paso['codigo']); ?>">
                                        <i class="fas fa-code"></i> Probar en el editor
                                    </button>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (isset($paso['explicacion'])): ?>
                                <div class="step-explanation">
                                    <?php echo parseMarkdown($paso['explicacion']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="practice-buttons">
                        <a href="../editor/index.php?mode=lesson&lesson_id=<?php echo $leccionId; ?>" class="btn btn-primary">
                            <i class="fas fa-code"></i> Abrir en el Editor
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Pestaña de Ejercicio -->
            <div class="tab-panel" id="exercise-panel">
                <div class="exercise-content">
                    <?php if (isset($leccion['contenido']['ejercicio'])): ?>
                    <div class="exercise-header">
                        <h2><?php echo htmlspecialchars($leccion['contenido']['ejercicio']['titulo']); ?></h2>
                        <p><?php echo htmlspecialchars($leccion['contenido']['ejercicio']['descripcion']); ?></p>
                    </div>
                    
                    <div class="exercise-instructions">
                        <h3>Instrucciones:</h3>
                        <?php echo parseMarkdown($leccion['contenido']['ejercicio']['instrucciones']); ?>
                    </div>
                    
                    <?php if (isset($leccion['contenido']['ejercicio']['pistas'])): ?>
                    <div class="exercise-hints">
                        <h3>Pistas:</h3>
                        <div class="hints-container">
                            <?php foreach ($leccion['contenido']['ejercicio']['pistas'] as $index => $pista): ?>
                            <div class="hint" data-hint-id="<?php echo $index; ?>">
                                <button class="hint-toggle">
                                    <i class="fas fa-lightbulb"></i> Pista <?php echo $index + 1; ?>
                                </button>
                                <div class="hint-content">
                                    <?php echo htmlspecialchars($pista); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="exercise-evaluation">
                        <h3>Criterios de Evaluación:</h3>
                        <ul class="criteria-list">
                            <?php foreach ($leccion['contenido']['ejercicio']['criterios_evaluacion'] as $criterio): ?>
                            <li><?php echo htmlspecialchars($criterio); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="exercise-buttons">
                        <a href="../editor/index.php?mode=exercise&lesson_id=<?php echo $leccionId; ?>" class="btn btn-primary">
                            <i class="fas fa-code"></i> Resolver Ejercicio
                        </a>
                    </div>
                    <?php else: ?>
                    <p>No hay ejercicios disponibles para esta lección.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Pestaña de Recursos -->
            <div class="tab-panel" id="resources-panel">
                <div class="resources-content">
                    <h2>Recursos Adicionales</h2>
                    
                    <?php if (isset($leccion['contenido']['recursos_adicionales']) && !empty($leccion['contenido']['recursos_adicionales'])): ?>
                    <ul class="resources-list">
                        <?php foreach ($leccion['contenido']['recursos_adicionales'] as $recurso): ?>
                        <li class="resource-item resource-<?php echo $recurso['tipo']; ?>">
                            <a href="<?php echo htmlspecialchars($recurso['url']); ?>" target="_blank" rel="noopener noreferrer">
                                <i class="<?php echo getResourceIcon($recurso['tipo']); ?>"></i>
                                <?php echo htmlspecialchars($recurso['titulo']); ?>
                                <span class="resource-type"><?php echo getResourceTypeName($recurso['tipo']); ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <p>No hay recursos adicionales para esta lección.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="lesson-navigation">
            <?php
            // Obtener IDs de lecciones anterior y siguiente
            $leccionesDelModulo = getLecciones($moduloId);
            $leccionActualIndex = -1;
            
            foreach ($leccionesDelModulo as $index => $leccionInfo) {
                if ($leccionInfo['id'] == $leccionId) {
                    $leccionActualIndex = $index;
                    break;
                }
            }
            
            $anteriorId = ($leccionActualIndex > 0) ? $leccionesDelModulo[$leccionActualIndex - 1]['id'] : null;
            $siguienteId = ($leccionActualIndex < count($leccionesDelModulo) - 1) ? $leccionesDelModulo[$leccionActualIndex + 1]['id'] : null;
            ?>
            
            <?php if ($anteriorId): ?>
            <a href="index.php?page=lessons&module_id=<?php echo $moduloId; ?>&lesson_id=<?php echo $anteriorId; ?>" class="btn btn-nav btn-prev">
                <i class="fas fa-arrow-left"></i> Lección Anterior
            </a>
            <?php endif; ?>
            
            <button id="markAsCompleted" class="btn btn-success <?php echo $completada ? 'completed' : ''; ?>" data-lesson-id="<?php echo $leccionId; ?>">
                <?php if ($completada): ?>
                <i class="fas fa-check-circle"></i> Lección Completada
                <?php else: ?>
                <i class="far fa-check-circle"></i> Marcar como Completada
                <?php endif; ?>
            </button>
            
            <?php if ($siguienteId): ?>
            <a href="index.php?page=lessons&module_id=<?php echo $moduloId; ?>&lesson_id=<?php echo $siguienteId; ?>" class="btn btn-nav btn-next">
                Siguiente Lección <i class="fas fa-arrow-right"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Script para manejar las pestañas
        document.addEventListener('DOMContentLoaded', function() {
            // Cambiar entre pestañas
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabPanels = document.querySelectorAll('.tab-panel');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Desactivar todas las pestañas
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabPanels.forEach(panel => panel.classList.remove('active'));
                    
                    // Activar la pestaña seleccionada
                    button.classList.add('active');
                    const tabId = button.getAttribute('data-tab');
                    document.getElementById(tabId + '-panel').classList.add('active');
                });
            });
            
            // Manejar pistas
            const hintToggles = document.querySelectorAll('.hint-toggle');
            
            hintToggles.forEach(toggle => {
                toggle.addEventListener('click', () => {
                    const hint = toggle.parentElement;
                    hint.classList.toggle('open');
                });
            });
            
            // Manejar botón de "Copiar"
            const clipboardJS = new ClipboardJS('.btn-copy');
            
            clipboardJS.on('success', function(e) {
                const button = e.trigger;
                const originalText = button.innerHTML;
                
                button.innerHTML = '<i class="fas fa-check"></i> Copiado!';
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                }, 2000);
                
                e.clearSelection();
            });
            
            // Manejar botón "Probar en el editor"
            const practiceButtons = document.querySelectorAll('.btn-practice');
            
            practiceButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const code = button.getAttribute('data-code');
                    
                    // Guardar el código en localStorage para que el editor lo recupere
                    localStorage.setItem('webcraft_practice_code', code);
                    
                    // Abrir el editor en una nueva pestaña
                    window.open('../editor/index.php?mode=practice', '_blank');
                });
            });
            
            // Manejar botón "Marcar como Completada"
            const completeButton = document.getElementById('markAsCompleted');
            
            if (completeButton) {
                completeButton.addEventListener('click', function() {
                    if (completeButton.classList.contains('completed')) {
                        return; // Ya está completada
                    }
                    
                    const lessonId = completeButton.getAttribute('data-lesson-id');
                    console.log('Marcando lección como completada:', lessonId);
                    
                    // Mostrar indicador de carga
                    const originalButtonText = completeButton.innerHTML;
                    completeButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                    completeButton.disabled = true;
                    
                    // Determinar la ruta correcta (relativa a la raíz del sitio)
                    const ajaxUrl = 'includes/ajax/update_lesson_progress.php';
                    
                    // Enviar solicitud AJAX para marcar como completada
                    fetch(ajaxUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            lesson_id: lessonId,
                            completed: true
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Respuesta del servidor:', data);
                        if (data.success) {
                            // Actualizar UI
                            completeButton.classList.add('completed');
                            completeButton.innerHTML = '<i class="fas fa-check-circle"></i> Lección Completada';
                            completeButton.disabled = false;
                            
                            // Añadir badge a título
                            const lessonTitle = document.querySelector('.lesson-title');
                            if (!lessonTitle.querySelector('.badge')) {
                                const badge = document.createElement('span');
                                badge.className = 'badge badge-success';
                                badge.innerHTML = '<i class="fas fa-check-circle"></i> Completada';
                                lessonTitle.appendChild(badge);
                            }
                            
                            // Mostrar notificación
                            showNotification('¡Lección completada! Has ganado ' + data.xp + ' XP.', 'success');
                            
                            // Actualizar la página después de un breve retraso
                            setTimeout(function() {
                                window.location.reload();
                            }, 2000);
                        } else {
                            // Restaurar botón y mostrar error
                            completeButton.innerHTML = originalButtonText;
                            completeButton.disabled = false;
                            showNotification('Error: ' + (data.message || 'Error al marcar la lección como completada.'), 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Restaurar botón
                        completeButton.innerHTML = originalButtonText;
                        completeButton.disabled = false;
                        
                        // Intentar método alternativo si falla
                        console.log('Intentando método alternativo...');
                        marcarLeccionCompletadaAlternativo(lessonId);
                    });
                });
            }
            
            // Función alternativa para marcar lección como completada
            function marcarLeccionCompletadaAlternativo(lessonId) {
                // Usar XMLHttpRequest como alternativa a fetch
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'includes/ajax/debug_progress.php', true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                
                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            const data = JSON.parse(xhr.responseText);
                            console.log('Respuesta alternativa:', data);
                            showNotification('¡Método alternativo exitoso! Recargando página...', 'success');
                            
                            // Recargar para reflejar los cambios
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } catch (e) {
                            console.error('Error parseando respuesta:', e);
                            showNotification('Error en la respuesta del servidor alternativo.', 'error');
                        }
                    } else {
                        console.error('Error en método alternativo:', xhr.status);
                        showNotification('Error en el método alternativo.', 'error');
                    }
                };
                
                xhr.onerror = function() {
                    console.error('Error de red en método alternativo');
                    showNotification('Error de conexión. Intente nuevamente más tarde.', 'error');
                };
                
                xhr.send(JSON.stringify({
                    lesson_id: lessonId,
                    completed: true
                }));
            }
        });
        
        // Función para mostrar notificaciones
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = 'notification notification-' + type;
            notification.innerHTML = message;
            
            document.body.appendChild(notification);
            
            // Mostrar con animación
            setTimeout(() => {
                notification.classList.add('show');
            }, 10);
            
            // Ocultar automáticamente
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
    </script>
    
    <?php
} else {
    // Mostrar lista de lecciones del módulo
    $lecciones = getLecciones($moduloId);
    ?>
    
    <div class="module-lessons-container">
        <div class="module-header">
            <div class="breadcrumbs">
                <a href="index.php?page=modules">Módulos</a>
                <span class="separator">/</span>
                <span class="current"><?php echo htmlspecialchars($modulo['titulo']); ?></span>
            </div>
            
            <h1><?php echo htmlspecialchars($modulo['titulo']); ?></h1>
            <p class="module-description"><?php echo htmlspecialchars($modulo['descripcion']); ?></p>
            
            <div class="module-meta">
                <span class="module-duration">
                    <i class="far fa-clock"></i> Duración estimada: <?php echo $modulo['duracion_estimada']; ?>
                </span>
                <span class="module-level">
                    <i class="fas fa-signal"></i> Nivel: <?php echo $modulo['nivel']; ?>
                </span>
            </div>
        </div>
        
        <div class="module-progress">
            <?php
            // Calcular progreso
            $totalLecciones = count($lecciones);
            $leccionesCompletadas = 0;
            
            foreach ($lecciones as $leccion) {
                if (hasCompletedLesson($_SESSION['user_id'], $leccion['id'])) {
                    $leccionesCompletadas++;
                }
            }
            
            $porcentajeProgreso = $totalLecciones > 0 ? round(($leccionesCompletadas / $totalLecciones) * 100) : 0;
            ?>
            
            <div class="progress-bar">
                <div class="progress" style="width: <?php echo $porcentajeProgreso; ?>%"></div>
            </div>
            
            <div class="progress-info">
                <span><?php echo $leccionesCompletadas; ?> de <?php echo $totalLecciones; ?> lecciones completadas (<?php echo $porcentajeProgreso; ?>%)</span>
                
                <?php if ($porcentajeProgreso == 100): ?>
                <span class="badge badge-success">Módulo Completado</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="lessons-list">
            <h2>Lecciones</h2>
            
            <div class="lesson-cards">
                <?php foreach ($lecciones as $index => $leccion): 
                    $completada = hasCompletedLesson($_SESSION['user_id'], $leccion['id']);
                    $disponible = true;
                    
                    // Verificar si la lección está disponible (lecciones anteriores completadas)
                    if ($index > 0 && !hasCompletedLesson($_SESSION['user_id'], $lecciones[$index - 1]['id'])) {
                        $disponible = false;
                    }
                ?>
                <div class="lesson-card <?php echo $completada ? 'completed' : ($disponible ? 'available' : 'locked'); ?>">
                    <div class="lesson-number"><?php echo $index + 1; ?></div>
                    
                    <div class="lesson-info">
                        <h3 class="lesson-title">
                            <?php echo htmlspecialchars($leccion['titulo']); ?>
                            
                            <?php if ($completada): ?>
                            <span class="status-indicator">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            <?php elseif (!$disponible): ?>
                            <span class="status-indicator">
                                <i class="fas fa-lock"></i>
                            </span>
                            <?php endif; ?>
                        </h3>
                        
                        <p class="lesson-desc"><?php echo htmlspecialchars($leccion['descripcion']); ?></p>
                        
                        <div class="lesson-meta">
                            <span class="lesson-duration">
                                <i class="far fa-clock"></i> <?php echo $leccion['duracion_estimada']; ?> min
                            </span>
                            <span class="lesson-xp">
                                <i class="fas fa-star"></i> <?php echo $leccion['xp_recompensa']; ?> XP
                            </span>
                        </div>
                    </div>
                    
                    <?php if ($disponible): ?>
                    <a href="index.php?page=lessons&module_id=<?php echo $moduloId; ?>&lesson_id=<?php echo $leccion['id']; ?>" class="lesson-link">
                        <span class="btn-text">Ver lección</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <?php else: ?>
                    <div class="lesson-link disabled">
                        <span class="btn-text">Bloqueada</span>
                        <i class="fas fa-lock"></i>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php if (isset($modulo['proyecto_final'])): ?>
        <div class="module-project">
            <h2>Proyecto Final</h2>
            
            <div class="project-card">
                <div class="project-info">
                    <h3><?php echo htmlspecialchars($modulo['proyecto_final']['titulo']); ?></h3>
                    <p><?php echo htmlspecialchars($modulo['proyecto_final']['descripcion']); ?></p>
                    
                    <div class="project-meta">
                        <span class="project-xp">
                            <i class="fas fa-trophy"></i> <?php echo $modulo['proyecto_final']['xp_recompensa']; ?> XP
                        </span>
                        <span class="project-badge">
                            <i class="fas fa-award"></i> Insignia: <?php echo $modulo['proyecto_final']['insignia']; ?>
                        </span>
                    </div>
                </div>
                
                <?php if ($porcentajeProgreso == 100): ?>
                <a href="../editor/index.php?mode=project&module_id=<?php echo $moduloId; ?>" class="project-link">
                    <span class="btn-text">Comenzar proyecto</span>
                    <i class="fas fa-code"></i>
                </a>
                <?php else: ?>
                <div class="project-link disabled">
                    <span class="btn-text">Completa todas las lecciones para desbloquear</span>
                    <i class="fas fa-lock"></i>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php
}

/**
 * Funciones auxiliares para la página de lecciones
 */

/**
 * Parsea texto en formato Markdown y lo convierte a HTML
 */
function parseMarkdown($text) {
    // Si está disponible Parsedown, usarlo
    if (class_exists('Parsedown')) {
        $parsedown = new Parsedown();
        return $parsedown->text($text);
    }
    
    // Si no, hacer un parseo básico
    $text = htmlspecialchars($text);
    
    // Convertir enlaces
    $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank">$1</a>', $text);
    
    // Convertir bloques de código
    $text = preg_replace('/```([^`]+)```/', '<pre><code>$1</code></pre>', $text);
    
    // Convertir código en línea
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    
    // Convertir negritas
    $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text);
    
    // Convertir cursivas
    $text = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $text);
    
    // Convertir saltos de línea
    $text = nl2br($text);
    
    return $text;
}

/**
 * Retorna el icono para un tipo de recurso
 */
function getResourceIcon($tipo) {
    switch ($tipo) {
        case 'documentacion':
            return 'fas fa-book';
        case 'articulo':
            return 'fas fa-newspaper';
        case 'tutorial':
            return 'fas fa-chalkboard-teacher';
        case 'video':
            return 'fas fa-video';
        case 'herramienta':
            return 'fas fa-tools';
        case 'referencia':
            return 'fas fa-list-ul';
        case 'ejemplo':
            return 'fas fa-code';
        case 'guia':
            return 'fas fa-map-signs';
        case 'curso':
            return 'fas fa-graduation-cap';
        default:
            return 'fas fa-link';
    }
}

/**
 * Retorna el nombre del tipo de recurso
 */
function getResourceTypeName($tipo) {
    switch ($tipo) {
        case 'documentacion':
            return 'Documentación';
        case 'articulo':
            return 'Artículo';
        case 'tutorial':
            return 'Tutorial';
        case 'video':
            return 'Video';
        case 'herramienta':
            return 'Herramienta';
        case 'referencia':
            return 'Referencia';
        case 'ejemplo':
            return 'Ejemplo';
        case 'guia':
            return 'Guía';
        case 'curso':
            return 'Curso';
        default:
            return 'Enlace';
    }
}
?>
