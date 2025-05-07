<?php
// Verificar que el script se ejecuta dentro del contexto adecuado
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Obtener datos del usuario
$user = getCurrentUser();

// Verificar si se solicita una lección específica
$lesson_id = isset($_GET['lesson_id']) ? (int)$_GET['lesson_id'] : null;

// Si no hay lección especificada, redirigir a módulos
if (!$lesson_id) {
    header('Location: index.php?page=modules');
    exit;
}

// Obtener datos de la lección
try {
    $db = getDbConnection();
    
    // Obtener detalles de la lección
    $stmt = $db->prepare("
        SELECT l.lesson_id, l.title, l.content, l.module_id, l.order_index, l.xp_reward, l.estimated_time,
               m.title as module_title, m.icon as module_icon,
               CASE WHEN p.completed = 1 THEN TRUE ELSE FALSE END as completed,
               p.score, p.completion_date
        FROM lessons l
        JOIN modules m ON l.module_id = m.module_id
        LEFT JOIN progress p ON l.lesson_id = p.lesson_id AND p.user_id = ?
        WHERE l.lesson_id = ? AND l.is_active = 1
    ");
    $stmt->execute([$_SESSION['user_id'], $lesson_id]);
    $lesson = $stmt->fetch();
    
    if (!$lesson) {
        // Lección no encontrada o no activa
        header('Location: index.php?page=modules');
        exit;
    }
    
    // Obtener lecciones anterior y siguiente
    $stmt = $db->prepare("
        SELECT lesson_id, title
        FROM lessons
        WHERE module_id = ? AND order_index < ? AND is_active = 1
        ORDER BY order_index DESC
        LIMIT 1
    ");
    $stmt->execute([$lesson['module_id'], $lesson['order_index']]);
    $prev_lesson = $stmt->fetch();
    
    $stmt = $db->prepare("
        SELECT lesson_id, title
        FROM lessons
        WHERE module_id = ? AND order_index > ? AND is_active = 1
        ORDER BY order_index ASC
        LIMIT 1
    ");
    $stmt->execute([$lesson['module_id'], $lesson['order_index']]);
    $next_lesson = $stmt->fetch();
    
    // Iniciar o continuar el registro de tiempo para esta lección
    if (!isset($_SESSION['lesson_start_time'])) {
        $_SESSION['lesson_start_time'] = time();
    }
    
    // Procesamiento de completado de lección
    if (isset($_POST['complete_lesson']) && !$lesson['completed']) {
        // Calcular tiempo empleado
        $time_spent = time() - $_SESSION['lesson_start_time'];
        
        // Verificar si ya existe un registro de progreso
        $stmt = $db->prepare("
            SELECT progress_id
            FROM progress
            WHERE user_id = ? AND lesson_id = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $lesson_id]);
        $progress = $stmt->fetch();
        
        if ($progress) {
            // Actualizar registro existente
            $stmt = $db->prepare("
                UPDATE progress
                SET completed = 1,
                    completion_date = NOW(),
                    time_spent = time_spent + ?
                WHERE progress_id = ?
            ");
            $stmt->execute([$time_spent, $progress['progress_id']]);
        } else {
            // Crear nuevo registro
            $stmt = $db->prepare("
                INSERT INTO progress (user_id, lesson_id, completed, completion_date, time_spent)
                VALUES (?, ?, 1, NOW(), ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $lesson_id, $time_spent]);
        }
        
        // Otorgar XP
        $xp_reward = (int)$lesson['xp_reward'];
        
        $stmt = $db->prepare("
            UPDATE user_profiles
            SET xp_points = xp_points + ?
            WHERE user_id = ?
        ");
        $stmt->execute([$xp_reward, $_SESSION['user_id']]);
        
        // Actualizar tablero de líderes
        $stmt = $db->prepare("
            INSERT INTO leaderboard (user_id, xp_total, last_updated)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE
            xp_total = xp_total + ?, last_updated = NOW()
        ");
        $stmt->execute([$_SESSION['user_id'], $xp_reward, $xp_reward]);
        
        // Mostrar mensaje de éxito y redirigir
        $_SESSION['lesson_completed'] = true;
        $_SESSION['xp_earned'] = $xp_reward;
        
        // Reiniciar tiempo de lección
        unset($_SESSION['lesson_start_time']);
        
        // Redirigir a la misma página para evitar reenvío del formulario
        header('Location: index.php?page=lessons&lesson_id=' . $lesson_id . '&completed=1');
        exit;
    }
    
    // Mensaje de lección completada
    $show_completion_message = isset($_GET['completed']) && $_GET['completed'] == '1' && isset($_SESSION['lesson_completed']);
    if ($show_completion_message) {
        $xp_earned = $_SESSION['xp_earned'] ?? 0;
        
        // Limpiar variables de sesión
        unset($_SESSION['lesson_completed']);
        unset($_SESSION['xp_earned']);
    }
    
} catch (PDOException $e) {
    // Manejar error
    if (DEV_MODE) {
        $error = 'Error de base de datos: ' . $e->getMessage();
    } else {
        $error = 'Error al cargar la lección. Por favor, intenta nuevamente más tarde.';
    }
    
    // Redirigir a módulos
    header('Location: index.php?page=modules');
    exit;
}
?>

<div class="lesson-container">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php else: ?>
        <?php if ($show_completion_message): ?>
            <div class="completion-message">
                <div class="completion-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="completion-content">
                    <h2>¡Lección Completada!</h2>
                    <p>Has ganado <strong><?php echo $xp_earned; ?> XP</strong>. ¡Sigue aprendiendo!</p>
                </div>
                <button class="close-message">&times;</button>
            </div>
        <?php endif; ?>
        
        <div class="lesson-header">
            <div class="lesson-header-content">
                <a href="index.php?page=modules&module_id=<?php echo $lesson['module_id']; ?>" class="back-link">
                    <i class="fas fa-arrow-left"></i> Volver al módulo
                </a>
                
                <div class="lesson-module">
                    <i class="<?php echo htmlspecialchars($lesson['module_icon']); ?>"></i>
                    <span><?php echo htmlspecialchars($lesson['module_title']); ?></span>
                </div>
                
                <h1 class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></h1>
                
                <div class="lesson-meta">
                    <div class="lesson-meta-item">
                        <i class="fas fa-clock"></i>
                        <span>Tiempo estimado: <?php echo $lesson['estimated_time']; ?> min</span>
                    </div>
                    <div class="lesson-meta-item">
                        <i class="fas fa-star"></i>
                        <span>Recompensa: <?php echo $lesson['xp_reward']; ?> XP</span>
                    </div>
                    <?php if ($lesson['completed']): ?>
                        <div class="lesson-meta-item completed">
                            <i class="fas fa-check-circle"></i>
                            <span>Completado: <?php echo date('d/m/Y', strtotime($lesson['completion_date'])); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="lesson-content">
            <div class="lesson-navigation">
                <div class="lesson-prev">
                    <?php if ($prev_lesson): ?>
                        <a href="index.php?page=lessons&lesson_id=<?php echo $prev_lesson['lesson_id']; ?>" class="nav-link">
                            <i class="fas fa-chevron-left"></i>
                            <span>Anterior</span>
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="lesson-next">
                    <?php if ($next_lesson): ?>
                        <a href="index.php?page=lessons&lesson_id=<?php echo $next_lesson['lesson_id']; ?>" class="nav-link">
                            <span>Siguiente</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="lesson-body">
                <?php echo $lesson['content']; // Nota: Asegúrate de que el contenido sea seguro (sanitizado) antes de guardarlo en la BD ?>
            </div>
            
            <div class="lesson-actions">
                <?php if (!$lesson['completed']): ?>
                    <form method="POST" action="index.php?page=lessons&lesson_id=<?php echo $lesson_id; ?>">
                        <button type="submit" name="complete_lesson" class="btn btn-primary">
                            <i class="fas fa-check"></i> Marcar como Completada
                        </button>
                    </form>
                <?php else: ?>
                    <div class="lesson-completed-message">
                        <i class="fas fa-check-circle"></i>
                        <span>Ya has completado esta lección</span>
                    </div>
                <?php endif; ?>
                
                <div class="lesson-navigation">
                    <?php if ($prev_lesson): ?>
                        <a href="index.php?page=lessons&lesson_id=<?php echo $prev_lesson['lesson_id']; ?>" class="btn btn-outline">
                            <i class="fas fa-chevron-left"></i> Lección Anterior
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($next_lesson): ?>
                        <a href="index.php?page=lessons&lesson_id=<?php echo $next_lesson['lesson_id']; ?>" class="btn btn-primary">
                            Siguiente Lección <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Script para cerrar mensaje de lección completada -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const completionMessage = document.querySelector('.completion-message');
    const closeButton = document.querySelector('.close-message');
    
    if (completionMessage && closeButton) {
        closeButton.addEventListener('click', function() {
            completionMessage.style.opacity = '0';
            setTimeout(() => {
                completionMessage.style.display = 'none';
            }, 300);
        });
        
        // Auto-cerrar después de 5 segundos
        setTimeout(() => {
            completionMessage.style.opacity = '0';
            setTimeout(() => {
                completionMessage.style.display = 'none';
            }, 300);
        }, 5000);
    }
});
</script>
