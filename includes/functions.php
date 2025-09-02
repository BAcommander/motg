<?php
// Master of the Galaxy - Helper Functions

/**
 * Sanitize user input
 */
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Format numbers with appropriate suffixes (K, M, B)
 */
function formatNumber($number) {
    if ($number >= 1000000000) {
        return round($number / 1000000000, 1) . 'B';
    } elseif ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return round($number / 1000, 1) . 'K';
    }
    return number_format($number);
}

/**
 * Calculate distance between two star systems
 */
function calculateDistance($x1, $y1, $x2, $y2) {
    return sqrt(pow($x2 - $x1, 2) + pow($y2 - $y1, 2));
}

/**
 * Generate random planet name
 */
function generatePlanetName($system_name, $orbit) {
    $suffixes = ['Prime', 'Alpha', 'Beta', 'Gamma', 'Delta', 'Epsilon', 'Zeta', 'Eta'];
    $numerals = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'];
    
    if (rand(0, 1)) {
        return $system_name . ' ' . $suffixes[array_rand($suffixes)];
    } else {
        return $system_name . ' ' . $numerals[min($orbit - 1, count($numerals) - 1)];
    }
}

/**
 * Generate random system name
 */
function generateSystemName() {
    $prefixes = ['Alpha', 'Beta', 'Gamma', 'Delta', 'Epsilon', 'Zeta', 'Eta', 'Theta', 'Iota', 'Kappa'];
    $names = [
        'Centauri', 'Vega', 'Sirius', 'Arcturus', 'Rigel', 'Betelgeuse', 'Altair', 'Deneb',
        'Spica', 'Aldebaran', 'Antares', 'Pollux', 'Fomalhaut', 'Regulus', 'Adhara',
        'Castor', 'Gacrux', 'Bellatrix', 'Elnath', 'Miaplacidus', 'Alnilam', 'Alnair',
        'Alioth', 'Dubhe', 'Mirfak', 'Wezen', 'Sargas', 'Kaus', 'Avior', 'Menkalinan'
    ];
    
    if (rand(0, 2) == 0) {
        return $prefixes[array_rand($prefixes)] . ' ' . $names[array_rand($names)];
    } else {
        return $names[array_rand($names)];
    }
}

/**
 * Get race information by ID
 */
function getRaceById($race_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM races WHERE race_id = ?");
    $stmt->execute([$race_id]);
    return $stmt->fetch();
}

/**
 * Check if player has discovered a technology
 */
function playerHasTechnology($player_id, $tech_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 1 FROM player_technologies 
        WHERE player_id = ? AND tech_id = ?
    ");
    $stmt->execute([$player_id, $tech_id]);
    return $stmt->fetch() !== false;
}

/**
 * Get available technologies for a player
 */
