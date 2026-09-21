<?php
$sql = file_get_contents("AssamiAPP/AssamiAPP/DB/db_assami.sql");
preg_match_all('/INSERT INTO [`"]?([a-zA-Z0-9_]+)[`"]?/i', $sql, $matches);
$tables = array_count_values($matches[1]);
echo "Tables in dump:\n";
foreach ($tables as $t => $cnt) {
    echo "  $t : $cnt INSERT statements\n";
}
