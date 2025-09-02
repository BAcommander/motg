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
require_once '../config/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

try {
    $game_name = $input['game_name'] ?? 'New Galaxy';
    $galaxy_size = $input['galaxy_size'] ?? 'medium';
    $difficulty = $input['difficulty'] ?? 'normal';
    $max_players = 8; // Default max players
    
    // Validate inputs
    $valid_sizes = ['small', 'medium', 'large', 'huge'];
    $valid_difficulties = ['easy', 'normal', 'hard', 'impossible'];
    
    if (!in_array($galaxy_size, $valid_sizes)) {
        throw new Exception('Invalid galaxy size');
    }
    
    if (!in_array($difficulty, $valid_difficulties)) {
        throw new Exception('Invalid difficulty level');
    }
    
    // Create new game
    $stmt = $pdo->prepare("
        INSERT INTO games (game_name, galaxy_size, difficulty, max_players, current_turn, game_status) 
        VALUES (?, ?, ?, ?, 1, 'setup')
    ");
    $stmt->execute([$game_name, $galaxy_size, $difficulty, $max_players]);
    
    $game_id = $pdo->lastInsertId();
    
    // Generate galaxy (systems and planets)
    generateGalaxy($game_id, $galaxy_size, $max_players);
    
    // Clear any buffered output before JSON
    ob_clean();
    
    echo json_encode([
        'success' => true,
        'game_id' => $game_id,
        'message' => 'Game created successfully',
        'debug_timestamp' => date('Y-m-d H:i:s'),
        'debug_version' => 'v2.1'
    ]);
    
} catch (Exception $e) {
    // Clear any buffered output before JSON
    ob_clean();
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>