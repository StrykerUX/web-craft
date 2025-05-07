-- Script de inicialización de Base de Datos para WebCraft Academy
-- Creado para: u171418069_webcraft

-- Eliminación de tablas si existen (para reinstalación limpia)
DROP TABLE IF EXISTS user_achievements;
DROP TABLE IF EXISTS user_progress;
DROP TABLE IF EXISTS lesson_submissions;
DROP TABLE IF EXISTS challenge_submissions;
DROP TABLE IF EXISTS challenges;
DROP TABLE IF EXISTS lessons;
DROP TABLE IF EXISTS modules;
DROP TABLE IF EXISTS achievements;
DROP TABLE IF EXISTS user_preferences;
DROP TABLE IF EXISTS users;

-- Tabla de Usuarios
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(100),
    bio TEXT,
    profile_image VARCHAR(255),
    developer_level ENUM('Principiante', 'Novato', 'Aprendiz', 'Desarrollador', 'Maestro') DEFAULT 'Principiante',
    experience_points INT DEFAULT 0,
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME,
    account_status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    role ENUM('student', 'mentor', 'admin') DEFAULT 'student',
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de preferencias de usuario
CREATE TABLE user_preferences (
    preference_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    theme_preference ENUM('light', 'dark', 'system') DEFAULT 'system',
    difficulty_preference ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    editor_font_size INT DEFAULT 14,
    notifications_enabled BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Módulos
CREATE TABLE modules (
    module_id INT AUTO_INCREMENT PRIMARY KEY,
    module_name VARCHAR(100) NOT NULL,
    module_description TEXT,
    module_order INT NOT NULL,
    icon_class VARCHAR(50),
    required_points INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_module_order (module_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Lecciones
CREATE TABLE lessons (
    lesson_id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT NOT NULL,
    lesson_title VARCHAR(255) NOT NULL,
    lesson_description TEXT,
    lesson_content LONGTEXT,
    lesson_order INT NOT NULL,
    estimated_time_minutes INT,
    xp_reward INT DEFAULT 10,
    required_lesson_id INT DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES modules(module_id) ON DELETE CASCADE,
    FOREIGN KEY (required_lesson_id) REFERENCES lessons(lesson_id) ON DELETE SET NULL,
    INDEX idx_module_lesson (module_id, lesson_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Logros/Insignias
CREATE TABLE achievements (
    achievement_id INT AUTO_INCREMENT PRIMARY KEY,
    achievement_name VARCHAR(100) NOT NULL,
    achievement_description TEXT,
    achievement_icon VARCHAR(255),
    achievement_type ENUM('skill', 'progress', 'challenge', 'social') NOT NULL,
    xp_reward INT DEFAULT 50,
    is_secret BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Logros de Usuarios
CREATE TABLE user_achievements (
    user_achievement_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    achievement_id INT NOT NULL,
    achieved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (achievement_id) REFERENCES achievements(achievement_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_achievement (user_id, achievement_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Progreso del Usuario
CREATE TABLE user_progress (
    progress_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    completion_percentage INT DEFAULT 0,
    started_at DATETIME,
    completed_at DATETIME,
    xp_earned INT DEFAULT 0,
    last_position VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(lesson_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_lesson (user_id, lesson_id),
    INDEX idx_user_progress (user_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Desafíos
CREATE TABLE challenges (
    challenge_id INT AUTO_INCREMENT PRIMARY KEY,
    challenge_title VARCHAR(255) NOT NULL,
    challenge_description TEXT,
    challenge_type ENUM('daily', 'bug_hunting', 'project', 'competition') NOT NULL,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'beginner',
    related_module_id INT,
    starting_code LONGTEXT,
    solution_code LONGTEXT,
    validation_criteria TEXT,
    time_limit_minutes INT DEFAULT NULL,
    xp_reward INT DEFAULT 25,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME DEFAULT NULL,
    FOREIGN KEY (related_module_id) REFERENCES modules(module_id) ON DELETE SET NULL,
    INDEX idx_challenge_type (challenge_type, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Envíos de Desafíos
CREATE TABLE challenge_submissions (
    submission_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    challenge_id INT NOT NULL,
    submitted_code LONGTEXT,
    is_correct BOOLEAN DEFAULT FALSE,
    feedback TEXT,
    execution_time_seconds INT,
    score INT DEFAULT 0,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (challenge_id) REFERENCES challenges(challenge_id) ON DELETE CASCADE,
    INDEX idx_challenge_submissions (challenge_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Envíos de Lecciones
CREATE TABLE lesson_submissions (
    submission_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    submitted_code LONGTEXT,
    meeting_requirements BOOLEAN DEFAULT FALSE,
    feedback TEXT,
    points_earned INT DEFAULT 0,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(lesson_id) ON DELETE CASCADE,
    INDEX idx_lesson_submissions (lesson_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar los módulos principales según la documentación del proyecto
INSERT INTO modules (module_name, module_description, module_order, icon_class, is_active) VALUES 
('Fundamentos HTML', 'Introducción a etiquetas, estructura básica de documentos, anatomía de una página web, elementos semánticos y formularios.', 1, 'fa-html5', TRUE),
('Estilización con CSS', 'Selectores y especificidad, Box Model y layout, Flexbox y Grid, Responsive Design, Animaciones y transiciones, Variables CSS.', 2, 'fa-css3-alt', TRUE),
('Interactividad con JavaScript', 'Variables y tipos de datos, Funciones y eventos, Manipulación del DOM, Validación de formularios, Local Storage, Fetch API.', 3, 'fa-js', TRUE),
('Mejoras con jQuery', 'Selectores simplificados, Manipulación del DOM, Eventos y animaciones, AJAX para carga dinámica.', 4, 'fa-code', TRUE),
('Animaciones con GSAP', 'Timelines y tweens, Efectos avanzados, ScrollTrigger, Interacciones complejas.', 5, 'fa-magic', TRUE),
('Backend con PHP', 'Sintaxis básica, Variables y estructuras de control, Procesamiento de formularios, Conexión con bases de datos, Sesiones y cookies.', 6, 'fa-php', TRUE);

-- Insertar algunos logros básicos
INSERT INTO achievements (achievement_name, achievement_description, achievement_icon, achievement_type, xp_reward) VALUES
('Primer Paso', 'Completaste tu primera lección', 'badge-first-step.png', 'progress', 25),
('Explorador HTML', 'Completaste 5 lecciones de HTML', 'badge-html-explorer.png', 'skill', 50),
('Maestro CSS', 'Completaste todas las lecciones del módulo CSS', 'badge-css-master.png', 'skill', 100),
('Debugger', 'Encontraste y arreglaste 10 bugs en los desafíos', 'badge-debugger.png', 'challenge', 75),
('Creador Web', 'Completaste tu primer proyecto web completo', 'badge-web-creator.png', 'progress', 150),
('Socializador', 'Ayudaste a 5 usuarios en el foro', 'badge-socializer.png', 'social', 50),
('Incansable', 'Completaste ejercicios durante 7 días consecutivos', 'badge-tireless.png', 'progress', 100);

-- Crear un usuario administrador predeterminado (contraseña: admin123)
INSERT INTO users (username, email, password_hash, display_name, developer_level, experience_points, role) 
VALUES ('admin', 'admin@webcraft.com', '$2y$10$uJYO6tDVUrDxHr5wA0W23.N.AzHxkqR7j3qw12GU0QIV1r8OHPzWa', 'Administrador', 'Maestro', 1000, 'admin');
