<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$_SERVER['REQUEST_METHOD'] = 'POST';

// Simulate JSON input
$json = json_encode(['id' => 3, 'role' => 'responder']);
$fp = fopen('php://input', 'w');
fwrite($fp, $json);
fclose($fp);

include_once __DIR__ . '/../api/auth/delete_user.php';
?>

