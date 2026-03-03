<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../api/auth/config.php';
require_once __DIR__ . '/../api/auth/queries.php';

header('Content-Type: application/json');

// Test delete - delete user id 1 from responder table
$userId = isset($_POST['id']) ? intval($_POST['id']) : 0;
$role = isset($_POST['role']) ? $_POST['role'] : '';

try {
    $result = deleteUser($pdo, $userId, $role);
    echo json_encode(['success' => $result, 'id' => $userId, 'role' => $role]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

