<?php
/**
 * Script de instalación de la base de datos para WebCraft Academy
 * 
 * Este script se ejecuta una sola vez para inicializar la base de datos
 * con la estructura necesaria para el funcionamiento de la plataforma.
 */

// Definir constante para permitir acceso al archivo de configuración
define('WEBCRAFT', true);

// Incluir archivo de configuración
require_once '../config.php';

// Verificación de seguridad (comentar después de usar en desarrollo)
if (!isset($_GET['install']) || $_GET['install'] !== 'confirm') {
    die('Para ejecutar la instalación, añade ?install=confirm a la URL. Esta es una medida de seguridad.');
}

// Mostrar errores en desarrollo
if (DEV_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

try {
    // Establecer conexión a la base de datos
    $pdo = getDbConnection();
    
    echo "<h1>Instalación de Base de Datos WebCraft Academy</h1>";
    echo "<p>Conectado correctamente a la base de datos.</p>";
    
    // Leer el archivo SQL
    $sqlFile = file_get_contents('db_init.sql');
    
    if (!$sqlFile) {
        throw new Exception("No se pudo leer el archivo SQL de inicialización.");
    }
    
    echo "<p>Archivo SQL leído correctamente.</p>";
    
    // Dividir las consultas SQL
    $queries = explode(';', $sqlFile);
    
    echo "<p>Iniciando ejecución de consultas SQL...</p>";
    echo "<ul>";
    
    // Ejecutar cada consulta
    foreach ($queries as $query) {
        $query = trim($query);
        
        // Omitir consultas vacías o comentarios
        if (empty($query) || strpos($query, '--') === 0) {
            continue;
        }
        
        // Ejecutar la consulta
        $pdo->exec($query);
        
        // Mostrar información sobre la consulta (versión simplificada para no mostrar datos sensibles)
        $queryPreview = strlen($query) > 100 ? substr($query, 0, 97) . '...' : $query;
        echo "<li>Ejecutado: " . htmlspecialchars($queryPreview) . "</li>";
    }
    
    echo "</ul>";
    echo "<p><strong>¡Instalación completada con éxito!</strong></p>";
    echo "<p>La base de datos ha sido inicializada correctamente con todas las tablas y datos iniciales.</p>";
    echo "<p><a href='../index.php'>Volver a la página principal</a></p>";

} catch (PDOException $e) {
    die("<h1>Error en la base de datos</h1><p>Error: " . $e->getMessage() . "</p>");
} catch (Exception $e) {
    die("<h1>Error en la instalación</h1><p>Error: " . $e->getMessage() . "</p>");
}
