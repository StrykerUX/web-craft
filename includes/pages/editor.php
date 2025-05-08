<?php
/**
 * Editor de código interactivo para WebCraft Academy
 * 
 * Esta página proporciona un editor de código en vivo con CodeMirror, 
 * vista previa en tiempo real y capacidad para guardar proyectos.
 */

// Verificar acceso directo
if (!defined('WEBCRAFT')) {
    die('Acceso directo no permitido');
}

// Obtener información del proyecto si se está editando uno existente
$projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
$projectData = null;

if ($projectId > 0 && isUserLoggedIn()) {
    // Obtener datos del proyecto desde la base de datos
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT * FROM projects WHERE project_id = ? AND user_id = ?");
    $stmt->execute([$projectId, $_SESSION['user_id']]);
    $projectData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Valores predeterminados para editor vacío
$htmlContent = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Proyecto</title>
    <style>
        /* Estilos CSS aquí */
    </style>
</head>
<body>
    <!-- Contenido HTML aquí -->
    <h1>¡Bienvenido a WebCraft!</h1>
    <p>Comienza a editar este código para crear tu proyecto.</p>
    
</body>
</html>';

$cssContent = '/* Agrega tus estilos CSS aquí */
body {
    font-family: Arial, sans-serif;