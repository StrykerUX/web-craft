<?php
/**
 * Funciones centralizadas de autenticación para WebCraft Academy
 * 
 * Este archivo contiene todas las funciones centralizadas para manejar la autenticación
 * de usuarios, validación de datos y seguridad relacionada con la autenticación.
 * 
 * @package WebCraft
 * @subpackage Authentication
 */

// Prevenir acceso directo a este archivo
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

/**
 * Autenticar a un usuario con nombre de usuario/email y contraseña
 * 
 * @param string $username_or_email Nombre de usuario o email
 * @param string $password Contraseña sin encriptar
 * @return array|false Datos del usuario en caso de éxito, false en caso de error
 */
function authenticateUser($username_or_email, $password) {
    if (empty($username_or_email) || empty($password)) {
        return false;
    }
    
    try {
        // Buscar usuario
        $stmt = getDbConnection()->prepare("SELECT u.user_id, u.username, u.password, u.email, u.is_active, p.level, p.xp_points 
                                          FROM users u 
                                          LEFT JOIN user_profiles p ON u.user_id = p.user_id 
                                          WHERE u.username = ? OR u.email = ?");
        $stmt->execute([$username_or_email, $username_or_email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Verificar estado de la cuenta
            if (!$user['is_active']) {
                // Cuenta inactiva
                return [
                    'success' => false,
                    'message' => 'Tu cuenta está inactiva. Por favor, contacta a soporte.',
                    'error_code' => 'inactive_account'
                ];
            }
            
            // Actualizar último login
            $updateStmt = getDbConnection()->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $updateStmt->execute([$user['user_id']]);
            
            // Eliminar la contraseña del array de retorno por seguridad
            unset($user['password']);
            
            return [
                'success' => true,
                'user' => $user
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Usuario o contraseña incorrectos. Por favor, intenta nuevamente.',
            'error_code' => 'invalid_credentials'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error al procesar la solicitud. Por favor, intenta nuevamente más tarde.',
            'error_code' => 'database_error',
            'dev_message' => DEV_MODE ? $e->getMessage() : null
        ];
    }
}

/**
 * Registrar un nuevo usuario
 * 
 * @param string $username Nombre de usuario
 * @param string $email Email
 * @param string $password Contraseña sin encriptar
 * @param string $full_name Nombre completo (opcional)
 * @return array Resultado de la operación
 */
function registerUser($username, $email, $password, $full_name = '') {
    // Validaciones básicas
    $validation = validateUserData($username, $email, $password);
    if (!$validation['success']) {
        return $validation;
    }
    
    try {
        $pdo = getDbConnection();
        
        // Verificar si el usuario o email ya existen
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetchColumn() > 0) {
            // Verificar cuál existe para un mensaje más específico
            $stmt = $pdo->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            $existing = $stmt->fetch();
            
            if ($existing['username'] === $username) {
                return [
                    'success' => false,
                    'message' => 'El nombre de usuario ya está en uso. Por favor, elige otro.',
                    'error_code' => 'username_exists'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'El correo electrónico ya está registrado. ¿Olvidaste tu contraseña?',
                    'error_code' => 'email_exists'
                ];
            }
        }
        
        // Crear el hash de la contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
        
        // Preparar full_name si está vacío
        if (empty($full_name)) {
            $full_name = $username;
        }
        
        // Iniciar transacción
        $pdo->beginTransaction();
        
        // Insertar nuevo usuario
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, registration_date)
            VALUES (?, ?, ?, NOW())
        ");
        
        $stmt->execute([$username, $email, $password_hash]);
        
        // Obtener el ID del usuario
        $user_id = $pdo->lastInsertId();
        
        // Crear perfil de usuario
        $stmt = $pdo->prepare("
            INSERT INTO user_profiles (user_id, full_name, level, xp_points, theme_preference)
            VALUES (?, ?, 'Principiante', 0, 'system')
        ");
        $stmt->execute([$user_id, $full_name]);
        
        // Confirmar la transacción
        $pdo->commit();
        
        return [
            'success' => true,
            'message' => 'Cuenta creada exitosamente.',
            'user_id' => $user_id,
            'username' => $username,
            'developer_level' => 'Principiante'
        ];
        
    } catch (PDOException $e) {
        // Revertir cambios en caso de error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        return [
            'success' => false,
            'message' => 'Error al crear la cuenta. Por favor, intenta nuevamente.',
            'error_code' => 'database_error',
            'dev_message' => DEV_MODE ? $e->getMessage() : null
        ];
    }
}

/**
 * Validar datos de usuario para registro y actualización
 * 
 * @param string $username Nombre de usuario
 * @param string $email Email
 * @param string $password Contraseña (opcional para actualizaciones)
 * @return array Resultado de la validación
 */
function validateUserData($username, $email, $password = null) {
    // Validar nombre de usuario
    if (empty($username)) {
        return [
            'success' => false,
            'message' => 'El nombre de usuario es obligatorio.',
            'error_code' => 'empty_username'
        ];
    }
    
    if (strlen($username) < 3 || strlen($username) > 50) {
        return [
            'success' => false,
            'message' => 'El nombre de usuario debe tener entre 3 y 50 caracteres.',
            'error_code' => 'invalid_username_length'
        ];
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return [
            'success' => false,
            'message' => 'El nombre de usuario solo puede contener letras, números y guiones bajos.',
            'error_code' => 'invalid_username_format'
        ];
    }
    
    // Validar email
    if (empty($email)) {
        return [
            'success' => false,
            'message' => 'El correo electrónico es obligatorio.',
            'error_code' => 'empty_email'
        ];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'success' => false,
            'message' => 'Por favor, ingresa un correo electrónico válido.',
            'error_code' => 'invalid_email'
        ];
    }
    
    // Validar contraseña (solo si se proporciona)
    if ($password !== null) {
        if (empty($password)) {
            return [
                'success' => false,
                'message' => 'La contraseña es obligatoria.',
                'error_code' => 'empty_password'
            ];
        }
        
        if (strlen($password) < 8) {
            return [
                'success' => false,
                'message' => 'La contraseña debe tener al menos 8 caracteres.',
                'error_code' => 'password_too_short'
            ];
        }
    }
    
    // Si todas las validaciones pasaron
    return [
        'success' => true
    ];
}

