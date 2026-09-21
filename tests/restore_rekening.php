<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'db_assami');
$currentCount = $mysqli->query("SELECT COUNT(*) FROM ms_rekening_belanja")->fetch_row()[0];
echo "Current row count in ms_rekening_belanja: $currentCount\n";

if ($currentCount <= 5) {
    echo "Restoring original ms_rekening_belanja from SQL...\n";
    $sqlContent = file_get_contents(__DIR__ . '/../AssamiAPP/AssamiAPP/DB/db_assami.sql');
    // Extract insert statements for ms_rekening_belanja
    if (preg_match('/LOCK TABLES `ms_rekening_belanja` WRITE;.*?(INSERT INTO `ms_rekening_belanja` VALUES.*?;).*?UNLOCK TABLES;/s', $sqlContent, $matches)) {
        $mysqli->query("TRUNCATE TABLE ms_rekening_belanja");
        $insertSql = $matches[1];
        if ($mysqli->query($insertSql)) {
            $newCount = $mysqli->query("SELECT COUNT(*) FROM ms_rekening_belanja")->fetch_row()[0];
            echo "Successfully restored $newCount rows to ms_rekening_belanja!\n";
        } else {
            echo "Error restoring: " . $mysqli->error . "\n";
        }
    } else {
        echo "Could not find ms_rekening_belanja insert block in sql.\n";
    }
} else {
    echo "Table already has full data ($currentCount rows).\n";
}
