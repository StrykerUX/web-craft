<?php
// Verificar que el script se ejecuta dentro del contexto adecuado
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Obtener datos completos del usuario
$user = getCurrentUser(true);

// Inicializar mensajes
$success = '';
$error = '';

// Procesar formulario si es enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar qué formulario se envió
    if (isset($_POST['update_profile'])) {
        // Actualizar perfil
        
        // Obtener y sanitizar datos
        $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $bio = filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        // Crear array con datos a actualizar
        $profileData = [
            'full_name' => $full_name,
            'email' => $email,
            'bio' => $bio
        ];
        
        // Actualizar perfil
        $result = updateUserProfile($profileData);
        
        if ($result['success']) {
            $success = $result['message'];
            // Actualizar datos del usuario
            $user = getCurrentUser(true);
        } else {
            $error = $result['message'];
        }
    } elseif (isset($_POST['update_password'])) {
        // Cambiar contraseña
        
        // Obtener datos
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validar datos
        if (empty($current_password)) {
            $error = 'Ingresa tu contraseña actual.';
        } elseif (empty($new_password)) {
            $error = 'Ingresa una nueva contraseña.';
        } elseif (strlen($new_password) < 8) {
            $error = 'La nueva contraseña debe tener al menos 8 caracteres.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Las nuevas contraseñas no coinciden.';
        } else {
            // Verificar contraseña actual
            try {
                $stmt = getDbConnection()->prepare("SELECT password FROM users WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $result = $stmt->fetch();
                
                if ($result && password_verify($current_password, $result['password'])) {
                    // Actualizar contraseña
                    $result = changeUserPassword($_SESSION['user_id'], $new_password);
                    
                    if ($result['success']) {
                        $success = $result['message'];
                    } else {
                        $error = $result['message'];
                    }
                } else {
                    $error = 'La contraseña actual es incorrecta.';
                }
            } catch (PDOException $e) {
                $error = 'Error al cambiar la contraseña. Por favor, intenta nuevamente más tarde.';
                if (DEV_MODE) {
                    $error .= ' - ' . $e->getMessage();
                }
            }
        }
    } elseif (isset($_POST['update_avatar'])) {
        // Actualizar avatar
        
        // Manejar la subida del archivo
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $upload = $_FILES['avatar'];
            
            // Validar tipo de archivo
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $uploadedFileType = finfo_file($fileInfo, $upload['tmp_name']);
            finfo_close($fileInfo);
            
            if (!in_array($uploadedFileType, $allowedTypes)) {
                $error = 'El archivo debe ser una imagen (JPG, PNG o GIF).';
            } else {
                // Validar tamaño (máximo 2MB)
                if ($upload['size'] > 2 * 1024 * 1024) {
                    $error = 'El tamaño del archivo no debe superar los 2MB.';
                } else {
                    // Crear directorio si no existe
                    $uploadDir = 'assets/images/avatars/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    // Generar nombre único para el archivo
                    $filename = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . pathinfo($upload['name'], PATHINFO_EXTENSION);
                    $destination = $uploadDir . $filename;
                    
                    // Mover archivo subido
                    if (move_uploaded_file($upload['tmp_name'], $destination)) {
                        // Actualizar en base de datos
                        try {
                            $stmt = getDbConnection()->prepare("UPDATE user_profiles SET avatar = ? WHERE user_id = ?");
                            $stmt->execute([$filename, $_SESSION['user_id']]);
                            
                            $success = 'Avatar actualizado exitosamente.';
                            
                            // Actualizar datos del usuario
                            $user = getCurrentUser(true);
                        } catch (PDOException $e) {
                            $error = 'Error al actualizar el avatar. Por favor, intenta nuevamente más tarde.';
                            if (DEV_MODE) {
                                $error .= ' - ' . $e->getMessage();
                            }
                        }
                    } else {
                        $error = 'Error al subir el archivo. Por favor, intenta nuevamente.';
                    }
                }
            }
        } else {
            $error = 'Selecciona un archivo para subir.';
        }
    }
}

// Generar token CSRF
$csrf_token = generateCSRFToken();
?>

