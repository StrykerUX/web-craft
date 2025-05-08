<?php
/**
 * WebCraft Academy - Verificación y Preparación de la Base de Datos
 * 
 * Este script verifica que todas las tablas necesarias existan en la base de datos
 * y las crea si es necesario. Es útil para la primera ejecución o actualizaciones.
 */

// Definir constante para permitir acceso a los archivos de configuración
if (!defined('WEBCRAFT')) {
    define('WEBCRAFT', true);
}

// Incluir archivo de configuración global si no está ya incluido
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../config.php';
}

// Verificar y crear tablas necesarias
function checkAndCreateTables() {
    try {
        $db = getDbConnection();
        
        // Lista de tablas esperadas
        $expectedTables = [
            'users',
            'user_profiles',
            'login_attempts',
            'remember_tokens',
            'password_resets',
            'modules',
            'lessons',
            'progress',
            'achievements',
            'user_achievements',
            'projects',
            'challenges',
            'challenge_attempts',
            'project_comments',
            'leaderboard',
            'forum_topics',
            'forum_replies'
        ];
        
        // Obtener lista de tablas existentes
        $stmt = $db->query("SHOW TABLES");
        $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Tablas faltantes
        $missingTables = array_diff($expectedTables, $existingTables);
        
        // Crear tablas faltantes
        if (count($missingTables) > 0) {
            // Comenzar transacción
            $db->beginTransaction();
            
            try {
                foreach ($missingTables as $table) {
                    switch ($table) {
                        case 'users':
                            $db->exec("
                                CREATE TABLE IF NOT EXISTS users (
                                    user_id INT AUTO_INCREMENT PRIMARY KEY,
                                    username VARCHAR(50) NOT NULL UNIQUE,
                                    email VARCHAR(100) NOT NULL UNIQUE,
                                    password VARCHAR(255) NOT NULL,
                                    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                                    last_login DATETIME,
                                    role ENUM('student', 'teacher', 'admin') DEFAULT 'student',
                                    is_active BOOLEAN DEFAULT TRUE
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                            ");
                            break;
                            
                        case 'user_profiles':
                            $db->exec("
                                CREATE TABLE IF NOT EXISTS user_profiles (
                                    profile_id INT AUTO_INCREMENT PRIMARY KEY,
                                    user_id INT NOT NULL,
                                    full_name VARCHAR(100),
                                    avatar VARCHAR(255) DEFAULT 'default.png',
                                    bio TEXT,
                                    level ENUM('Principiante', 'Novato', 'Aprendiz', 'Desarrollador', 'Maestro') DEFAULT 'Principiante',
                                    xp_points INT DEFAULT 0,
                                    theme_preference ENUM('light', 'dark', 'system') DEFAULT 'system',
                                    accessibility_settings TEXT,
                                    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                            ");
                            break;
                            
                        case 'login_attempts':
                            $db->exec("
                                CREATE TABLE IF NOT EXISTS login_attempts (
                                    user_id INT PRIMARY KEY,
                                    failed_attempts INT DEFAULT 0,
                                    last_failed_attempt INT,
                                    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                            ");
                            break;
                            
                        case 'remember_tokens':
                            $db->exec("
                                CREATE TABLE IF NOT EXISTS remember_tokens (
                                    id INT AUTO_INCREMENT PRIMARY KEY,
                                    user_id INT NOT NULL,
                                    selector VARCHAR(255) NOT NULL,
                                    token VARCHAR(255) NOT NULL,
                                    expires DATETIME NOT NULL,
                                    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                            ");
                            break;
                            
                        case 'password_resets':
                            $db->exec("
                                CREATE TABLE IF NOT EXISTS password_resets (
                                    id INT AUTO_INCREMENT PRIMARY KEY,
                                    user_id INT NOT NULL,
                                    token VARCHAR(255) NOT NULL,
                                    expire_time INT NOT NULL,
                                    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                            ");
                            break;
                            
                        // Añadir casos para otras tablas según sea necesario
                        
                        default:
                            // Para tablas que no necesitan estructura específica ahora mismo
                            if (DEV_MODE) {
                                error_log("Tabla {$table} no implementada en checkAndCreateTables()");
                            }
                            break;
                    }
                }
                
                // Confirmar transacción
                $db->commit();
                return true;
                
            } catch (PDOException $e) {
                // Revertir en caso de error
                $db->rollBack();
                if (DEV_MODE) {
                    error_log("Error al crear tablas: " . $e->getMessage());
                }
                return false;
            }
        }
        
        return true;
    } catch (PDOException $e) {
        if (DEV_MODE) {
            error_log("Error en checkAndCreateTables: " . $e->getMessage());
        }
        return false;
    }
}

// Ejecutar verificación solo si se llama directamente
if (basename($_SERVER['SCRIPT_NAME']) === 'db_check.php') {
    $result = checkAndCreateTables();
    echo ($result ? "Base de datos verificada y actualizada correctamente." : "Error al verificar la base de datos.");
}
