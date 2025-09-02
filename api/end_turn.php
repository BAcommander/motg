<?php
// Suppress deprecation warnings for clean JSON output
error_reporting(E_ALL & ~E_DEPRECATED);

header('Content-Type: application/json');
session_start();
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['player_id'])) {
    echo json_encode(['success' => false, 'error' => 'No active session']);
    exit;
}

try {
    $player_id = $_SESSION['player_id'];
    
    // Get player's game
    $stmt = $pdo->prepare("
        SELECT game_id FROM players WHERE player_id = ?
    ");
    $stmt->execute([$player_id]);
    $player = $stmt->fetch();
    
    if (!$player) {
        throw new Exception('Player not found');
    }
    
    $game_id = $player['game_id'];
    
    // Mark player as ready for next turn
    $stmt = $pdo->prepare("
        UPDATE players 
        SET turn_ready = 1, last_action_time = datetime('now') 
        WHERE player_id = ?
    ");
    $stmt->execute([$player_id]);
    
    // Check if all players are ready
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as ready_count,
               (SELECT COUNT(*) FROM players WHERE game_id = ? AND status = 'active') as total_count
        FROM players 
        WHERE game_id = ? AND status = 'active' AND turn_ready = 1
    ");
    $stmt->execute([$game_id, $game_id]);
    $counts = $stmt->fetch();
    
    $turn_processed = false;
    
    if ($counts['ready_count'] >= $counts['total_count']) {
        // All players ready - advance turn
        $stmt = $pdo->prepare("
            UPDATE games SET current_turn = current_turn + 1 WHERE game_id = ?
        ");
        $stmt->execute([$game_id]);
        
        // Reset player turn flags
        $stmt = $pdo->prepare("
            UPDATE players SET turn_ready = 0 WHERE game_id = ?
        ");
        $stmt->execute([$game_id]);
        
        $turn_processed = true;
    }
    
    echo json_encode([
        'success' => true,
        'turn_processed' => $turn_processed,
        'ready_players' => $counts['ready_count'],
        'total_players' => $counts['total_count']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>