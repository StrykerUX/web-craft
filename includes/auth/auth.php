<?php
/**
 * Funciones de autenticación para WebCraft Academy
 * 
 * Este archivo contiene las funciones principales para manejar la autenticación,
 * registro, validación y manejo de sesiones de usuario.
 */

// Prevenir acceso directo a este archivo
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

/**
 * Registra un nuevo usuario en el sistema
 * 
 * @param string $username Nombre de usuario
 * @param string $email Correo electrónico
 * @param string $password Contraseña sin encriptar
 * @param string $confirmPassword Confirmación de contraseña
 * @return array Resultado del registro ['success' => bool, 'message' => string, 'user_id' => int|null]
 */
function registerUser($username, $email, $password, $confirmPassword) {
    $result = ['success' => false, 'message' => '', 'user_id' => null];
    
    // Validar datos de entrada
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $result['message'] = 'Todos los campos son obligatorios.';
        return $result;
    }
    
    // Validar que las contraseñas coincidan
    if ($password !== $confirmPassword) {
        $result['message'] = 'Las contraseñas no coinciden.';
        return $result;
    }
    
    // Validar formato de correo electrónico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $result['message'] = 'El formato del correo electrónico no es válido.';
        return $result;
    }
    
    // Validar longitud de nombre de usuario
    if (strlen($username) < 3 || strlen($username) > 50) {
        $result['message'] = 'El nombre de usuario debe tener entre 3 y 50 caracteres.';
        return $result;
    }
    
    // Validar complejidad de contraseña
    if (strlen($password) < 8) {
        $result['message'] = 'La contraseña debe tener al menos 8 caracteres.';
        return $result;
    }
    
    try {
        $db = getDbConnection();
        
        // Verificar si el usuario ya existe
        $checkStmt = $db->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $checkStmt->execute([$username, $email]);
        
        if ($checkStmt->rowCount() > 0) {
            $result['message'] = 'El nombre de usuario o correo electrónico ya está en uso.';
            return $result;
        }
        
        // Encriptar la contraseña
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
        
        // Insertar el nuevo usuario
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password_hash, display_name, registration_date) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([$username, $email, $passwordHash, $username]);
        $userId = $db->lastInsertId();
        
        // Inicializar preferencias de usuario
        $prefStmt = $db->prepare("
            INSERT INTO user_preferences (user_id, created_at, updated_at) 
            VALUES (?, NOW(), NOW())
        ");
        $prefStmt->execute([$userId]);
        
        $result['success'] = true;
        $result['message'] = 'Registro exitoso. Ya puedes iniciar sesión.';
        $result['user_id'] = $userId;
        
    } catch (PDOException $e) {
        $result['message'] = DEV_MODE ? 'Error al registrar: ' . $e->getMessage() : 'Error al procesar el registro. Inténtalo de nuevo más tarde.';
    }
    
    return $result;
}

/**
 * Autentica a un usuario en el sistema
 * 
 * @param string $usernameOrEmail Nombre de usuario o correo electrónico
 * @param string $password Contraseña
 * @param bool $remember Activar "recordarme"
 * @return array Resultado del login ['success' => bool, 'message' => string, 'user' => array|null]
 */
