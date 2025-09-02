<?php
// Suppress deprecation warnings for clean JSON output
error_reporting(E_ALL & ~E_DEPRECATED);

header('Content-Type: application/json');
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['player_id'])) {
    echo json_encode(['success' => false, 'error' => 'No active session']);
    exit;
}

try {
    $player_id = $_SESSION['player_id'];
    
    // Get player empire data
    $stmt = $pdo->prepare("
        SELECT p.*, r.race_name, r.traits, r.bonuses, g.current_turn, g.game_name
        FROM players p 
        JOIN races r ON p.race_id = r.race_id 
        JOIN games g ON p.game_id = g.game_id
        WHERE p.player_id = ?
    ");
    $stmt->execute([$player_id]);
    $empire = $stmt->fetch();
    
    if (!$empire) {
        echo json_encode(['success' => false, 'error' => 'Player not found']);
        exit;
    }
    
    // Get colonies
    $stmt = $pdo->prepare("
        SELECT c.*, p.name as planet_name, s.name as system_name
        FROM colonies c
        JOIN planets p ON c.planet_id = p.planet_id
        JOIN systems s ON p.system_id = s.system_id
        WHERE c.player_id = ? AND c.status = 'active'
        ORDER BY c.founded_turn ASC
    ");
    $stmt->execute([$player_id]);
    $colonies = $stmt->fetchAll();
    
    // Get current research
    $stmt = $pdo->prepare("
        SELECT r.*, t.tech_name, t.description, t.cost
        FROM research_queue r
        JOIN technologies t ON r.tech_id = t.tech_id
        WHERE r.player_id = ? AND r.status = 'researching'
        ORDER BY r.priority ASC
        LIMIT 1
    ");
    $stmt->execute([$player_id]);
    $current_research = $stmt->fetch();
    
    // Get recent events
    $stmt = $pdo->prepare("
        SELECT * FROM events 
        WHERE player_id = ? 
        ORDER BY turn_occurred DESC, event_id DESC 
        LIMIT 5
    ");
    $stmt->execute([$player_id]);
    $events = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'empire' => $empire,
        'colonies' => $colonies,
        'current_research' => $current_research,
        'recent_events' => $events,
        'current_turn' => $empire['current_turn']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>