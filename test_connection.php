<?php
/**
 * Test Connection File
 * Access: http://localhost:8080/vaxforsure/test_connection.php
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'Backend is working!',
    'timestamp' => date('Y-m-d H:i:s'),
    'server' => $_SERVER['SERVER_NAME'] ?? 'unknown',
    'port' => $_SERVER['SERVER_PORT'] ?? 'unknown'
]);

?>

