<?php
/**
 * Archivo de depuración para problemas de actualización de progreso
 */

// Habilitar visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Registrar la solicitud para depuración
$requestData = file_get_contents('php://input');
error_log('Solicitud recibida en debug_progress.php: ' . $requestData);

// Responder con un mensaje de éxito para pruebas
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Depuración exitosa. Datos recibidos: ' . $requestData,
    'xp' => 50 // Valor de prueba
]);
