<?php

require_once __DIR__ . '/test_roles.php';

echo "Testing user access for 'kominfo'...\n";
$tests = [
    'dashboards/main'           => 'Dashboard Utama (Harus ALLOWED/200)',
    'spm/pengajuanspmhome'      => 'Pengajuan SPM (Harus ALLOWED/200)',
    'spm/pengajuannpdhome'      => 'Pengajuan NPD (Harus ALLOWED/200)',
    'spm/statuspengajuanhome'   => 'Status Pengajuan SPM (Harus ALLOWED/200)',
    'sipd/monitoringsipdhome'   => 'Monitoring SIPD (Harus ALLOWED/200)',
    'bpkad/persetujuanspmhome'  => 'Persetujuan BPKAD (Harus BLOCKED/302)',
    'bpkad/persetujuansp2dhome' => 'Pencairan SP2D (Harus BLOCKED/302)',
    'user/userhome'             => 'Manajemen User Admin (Harus BLOCKED/302)',
];

foreach ($tests as $url => $expected) {
    $res = testUserAccess('kominfo', '123', $url);
    $status = ($res['code'] == 200) ? 'HTTP 200 OK' : "HTTP {$res['code']} -> {$res['redirect']}";
    echo sprintf("  [%s] %-28s : %s\n", ($res['code'] == 200 && strpos($expected, 'ALLOWED') !== false) || ($res['code'] == 302 && strpos($expected, 'BLOCKED') !== false) ? 'PASS' : 'FAIL', $url, $status);
}
