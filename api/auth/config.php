<?php
// simple DB config - adjust as needed
$DB_HOST = 'localhost';
$DB_NAME = 'vitalwear';
$DB_USER = 'root';
$DB_PASS = ''; // set your password

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Database connection failed','details'=>$e->getMessage()]);
    exit;
}
?>