/**
 * Iniciar sesión para un usuario
 * 
 * @param array $user Datos del usuario
 * @param bool $remember Recordar sesión
 * @return void
 */
function loginUser($user, $remember = false) {
    // Establecer variables de sesión
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['developer_level'] = $user['level'] ?? 'Principiante';
    
    // Si se solicita recordar la sesión
    if ($remember) {
        // Generar token único
        $selector = bin2hex(random_bytes(8));
        $validator = bin2hex(random_bytes(32));
        
        // Almacenar hash del token en la base de datos
        $token_hash = hash('sha256', $validator);
        $expires = date('Y-m-d H:i:s', time() + 30*24*60*60); // 30 días
        
        try {
            $stmt = getDbConnection()->prepare("
                INSERT INTO user_tokens (user_id, selector, token, expires)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$user['user_id'], $selector, $token_hash, $expires]);
            
            // Crear cookie
            $cookie_value = $selector . ':' . $validator;
            setcookie(
                'remember_me',
                $cookie_value,
                time() + 30*24*60*60, // 30 días
                '/',
                '',
                true, // Requiere HTTPS
                true  // HttpOnly
            );
        } catch (PDOException $e) {
            // Silenciar errores, la sesión seguirá funcionando sin "recordarme"
            if (DEV_MODE) {
                error_log('Error al crear token de "recordarme": ' . $e->getMessage());
            }
        }
    }
}

/**
 * Verificar y restaurar la sesión usando cookie "recordarme"
 * 
 * @return bool Éxito de la operación
 */
