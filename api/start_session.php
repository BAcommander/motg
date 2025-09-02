<?php
// Suppress deprecation warnings for clean JSON output
error_reporting(E_ALL & ~E_DEPRECATED);

session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

try {
    $player_id = $input['player_id'] ?? null;
    
    if (!$player_id) {
        throw new Exception('Player ID is required');
    }
    
    // Set session variable
    $_SESSION['player_id'] = $player_id;
    
    echo json_encode([
        'success' => true,
        'message' => 'Session started successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>