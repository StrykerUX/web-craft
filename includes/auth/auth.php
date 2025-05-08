<?php
/**
 * WebCraft Academy - Funciones de autenticación
 * 
 * Este archivo contiene las funciones relacionadas con la autenticación de usuarios,
 * incluyendo inicio de sesión, registro, y gestión de sesiones.
 */

// Definir constante para permitir acceso a los archivos de configuración
if (!defined('WEBCRAFT')) {
    define('WEBCRAFT', true);
}

// Incluir archivo de configuración global si no está ya incluido
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../config.php';
}

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    // Configurar opciones de sesión
    session_name(SESSION_NAME);
    
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => SESSION_PATH,
        'secure' => SESSION_SECURE,
        'httponly' => SESSION_HTTPONLY,
        'samesite' => 'Lax'
    ]);
    
    session_start();
}

/**
 * Verifica si un usuario está autenticado
 * 
 * @return bool True si el usuario está autenticado, false en caso contrario
 */
function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

/**
 * Autentica a un usuario
 * 
 * @param string $userOrEmail Nombre de usuario o Email del usuario
 * @param string $password Contraseña del usuario
 * @return array Resultado de la operación
 */
function loginUser($userOrEmail, $password) {
    try {
        $db = getDbConnection();
        
        // Verificar si es email o nombre de usuario
        $isEmail = filter_var($userOrEmail, FILTER_VALIDATE_EMAIL);
        
        // Preparar consulta según el tipo de entrada
        if ($isEmail) {
            // Buscar por email
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        } else {
            // Buscar por nombre de usuario
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        }
        
        $stmt->execute([$userOrEmail]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Si el usuario no existe en la base de datos
            if (DEV_MODE) {
                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado en la base de datos'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Credenciales inválidas'
                ];
            }
        }
        
        // Verificar si la contraseña está almacenada como texto plano
        // NOTA: Esto es solo una medida temporal para migrar desde un sistema sin hash
        if (substr($user['password'], 0, 1) !== '$') {
            // Contraseña en texto plano: comparación directa para migración
            if ($password === $user['password']) {
                // Actualizar contraseña a formato hash seguro
                $newPasswordHash = password_hash($password, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
                $updateStmt = $db->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $updateStmt->execute([$newPasswordHash, $user['user_id']]);
                
                // Continuar con el inicio de sesión
            } else {
                // Contraseña incorrecta
                updateLoginAttempts($user['user_id'], true);
                
                if (DEV_MODE) {
                    return [
                        'success' => false,
                        'message' => 'Contraseña en texto plano incorrecta'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Credenciales inválidas'
                    ];
                }
            }
        } else {
            // Contraseña con hash: verificar normalmente
            if (!password_verify($password, $user['password'])) {
                // Incrementar contador de intentos fallidos
                updateLoginAttempts($user['user_id'], true);
                
                if (DEV_MODE) {
                    return [
                        'success' => false,
                        'message' => 'Contraseña hash incorrecta. Hash almacenado: ' . substr($user['password'], 0, 10) . '...'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Credenciales inválidas'
                    ];
                }
            }
            
            // Actualizar hash si es necesario (rehash)
            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT, ['cost' => HASH_COST])) {
                $newPasswordHash = password_hash($password, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
                $updateStmt = $db->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $updateStmt->execute([$newPasswordHash, $user['user_id']]);
            }
        }
        
        // Verificar si la cuenta está bloqueada
        if (isAccountLocked($user['user_id'])) {
            return [
                'success' => false,
                'message' => 'La cuenta está temporalmente bloqueada debido a múltiples intentos fallidos. Por favor, intenta nuevamente más tarde.'
            ];
        }
        
        // Resetear intentos fallidos
        updateLoginAttempts($user['user_id'], false);
        
        // Actualizar último acceso
        $stmt = $db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        
        // Generar token de "recordarme" si es necesario
        $rememberToken = null;
        if (isset($_POST['remember']) && $_POST['remember'] == '1') {
            $rememberToken = generateRememberToken($user['user_id']);
        }
        
        // Establecer datos de sesión
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        
        // Obtener datos de perfil
        $stmt = $db->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        $profile = $stmt->fetch();
        
        if ($profile) {
            $_SESSION['user_level'] = $profile['level'];
            $_SESSION['user_xp'] = $profile['xp_points'];
        }
        
        return [
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'user' => [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ],
            'remember_token' => $rememberToken
        ];
    } catch (PDOException $e) {
        // Error en la base de datos
        if (DEV_MODE) {
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error del sistema. Por favor, intenta nuevamente más tarde.'
            ];
        }
    }
}