function checkRememberMeCookie() {
    if (!isset($_COOKIE['remember_me'])) {
        return false;
    }
    
    // Obtener partes del token
    $parts = explode(':', $_COOKIE['remember_me']);
    if (count($parts) !== 2) {
        setcookie('remember_me', '', time() - 3600, '/');
        return false;
    }
    
    $selector = $parts[0];
    $validator = $parts[1];
    
    try {
        $stmt = getDbConnection()->prepare("
            SELECT t.user_id, t.token, u.username, p.level
            FROM user_tokens t
            JOIN users u ON t.user_id = u.user_id
            LEFT JOIN user_profiles p ON u.user_id = p.user_id
            WHERE t.selector = ? AND t.expires > NOW()
        ");
        $stmt->execute([$selector]);
        $tokenData = $stmt->fetch();
        
        if (!$tokenData) {
            setcookie('remember_me', '', time() - 3600, '/');
            return false;
        }
        
        // Verificar token
        $tokenBin = hex2bin($validator);
        $tokenHash = hash('sha256', $validator);
        
        if (hash_equals($tokenData['token'], $tokenHash)) {
            // Iniciar sesión
            $_SESSION['user_id'] = $tokenData['user_id'];
            $_SESSION['username'] = $tokenData['username'];
            $_SESSION['developer_level'] = $tokenData['level'] ?? 'Principiante';
            
            // Regenerar token para seguridad
            $newValidator = bin2hex(random_bytes(32));
            $newTokenHash = hash('sha256', $newValidator);
            $expires = date('Y-m-d H:i:s', time() + 30*24*60*60); // 30 días
            
            $stmt = getDbConnection()->prepare("
                UPDATE user_tokens 
                SET token = ?, expires = ? 
                WHERE selector = ?
            ");
            $stmt->execute([$newTokenHash, $expires, $selector]);
            
            // Actualizar cookie
            $newCookieValue = $selector . ':' . $newValidator;
            setcookie(
                'remember_me',
                $newCookieValue,
                time() + 30*24*60*60,
                '/',
                '',
                true,
                true
            );
            
            return true;
        }
    } catch (PDOException $e) {
        if (DEV_MODE) {
            error_log('Error al verificar token de "recordarme": ' . $e->getMessage());
        }
    }
    
    // Si llegamos aquí, algo falló
    setcookie('remember_me', '', time() - 3600, '/');
    return false;
}

/**
 * Cerrar sesión de usuario
 * 
 * @param bool $clear_remember Eliminar también cookie "recordarme"
 * @return void
 */
function logoutUser($clear_remember = true) {
    // Eliminar todas las variables de sesión
    $_SESSION = array();
    
    // Si se usa un cookie de sesión, eliminarla
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
    
    // Eliminar cookie "recordarme" si se solicita
    if ($clear_remember && isset($_COOKIE['remember_me'])) {
        // Obtener selector para eliminar de la base de datos
        $parts = explode(':', $_COOKIE['remember_me']);
        if (count($parts) === 2) {
            try {
                $stmt = getDbConnection()->prepare("DELETE FROM user_tokens WHERE selector = ?");
                $stmt->execute([$parts[0]]);
            } catch (PDOException $e) {
                // Silenciar errores
            }
        }
        
        // Eliminar la cookie
        setcookie('remember_me', '', time() - 3600, '/');
    }
    
    // Destruir la sesión
    session_destroy();
}

/**
 * Generar token para restablecimiento de contraseña
 * 
 * @param string $email Email del usuario
 * @return array Resultado de la operación
 */
function generatePasswordResetToken($email) {
    if (empty($email)) {
        return [
            'success' => false,
            'message' => 'El correo electrónico es requerido.'
        ];
    }
    
    try {
        $pdo = getDbConnection();
        
        // Verificar si el email existe
        $stmt = $pdo->prepare("SELECT user_id, username FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'No se encontró una cuenta activa con ese correo electrónico.'
            ];
        }
        
        // Generar token único
        $selector = bin2hex(random_bytes(8));
        $token = bin2hex(random_bytes(32));
        
        // Hash para almacenar en la base de datos
        $token_hash = hash('sha256', $token);
        $expires = date('Y-m-d H:i:s', time() + 1*60*60); // 1 hora
        
        // Eliminar tokens anteriores para este usuario
        $stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        
        // Almacenar nuevo token
        $stmt = $pdo->prepare("
            INSERT INTO password_reset_tokens (user_id, selector, token, expires)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$user['user_id'], $selector, $token_hash, $expires]);
        
        // Generar URL para reseteo
        $reset_url = BASE_URL . 'index.php?page=reset-password&selector=' . urlencode($selector) . '&validator=' . urlencode($token);
        
        return [
            'success' => true,
            'message' => 'Token generado exitosamente.',
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'email' => $email,
            'reset_url' => $reset_url
        ];
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error al procesar la solicitud. Por favor, intenta nuevamente más tarde.',
            'dev_message' => DEV_MODE ? $e->getMessage() : null
        ];
    }
}

