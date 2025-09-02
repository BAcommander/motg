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

// Get all colonies
$stmt = $pdo->prepare("
    SELECT c.*, p.name as planet_name, s.name as system_name, p.planet_type, p.planet_size
    FROM colonies c
    JOIN planets p ON c.planet_id = p.planet_id
    JOIN systems s ON p.system_id = s.system_id
    WHERE c.player_id = ? AND c.status = 'active'
    ORDER BY c.founded_turn ASC
");
$stmt->execute([$player_id]);
$colonies = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colonies - Master of the Galaxy</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .colony-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .colony-card {
            background: var(--secondary-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            transition: border-color 0.3s ease;
        }
        
        .colony-card:hover {
            border-color: var(--text-accent);
        }
        
        .colony-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .colony-name {
            color: var(--text-accent);
            font-size: 1.3em;
            font-weight: bold;
        }
        
        .colony-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .detail-group h4 {
            color: var(--text-secondary);
            margin: 0 0 8px 0;
            font-size: 0.9em;
            text-transform: uppercase;
        }
        
        .detail-value {
            font-size: 1.1em;
            font-weight: bold;
        }
        
        .population { color: var(--success-color); }
        .production { color: var(--warning-color); }
        .research { color: var(--info-color); }
        
        .back-btn {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="game-header">
            <h1>Colony Management</h1>
            <div class="empire-info">
                <span class="empire-name"><?php echo htmlspecialchars($empire['empire_name'] ?? 'Unknown Empire'); ?></span>
                <span class="race-name"><?php echo htmlspecialchars($empire['race_name'] ?? 'Unknown Race'); ?></span>
                <span class="turn-info">Turn <?php echo $current_turn; ?></span>
            </div>
        </header>

        <div class="back-btn">
            <a href="index.php" class="btn-primary">← Back to Overview</a>
        </div>

        <div class="colony-grid">
            <?php if (empty($colonies)): ?>
                <div class="colony-card">
                    <h3>No Colonies Found</h3>
                    <p>You don't have any colonies yet. Use colony ships to establish new settlements!</p>
                </div>
            <?php else: ?>
                <?php foreach ($colonies as $colony): ?>
                    <div class="colony-card">
                        <div class="colony-header">
                            <div class="colony-name"><?php echo htmlspecialchars($colony['name']); ?></div>
                            <div class="planet-type"><?php echo ucfirst($colony['planet_type']); ?> <?php echo ucfirst($colony['planet_size']); ?></div>
                        </div>
                        
                        <div class="colony-details">
                            <div class="detail-group">
                                <h4>Population</h4>
                                <div class="detail-value population">
                                    <?php echo number_format($colony['population'] ?? 0); ?>
                                </div>
                            </div>
                            
                            <div class="detail-group">
                                <h4>Location</h4>
                                <div class="detail-value">
                                    <?php echo htmlspecialchars($colony['system_name']); ?>
                                </div>
                            </div>
                            
                            <div class="detail-group">
                                <h4>Workers</h4>
                                <div class="detail-value">
                                    <div>Farmers: <?php echo number_format($colony['farmers'] ?? 0); ?></div>
                                    <div>Workers: <?php echo number_format($colony['workers'] ?? 0); ?></div>
                                    <div>Scientists: <?php echo number_format($colony['scientists'] ?? 0); ?></div>
                                </div>
                            </div>
                            
                            <div class="detail-group">
                                <h4>Founded</h4>
                                <div class="detail-value">
                                    Turn <?php echo $colony['founded_turn']; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>