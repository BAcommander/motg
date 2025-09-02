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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fleet Management - Master of the Galaxy</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <div class="container">
        <header class="game-header">
            <h1>Fleet Management</h1>
            <div class="empire-info">
                <span class="empire-name"><?php echo htmlspecialchars($empire['empire_name'] ?? 'Unknown Empire'); ?></span>
                <span class="race-name"><?php echo htmlspecialchars($empire['race_name'] ?? 'Unknown Race'); ?></span>
                <span class="turn-info">Turn <?php echo $current_turn; ?></span>
            </div>
        </header>

        <div class="back-btn" style="margin-bottom: 20px;">
            <a href="index.php" class="btn-primary">← Back to Overview</a>
        </div>

        <div style="background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 8px; padding: 40px; text-align: center;">
            <h2>Fleet Management</h2>
            <p>Ship construction and fleet management features coming soon!</p>
            <p><em>This will include ship design, fleet composition, and tactical combat management.</em></p>
        </div>
    </div>
</body>
</html>