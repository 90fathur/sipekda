<?php

$db = new PDO('mysql:host=127.0.0.1;dbname=db_assami', 'root', '');
$stmt = $db->query('SELECT ID_USER, USER_NAME, NAMA_LENGKAP, JENIS_USER, PASSWORD, AKTIF, STS_BLOCK FROM tb_users');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total users in database: " . count($users) . "\n";
$hash123 = strtolower(hash('sha512', '123'));
echo "SHA-512 of '123': " . substr($hash123, 0, 30) . "...\n\n";

foreach ($users as $u) {
    $matches123 = ($u['PASSWORD'] === $hash123) ? 'MATCH "123"' : 'DIFFERENT HASH';
    echo sprintf("[%-15s] Nama: %-25s Role: %-15s Aktif: %s -> %s\n", 
        $u['USER_NAME'], 
        substr($u['NAMA_LENGKAP'], 0, 25), 
        $u['JENIS_USER'], 
        $u['AKTIF'], 
        $matches123
    );
}
