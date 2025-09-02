<?php
// Suppress PHP warnings for clean game interface
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', 0);

session_start();
require_once 'config/config.php';
require_once 'includes/functions.php';

// Check if player is logged in
if (!isset($_SESSION['player_id'])) {
    header('Location: login.php');
    exit;
}

$player_id = $_SESSION['player_id'];
$game = new Game($player_id);
$empire = $game->getEmpire();
$current_turn = $game->getCurrentTurn();

// Get recent events
$stmt = $pdo->prepare("
    SELECT * FROM events 
    WHERE player_id = ? 
    ORDER BY turn_occurred DESC, event_id DESC 
    LIMIT 20
");
$stmt->execute([$player_id]);
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Master of the Galaxy</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .reports-container {
            display: grid;
            gap: 20px;
            margin-top: 20px;
        }
        
        .report-section {
            background: var(--secondary-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
        }
        
        .report-section h3 {
            color: var(--text-accent);
            margin: 0 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .event-item {
            background: var(--primary-bg);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 10px;
        }
        
        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .event-type {
            color: var(--text-accent);
            font-weight: bold;
            text-transform: capitalize;
        }
        
        .event-turn {
            color: var(--text-secondary);
            font-size: 0.9em;
        }
        
        .event-message {
            color: var(--text-primary);
        }
        
        .importance-high {
            border-left: 4px solid var(--danger-color);
        }
        
        .importance-normal {
            border-left: 4px solid var(--info-color);
        }
        
        .importance-low {
            border-left: 4px solid var(--text-secondary);
        }
        
        .no-events {
            text-align: center;
            color: var(--text-secondary);
            font-style: italic;
            padding: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="game-header">
            <h1>Empire Reports</h1>
            <div class="empire-info">
                <span class="empire-name"><?php echo htmlspecialchars($empire['empire_name'] ?? 'Unknown Empire'); ?></span>
                <span class="race-name"><?php echo htmlspecialchars($empire['race_name'] ?? 'Unknown Race'); ?></span>
                <span class="turn-info">Turn <?php echo $current_turn; ?></span>
            </div>
        </header>

        <div class="back-btn" style="margin-bottom: 20px;">
            <a href="index.php" class="btn-primary">← Back to Overview</a>
        </div>

        <div class="reports-container">
            <div class="report-section">
                <h3>Recent Events</h3>
                <?php if (empty($events)): ?>
                    <div class="no-events">
                        No events recorded yet.<br>
                        <small>Events will appear here as your empire grows and develops.</small>
                    </div>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                        <div class="event-item importance-<?php echo $event['importance'] ?? 'normal'; ?>">
                            <div class="event-header">
                                <div class="event-type"><?php echo htmlspecialchars($event['event_type']); ?></div>
                                <div class="event-turn">Turn <?php echo $event['turn_occurred']; ?></div>
                            </div>
                            <div class="event-message"><?php echo htmlspecialchars($event['message']); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="report-section">
                <h3>Empire Statistics</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div>
                        <h4 style="color: var(--text-secondary); margin: 0 0 5px 0;">Total Colonies</h4>
                        <div style="font-size: 1.3em; font-weight: bold; color: var(--success-color);">
                            <?php 
                            $stmt = $pdo->prepare("
                                SELECT COUNT(*) FROM colonies c 
                                JOIN planets p ON c.planet_id = p.planet_id 
                                JOIN systems s ON p.system_id = s.system_id 
                                WHERE c.player_id = ? AND c.status = 'active'
                            ");
                            $stmt->execute([$player_id]);
                            echo $stmt->fetchColumn();
                            ?>
                        </div>
                    </div>
                    
                    <div>
                        <h4 style="color: var(--text-secondary); margin: 0 0 5px 0;">Total Population</h4>
                        <div style="font-size: 1.3em; font-weight: bold; color: var(--success-color);">
                            <?php 
                            $stmt = $pdo->prepare("
                                SELECT SUM(population) FROM colonies c 
                                JOIN planets p ON c.planet_id = p.planet_id 
                                JOIN systems s ON p.system_id = s.system_id 
                                WHERE c.player_id = ? AND c.status = 'active'
                            ");
                            $stmt->execute([$player_id]);
                            echo number_format($stmt->fetchColumn() ?? 0);
                            ?>
                        </div>
                    </div>
                    
                    <div>
                        <h4 style="color: var(--text-secondary); margin: 0 0 5px 0;">Credits</h4>
                        <div style="font-size: 1.3em; font-weight: bold; color: var(--warning-color);">
                            <?php echo number_format($empire['credits'] ?? 0); ?> BC
                        </div>
                    </div>
                    
                    <div>
                        <h4 style="color: var(--text-secondary); margin: 0 0 5px 0;">Research Points</h4>
                        <div style="font-size: 1.3em; font-weight: bold; color: var(--info-color);">
                            <?php echo number_format($empire['research_points'] ?? 0); ?> RP
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>