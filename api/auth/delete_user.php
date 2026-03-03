 <?php
// API endpoint for deleting a user

header('Content-Type: application/json');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/queries.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get input data - try both JSON and form data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    $input = $_POST;
}

$userId = isset($input['id']) ? intval($input['id']) : 0;
$role = isset($input['role']) ? trim($input['role']) : '';

// Debug log
error_log("Delete request - ID: $userId, Role: $role");

// Validate input
if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid user ID', 'received' => $input]);
    exit;
}

// Try to delete the user
try {
    $result = deleteUser($pdo, $userId, $role);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'User not found or already deleted']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to delete user: ' . $e->getMessage()]);
}
?>