<div class="profile-container">
    <div class="profile-header">
        <h1>Mi Perfil</h1>
        <a href="index.php?page=dashboard" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <div class="profile-content">
        <!-- Barra lateral con avatar e información básica -->
        <div class="profile-sidebar">
            <div class="user-avatar-large">
                <img src="assets/images/avatars/<?php echo htmlspecialchars($user['avatar'] ?? 'default.png'); ?>" alt="Avatar de <?php echo htmlspecialchars($user['username']); ?>">
                <form method="POST" enctype="multipart/form-data" class="avatar-upload-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <label for="avatar" class="avatar-upload-button">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" id="avatar" name="avatar" accept="image/*" class="hidden-file-input">
                    <button type="submit" name="update_avatar" class="hidden-submit"></button>
                </form>
            </div>
            
            <div class="user-info-basic">
                <h2 class="username"><?php echo htmlspecialchars($user['username']); ?></h2>
                <div class="user-level">
                    <span class="level-badge"><?php echo htmlspecialchars($user['level']); ?></span>
                    <span class="xp-count"><?php echo $user['xp_points']; ?> XP</span>
                </div>
                <p class="user-bio"><?php echo htmlspecialchars($user['bio'] ?? 'Aún no has añadido una biografía.'); ?></p>
            </div>
            
            <div class="user-stats">
                <div class="stat">
                    <div class="stat-label">Miembro desde</div>
                    <div class="stat-value"><?php echo date('d/m/Y', strtotime($user['registration_date'])); ?></div>
                </div>
                <div class="stat">
                    <div class="stat-label">Último acceso</div>
                    <div class="stat-value"><?php echo date('d/m/Y', strtotime($user['last_login'] ?? $user['registration_date'])); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Contenido principal con formularios -->
        <div class="profile-forms">
            <div class="tabs">
                <div class="tab-list">
                    <button class="tab-button active" data-tab="profile">Perfil</button>
                    <button class="tab-button" data-tab="account">Cuenta</button>
                    <button class="tab-button" data-tab="preferences">Preferencias</button>
                </div>
                
                <!-- Tab de Perfil -->
                <div class="tab-content active" id="profile-tab">
                    <form method="POST" class="profile-form">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="form-section">
                            <h3>Información Personal</h3>
                            
                            <div class="form-group">
                                <label for="full_name">Nombre Completo</label>
                                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Correo Electrónico</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="bio">Biografía</label>
                                <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                <small class="form-text">Cuéntanos un poco sobre ti. Esta información será visible para otros usuarios.</small>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_profile" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
                
                <!-- Tab de Cuenta -->
                <div class="tab-content" id="account-tab">
                    <form method="POST" class="account-form">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="form-section">
                            <h3>Cambiar Contraseña</h3>
                            
                            <div class="form-group">
                                <label for="current_password">Contraseña Actual</label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="current_password" name="current_password" required>
                                    <button type="button" class="password-toggle" aria-label="Ver contraseña">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">Nueva Contraseña</label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="new_password" name="new_password" required minlength="8">
                                    <button type="button" class="password-toggle" aria-label="Ver contraseña">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="form-text">Mínimo 8 caracteres. Se recomienda incluir letras mayúsculas, minúsculas, números y símbolos.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirmar Nueva Contraseña</label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                                    <button type="button" class="password-toggle" aria-label="Ver contraseña">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_password" class="btn btn-primary">Cambiar Contraseña</button>
                        </div>
                    </form>
                    
                    <div class="account-danger-zone">
                        <h3>Zona de Peligro</h3>
                        <p>Las siguientes acciones son irreversibles. Por favor, ten cuidado.</p>
                        
                        <div class="danger-actions">
                            <button type="button" class="btn btn-danger" id="deleteAccountBtn">
                                <i class="fas fa-trash-alt"></i> Eliminar Cuenta
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Tab de Preferencias -->
                <div class="tab-content" id="preferences-tab">
                    <form method="POST" class="preferences-form">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="form-section">
                            <h3>Tema</h3>
                            
                            <div class="theme-selector">
                                <label class="theme-option">
                                    <input type="radio" name="theme" value="light" <?php echo ($user['theme_preference'] === 'light' ? 'checked' : ''); ?>>
                                    <span class="theme-preview light-theme">
                                        <i class="fas fa-sun"></i>
                                        <span>Claro</span>
                                    </span>
                                </label>
                                
                                <label class="theme-option">
                                    <input type="radio" name="theme" value="dark" <?php echo ($user['theme_preference'] === 'dark' ? 'checked' : ''); ?>>
                                    <span class="theme-preview dark-theme">
                                        <i class="fas fa-moon"></i>
                                        <span>Oscuro</span>
                                    </span>
                                </label>
                                
                                <label class="theme-option">
                                    <input type="radio" name="theme" value="system" <?php echo ($user['theme_preference'] === 'system' ? 'checked' : ''); ?>>
                                    <span class="theme-preview system-theme">
                                        <i class="fas fa-desktop"></i>
                                        <span>Sistema</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Accesibilidad</h3>
                            
                            <div class="form-group">
                                <label for="fontSize">Tamaño de Texto</label>
                                <select id="fontSize" name="fontSize" class="form-control">
                                    <option value="normal" selected>Normal</option>
                                    <option value="large">Grande</option>
                                    <option value="larger">Más Grande</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="contrast">Contraste</label>
                                <select id="contrast" name="contrast" class="form-control">
                                    <option value="normal" selected>Normal</option>
                                    <option value="high">Alto Contraste</option>
                                </select>
                            </div>
                            
                            <div class="form-check">
                                <input type="checkbox" id="reduceMotion" name="reduceMotion" class="form-check-input">
                                <label for="reduceMotion" class="form-check-label">Reducir Animaciones</label>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Notificaciones</h3>
                            
                            <div class="form-check">
                                <input type="checkbox" id="emailNotifications" name="emailNotifications" class="form-check-input" checked>
                                <label for="emailNotifications" class="form-check-label">Recibir notificaciones por Email</label>
                            </div>
                            
                            <div class="form-check">
                                <input type="checkbox" id="achievementNotifications" name="achievementNotifications" class="form-check-input" checked>
                                <label for="achievementNotifications" class="form-check-label">Mostrar notificaciones de logros</label>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_preferences" class="btn btn-primary">Guardar Preferencias</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar cuenta -->