function getAvailableTechnologies($player_id, $category = null) {
    global $pdo;
    
    $where_clause = "WHERE t.tech_id NOT IN (SELECT tech_id FROM player_technologies WHERE player_id = ?)";
    $params = [$player_id];
    
    if ($category) {
        $where_clause .= " AND t.category = ?";
        $params[] = $category;
    }
    
    $stmt = $pdo->prepare("
        SELECT t.* FROM technologies t
        $where_clause
        ORDER BY t.category, t.level, t.tech_name
    ");
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Calculate colony food balance
 */
function calculateFoodBalance($colony_data, $race_bonuses) {
    $farmers = $colony_data['farmers'];
    $population = $colony_data['population'];
    
    $base_food_per_farmer = 2;
    if (isset($race_bonuses['food'])) {
        $base_food_per_farmer += $race_bonuses['food'];
    }
    
    $food_produced = $farmers * $base_food_per_farmer;
    $food_consumed = $population * 2; // Each pop consumes 2 food
    
    return $food_produced - $food_consumed;
}

/**
 * Calculate colony production output
 */
function calculateProduction($colony_data, $race_bonuses) {
    $workers = $colony_data['workers'];
    $base_production = 1;
    
    if (isset($race_bonuses['production'])) {
        $base_production += $race_bonuses['production'];
    }
    
    return $workers * $base_production;
}

/**
 * Generate galaxy systems for a new game
 */
function generateGalaxy($game_id, $galaxy_size, $num_players) {
    global $pdo;
    
    $size_multiplier = [
        'small' => 0.7,
        'medium' => 1.0,
        'large' => 1.4,
        'huge' => 2.0
    ];
    
    $systems_per_player = 15; // Default value
    $base_systems = $num_players * $systems_per_player;
    $total_systems = max(20, floor($base_systems * $size_multiplier[$galaxy_size]));
    
    // Generate galaxy dimensions
    $galaxy_radius = (int)(sqrt($total_systems) * 15);
    
    for ($i = 0; $i < $total_systems; $i++) {
        // Generate random coordinates within galaxy bounds
        $angle = rand(0, 360) * (M_PI / 180);
        $distance = rand(10, $galaxy_radius);
        
        $x = (int)($distance * cos($angle));
        $y = (int)($distance * sin($angle));
        
        $system_name = generateSystemName();
        $star_types = ['red', 'orange', 'yellow', 'white', 'blue'];
        $star_type = $star_types[array_rand($star_types)];
        
        // Insert system
        $stmt = $pdo->prepare("
            INSERT INTO systems (game_id, name, x_coordinate, y_coordinate, star_type)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$game_id, $system_name, $x, $y, $star_type]);
        $system_id = $pdo->lastInsertId();
        
        // Generate planets for this system
        $planets_per_system_min = 1;
        $planets_per_system_max = 5;
        $num_planets = rand($planets_per_system_min, $planets_per_system_max);
        generatePlanetsForSystem($system_id, $system_name, $num_planets);
    }
}

/**
 * Generate planets for a star system
 */
function generatePlanetsForSystem($system_id, $system_name, $num_planets) {
    global $pdo;
    
    $planet_types = ['terran', 'ocean', 'swamp', 'arid', 'desert', 'tundra', 'barren'];
    $planet_max_pops = ['terran' => 10, 'ocean' => 8, 'swamp' => 6, 'arid' => 8, 'desert' => 6, 'tundra' => 5, 'barren' => 3];
    
    for ($orbit = 1; $orbit <= $num_planets; $orbit++) {
        $planet_name = generatePlanetName($system_name, $orbit);
        $planet_type = $planet_types[array_rand($planet_types)];
        $max_population = $planet_max_pops[$planet_type];
        
        $sizes = ['tiny', 'small', 'medium', 'large', 'huge'];
        $size = $sizes[array_rand($sizes)];
        
        $richness_types = ['ultra_poor', 'poor', 'normal', 'rich', 'ultra_rich'];
        $richness_weights = [5, 15, 50, 25, 5]; // Weighted probability
        $richness = weightedRandom($richness_types, $richness_weights);
        
        $gravity_types = ['low', 'normal', 'high'];
        $gravity = $gravity_types[array_rand($gravity_types)];
        
        $stmt = $pdo->prepare("
            INSERT INTO planets (system_id, name, orbit_position, planet_type, planet_size, 
                               max_population, mineral_richness, gravity, climate)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $system_id, $planet_name, $orbit, $planet_type, $size,
            $max_population, $richness, $gravity, $planet_type
        ]);
    }
}

/**
 * Weighted random selection
 */
function weightedRandom($values, $weights) {
    $total = array_sum($weights);
    $random = rand(1, $total);
    
    $current = 0;
    for ($i = 0; $i < count($values); $i++) {
        $current += $weights[$i];
        if ($random <= $current) {
            return $values[$i];
        }
    }
    
    return $values[0]; // Fallback
}

/**
 * Create starting colony for a player
 */
