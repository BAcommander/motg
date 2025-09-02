<?php
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
    <title>Master of the Galaxy - Turn <?php echo $current_turn; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <div class="container">
        <header class="game-header">
            <h1>Master of the Galaxy</h1>
            <div class="empire-info">
                <span class="empire-name"><?php echo htmlspecialchars($empire['name']); ?></span>
                <span class="race-name"><?php echo htmlspecialchars($empire['race_name']); ?></span>
                <span class="turn-info">Turn <?php echo $current_turn; ?></span>
            </div>
            <div class="resources">
                <span class="bc">BC: <?php echo number_format($empire['credits']); ?></span>
                <span class="research">Research: <?php echo number_format($empire['research_points']); ?></span>
            </div>
        </header>

        <nav class="main-nav">
            <a href="galaxy.php" class="nav-btn">Galaxy Map</a>
            <a href="colonies.php" class="nav-btn">Colonies</a>
            <a href="ships.php" class="nav-btn">Ships</a>
            <a href="research.php" class="nav-btn">Research</a>
            <a href="diplomacy.php" class="nav-btn">Diplomacy</a>
            <a href="leaders.php" class="nav-btn">Leaders</a>
            <a href="reports.php" class="nav-btn">Reports</a>
        </nav>

        <main class="game-content">
            <div class="dashboard">
                <div class="colony-summary">
                    <h3>Colony Summary</h3>
                    <?php
                    $colonies = $game->getPlayerColonies();
                    foreach ($colonies as $colony): ?>
                        <div class="colony-item">
                            <strong><?php echo htmlspecialchars($colony['name']); ?></strong>
                            <span>Pop: <?php echo $colony['population']; ?></span>
                            <span>Prod: <?php echo $colony['production']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="research-progress">
                    <h3>Current Research</h3>
                    <?php $current_research = $game->getCurrentResearch(); ?>
                    <div class="research-item">
                        <strong><?php echo htmlspecialchars($current_research['tech_name']); ?></strong>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?php echo $current_research['progress']; ?>%"></div>
                        </div>
                        <span><?php echo $current_research['turns_remaining']; ?> turns remaining</span>
                    </div>
                </div>

                <div class="news-events">
                    <h3>Recent Events</h3>
                    <?php
                    $events = $game->getRecentEvents();
                    foreach ($events as $event): ?>
                        <div class="event-item">
                            <?php echo htmlspecialchars($event['message']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="turn-actions">
                <button id="end-turn-btn" class="btn-primary">End Turn</button>
                <button id="auto-turn-btn" class="btn-secondary">Auto Turn</button>
            </div>
        </main>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>