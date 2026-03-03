<?php
// API endpoint for updating a user

header('Content-Type: application/json');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/queries.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    $input = $_POST;
}

$userId = isset($input['id']) ? intval($input['id']) : 0;
$email = isset($input['email']) ? trim($input['email']) : '';
$role = isset($input['role']) ? trim($input['role']) : '';
$name = isset($input['name']) ? trim($input['name']) : '';
$password = isset($input['password']) ? $input['password'] : null;

// Validate input
if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
    exit;
}

if (empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email is required']);
    exit;
}

if (empty($role)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Role is required']);
    exit;
}

// Hash password if provided using MD5 (to match database format)
$passwordHash = null;
if (!empty($password)) {
    $passwordHash = md5($password);
}

// Try to update the user
try {
    $result = updateUser($pdo, $userId, $email, $role, $passwordHash, $name);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'No changes made or user not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to update user: ' . $e->getMessage()]);
}
?>

