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

// Get current research
$stmt = $pdo->prepare("
    SELECT r.*, t.tech_name, t.description, t.cost, t.category
    FROM research_queue r
    JOIN technologies t ON r.tech_id = t.tech_id
    WHERE r.player_id = ? AND r.status = 'researching'
    ORDER BY r.priority ASC
");
$stmt->execute([$player_id]);
$current_research = $stmt->fetchAll();

// Get completed technologies
$stmt = $pdo->prepare("
    SELECT pt.*, t.tech_name, t.description, t.category
    FROM player_technologies pt
    JOIN technologies t ON pt.tech_id = t.tech_id
    WHERE pt.player_id = ?
    ORDER BY pt.researched_turn DESC
");
$stmt->execute([$player_id]);
$completed_tech = $stmt->fetchAll();

// Get available technologies
$available_tech = getAvailableTechnologies($player_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research - Master of the Galaxy</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .research-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }
        
        .research-section {
            background: var(--secondary-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
        }
        
        .research-section h3 {
            color: var(--text-accent);
            margin: 0 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .tech-item {
            background: var(--primary-bg);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 10px;
        }
        
        .tech-name {
            color: var(--text-accent);
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .tech-category {
            color: var(--text-secondary);
            font-size: 0.9em;
            text-transform: uppercase;
        }
        
        .tech-cost {
            color: var(--warning-color);
            font-weight: bold;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: var(--primary-bg);
            border-radius: 4px;
            overflow: hidden;
            margin: 5px 0;
        }
        
        .progress-fill {
            height: 100%;
            background: var(--success-color);
            transition: width 0.3s ease;
        }
        
        .no-research {
            text-align: center;
            color: var(--text-secondary);
            font-style: italic;
            padding: 30px;
        }
        
        .back-btn {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="game-header">
            <h1>Research & Development</h1>
            <div class="empire-info">
                <span class="empire-name"><?php echo htmlspecialchars($empire['empire_name'] ?? 'Unknown Empire'); ?></span>
                <span class="race-name"><?php echo htmlspecialchars($empire['race_name'] ?? 'Unknown Race'); ?></span>
                <span class="turn-info">Turn <?php echo $current_turn; ?></span>
            </div>
            <div class="resources">
                <span class="research">Research Points: <?php echo number_format($empire['research_points'] ?? 0); ?></span>
            </div>
        </header>

        <div class="back-btn">
            <a href="index.php" class="btn-primary">← Back to Overview</a>
        </div>

        <div class="research-sections">
            <div class="research-section">
                <h3>Current Research</h3>
                <?php if (empty($current_research)): ?>
                    <div class="no-research">
                        No active research projects.<br>
                        <small>Select a technology to research from the available list.</small>
                    </div>
                <?php else: ?>
                    <?php foreach ($current_research as $research): ?>
                        <div class="tech-item">
                            <div class="tech-name"><?php echo htmlspecialchars($research['tech_name']); ?></div>
                            <div class="tech-category"><?php echo htmlspecialchars($research['category']); ?></div>
                            <div class="tech-cost">Cost: <?php echo number_format($research['cost']); ?> RP</div>
                            <div class="progress-bar">
                                <?php 
                                $progress = min(100, ($research['invested_points'] / max(1, $research['cost'])) * 100);
                                ?>
                                <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                            </div>
                            <small><?php echo number_format($research['invested_points'] ?? 0); ?> / <?php echo number_format($research['cost']); ?> RP</small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="research-section">
                <h3>Available Technologies</h3>
                <?php if (empty($available_tech)): ?>
                    <div class="no-research">
                        No new technologies available for research.<br>
                        <small>Complete current research to unlock more options.</small>
                    </div>
                <?php else: ?>
                    <?php foreach (array_slice($available_tech, 0, 5) as $tech): ?>
                        <div class="tech-item">
                            <div class="tech-name"><?php echo htmlspecialchars($tech['tech_name']); ?></div>
                            <div class="tech-category"><?php echo htmlspecialchars($tech['category']); ?></div>
                            <div class="tech-cost">Cost: <?php echo number_format($tech['cost']); ?> RP</div>
                            <?php if (!empty($tech['description'])): ?>
                                <p><small><?php echo htmlspecialchars($tech['description']); ?></small></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <?php if (count($available_tech) > 5): ?>
                        <div class="no-research">
                            <small>... and <?php echo count($available_tech) - 5; ?> more technologies</small>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($completed_tech)): ?>
            <div class="research-section" style="grid-column: 1 / -1; margin-top: 20px;">
                <h3>Completed Research</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 10px;">
                    <?php foreach (array_slice($completed_tech, 0, 6) as $tech): ?>
                        <div class="tech-item">
                            <div class="tech-name"><?php echo htmlspecialchars($tech['tech_name']); ?></div>
                            <div class="tech-category"><?php echo htmlspecialchars($tech['category']); ?></div>
                            <small>Completed: Turn <?php echo $tech['researched_turn']; ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>