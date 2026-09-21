<?php
$db = new PDO("mysql:host=127.0.0.1;dbname=db_assami", "root", "");
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

echo "Tables in db_assami:\n";
foreach ($tables as $t) {
    $count = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo sprintf("  %-25s : %d rows\n", $t, $count);
}
