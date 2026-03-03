<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/queries.php';

// expect JSON payload with email, password, role
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success'=>false,'error'=>'Invalid JSON']);
    exit;
}
$email = isset($input['email']) ? trim($input['email']) : '';
$password = isset($input['password']) ? $input['password'] : '';
$role = isset($input['role']) ? $input['role'] : 'admin';

if (!$email || !$password) {
    echo json_encode(['success'=>false,'error'=>'Email and password required']);
    exit;
}

try {
    // ensure users table exists
    ensureUsersTable($pdo);

    // check if the user already exists
    $user = getUserByEmailRole($pdo, $email, $role);
    if (!$user) {
        // insert new admin user
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $userId = createUser($pdo, $email, $hash, $role);
        $user = ['id'=>$userId, 'email'=>$email, 'role'=>$role];
    } else {
        // verify password
        if (!password_verify($password, $user['password'])) {
            echo json_encode(['success'=>false,'error'=>'Invalid credentials']);
            exit;
        }
    }

    // return success with user info and mock token
    $token = bin2hex(random_bytes(16));
    echo json_encode(['success'=>true, 'data'=>['user'=>['id'=>$user['id'],'email'=>$user['email'],'role'=>$user['role']], 'token'=>$token]]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Server error','details'=>$e->getMessage()]);
}
?>