function createStartingColony($player_id, $game_id) {
    global $pdo;
    
    // Find a suitable homeworld (Terran planet)
    $stmt = $pdo->prepare("
        SELECT p.*, s.name as system_name 
        FROM planets p 
        JOIN systems s ON p.system_id = s.system_id 
        WHERE s.game_id = ? AND p.climate = 'terran' 
        AND p.planet_id NOT IN (SELECT planet_id FROM colonies)
        ORDER BY RANDOM() 
        LIMIT 1
    ");
    $stmt->execute([$game_id]);
    $homeworld = $stmt->fetch();
    
    if (!$homeworld) {
        // If no terran planet, find any habitable planet
        $stmt = $pdo->prepare("
            SELECT p.*, s.name as system_name 
            FROM planets p 
            JOIN systems s ON p.system_id = s.system_id 
            WHERE s.game_id = ? AND p.climate NOT IN ('toxic', 'radiated')
            AND p.planet_id NOT IN (SELECT planet_id FROM colonies)
            ORDER BY RANDOM() 
            LIMIT 1
        ");
        $stmt->execute([$game_id]);
        $homeworld = $stmt->fetch();
    }
    
    if (!$homeworld) {
        throw new Exception("No suitable homeworld found!");
    }
    
    // Create colony
    $colony_name = $homeworld['system_name'] . " Prime";
    $current_turn = 1;
    $starting_population = 2500; // Default starting population
    
    $stmt = $pdo->prepare("
        INSERT INTO colonies (planet_id, player_id, name, population, farmers, workers, 
                            scientists, founded_turn)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $homeworld['planet_id'], $player_id, $colony_name,
        $starting_population, 
        floor($starting_population * 0.5), // 50% farmers initially
        floor($starting_population * 0.5), // 50% workers initially
        0, // No scientists initially
        $current_turn
    ]);
    
    return $homeworld;
}

/**
 * Log game event
 */
function logEvent($player_id, $event_type, $message, $importance = 'normal', $data = null) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT current_turn FROM games 
        WHERE game_id = (SELECT game_id FROM players WHERE player_id = ?)
    ");
    $stmt->execute([$player_id]);
    $game = $stmt->fetch();
    $current_turn = $game ? $game['current_turn'] : 1;
    
    $stmt = $pdo->prepare("
        INSERT INTO events (player_id, turn_occurred, event_type, message, importance, data)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $player_id, $current_turn, $event_type, $message, $importance,
        $data ? json_encode($data) : null
    ]);
}

/**
 * Check if all players are ready for next turn
 */
function checkTurnStatus($game_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_players,
            SUM(turn_ready) as ready_players
        FROM players 
        WHERE game_id = ? AND status = 'active'
    ");
    $stmt->execute([$game_id]);
    $status = $stmt->fetch();
    
    return [
        'total' => $status['total_players'],
        'ready' => $status['ready_players'],
        'all_ready' => $status['total_players'] > 0 && $status['ready_players'] >= $status['total_players']
    ];
}

/**
 * Format time remaining
 */
function formatTimeRemaining($seconds) {
    if ($seconds <= 0) return "Time expired";
    
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    
    if ($hours > 0) {
        return "{$hours}h {$minutes}m";
    } elseif ($minutes > 0) {
        return "{$minutes}m";
    } else {
        return "{$seconds}s";
    }
}

/**
 * Get player's diplomatic status with another player
 */
function getDiplomaticStatus($player1_id, $player2_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT relation_type, trust_level 
        FROM diplomacy 
        WHERE (player1_id = ? AND player2_id = ?) 
           OR (player1_id = ? AND player2_id = ?)
    ");
    $stmt->execute([$player1_id, $player2_id, $player2_id, $player1_id]);
    $relation = $stmt->fetch();
    
    return $relation ?: ['relation_type' => 'neutral', 'trust_level' => 0];
}

/**
 * Check if player can colonize a planet type
 */
function canColonizePlanet($player_id, $planet_type) {
    // Check for racial tolerances and colonization technologies
    $race = getRaceById($player_id);
    $traits = json_decode($race['traits'], true) ?: [];
    
    // Tolerant races can colonize more planet types
    if (in_array('tolerant', $traits)) {
        return true;
    }
    
    // Check for specific colonization technologies
    $hostile_types = ['toxic', 'radiated', 'barren', 'volcanic'];
    if (in_array($planet_type, $hostile_types)) {
        return playerHasTechnology($player_id, 'environmental_suit') || 
               playerHasTechnology($player_id, 'bioadaptation');
    }
    
    return true; // Most planet types are colonizable by default
}
?>