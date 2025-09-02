<?php
header('Content-Type: application/json');

try {
    // Try to connect to database
    $pdo = new PDO(
        "mysql:host=" . (defined('DB_HOST') ? DB_HOST : 'localhost'),
        defined('DB_USER') ? DB_USER : 'root',
        defined('DB_PASS') ? DB_PASS : '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Try to select the database
    $db_name = defined('DB_NAME') ? DB_NAME : 'master_of_galaxy';
    $pdo->exec("USE `$db_name`");
    
    // Check if tables exist
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $required_tables = ['games', 'players', 'races', 'systems', 'planets', 'colonies', 'technologies'];
    $missing_tables = array_diff($required_tables, $tables);
    
    if (empty($missing_tables)) {
        echo json_encode([
            'success' => true,
            'message' => 'Database connection successful and tables exist',
            'tables_count' => count($tables)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Database exists but missing tables: ' . implode(', ', $missing_tables),
            'needs_setup' => true
        ]);
    }
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo json_encode([
            'success' => false,
            'error' => 'Database does not exist',
            'needs_setup' => true
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Database connection failed: ' . $e->getMessage()
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Configuration error: ' . $e->getMessage()
    ]);
}
?>