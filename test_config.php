<?php
require_once 'config/config.php';

echo "Configuration Test:\n";
echo "==================\n";
echo "DB_TYPE: " . (defined('DB_TYPE') ? DB_TYPE : 'NOT DEFINED') . "\n";
echo "DB_PATH: " . (defined('DB_PATH') ? DB_PATH : 'NOT DEFINED') . "\n";
echo "SQLite extensions loaded: " . (extension_loaded('pdo_sqlite') ? 'YES' : 'NO') . "\n";
echo "MySQL extensions loaded: " . (extension_loaded('pdo_mysql') ? 'YES' : 'NO') . "\n";

// Test connection
try {
    if (defined('DB_TYPE') && DB_TYPE === 'sqlite') {
        $db_path = DB_PATH;
        echo "Attempting SQLite connection to: $db_path\n";
        $pdo = new PDO("sqlite:" . $db_path);
        echo "SQLite connection: SUCCESS\n";
        $pdo = null;
    } else {
        echo "MySQL connection would be attempted\n";
    }
} catch (Exception $e) {
    echo "Connection test failed: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";
?>