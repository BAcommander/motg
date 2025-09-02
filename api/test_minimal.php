<?php
// Ultra-aggressive error suppression for clean JSON output
error_reporting(0);
ini_set('display_errors', 'Off');
ini_set('display_startup_errors', 'Off');
ini_set('log_errors', 'Off');
ini_set('html_errors', 'Off');

// Start output buffering to catch any stray output
ob_start();

// Additional safety - turn off all possible error output
@ini_set('display_errors', 0);
@ini_set('display_startup_errors', 0);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

try {
    $game_name = $input['game_name'] ?? 'Test Game';
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'game_id' => '999',
        'message' => 'Minimal test successful',
        'received_data' => $input
    ]);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>