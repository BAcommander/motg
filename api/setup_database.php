<?php
// Suppress deprecation warnings for clean JSON output
error_reporting(E_ALL & ~E_DEPRECATED);

header('Content-Type: application/json');
require_once '../config/config.php';

try {
    if (defined('DB_TYPE') && DB_TYPE === 'sqlite') {
        // SQLite setup
        $db_path = defined('DB_PATH') ? DB_PATH : __DIR__ . '/../database/master_of_galaxy.db';
        
        // Ensure database directory exists
        $db_dir = dirname($db_path);
        if (!is_dir($db_dir)) {
            mkdir($db_dir, 0755, true);
        }
        
        $pdo = new PDO(
            "sqlite:" . $db_path,
            null,
            null,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        $pdo->exec('PRAGMA foreign_keys = ON');
    } else {
        // MySQL setup
        $pdo = new PDO(
            "mysql:host=" . (defined('DB_HOST') ? DB_HOST : 'localhost'),
            defined('DB_USER') ? DB_USER : 'root',  
            defined('DB_PASS') ? DB_PASS : '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        
        $db_name = defined('DB_NAME') ? DB_NAME : 'master_of_galaxy';
        
        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$db_name`");
    }
    
    // Read and execute schema file
    if (defined('DB_TYPE') && DB_TYPE === 'sqlite') {
        $schema_file = __DIR__ . '/../database/schema_sqlite.sql';
    } else {
        $schema_file = __DIR__ . '/../database/schema.sql';
    }
    
    if (!file_exists($schema_file)) {
        throw new Exception('Schema file not found: ' . $schema_file);
    }
    
    $schema_sql = file_get_contents($schema_file);
    
    // Remove comments and split by semicolon
    $schema_sql = preg_replace('/--.*$/m', '', $schema_sql);
    $schema_sql = preg_replace('/\/\*.*?\*\//s', '', $schema_sql);
    $statements = array_filter(array_map('trim', explode(';', $schema_sql)));
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (empty($statement) || stripos($statement, 'CREATE DATABASE') !== false || 
            stripos($statement, 'USE ') !== false) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
        } catch (PDOException $e) {
            // Ignore "table already exists" errors
            if (strpos($e->getMessage(), 'already exists') === false) {
                throw $e;
            }
        }
    }
    
    // Add initial race data directly (SQLite compatible)
    if (defined('DB_TYPE') && DB_TYPE === 'sqlite') {
        // Insert races directly from config
        $races = array(
            array('Humans', 'Versatile and diplomatic race with strong trading abilities', '["charismatic", "lucky"]', '{"diplomacy": 25, "trade": 25}', 'democracy'),
            array('Psilons', 'Highly intelligent research-focused race with creative abilities', '["creative", "large_homeworld", "low_gravity"]', '{"research": 2, "all_tech_choices": true}', 'technocracy'),
            array('Silicoids', 'Silicon-based lifeforms immune to pollution with slow growth', '["lithovore", "tolerant", "repulsive"]', '{"pollution_immunity": true, "growth_penalty": -50}', 'dictatorship'),
            array('Sakkra', 'Fast-breeding reptilian race with subterranean adaptation', '["fast_breeding", "subterranean", "large_homeworld"]', '{"growth": 100, "agriculture": 1}', 'feudalism'),
            array('Meklars', 'Cybernetic race with enhanced industrial capabilities', '["cybernetic"]', '{"production": 2, "extra_factories": 2}', 'dictatorship'),
            array('Klackons', 'Insectoid hive-mind with industrial focus but uncreative', '["uncreative"]', '{"production": 1, "food": 1, "random_tech": true}', 'unification')
        );
        
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO races (race_name, description, traits, bonuses, government_type) VALUES (?, ?, ?, ?, ?)");
        foreach ($races as $race) {
            try {
                $stmt->execute($race);
            } catch (PDOException $e) {
                // Ignore if race already exists
            }
        }
        
        // Add basic technologies
        $technologies = array(
            array('Research Laboratory', 'computers', 2, 150, 'Advanced research facility', '{"unlock_building": {"building_id": 8}}'),
            array('Colony Ship', 'sociology', 1, 80, 'Interstellar colonization', '{"unlock_ship_component": {"component_id": 40}}'),
            array('Laser Cannon', 'physics', 1, 80, 'Basic beam weapon', '{"unlock_component": {"component_id": 10}}')
        );
        
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO technologies (tech_name, category, level, cost, description, effects) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($technologies as $tech) {
            try {
                $stmt->execute($tech);
            } catch (PDOException $e) {
                // Ignore if tech already exists
            }
        }
        
        // Add basic buildings
        $buildings = array(
            array('Colony Base', 'infrastructure', 60, 0, 'Basic colony structure required for all colonies', '{"required": true, "population_cap": 1000}'),
            array('Housing', 'infrastructure', 20, 1, 'Increases population capacity', '{"population_cap": 200}'),
            array('Factory', 'production', 60, 2, 'Increases industrial production', '{"production_bonus": 3}'),
            array('Research Laboratory', 'science', 60, 1, 'Increases research output', '{"research_bonus": 3}')
        );
        
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO buildings (building_name, building_type, cost, maintenance, description, effects) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($buildings as $building) {
            try {
                $stmt->execute($building);
            } catch (PDOException $e) {
                // Ignore if building already exists
            }
        }
    } else {
        // Read and execute seed data for MySQL
        $seed_file = __DIR__ . '/../database/seed_data.sql';
        if (file_exists($seed_file)) {
            $seed_sql = file_get_contents($seed_file);
            $seed_sql = preg_replace('/--.*$/m', '', $seed_sql);
            $seed_sql = preg_replace('/\/\*.*?\*\//s', '', $seed_sql);
            $seed_statements = array_filter(array_map('trim', explode(';', $seed_sql)));
            
            foreach ($seed_statements as $statement) {
                if (empty($statement) || stripos($statement, 'USE ') !== false) {
                    continue;
                }
                
                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    // Ignore duplicate entry errors for seed data
                    if (strpos($e->getMessage(), 'Duplicate entry') === false &&
                        strpos($e->getMessage(), 'already exists') === false) {
                        throw $e;
                    }
                }
            }
        }
    }
    
    // Verify tables were created
    if (defined('DB_TYPE') && DB_TYPE === 'sqlite') {
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Database setup completed successfully',
        'tables_created' => count($tables),
        'database' => (defined('DB_TYPE') && DB_TYPE === 'sqlite') ? 'SQLite' : (defined('DB_NAME') ? DB_NAME : 'master_of_galaxy')
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Setup error: ' . $e->getMessage()
    ]);
}
?>