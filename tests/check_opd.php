<?php
$db = new PDO("mysql:host=127.0.0.1;dbname=db_assami", "root", "");

echo "--- 1. Pencarian Dinas Komunikasi / Kominfo di ms_skpd ---\n";
$stmt = $db->query("SELECT KD_SKPD, NM_SKPD FROM ms_skpd WHERE NM_SKPD LIKE '%kominfo%' OR NM_SKPD LIKE '%komunikasi%' OR NM_SKPD LIKE '%informatika%'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($rows) {
    foreach ($rows as $r) {
        echo "  [{$r['KD_SKPD']}] {$r['NM_SKPD']}\n";
    }
} else {
    echo "  Tidak ditemukan nama kominfo persis. Menampilkan 10 OPD pertama:\n";
    $all = $db->query("SELECT KD_SKPD, NM_SKPD FROM ms_skpd LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($all as $r) {
        echo "  [{$r['KD_SKPD']}] {$r['NM_SKPD']}\n";
    }
}

echo "\n--- 2. Daftar Pengguna OPD (Role: 'User') di tb_users ---\n";
$stmt = $db->query("
    SELECT u.ID_USER, u.USER_NAME, u.NAMA_LENGKAP, u.JENIS_USER, u.KD_UNITKER, s.NM_SKPD 
    FROM tb_users u
    LEFT JOIN ms_skpd s ON u.KD_UNITKER = s.KD_SKPD
    WHERE u.JENIS_USER = 'User'
");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $u) {
    echo "  Username    : {$u['USER_NAME']}\n";
    echo "  Nama        : {$u['NAMA_LENGKAP']}\n";
    echo "  Unit/OPD    : [{$u['KD_UNITKER']}] " . ($u['NM_SKPD'] ?? 'Belum Ditautkan') . "\n\n";
}