function loginUser($usernameOrEmail, $password, $remember = false) {
    $result = ['success' => false, 'message' => '', 'user' => null];
    
    // Validar datos de entrada
    if (empty($usernameOrEmail) || empty($password)) {
        $result['message'] = 'Todos los campos son obligatorios.';
        return $result;
    }
    
    try {
        $db = getDbConnection();
        
        // Obtener usuario por username o email
        $stmt = $db->prepare("
            SELECT user_id, username, display_name, email, password_hash, developer_level, 
                   experience_points, account_status, role
            FROM users 
            WHERE username = ? OR email = ?
        ");
        
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        $user = $stmt->fetch();
        
        // Verificar si el usuario existe
        if (!$user) {
            $result['message'] = 'Nombre de usuario o contraseña incorrectos.';
            return $result;
        }
        
        // Verificar estado de la cuenta
        if ($user['account_status'] !== 'active') {
            $result['message'] = 'Esta cuenta está ' . ($user['account_status'] === 'suspended' ? 'suspendida.' : 'inactiva.');
            return $result;
        }
        
        // Verificar contraseña
        if (!password_verify($password, $user['password_hash'])) {
            // Incrementar contador de intentos fallidos de login (no implementado en esta versión)
            $result['message'] = 'Nombre de usuario o contraseña incorrectos.';
            return $result;
        }
        
        // Actualizar último login
        $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $updateStmt->execute([$user['user_id']]);
        
        // Quitar el hash de la contraseña del array de usuario
        unset($user['password_hash']);
        
        // Establecer sesión de usuario
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        
        // Configurar "recordarme" si está activado
        if ($remember) {
            $token = generateRememberToken();
            $tokenHash = password_hash($token, PASSWORD_DEFAULT);
            
            // Guardar token en la base de datos (expiración de 30 días)
            $tokenExpires = date('Y-m-d H:i:s', strtotime('+30 days'));
            $tokenStmt = $db->prepare("
                INSERT INTO remember_tokens (user_id, token_hash, expires_at) 
                VALUES (?, ?, ?)
            ");
            $tokenStmt->execute([$user['user_id'], $tokenHash, $tokenExpires]);
            
            // Establecer cookie de "recordarme" (expiración de 30 días)
            $cookieValue = $user['user_id'] . ':' . $token;
            setcookie(
                'webcraft_remember',
                $cookieValue,
                time() + (86400 * 30),
                '/',
                '',
                SESSION_SECURE,
                true
            );
        }
        
        $result['success'] = true;
        $result['message'] = 'Inicio de sesión exitoso.';
        $result['user'] = $user;
        
    } catch (PDOException $e) {
        $result['message'] = DEV_MODE ? 'Error al iniciar sesión: ' . $e->getMessage() : 'Error al procesar el inicio de sesión. Inténtalo de nuevo más tarde.';
    }
    
    return $result;
}

/**
 * Cierra la sesión del usuario actual
 * 
 * @param bool $clearRememberToken Eliminar token de "recordarme"
 * @return bool Éxito de la operación
 */
function logoutUser($clearRememberToken = true) {
    // Limpiar token de "recordarme" si está activo
    if ($clearRememberToken && isset($_COOKIE['webcraft_remember'])) {
        try {
            // Obtener partes del token
            list($userId, $token) = explode(':', $_COOKIE['webcraft_remember']);
            
            // Eliminar token de la base de datos
            $db = getDbConnection();
            $stmt = $db->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // Eliminar cookie
            setcookie('webcraft_remember', '', time() - 3600, '/', '', SESSION_SECURE, true);
        } catch (Exception $e) {
            // Solo registrar error, no interrumpir el cierre de sesión
            if (DEV_MODE) {
                error_log('Error al eliminar token de remember: ' . $e->getMessage());
            }
        }
    }
    
    // Destruir la sesión
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    return session_destroy();
}

/**
 * Verifica si el usuario actual tiene una sesión activa
 * 
 * @return bool Usuario autenticado o no
 */
function isAuthenticated() {
    // Verificar si existe la sesión de usuario
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        // Verificar tiempo de inactividad
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] < SESSION_LIFETIME)) {
            // Actualizar tiempo de última actividad
            $_SESSION['last_activity'] = time();
            return true;
        } else {
            // Sesión expirada por inactividad
            logoutUser();
        }
    }
    
    // Verificar si existe cookie de "recordarme"
    if (isset($_COOKIE['webcraft_remember'])) {
        if (validateRememberToken()) {
            return true;
        }
    }
    
    return false;
}

/**
 * Verifica si el usuario actual tiene el rol especificado
 * 
 * @param string|array $roles Rol o roles permitidos
 * @return bool Usuario tiene el rol o no
 */
function hasRole($roles) {
    if (!isAuthenticated() || !isset($_SESSION['role'])) {
        return false;
    }
    
    if (is_array($roles)) {
        return in_array($_SESSION['role'], $roles);
    } else {
        return $_SESSION['role'] === $roles;
    }
}

