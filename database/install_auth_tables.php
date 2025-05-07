<?php
/**
 * Script de instalación de tablas de autenticación para WebCraft Academy
 * 
 * Este script crea las tablas adicionales necesarias para el sistema de autenticación.
 */

// Definir constante para permitir acceso a los archivos de configuración
define('WEBCRAFT', true);

// Incluir archivo de configuración
require_once '../config.php';

// Función para ejecutar un archivo SQL
function executeSQL($file) {
    try {
        $pdo = getDbConnection();
        
        // Leer el archivo
        $sql = file_get_contents($file);
        
        // Dividir las consultas (separadas por ;)
        $queries = explode(';', $sql);
        
        // Contador de consultas ejecutadas
        $executed = 0;
        
        // Ejecutar cada consulta
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $pdo->exec($query);
                $executed++;
                echo "Consulta ejecutada exitosamente.<br>";
            }
        }
        
        return [
            'success' => true,
            'message' => "Archivo SQL ejecutado exitosamente. Total de consultas: $executed",
            'queries_executed' => $executed
        ];
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error al ejecutar el archivo SQL: ' . $e->getMessage()
        ];
    }
}

// Verificar si estamos en modo de desarrollo
if (!DEV_MODE) {
    die('Este script solo puede ejecutarse en modo de desarrollo.');
}

// Ejecutar el archivo SQL de autenticación
$result = executeSQL('auth_tables.sql');

// Mostrar resultado
if ($result['success']) {
    echo "<h1>Instalación completada</h1>";
    echo "<p>{$result['message']}</p>";
    echo "<p>Las tablas para el sistema de autenticación han sido creadas correctamente.</p>";
} else {
    echo "<h1>Error en la instalación</h1>";
    echo "<p>{$result['message']}</p>";
}

// Enlace para volver
echo '<p><a href="../index.php">Volver al inicio</a></p>';
