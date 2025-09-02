<?php
// Suppress deprecation warnings for clean JSON output
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Start output buffering to catch any stray output
ob_start();

header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

try {
    $game_id = $input['game_id'] ?? null;
    $empire_name = $input['empire_name'] ?? 'Empire';
    $leader_name = $input['leader_name'] ?? 'Leader';
    $race_id = $input['race_id'] ?? 'humans';
    
    if (!$game_id) {
        throw new Exception('Game ID is required');
    }
    
    // Check if game exists
    $stmt = $pdo->prepare("SELECT * FROM games WHERE game_id = ?");
    $stmt->execute([$game_id]);
    $game = $stmt->fetch();
    
    if (!$game) {
        throw new Exception('Game not found');
    }
    
    // Get race data
    $races = RACES;
    if (!isset($races[$race_id])) {
        throw new Exception('Invalid race selected');
    }
    
    $race_data = $races[$race_id];
    
    // Insert race if it doesn't exist
    $stmt = $pdo->prepare("SELECT race_id FROM races WHERE race_name = ?");
    $stmt->execute([$race_data['name']]);
    $existing_race = $stmt->fetch();
    
    if (!$existing_race) {
        $stmt = $pdo->prepare("
            INSERT INTO races (race_name, description, traits, bonuses, government_type) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $race_data['name'],
            'Playable race',
            json_encode($race_data['traits']),
            json_encode($race_data['bonuses']),
            $race_data['government']
        ]);
        $db_race_id = $pdo->lastInsertId();
    } else {
        $db_race_id = $existing_race['race_id'];
    }
    
    // Create player
    $stmt = $pdo->prepare("
        INSERT INTO players (game_id, empire_name, leader_name, race_id, credits, research_points, government_type, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
    ");
    $stmt->execute([
        $game_id,
        $empire_name,
        $leader_name,
        $db_race_id,
        1000, // Starting credits
        0,    // Starting research points
        $race_data['government']
    ]);
    
    $player_id = $pdo->lastInsertId();
    
    // Create starting colony
    $homeworld = createStartingColony($player_id, $game_id);
    
    // Clear any buffered output before JSON
    ob_clean();
    
    echo json_encode([
        'success' => true,
        'player_id' => $player_id,
        'homeworld' => $homeworld['name'],
        'message' => 'Player created successfully'
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