/**
 * Genera un token para la funcionalidad "Recordarme"
 * 
 * @param int $userId ID del usuario
 * @return string Token generado
 */
function generateRememberToken($userId) {
    try {
        $db = getDbConnection();
        
        // Generar token único
        $selector = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(32));
        
        // Almacenar hash del token en la base de datos
        $hashedValidator = password_hash($validator, PASSWORD_DEFAULT);
        $expires = date('Y-m-d H:i:s', time() + 30 * 24 * 60 * 60); // 30 días
        
        // Eliminar tokens antiguos del usuario
        $stmt = $db->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Insertar nuevo token
        $stmt = $db->prepare("
            INSERT INTO remember_tokens (user_id, selector, token, expires) 
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([$userId, $selector, $hashedValidator, $expires]);
        
        // Devolver token completo para establecer cookie
        return $selector . ':' . $validator;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Cierra la sesión del usuario actual
 */
function logoutUser() {
    // Destruir todas las variables de sesión
    $_SESSION = [];
    
    // Destruir la cookie de sesión
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
    
    // Eliminar cookie de "recordarme"
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    // Destruir la sesión
    session_destroy();
}

/**
 * Registra un nuevo usuario
 * 
 * @param string $username Nombre de usuario
 * @param string $email Email del usuario
 * @param string $password Contraseña del usuario
 * @param string $fullName Nombre completo del usuario (opcional)
 * @return array Resultado de la operación
 */
function registerUser($username, $email, $password, $fullName = '') {
    try {
        $db = getDbConnection();
        
        // Verificar si el correo ya existe
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            return [
                'success' => false,
                'message' => 'El correo electrónico ya está registrado'
            ];
        }
        
        // Verificar si el nombre de usuario ya existe
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            return [
                'success' => false,
                'message' => 'El nombre de usuario ya está en uso'
            ];
        }
        
        // Crear hash de la contraseña
        $passwordHash = password_hash($password, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
        
        // Iniciar transacción
        $db->beginTransaction();
        
        // Insertar usuario
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password, registration_date, role, is_active) 
            VALUES (?, ?, ?, CURRENT_TIMESTAMP, 'student', 1)
        ");
        
        $stmt->execute([$username, $email, $passwordHash]);
        $userId = $db->lastInsertId();
        
        // Crear perfil de usuario
        $stmt = $db->prepare("
            INSERT INTO user_profiles (user_id, full_name, level, xp_points) 
            VALUES (?, ?, 'Principiante', 0)
        ");
        
        // Si no se proporcionó nombre completo, usar nombre de usuario
        if (empty($fullName)) {
            $fullName = $username;
        }
        
        $stmt->execute([$userId, $fullName]);
        
        // Commit transacción
        $db->commit();
        
        return [
            'success' => true,
            'message' => 'Usuario registrado correctamente. Puedes iniciar sesión ahora.',
            'user_id' => $userId
        ];
    } catch (PDOException $e) {
        // Rollback en caso de error
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        
        if (DEV_MODE) {
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al registrar usuario. Por favor, intenta nuevamente.'
            ];
        }
    }
}

/**
 * Verifica si la cuenta de usuario está bloqueada por intentos fallidos
 * 
 * @param int $userId ID del usuario
 * @return bool True si la cuenta está bloqueada, false en caso contrario
 */
