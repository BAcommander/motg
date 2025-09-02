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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

try {
    require_once '../config/config.php';
    require_once '../includes/functions.php';
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $game_name = $input['game_name'] ?? 'Test Game';
    $galaxy_size = $input['galaxy_size'] ?? 'medium';
    $difficulty = $input['difficulty'] ?? 'normal';
    $max_players = 8;
    
    // Test database insertion without galaxy generation
    $stmt = $pdo->prepare("
        INSERT INTO games (game_name, galaxy_size, difficulty, max_players, current_turn, game_status) 
        VALUES (?, ?, ?, ?, 1, 'setup')
    ");
    $stmt->execute([$game_name, $galaxy_size, $difficulty, $max_players]);
    
    $game_id = $pdo->lastInsertId();
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'game_id' => $game_id,
        'message' => 'Database operations successful (without galaxy generation)',
        'test' => 'database_only'
    ]);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>