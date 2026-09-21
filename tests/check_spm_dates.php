<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=db_assami', 'root', '');
echo "Current Date: " . date('Y-m-d') . "\n";
echo "Current Year: " . date('Y') . "\n";

echo "\n--- All Tables and Row Counts in db_assami ---\n";
$tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    $c = $db->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
    echo str_pad($t, 30) . ": {$c} rows\n";
}