/**
 * Obtiene los datos del usuario actual
 * 
 * @return array|null Datos del usuario o null si no está autenticado
 */
function getCurrentUser() {
    if (!isAuthenticated()) {
        return null;
    }
    
    try {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT user_id, username, display_name, email, bio, profile_image, 
                   developer_level, experience_points, registration_date, last_login, 
                   account_status, role
            FROM users 
            WHERE user_id = ?
        ");
        
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        if (DEV_MODE) {
            error_log('Error al obtener usuario actual: ' . $e->getMessage());
        }
        return null;
    }
}

/**
 * Genera un token aleatorio para la funcionalidad "recordarme"
 * 
 * @return string Token generado
 */
function generateRememberToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Valida un token de "recordarme" y establece la sesión si es válido
 * 
 * @return bool Token válido o no
 */
function validateRememberToken() {
    if (!isset($_COOKIE['webcraft_remember'])) {
        return false;
    }
    
    // Obtener partes del token
    $parts = explode(':', $_COOKIE['webcraft_remember']);
    if (count($parts) !== 2) {
        return false;
    }
    
    list($userId, $token) = $parts;
    
    try {
        $db = getDbConnection();
        
        // Buscar token activo para el usuario
        $stmt = $db->prepare("
            SELECT token_hash, expires_at
            FROM remember_tokens
            WHERE user_id = ? AND expires_at > NOW()
        ");
        
        $stmt->execute([$userId]);
        $tokenData = $stmt->fetch();
        
        if (!$tokenData) {
            return false;
        }
        
        // Verificar token
        if (!password_verify($token, $tokenData['token_hash'])) {
            return false;
        }
        
        // Obtener datos del usuario
        $userStmt = $db->prepare("
            SELECT user_id, username, role
            FROM users
            WHERE user_id = ? AND account_status = 'active'
        ");
        
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch();
        
        if (!$user) {
            return false;
        }
        
        // Establecer sesión de usuario
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        
        return true;
    } catch (PDOException $e) {
        if (DEV_MODE) {
            error_log('Error al validar token de remember: ' . $e->getMessage());
        }
        return false;
    }
}

/**
 * Genera un token CSRF para protección de formularios
 * 
 * @return string Token CSRF
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida un token CSRF enviado en una solicitud
 * 
 * @param string $token Token CSRF enviado
 * @return bool Token válido o no
 */
function validateCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirige a la página de login si el usuario no está autenticado
 * 
 * @param string $redirect URL a la que redirigir después del login (opcional)
 */
function requireAuthentication($redirect = '') {
    if (!isAuthenticated()) {
        $redirectParam = !empty($redirect) ? '&redirect=' . urlencode($redirect) : '';
        header('Location: index.php?page=login' . $redirectParam);
        exit;
    }
}

/**
 * Redirige si el usuario no tiene el rol requerido
 * 
 * @param string|array $roles Rol o roles permitidos
 * @param string $redirectPage Página a la que redirigir si no tiene permiso
 */
function requireRole($roles, $redirectPage = 'home') {
    if (!hasRole($roles)) {
        header('Location: index.php?page=' . $redirectPage);
        exit;
    }
}

/**
 * Actualiza las preferencias del usuario
 * 
 * @param int $userId ID del usuario
 * @param array $preferences Arreglo de preferencias [clave => valor]
 * @return bool Éxito de la operación
 */
function updateUserPreferences($userId, $preferences) {
    if (empty($userId) || empty($preferences) || !is_array($preferences)) {
        return false;
    }
    
    // Lista de preferencias permitidas para actualizar
    $allowedPreferences = [
        'theme_preference',
        'difficulty_preference',
        'editor_font_size',
        'notifications_enabled'
    ];
    
    // Filtrar solo las preferencias permitidas
    $filteredPreferences = array_intersect_key($preferences, array_flip($allowedPreferences));
    
    if (empty($filteredPreferences)) {
        return false;
    }
    
    try {
        $db = getDbConnection();
        
        // Construir consulta dinámica para actualización
        $setClauses = [];
        $params = [];
        
        foreach ($filteredPreferences as $key => $value) {
            $setClauses[] = "{$key} = ?";
            $params[] = $value;
        }
        
        $setClauses[] = "updated_at = NOW()";
        $params[] = $userId; // Para la cláusula WHERE
        
        $sql = "UPDATE user_preferences SET " . implode(', ', $setClauses) . " WHERE user_id = ?";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
        
    } catch (PDOException $e) {
        if (DEV_MODE) {
            error_log('Error al actualizar preferencias: ' . $e->getMessage());
        }
        return false;
    }
}

/**
 * Obtiene las preferencias del usuario
 * 
 * @param int $userId ID del usuario
 * @return array|null Preferencias del usuario o null en caso de error
 */
function getUserPreferences($userId) {
    if (empty($userId)) {
        return null;
    }
    
    try {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        if (DEV_MODE) {
            error_log('Error al obtener preferencias: ' . $e->getMessage());
        }
        return null;
    }
}

/**
 * Actualiza el perfil del usuario
 * 
 * @param int $userId ID del usuario
 * @param array $profileData Datos del perfil a actualizar
 * @return bool Éxito de la operación
 */
function updateUserProfile($userId, $profileData) {
    if (empty($userId) || empty($profileData) || !is_array($profileData)) {
        return false;
    }
    
    // Lista de campos del perfil permitidos para actualizar
    $allowedFields = [
        'display_name',
        'bio',
        'profile_image',
        'email'
    ];
    
    // Filtrar solo los campos permitidos
    $filteredData = array_intersect_key($profileData, array_flip($allowedFields));
    
    if (empty($filteredData)) {
        return false;
    }
    
    try {
        $db = getDbConnection();
        
        // Construir consulta dinámica para actualización
        $setClauses = [];
        $params = [];
        
        foreach ($filteredData as $key => $value) {
            $setClauses[] = "{$key} = ?";
            $params[] = $value;
        }
        
        $params[] = $userId; // Para la cláusula WHERE
        
        $sql = "UPDATE users SET " . implode(', ', $setClauses) . " WHERE user_id = ?";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
        
    } catch (PDOException $e) {
        if (DEV_MODE) {
            error_log('Error al actualizar perfil: ' . $e->getMessage());
        }
        return false;
    }
}

/**
 * Cambia la contraseña del usuario
 * 
 * @param int $userId ID del usuario
 * @param string $currentPassword Contraseña actual
 * @param string $newPassword Nueva contraseña
 * @return array Resultado ['success' => bool, 'message' => string]
 */
function changeUserPassword($userId, $currentPassword, $newPassword) {
    $result = ['success' => false, 'message' => ''];
    
    if (empty($userId) || empty($currentPassword) || empty($newPassword)) {
        $result['message'] = 'Todos los campos son obligatorios.';
        return $result;
    }
    
    // Validar complejidad de la nueva contraseña
    if (strlen($newPassword) < 8) {
        $result['message'] = 'La nueva contraseña debe tener al menos 8 caracteres.';
        return $result;
    }
    
    try {
        $db = getDbConnection();
        
        // Obtener hash de contraseña actual
        $stmt = $db->prepare("SELECT password_hash FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $result['message'] = 'Usuario no encontrado.';
            return $result;
        }
        
        // Verificar contraseña actual
        if (!password_verify($currentPassword, $user['password_hash'])) {
            $result['message'] = 'La contraseña actual es incorrecta.';
            return $result;
        }
        
        // Generar hash para la nueva contraseña
        $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
        
        // Actualizar contraseña
        $updateStmt = $db->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $updateStmt->execute([$newPasswordHash, $userId]);
        
        $result['success'] = true;
        $result['message'] = 'Contraseña actualizada correctamente.';
        
    } catch (PDOException $e) {
        $result['message'] = DEV_MODE ? 'Error al cambiar contraseña: ' . $e->getMessage() : 'Error al procesar el cambio de contraseña. Inténtalo de nuevo más tarde.';
    }
    
    return $result;
}
