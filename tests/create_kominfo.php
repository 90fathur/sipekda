<?php
$db = new PDO("mysql:host=127.0.0.1;dbname=db_assami", "root", "");

$check = $db->query("SELECT * FROM tb_users WHERE USER_NAME = 'kominfo'")->fetch(PDO::FETCH_ASSOC);

if (!$check) {
    $hash123 = strtolower(hash('sha512', '123'));
    $stmt = $db->prepare("
        INSERT INTO tb_users (USER_NAME, NAMA_LENGKAP, PASSWORD, JENIS_USER, KD_UNITKER, AKTIF, STS_BLOCK)
        VALUES (:user, :nama, :pass, 'User', :kd_skpd, 1, 0)
    ");
    $stmt->execute([
        'user'    => 'kominfo',
        'nama'    => 'Operator Kominfo Polman',
        'pass'    => $hash123,
        'kd_skpd' => '2.16.2.20.2.21.22.0000'
    ]);
    echo "Akun 'kominfo' berhasil dibuat!\n";
} else {
    echo "Akun 'kominfo' sudah ada.\n";
}

$u = $db->query("
    SELECT u.ID_USER, u.USER_NAME, u.NAMA_LENGKAP, u.JENIS_USER, u.KD_UNITKER, s.NM_SKPD
    FROM tb_users u
    LEFT JOIN ms_skpd s ON u.KD_UNITKER = s.KD_SKPD
    WHERE u.USER_NAME = 'kominfo'
")->fetch(PDO::FETCH_ASSOC);

print_r($u);
