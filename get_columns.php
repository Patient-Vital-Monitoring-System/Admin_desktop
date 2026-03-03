<?php
$pdo=new PDO('mysql:host=localhost;dbname=vitalwear','root','');
$table = isset($_GET['table']) ? preg_replace('/[^a-z0-9_]/i','',$_GET['table']) : 'incident';
foreach($pdo->query("SHOW COLUMNS FROM $table") as $col) {
    echo $col['Field'].' '.$col['Type'].'<br>';
}
