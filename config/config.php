<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'master_of_galaxy');
define('DB_USER', 'root');
define('DB_PASS', '');

// Game configuration
define('GAME_NAME', 'Master of the Galaxy');
define('GAME_VERSION', '1.0.0');
define('MAX_PLAYERS', 8);
define('GALAXY_SIZE', 'medium'); // small, medium, large, huge
define('DIFFICULTY', 'normal'); // easy, normal, hard, impossible

// Galaxy settings
define('SYSTEMS_PER_PLAYER', 15);
define('PLANETS_PER_SYSTEM_MIN', 1);
define('PLANETS_PER_SYSTEM_MAX', 5);

// Turn settings
define('TURN_TIME_LIMIT', 86400); // 24 hours in seconds
define('AUTO_END_TURN', true);

// Resource settings
define('STARTING_CREDITS', 1000);
define('STARTING_RESEARCH_POINTS', 0);
define('STARTING_POPULATION', 2500);

// Technology settings
define('TECH_CATEGORIES', [
    'engineering' => 'Engineering',
    'physics' => 'Physics', 
    'chemistry' => 'Chemistry',
    'biology' => 'Biology',
    'computers' => 'Computers',
    'sociology' => 'Sociology',
    'power' => 'Power',
    'force_fields' => 'Force Fields'
]);

// Race definitions based on MOO2
define('RACES', [
    'humans' => [
        'name' => 'Humans',
        'traits' => ['charismatic', 'lucky'],
        'government' => 'democracy',
        'bonuses' => [
            'diplomacy' => 25,
            'trade' => 25
        ]
    ],
    'psilons' => [
        'name' => 'Psilons',
        'traits' => ['creative', 'large_homeworld'],
        'government' => 'technocracy',
        'bonuses' => [
            'research' => 50,
            'all_tech_choices' => true
        ]
    ],
    'silicoids' => [
        'name' => 'Silicoids',
        'traits' => ['lithovore', 'tolerant', 'repulsive'],
        'government' => 'dictatorship',
        'bonuses' => [
            'pollution_immunity' => true,
            'growth_penalty' => -50
        ]
    ],
    'sakkra' => [
        'name' => 'Sakkra',
        'traits' => ['fast_breeding', 'subterranean'],
        'government' => 'feudalism',
        'bonuses' => [
            'growth' => 100,
            'agriculture' => 1
        ]
    ],
    'meklars' => [
        'name' => 'Meklars',
        'traits' => ['cybernetic'],
        'government' => 'dictatorship',
        'bonuses' => [
            'production' => 2,
            'extra_factories' => 2
        ]
    ],
    'klackons' => [
        'name' => 'Klackons',
        'traits' => ['uncreative'],
        'government' => 'unification',
        'bonuses' => [
            'production' => 1,
            'food' => 1,
            'random_tech' => true
        ]
    ]
]);

// Planet types
define('PLANET_TYPES', [
    'terran' => ['name' => 'Terran', 'max_pop' => 10, 'growth_bonus' => 0],
    'ocean' => ['name' => 'Ocean', 'max_pop' => 8, 'growth_bonus' => 10],
    'swamp' => ['name' => 'Swamp', 'max_pop' => 6, 'growth_bonus' => 5],
    'arid' => ['name' => 'Arid', 'max_pop' => 8, 'growth_bonus' => -10],
    'desert' => ['name' => 'Desert', 'max_pop' => 6, 'growth_bonus' => -20],
    'tundra' => ['name' => 'Tundra', 'max_pop' => 5, 'growth_bonus' => -15],
    'barren' => ['name' => 'Barren', 'max_pop' => 3, 'growth_bonus' => -25],
    'minimal' => ['name' => 'Minimal', 'max_pop' => 2, 'growth_bonus' => -30],
    'toxic' => ['name' => 'Toxic', 'max_pop' => 4, 'growth_bonus' => -35],
    'radiated' => ['name' => 'Radiated', 'max_pop' => 3, 'growth_bonus' => -40],
    'volcanic' => ['name' => 'Volcanic', 'max_pop' => 4, 'growth_bonus' => -20],
    'gas_giant' => ['name' => 'Gas Giant', 'max_pop' => 0, 'growth_bonus' => 0]
]);

// Building definitions
define('BUILDINGS', [
    'colony_base' => [
        'name' => 'Colony Base',
        'cost' => 60,
        'maintenance' => 0,
        'effect' => 'Basic colony structure'
    ],
    'housing' => [
        'name' => 'Housing',
        'cost' => 20,
        'maintenance' => 1,
        'effect' => '+2 population capacity'
    ],
    'factory' => [
        'name' => 'Factory',
        'cost' => 60,
        'maintenance' => 2,
        'effect' => '+3 production per population'
    ],
    'research_lab' => [
        'name' => 'Research Laboratory',
        'cost' => 60,
        'maintenance' => 1,
        'effect' => '+3 research per scientist'
    ],
    'trade_goods' => [
        'name' => 'Trade Goods',
        'cost' => 0,
        'maintenance' => 0,
        'effect' => 'Converts production to credits'
    ]
]);

// Initialize database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Auto-include classes
spl_autoload_register(function($class_name) {
    $class_file = __DIR__ . '/../classes/' . $class_name . '.php';
    if (file_exists($class_file)) {
        require_once $class_file;
    }
});
?>