function isAccountLocked($userId) {
    try {
        $db = getDbConnection();
        
        // Verificar si existe la tabla
        $stmt = $db->prepare("SHOW TABLES LIKE 'login_attempts'");
        $stmt->execute();
        if ($stmt->rowCount() === 0) {
            // La tabla no existe, crear la tabla
            $db->exec("
                CREATE TABLE login_attempts (
                    user_id INT PRIMARY KEY,
                    failed_attempts INT DEFAULT 0,
                    last_failed_attempt INT
                )
            ");
            return false;
        }
        
        // Obtener información de intentos fallidos
        $stmt = $db->prepare("
            SELECT failed_attempts, last_failed_attempt 
            FROM login_attempts 
            WHERE user_id = ?
        ");
        
        $stmt->execute([$userId]);
        $attemptInfo = $stmt->fetch();
        
        if (!$attemptInfo) {
            return false;
        }
        
        // Verificar si excede el máximo permitido y si no ha pasado el tiempo de bloqueo
        if ($attemptInfo['failed_attempts'] >= MAX_LOGIN_ATTEMPTS) {
            $lockoutTime = $attemptInfo['last_failed_attempt'] + LOCKOUT_TIME;
            
            if (time() < $lockoutTime) {
                return true;
            }
            
            // Si ya pasó el tiempo de bloqueo, reiniciar contador
            updateLoginAttempts($userId, false);
        }
        
        return false;
    } catch (PDOException $e) {
        // En caso de error, permitir el acceso para evitar bloqueo permanente
        return false;
    }
}

/**
 * Actualiza el contador de intentos fallidos de inicio de sesión
 * 
 * @param int $userId ID del usuario
 * @param bool $failed Indica si el intento fue fallido
 */
function updateLoginAttempts($userId, $failed) {
    try {
        $db = getDbConnection();
        
        // Verificar si existe la tabla
        $stmt = $db->prepare("SHOW TABLES LIKE 'login_attempts'");
        $stmt->execute();
        if ($stmt->rowCount() === 0) {
            // La tabla no existe, crear la tabla
            $db->exec("
                CREATE TABLE login_attempts (
                    user_id INT PRIMARY KEY,
                    failed_attempts INT DEFAULT 0,
                    last_failed_attempt INT
                )
            ");
        }
        
        if ($failed) {
            // Incrementar contador de intentos fallidos
            $stmt = $db->prepare("
                INSERT INTO login_attempts (user_id, failed_attempts, last_failed_attempt) 
                VALUES (?, 1, ?) 
                ON DUPLICATE KEY UPDATE 
                failed_attempts = failed_attempts + 1, last_failed_attempt = ?
            ");
            
            $stmt->execute([$userId, time(), time()]);
        } else {
            // Resetear contador de intentos fallidos
            $stmt = $db->prepare("
                UPDATE login_attempts 
                SET failed_attempts = 0, last_failed_attempt = NULL 
                WHERE user_id = ?
            ");
            
            $stmt->execute([$userId]);
        }
    } catch (PDOException $e) {
        // Ignorar errores
        if (DEV_MODE) {
            error_log('Error en updateLoginAttempts: ' . $e->getMessage());
        }
    }
}

/**
 * Obtiene información del usuario actual
 * 
 * @return array|null Datos del usuario o null si no está autenticado
 */
function getCurrentUser() {
    if (!isUserLoggedIn()) {
        return null;
    }
    
    try {
        $db = getDbConnection();
        
        // Obtener datos de usuario
        $stmt = $db->prepare("
            SELECT u.user_id, u.username, u.email, u.role, u.registration_date, u.last_login,
                   p.level, p.xp_points, p.avatar, p.bio, p.theme_preference
            FROM users u
            LEFT JOIN user_profiles p ON u.user_id = p.user_id
            WHERE u.user_id = ?
        ");
        
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Verifica si el usuario actual tiene el rol especificado
 * 
 * @param string|array $roles Rol o roles permitidos
 * @return bool True si el usuario tiene uno de los roles especificados
 */
function userHasRole($roles) {
    if (!isUserLoggedIn() || !isset($_SESSION['role'])) {
        return false;
    }
    
    if (is_string($roles)) {
        $roles = [$roles];
    }
    
    return in_array($_SESSION['role'], $roles);
}

/**
 * Verifica si la sesión del usuario actual ha expirado
 * 
 * @return bool True si la sesión ha expirado, false en caso contrario
 */
function isSessionExpired() {
    if (!isset($_SESSION['last_activity'])) {
        return true;
    }
    
    if (time() - $_SESSION['last_activity'] > SESSION_LIFETIME) {
        return true;
    }
    
    // Actualizar tiempo de última actividad
    $_SESSION['last_activity'] = time();
    
    return false;
}

// Verificar si la sesión ha expirado y cerrar si es necesario
if (isUserLoggedIn() && isSessionExpired()) {
    logoutUser();
}