/**
 * Verificar token de restablecimiento de contraseña
 * 
 * @param string $selector Selector del token
 * @param string $validator Validator del token
 * @return array Resultado de la operación
 */
function verifyPasswordResetToken($selector, $validator) {
    if (empty($selector) || empty($validator)) {
        return [
            'success' => false,
            'message' => 'Token inválido o expirado.'
        ];
    }
    
    try {
        $stmt = getDbConnection()->prepare("
            SELECT t.user_id, t.token, u.username, u.email
            FROM password_reset_tokens t
            JOIN users u ON t.user_id = u.user_id
            WHERE t.selector = ? AND t.expires > NOW()
        ");
        $stmt->execute([$selector]);
        $tokenData = $stmt->fetch();
        
        if (!$tokenData) {
            return [
                'success' => false,
                'message' => 'Token inválido o expirado.'
            ];
        }
        
        // Verificar token
        $tokenHash = hash('sha256', $validator);
        
        if (hash_equals($tokenData['token'], $tokenHash)) {
            return [
                'success' => true,
                'user_id' => $tokenData['user_id'],
                'username' => $tokenData['username'],
                'email' => $tokenData['email']
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Token inválido o expirado.'
            ];
        }
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error al procesar la solicitud. Por favor, intenta nuevamente más tarde.',
            'dev_message' => DEV_MODE ? $e->getMessage() : null
        ];
    }
}

/**
 * Cambiar contraseña de usuario
 * 
 * @param int $user_id ID del usuario
 * @param string $new_password Nueva contraseña
 * @return array Resultado de la operación
 */
function changeUserPassword($user_id, $new_password) {
    if (empty($user_id) || empty($new_password)) {
        return [
            'success' => false,
            'message' => 'Datos incompletos para cambiar la contraseña.'
        ];
    }
    
    if (strlen($new_password) < 8) {
        return [
            'success' => false,
            'message' => 'La contraseña debe tener al menos 8 caracteres.'
        ];
    }
    
    try {
        $pdo = getDbConnection();
        
        // Generar hash de la nueva contraseña
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
        
        // Actualizar contraseña
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->execute([$password_hash, $user_id]);
        
        // Eliminar tokens de restablecimiento para este usuario
        $stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        return [
            'success' => true,
            'message' => 'Contraseña actualizada exitosamente.'
        ];
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error al cambiar la contraseña. Por favor, intenta nuevamente más tarde.',
            'dev_message' => DEV_MODE ? $e->getMessage() : null
        ];
    }
}

/**
 * Verificar si un usuario está autenticado
 * 
 * @return bool True si está autenticado, false si no
 */
function isUserLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Obtener datos del usuario actual
 * 
 * @param bool $complete Obtener datos completos del perfil
 * @return array|false Datos del usuario o false si no está autenticado
 */