<div class="modal" id="deleteAccountModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Eliminar Cuenta</h2>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p>¿Estás seguro de que quieres eliminar tu cuenta? Esta acción no se puede deshacer y perderás todo tu progreso, proyectos y datos.</p>
            
            <div class="modal-confirm-form">
                <div class="form-group">
                    <label for="confirm_username">Escribe tu nombre de usuario para confirmar</label>
                    <input type="text" id="confirm_username" placeholder="<?php echo htmlspecialchars($user['username']); ?>">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline modal-cancel">Cancelar</button>
            <button class="btn btn-danger" id="confirmDeleteAccount" disabled>Eliminar Permanentemente</button>
        </div>
    </div>
</div>

<!-- JavaScript para la página -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tabs
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Desactivar todos los tabs
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Activar tab seleccionado
            button.classList.add('active');
            document.getElementById(button.dataset.tab + '-tab').classList.add('active');
        });
    });
    
    // Verificar si hay un hash en la URL para activar un tab específico
    if (window.location.hash) {
        const tabId = window.location.hash.substring(1);
        const tabButton = document.querySelector(`[data-tab="${tabId}"]`);
        
        if (tabButton) {
            tabButton.click();
        }
    }
    
    // Inicializar toggles de contraseña
    const toggles = document.querySelectorAll('.password-toggle');
    
    toggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Subir avatar al seleccionar archivo
    const avatarInput = document.getElementById('avatar');
    const hiddenSubmit = document.querySelector('.hidden-submit');
    
    if (avatarInput && hiddenSubmit) {
        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                hiddenSubmit.click();
            }
        });
    }
    
    // Modal de eliminar cuenta
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    const deleteAccountModal = document.getElementById('deleteAccountModal');
    const modalClose = document.querySelector('.modal-close');
    const modalCancel = document.querySelector('.modal-cancel');
    const confirmDeleteAccount = document.getElementById('confirmDeleteAccount');
    const confirmUsername = document.getElementById('confirm_username');
    const expectedUsername = '<?php echo htmlspecialchars($user['username']); ?>';
    
    if (deleteAccountBtn && deleteAccountModal) {
        // Abrir modal
        deleteAccountBtn.addEventListener('click', function() {
            deleteAccountModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        // Cerrar modal
        if (modalClose) {
            modalClose.addEventListener('click', function() {
                deleteAccountModal.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
        
        // Cancelar
        if (modalCancel) {
            modalCancel.addEventListener('click', function() {
                deleteAccountModal.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
        
        // Verificar nombre de usuario
        if (confirmUsername && confirmDeleteAccount) {
            confirmUsername.addEventListener('input', function() {
                confirmDeleteAccount.disabled = (this.value !== expectedUsername);
            });
        }
        
        // Confirmar eliminación
        if (confirmDeleteAccount) {
            confirmDeleteAccount.addEventListener('click', function() {
                if (confirmUsername.value === expectedUsername) {
                    // Enviar solicitud para eliminar cuenta
                    window.location.href = 'index.php?page=delete-account&confirm=true&token=<?php echo $csrf_token; ?>';
                }
            });
        }
        
        // Cerrar modal con Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && deleteAccountModal.classList.contains('active')) {
                deleteAccountModal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
        
        // Cerrar modal haciendo clic fuera
        deleteAccountModal.addEventListener('click', function(e) {
            if (e.target === deleteAccountModal) {
                deleteAccountModal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
});
</script>
