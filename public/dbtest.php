<?php
// quick database connectivity check
require_once __DIR__ . '/../api/auth/config.php';

header('Content-Type: text/plain');
if (isset($pdo)) {
    echo "Connected to database {$DB_NAME} as {$DB_USER}\n";
    try {
        $count = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        echo "users table rows: {$count}\n";
    } catch (Exception $e) {
        echo "Query failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "PDO object not set - connection failed.\n";
}
?>