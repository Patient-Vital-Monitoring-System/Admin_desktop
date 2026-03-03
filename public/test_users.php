<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../api/auth/config.php';
require_once __DIR__ . '/../api/auth/queries.php';

header('Content-Type: application/json');

$search = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';

try {
    $users = getAllUsers($pdo, $search, $roleFilter);
    echo json_encode(['success' => true, 'users' => $users, 'count' => count($users)]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

