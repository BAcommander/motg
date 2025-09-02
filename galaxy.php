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

// Get all star systems
$stmt = $pdo->prepare("
    SELECT s.*, 
           (SELECT COUNT(*) FROM colonies c 
            JOIN planets p ON c.planet_id = p.planet_id 
            WHERE p.system_id = s.system_id AND c.player_id = ?) as my_colonies,
           (SELECT COUNT(*) FROM colonies c 
            JOIN planets p ON c.planet_id = p.planet_id 
            WHERE p.system_id = s.system_id AND c.player_id != ?) as other_colonies
    FROM systems s 
    WHERE s.game_id = (SELECT game_id FROM players WHERE player_id = ?)
    ORDER BY s.name
");
$stmt->execute([$player_id, $player_id, $player_id]);
$systems = $stmt->fetchAll();

// Get galaxy bounds for display
$max_x = max(array_column($systems, 'x_coordinate'));
$min_x = min(array_column($systems, 'x_coordinate'));
$max_y = max(array_column($systems, 'y_coordinate'));
$min_y = min(array_column($systems, 'y_coordinate'));

$galaxy_width = $max_x - $min_x;
$galaxy_height = $max_y - $min_y;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galaxy Map - Master of the Galaxy</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .galaxy-container {
            position: relative;
            width: 100%;
            height: 70vh;
            background: radial-gradient(ellipse at center, #0a0a2e 0%, #000 100%);
            border: 2px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .galaxy-map {
            position: relative;
            width: 100%;
            height: 100%;
        }
        
        .star-system {
            position: absolute;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            cursor: pointer;
            transform: translate(-50%, -50%);
            transition: all 0.3s ease;
        }
        
        .star-system:hover {
            width: 12px;
            height: 12px;
            z-index: 10;
        }
        
        .star-red { background: #ff4444; box-shadow: 0 0 8px #ff4444; }
        .star-orange { background: #ff8844; box-shadow: 0 0 8px #ff8844; }
        .star-yellow { background: #ffff44; box-shadow: 0 0 8px #ffff44; }
        .star-white { background: #ffffff; box-shadow: 0 0 8px #ffffff; }
        .star-blue { background: #4488ff; box-shadow: 0 0 8px #4488ff; }
        
        .system-my-colony {
            border: 2px solid var(--success-color);
            animation: pulse 2s infinite;
        }
        
        .system-other-colony {
            border: 2px solid var(--danger-color);
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 8px var(--success-color); }
            50% { box-shadow: 0 0 16px var(--success-color); }
            100% { box-shadow: 0 0 8px var(--success-color); }
        }
        
        .system-info {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: var(--secondary-bg);
            padding: 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            min-width: 250px;
            display: none;
        }
        
        .system-info h3 {
            margin: 0 0 10px 0;
            color: var(--text-accent);
        }
        
        .galaxy-legend {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--secondary-bg);
            padding: 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .legend-star {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .back-btn {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="game-header">
            <h1>Galaxy Map</h1>
            <div class="empire-info">
                <span class="empire-name"><?php echo htmlspecialchars($empire['empire_name'] ?? 'Unknown Empire'); ?></span>
                <span class="race-name"><?php echo htmlspecialchars($empire['race_name'] ?? 'Unknown Race'); ?></span>
                <span class="turn-info">Turn <?php echo $current_turn; ?></span>
            </div>
        </header>

        <div class="back-btn">
            <a href="index.php" class="btn-primary">← Back to Overview</a>
        </div>

        <div class="galaxy-container">
            <div class="galaxy-map" id="galaxy-map">
                <?php foreach ($systems as $system): ?>
                    <?php
                    // Convert coordinates to percentage for positioning
                    $x_percent = (($system['x_coordinate'] - $min_x) / max(1, $galaxy_width)) * 90 + 5;
                    $y_percent = (($system['y_coordinate'] - $min_y) / max(1, $galaxy_height)) * 90 + 5;
                    
                    $classes = ['star-system', 'star-' . $system['star_type']];
                    if ($system['my_colonies'] > 0) {
                        $classes[] = 'system-my-colony';
                    } elseif ($system['other_colonies'] > 0) {
                        $classes[] = 'system-other-colony';
                    }
                    ?>
                    <div class="<?php echo implode(' ', $classes); ?>"
                         style="left: <?php echo $x_percent; ?>%; top: <?php echo $y_percent; ?>%;"
                         data-system-id="<?php echo $system['system_id']; ?>"
                         data-system-name="<?php echo htmlspecialchars($system['name']); ?>"
                         data-star-type="<?php echo $system['star_type']; ?>"
                         data-my-colonies="<?php echo $system['my_colonies']; ?>"
                         data-other-colonies="<?php echo $system['other_colonies']; ?>"
                         onclick="showSystemInfo(this)">
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="galaxy-legend">
                <h4>Star Types</h4>
                <div class="legend-item">
                    <div class="legend-star star-red"></div>
                    <span>Red Giant</span>
                </div>
                <div class="legend-item">
                    <div class="legend-star star-orange"></div>
                    <span>Orange Star</span>
                </div>
                <div class="legend-item">
                    <div class="legend-star star-yellow"></div>
                    <span>Yellow Star</span>
                </div>
                <div class="legend-item">
                    <div class="legend-star star-white"></div>
                    <span>White Star</span>
                </div>
                <div class="legend-item">
                    <div class="legend-star star-blue"></div>
                    <span>Blue Giant</span>
                </div>
            </div>
            
            <div class="system-info" id="system-info">
                <h3 id="system-name">System Name</h3>
                <p><strong>Star Type:</strong> <span id="star-type">-</span></p>
                <p><strong>Your Colonies:</strong> <span id="my-colonies">0</span></p>
                <p><strong>Other Colonies:</strong> <span id="other-colonies">0</span></p>
                <p><em>Click elsewhere to close</em></p>
            </div>
        </div>
    </div>

    <script>
        function showSystemInfo(element) {
            const info = document.getElementById('system-info');
            const systemName = element.getAttribute('data-system-name');
            const starType = element.getAttribute('data-star-type');
            const myColonies = element.getAttribute('data-my-colonies');
            const otherColonies = element.getAttribute('data-other-colonies');
            
            document.getElementById('system-name').textContent = systemName;
            document.getElementById('star-type').textContent = starType.charAt(0).toUpperCase() + starType.slice(1);
            document.getElementById('my-colonies').textContent = myColonies;
            document.getElementById('other-colonies').textContent = otherColonies;
            
            info.style.display = 'block';
        }
        
        // Close system info when clicking elsewhere
        document.getElementById('galaxy-map').addEventListener('click', function(e) {
            if (!e.target.classList.contains('star-system')) {
                document.getElementById('system-info').style.display = 'none';
            }
        });
    </script>
</body>
</html>