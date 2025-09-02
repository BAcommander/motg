<?php
// Suppress deprecation warnings for clean JSON output
error_reporting(E_ALL & ~E_DEPRECATED);

header('Content-Type: application/json');
require_once '../config/config.php';

try {
    if (defined('DB_TYPE') && DB_TYPE === 'sqlite') {
        // SQLite connection
        $db_path = defined('DB_PATH') ? DB_PATH : __DIR__ . '/../database/master_of_galaxy.db';
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
        // MySQL connection
        $pdo = new PDO(
            "mysql:host=" . (defined('DB_HOST') ? DB_HOST : 'localhost'),
            defined('DB_USER') ? DB_USER : 'root',
            defined('DB_PASS') ? DB_PASS : '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }
    
    // Check if tables exist
    if (defined('DB_TYPE') && DB_TYPE === 'sqlite') {
        // SQLite table check
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        // MySQL table check
        $db_name = defined('DB_NAME') ? DB_NAME : 'master_of_galaxy';
        $pdo->exec("USE `$db_name`");
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
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
    if (defined('DB_TYPE') && DB_TYPE === 'sqlite') {
        // SQLite doesn't exist yet, needs setup
        echo json_encode([
            'success' => false,
            'error' => 'SQLite database not found',
            'needs_setup' => true
        ]);
    } else {
        // MySQL errors
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
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Configuration error: ' . $e->getMessage()
    ]);
}
?>