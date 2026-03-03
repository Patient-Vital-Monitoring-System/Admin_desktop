<?php
// Test fetching users the same way users.php does
require_once __DIR__ . '/../api/auth/config.php';
require_once __DIR__ . '/../api/auth/queries.php';

// Ensure users table exists and sync data from legacy tables
ensureUsersTable($pdo);
syncUsersFromLegacyTables($pdo);

$search = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';
$users = getAllUsers($pdo, $search, $roleFilter);

header('Content-Type: text/plain');
echo "Found " . count($users) . " users\n\n";

foreach ($users as $user) {
    echo "ID: " . $user['id'] . "\n";
    echo "Name: " . ($user['name'] ?? 'N/A') . "\n";
    echo "Email: " . $user['email'] . "\n";
    echo "Role: " . $user['role'] . "\n";
    echo "---\n";
}
?>
