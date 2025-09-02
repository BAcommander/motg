<?php
// Ultra-aggressive error suppression for clean JSON output
error_reporting(0);
ini_set('display_errors', 'Off');
ini_set('display_startup_errors', 'Off');
ini_set('log_errors', 'Off');
ini_set('html_errors', 'Off');

// Start output buffering to catch any stray output
ob_start();

header('Content-Type: application/json');

try {
    require_once '../config/config.php';
    require_once '../includes/functions.php';
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Functions loaded successfully',
        'function_exists' => function_exists('generateGalaxy')
    ]);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>