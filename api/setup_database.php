<?php
header('Content-Type: application/json');

try {
    // Connect to MySQL without specifying database
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
    
    // Read and execute schema file
    $schema_file = __DIR__ . '/../database/schema.sql';
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
    
    // Read and execute seed data
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
    
    // Verify tables were created
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'message' => 'Database setup completed successfully',
        'tables_created' => count($tables),
        'database' => $db_name
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