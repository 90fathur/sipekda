<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=db_assami', 'root', '');
echo "--- DAFTAR MENU DARI TB_MENU_ITEMS (DATABASE ASLI) ---\n";
$stmt = $db->query('SELECT * FROM tb_menu_items');
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($menus as $m) {
    echo "KD_MENU: {$m['KD_MENU']} | NM_MENU: {$m['NM_MENU']} | Controller: " . ($m['NM_CONTROLLER'] ?? '-') . " | Action: " . ($m['NM_ACTION'] ?? '-') . "\n";
}