function getCurrentUser($complete = false) {
    if (!isUserLoggedIn()) {
        return false;
    }
    
    try {
        if ($complete) {
            // Obtener datos completos incluyendo perfil
            $stmt = getDbConnection()->prepare("
                SELECT u.user_id, u.username, u.email, u.registration_date, u.last_login, u.role,
                       p.profile_id, p.full_name, p.avatar, p.bio, p.level, p.xp_points, p.theme_preference
                FROM users u
                LEFT JOIN user_profiles p ON u.user_id = p.user_id
                WHERE u.user_id = ?
            ");
        } else {
            // Obtener datos básicos
            $stmt = getDbConnection()->prepare("
                SELECT u.user_id, u.username, u.email, p.level, p.xp_points, p.avatar
                FROM users u
                LEFT JOIN user_profiles p ON u.user_id = p.user_id
                WHERE u.user_id = ?
            ");
        }
        
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        if (DEV_MODE) {
            error_log('Error al obtener datos del usuario: ' . $e->getMessage());
        }
        return false;
    }
}

/**
 * Actualizar datos del perfil de usuario
 * 
 * @param array $data Datos a actualizar
 * @return array Resultado de la operación
 */
function updateUserProfile($data) {
    if (!isUserLoggedIn()) {
        return [
            'success' => false,
            'message' => 'Debes iniciar sesión para actualizar tu perfil.'
        ];
    }
    
    try {
        $pdo = getDbConnection();
        $user_id = $_SESSION['user_id'];
        
        // Actualizar campos en la tabla user_profiles
        $allowedFields = ['full_name', 'bio', 'theme_preference', 'accessibility_settings'];
        $updates = [];
        $params = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (!empty($updates)) {
            $params[] = $user_id; // Para WHERE user_id = ?
            $stmt = $pdo->prepare("
                UPDATE user_profiles 
                SET " . implode(', ', $updates) . "
                WHERE user_id = ?
            ");
            $stmt->execute($params);
        }
        
        // Si hay campos que actualizar en la tabla users
        if (isset($data['email'])) {
            // Validar email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Por favor, ingresa un correo electrónico válido.'
                ];
            }
            
            // Verificar si el email ya existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND user_id != ?");
            $stmt->execute([$data['email'], $user_id]);
            
            if ($stmt->fetchColumn() > 0) {
                return [
                    'success' => false,
                    'message' => 'El correo electrónico ya está en uso por otra cuenta.'
                ];
            }
            
            // Actualizar email
            $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE user_id = ?");
            $stmt->execute([$data['email'], $user_id]);
        }
        
        return [
            'success' => true,
            'message' => 'Perfil actualizado exitosamente.'
        ];
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error al actualizar el perfil. Por favor, intenta nuevamente más tarde.',
            'dev_message' => DEV_MODE ? $e->getMessage() : null
        ];
    }
}

/**
 * Generar un token CSRF
 *
 * @return string Token CSRF
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar un token CSRF
 *
 * @param string $token Token a verificar
 * @return bool Resultado de la validación
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Registrar un intento de inicio de sesión fallido
 *
 * @param string $username_or_email Nombre de usuario o email
 * @return bool True si se ha superado el límite de intentos
 */
function registerFailedLoginAttempt($username_or_email) {
    // Obtener la IP actual
    $ip = $_SERVER['REMOTE_ADDR'];
    
    try {
        $pdo = getDbConnection();
        
        // Limpiar registros antiguos
        $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE attempt_time < (NOW() - INTERVAL ? SECOND)");
        $stmt->execute([LOCKOUT_TIME]);
        
        // Registrar el nuevo intento
        $stmt = $pdo->prepare("
            INSERT INTO login_attempts (username_or_email, ip_address, attempt_time)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$username_or_email, $ip]);
        
        // Verificar si se ha excedido el límite de intentos
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM login_attempts
            WHERE (username_or_email = ? OR ip_address = ?)
            AND attempt_time > (NOW() - INTERVAL ? SECOND)
        ");
        $stmt->execute([$username_or_email, $ip, LOCKOUT_TIME]);
        
        return $stmt->fetchColumn() >= MAX_LOGIN_ATTEMPTS;
        
    } catch (PDOException $e) {
        // En caso de error, asumimos que no se ha superado el límite
        if (DEV_MODE) {
            error_log('Error al registrar intento de inicio de sesión: ' . $e->getMessage());
        }
        return false;
    }
}

