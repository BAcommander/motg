<?php
require_once 'config/config.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master of the Galaxy - Setup</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .setup-container {
            max-width: 800px;
            margin: 50px auto;
            background: var(--secondary-bg);
            padding: 30px;
            border-radius: 12px;
            border: 2px solid var(--border-color);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
        }
        
        .setup-step {
            margin-bottom: 30px;
            padding: 20px;
            background: var(--primary-bg);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .step-header {
            color: var(--text-accent);
            font-size: 1.4em;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .status-pending { background: var(--warning-color); }
        .status-success { background: var(--success-color); }
        .status-error { background: var(--danger-color); }
        
        .setup-actions {
            text-align: center;
            margin-top: 30px;
        }
        
        .race-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .race-card {
            background: var(--accent-bg);
            padding: 15px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .race-card:hover {
            border-color: var(--text-accent);
            transform: translateY(-2px);
        }
        
        .race-card.selected {
            border-color: var(--success-color);
            background: linear-gradient(135deg, var(--accent-bg), var(--secondary-bg));
        }
        
        .race-name {
            font-weight: bold;
            color: var(--text-accent);
            margin-bottom: 8px;
        }
        
        .race-traits {
            font-size: 0.9em;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-container">
            <h1 style="text-align: center; color: var(--text-accent); margin-bottom: 30px;">
                Master of the Galaxy - Game Setup
            </h1>
            
            <div class="setup-step">
                <div class="step-header">
                    <span class="status-indicator" id="db-status"></span>
                    Step 1: Database Setup
                </div>
                <p id="db-message">Checking database connection...</p>
                <button id="setup-db-btn" class="btn-primary" onclick="setupDatabase()" style="margin-top: 10px;">
                    Initialize Database
                </button>
            </div>
            
            <div class="setup-step">
                <div class="step-header">
                    <span class="status-indicator status-pending" id="game-status"></span>
                    Step 2: Create New Game
                </div>
                <form id="game-setup-form">
                    <div class="form-group">
                        <label for="game-name">Game Name:</label>
                        <input type="text" id="game-name" name="game_name" value="Test Galaxy" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="galaxy-size">Galaxy Size:</label>
                        <select id="galaxy-size" name="galaxy_size">
                            <option value="small">Small (4-6 players)</option>
                            <option value="medium" selected>Medium (6-8 players)</option>
                            <option value="large">Large (8-12 players)</option>
                            <option value="huge">Huge (12-16 players)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="difficulty">Difficulty:</label>
                        <select id="difficulty" name="difficulty">
                            <option value="easy">Easy</option>
                            <option value="normal" selected>Normal</option>
                            <option value="hard">Hard</option>
                            <option value="impossible">Impossible</option>
                        </select>
                    </div>
                </form>
                <button id="create-game-btn" class="btn-primary" onclick="createGame()">
                    Create Game
                </button>
            </div>
            
            <div class="setup-step">
                <div class="step-header">
                    <span class="status-indicator status-pending" id="player-status"></span>
                    Step 3: Create Player
                </div>
                <form id="player-setup-form">
                    <div class="form-group">
                        <label for="empire-name">Empire Name:</label>
                        <input type="text" id="empire-name" name="empire_name" value="Terran Federation" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="leader-name">Leader Name:</label>
                        <input type="text" id="leader-name" name="leader_name" value="Emperor" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Select Race:</label>
                        <div class="race-grid" id="race-selection">
                            <!-- Races will be populated by JavaScript -->
                        </div>
                    </div>
                </form>
                <button id="create-player-btn" class="btn-primary" onclick="createPlayer()">
                    Create Player & Start Game
                </button>
            </div>
            
            <div class="setup-actions">
                <p id="setup-status" style="margin-bottom: 20px;"></p>
                <button id="start-game-btn" class="btn-primary" onclick="startGame()" style="display: none;">
                    Enter Galaxy
                </button>
            </div>
        </div>
    </div>

    <script>
        let gameId = null;
        let selectedRaceId = null;
        
        // Initialize setup process
        document.addEventListener('DOMContentLoaded', function() {
            checkDatabaseStatus();
            loadRaces();
        });
        
        function checkDatabaseStatus() {
            fetch('api/check_database.php')
                .then(response => response.json())
                .then(data => {
                    const statusEl = document.getElementById('db-status');
                    const messageEl = document.getElementById('db-message');
                    
                    if (data.success) {
                        statusEl.className = 'status-indicator status-success';
                        messageEl.textContent = 'Database connection successful!';
                        document.getElementById('setup-db-btn').style.display = 'none';
                    } else {
                        statusEl.className = 'status-indicator status-error';
                        messageEl.textContent = 'Database connection failed: ' + data.error;
                    }
                })
                .catch(error => {
                    console.error('Error checking database:', error);
                    document.getElementById('db-status').className = 'status-indicator status-error';
                    document.getElementById('db-message').textContent = 'Error checking database status';
                });
        }
        
        function setupDatabase() {
            document.getElementById('setup-db-btn').disabled = true;
            document.getElementById('setup-db-btn').textContent = 'Setting up...';
            
            fetch('api/setup_database.php', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('db-status').className = 'status-indicator status-success';
                        document.getElementById('db-message').textContent = 'Database setup completed successfully!';
                        document.getElementById('setup-db-btn').style.display = 'none';
                    } else {
                        document.getElementById('db-status').className = 'status-indicator status-error';
                        document.getElementById('db-message').textContent = 'Database setup failed: ' + data.error;
                        document.getElementById('setup-db-btn').disabled = false;
                        document.getElementById('setup-db-btn').textContent = 'Initialize Database';
                    }
                })
                .catch(error => {
                    console.error('Error setting up database:', error);
                    document.getElementById('setup-db-btn').disabled = false;
                    document.getElementById('setup-db-btn').textContent = 'Initialize Database';
                });
        }
        
        function loadRaces() {
            const raceGrid = document.getElementById('race-selection');
            const races = <?php echo json_encode(RACES); ?>;
            
            for (const [raceKey, raceData] of Object.entries(races)) {
                const raceCard = document.createElement('div');
                raceCard.className = 'race-card';
                raceCard.onclick = () => selectRace(raceKey, raceCard);
                
                const traits = raceData.traits.join(', ');
                const bonuses = Object.entries(raceData.bonuses).map(([key, value]) => 
                    typeof value === 'boolean' ? key : `${key}: ${value}`
                ).join(', ');
                
                raceCard.innerHTML = `
                    <div class="race-name">${raceData.name}</div>
                    <div class="race-traits">
                        <strong>Traits:</strong> ${traits}<br>
                        <strong>Bonuses:</strong> ${bonuses}<br>
                        <strong>Government:</strong> ${raceData.government}
                    </div>
                `;
                
                raceGrid.appendChild(raceCard);
            }
        }
        
        function selectRace(raceKey, cardElement) {
            // Remove selection from other cards
            document.querySelectorAll('.race-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Select this card
            cardElement.classList.add('selected');
            selectedRaceId = raceKey;
        }
        
        function createGame() {
            const formData = new FormData(document.getElementById('game-setup-form'));
            const gameData = Object.fromEntries(formData.entries());
            
            document.getElementById('create-game-btn').disabled = true;
            document.getElementById('create-game-btn').textContent = 'Creating...';
            
            fetch('api/create_game.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(gameData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    gameId = data.game_id;
                    document.getElementById('game-status').className = 'status-indicator status-success';
                    document.getElementById('create-game-btn').textContent = 'Game Created!';
                    updateSetupStatus('Game created successfully! Game ID: ' + gameId);
                } else {
                    document.getElementById('game-status').className = 'status-indicator status-error';
                    document.getElementById('create-game-btn').disabled = false;
                    document.getElementById('create-game-btn').textContent = 'Create Game';
                    updateSetupStatus('Failed to create game: ' + data.error, true);
                }
            })
            .catch(error => {
                console.error('Error creating game:', error);
                document.getElementById('create-game-btn').disabled = false;
                document.getElementById('create-game-btn').textContent = 'Create Game';
            });
        }
        
        function createPlayer() {
            if (!gameId) {
                updateSetupStatus('Please create a game first!', true);
                return;
            }
            
            if (!selectedRaceId) {
                updateSetupStatus('Please select a race!', true);
                return;
            }
            
            const formData = new FormData(document.getElementById('player-setup-form'));
            const playerData = Object.fromEntries(formData.entries());
            playerData.game_id = gameId;
            playerData.race_id = selectedRaceId;
            
            document.getElementById('create-player-btn').disabled = true;
            document.getElementById('create-player-btn').textContent = 'Creating Player...';
            
            fetch('api/create_player.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(playerData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('player-status').className = 'status-indicator status-success';
                    document.getElementById('create-player-btn').textContent = 'Player Created!';
                    document.getElementById('start-game-btn').style.display = 'inline-block';
                    updateSetupStatus('Setup completed successfully! Ready to enter the galaxy.');
                    
                    // Store player session
                    sessionStorage.setItem('player_id', data.player_id);
                    
                } else {
                    document.getElementById('player-status').className = 'status-indicator status-error';
                    document.getElementById('create-player-btn').disabled = false;
                    document.getElementById('create-player-btn').textContent = 'Create Player & Start Game';
                    updateSetupStatus('Failed to create player: ' + data.error, true);
                }
            })
            .catch(error => {
                console.error('Error creating player:', error);
                document.getElementById('create-player-btn').disabled = false;
                document.getElementById('create-player-btn').textContent = 'Create Player & Start Game';
            });
        }
        
        function startGame() {
            const playerId = sessionStorage.getItem('player_id');
            if (playerId) {
                // Create a simple session for demo purposes
                fetch('api/start_session.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ player_id: playerId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'index.php';
                    } else {
                        updateSetupStatus('Failed to start session: ' + data.error, true);
                    }
                });
            } else {
                updateSetupStatus('No player ID found. Please create a player first.', true);
            }
        }
        
        function updateSetupStatus(message, isError = false) {
            const statusEl = document.getElementById('setup-status');
            statusEl.textContent = message;
            statusEl.style.color = isError ? 'var(--danger-color)' : 'var(--success-color)';
        }
    </script>
</body>
</html>