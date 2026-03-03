<?php
require_once '../../api/auth/config.php';
$cols = $pdo->query("SHOW COLUMNS FROM incident")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($cols);
echo "</pre>";