/**
 * Verificar si una cuenta está bloqueada por demasiados intentos
 *
 * @param string $username_or_email Nombre de usuario o email
 * @return bool True si la cuenta está bloqueada
 */
function isAccountLocked($username_or_email) {
    // Obtener la IP actual
    $ip = $_SERVER['REMOTE_ADDR'];
    
    try {
        $stmt = getDbConnection()->prepare("
            SELECT COUNT(*) FROM login_attempts
            WHERE (username_or_email = ? OR ip_address = ?)
            AND attempt_time > (NOW() - INTERVAL ? SECOND)
        ");
        $stmt->execute([$username_or_email, $ip, LOCKOUT_TIME]);
        
        return $stmt->fetchColumn() >= MAX_LOGIN_ATTEMPTS;
        
    } catch (PDOException $e) {
        // En caso de error, asumimos que la cuenta no está bloqueada
        if (DEV_MODE) {
            error_log('Error al verificar bloqueo de cuenta: ' . $e->getMessage());
        }
        return false;
    }
}

/**
 * Generar un código de activación para cuenta de usuario
 *
 * @param int $user_id ID del usuario
 * @return array Resultado de la operación
 */
function generateActivationCode($user_id) {
    try {
        $pdo = getDbConnection();
        
        // Verificar si el usuario existe
        $stmt = $pdo->prepare("SELECT username, email FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado.'
            ];
        }
        
        // Generar código de activación
        $code = bin2hex(random_bytes(16));
        $expires = date('Y-m-d H:i:s', time() + 24*60*60); // 24 horas
        
        // Eliminar códigos anteriores
        $stmt = $pdo->prepare("DELETE FROM activation_codes WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Almacenar nuevo código
        $stmt = $pdo->prepare("
            INSERT INTO activation_codes (user_id, code, expires)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$user_id, $code, $expires]);
        
        // Generar URL de activación
        $activation_url = BASE_URL . 'index.php?page=activate&code=' . urlencode($code);
        
        return [
            'success' => true,
            'message' => 'Código de activación generado exitosamente.',
            'username' => $user['username'],
            'email' => $user['email'],
            'activation_url' => $activation_url
        ];
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error al generar código de activación.',
            'dev_message' => DEV_MODE ? $e->getMessage() : null
        ];
    }
}

/**
 * Activar cuenta de usuario
 *
 * @param string $code Código de activación
 * @return array Resultado de la operación
 */
function activateUserAccount($code) {
    try {
        $pdo = getDbConnection();
        
        // Buscar el código
        $stmt = $pdo->prepare("
            SELECT user_id FROM activation_codes
            WHERE code = ? AND expires > NOW()
        ");
        $stmt->execute([$code]);
        $result = $stmt->fetch();
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Código de activación inválido o expirado.'
            ];
        }
        
        $user_id = $result['user_id'];
        
        // Activar la cuenta
        $stmt = $pdo->prepare("UPDATE users SET is_active = 1 WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Eliminar el código usado
        $stmt = $pdo->prepare("DELETE FROM activation_codes WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        return [
            'success' => true,
            'message' => 'Cuenta activada exitosamente. Ahora puedes iniciar sesión.',
            'user_id' => $user_id
        ];
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error al activar la cuenta.',
            'dev_message' => DEV_MODE ? $e->getMessage() : null
        ];
    }
}
