<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing game creation...\n";

try {
    require_once 'config/config.php';
    echo "Config loaded successfully\n";
    
    require_once 'includes/functions.php';
    echo "Functions loaded successfully\n";
    
    // Test database connection
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM games");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "Current games in database: " . $result['count'] . "\n";
    
    // Test basic game creation
    $game_name = 'Test Galaxy';
    $galaxy_size = 'medium';
    $difficulty = 'normal';
    $max_players = 8;
    
    echo "Attempting to create game...\n";
    
    $stmt = $pdo->prepare("
        INSERT INTO games (game_name, galaxy_size, difficulty, max_players, current_turn, game_status) 
        VALUES (?, ?, ?, ?, 1, 'setup')
    ");
    $stmt->execute([$game_name, $galaxy_size, $difficulty, $max_players]);
    
    $game_id = $pdo->lastInsertId();
    echo "Game created with ID: $game_id\n";
    
    // Test galaxy generation
    echo "Testing galaxy generation...\n";
    generateGalaxy($game_id, $galaxy_size, $max_players);
    echo "Galaxy generation completed\n";
    
    echo "Test completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
?>