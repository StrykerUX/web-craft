<?php
/**
 * WebCraft Academy - Instalador de Módulos y Lecciones
 * 
 * Este script carga los datos iniciales de módulos y lecciones en la base de datos.
 * Debe ejecutarse después de crear la estructura de la base de datos con db_init.sql.
 */

// Definir constante para permitir acceso a los archivos de configuración
define('WEBCRAFT', true);

// Incluir archivo de configuración
require_once '../config.php';

try {
    // Conectar a la base de datos
    $db = getDbConnection();
    
    echo "Conexión a la base de datos establecida correctamente.\n";
    
    // Vaciar tablas existentes para evitar duplicados
    $db->exec("TRUNCATE TABLE lessons");
    $db->exec("TRUNCATE TABLE modules");
    
    echo "Tablas de módulos y lecciones limpiadas correctamente.\n";
    
    // Cargar el archivo SQL con los módulos y lecciones
    $modulesSQL = file_get_contents('seed_modules.sql');
    
    // Ejecutar las consultas SQL
    $db->exec($modulesSQL);
    
    echo "Módulos y lecciones de HTML cargados correctamente.\n";
    
    // Cargar las actualizaciones para las lecciones de CSS si existe el archivo
    if (file_exists('seed_css_modules_continued.sql')) {
        $cssUpdateSQL = file_get_contents('seed_css_modules_continued.sql');
        $db->exec($cssUpdateSQL);
        echo "Actualizaciones para el módulo de CSS cargadas correctamente.\n";
    }
    
    // Verificar que todo se haya cargado correctamente
    $moduleCount = $db->query("SELECT COUNT(*) FROM modules")->fetchColumn();
    $lessonCount = $db->query("SELECT COUNT(*) FROM lessons")->fetchColumn();
    
    echo "Se han cargado $moduleCount módulos y $lessonCount lecciones en total.\n";
    
    echo "¡Instalación de contenido educativo completada con éxito!\n";
    
} catch (PDOException $e) {
    die("Error al conectar a la base de datos o ejecutar consultas: " . $e->getMessage());
}
