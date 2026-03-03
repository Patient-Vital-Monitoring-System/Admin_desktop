<?php
// Check if legacy tables exist and have data
require_once __DIR__ . '/../api/auth/config.php';

header('Content-Type: text/plain');

$tables = ['management', 'admin', 'responder', 'rescuer', 'users'];

foreach ($tables as $table) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "$table: $count rows\n";
    } catch (Exception $e) {
        echo "$table: ERROR - " . $e->getMessage() . "\n";
    }
}
?>
