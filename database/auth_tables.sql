-- Tablas adicionales para el sistema de autenticación de WebCraft Academy

-- Tabla para almacenar tokens "recordarme"
CREATE TABLE IF NOT EXISTS user_tokens (
    token_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    selector VARCHAR(64) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY (selector)
);

-- Tabla para almacenar intentos de inicio de sesión fallidos
CREATE TABLE IF NOT EXISTS login_attempts (
    attempt_id INT AUTO_INCREMENT PRIMARY KEY,
    username_or_email VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempt_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (username_or_email),
    INDEX (ip_address)
);

-- Tabla para almacenar tokens de restablecimiento de contraseña
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    token_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    selector VARCHAR(64) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY (selector)
);

-- Tabla para almacenar códigos de activación de cuenta
CREATE TABLE IF NOT EXISTS activation_codes (
    code_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(255) NOT NULL,
    expires DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY (code)
);
