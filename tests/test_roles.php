<?php

function testUserAccess($username, $password, $url) {
    $cookieFile = __DIR__ . "/cookie_{$username}.txt";
    if (file_exists($cookieFile)) unlink($cookieFile);

    // 1. Login
    $ch = curl_init('http://localhost/sipekda/public/user/validasilogin');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['CryptUSER_NAME' => $username, 'CryptPASSWORD' => $password]);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_exec($ch);

    // 2. Request URL
    $ch = curl_init('http://localhost/sipekda/public/' . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Do not auto-follow to check 302 vs 200
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);

    if (file_exists($cookieFile)) unlink($cookieFile);
    return ['code' => $httpCode, 'redirect' => $redirectUrl];
}

echo "=== Testing Role-Based Access Control (RBAC) ===\n\n";

$tests = [
    // [Role, User, Pass, Target URL, Expected Outcome]
    ['User', 'user', '123', 'user/userhome', 'BLOCKED (Admin only)'],
    ['User', 'user', '123', 'bpkad/persetujuanspmhome', 'BLOCKED (Verifikasi only)'],
    ['User', 'user', '123', 'bpkad/persetujuansp2dhome', 'BLOCKED (Persetujuan only)'],
    ['User', 'user', '123', 'spm/pengajuanspmhome', 'ALLOWED (HTTP 200)'],
    
    ['Verifikasi 1', 'verifikasi1', '123', 'user/userhome', 'BLOCKED (Admin only)'],
    ['Verifikasi 1', 'verifikasi1', '123', 'bpkad/persetujuanspmhome', 'ALLOWED (HTTP 200)'],
    ['Verifikasi 1', 'verifikasi1', '123', 'bpkad/persetujuansp2dhome', 'BLOCKED (Persetujuan only)'],
    ['Verifikasi 1', 'verifikasi1', '123', 'spm/pengajuanspmhome', 'BLOCKED (User only)'],

    ['Persetujuan', 'bpkd', '123', 'user/userhome', 'BLOCKED (Admin only)'],
    ['Persetujuan', 'bpkd', '123', 'bpkad/persetujuanspmhome', 'ALLOWED (HTTP 200)'],
    ['Persetujuan', 'bpkd', '123', 'bpkad/persetujuansp2dhome', 'ALLOWED (HTTP 200)'],
    
    ['Admin', 'admin', '123', 'user/userhome', 'ALLOWED (HTTP 200)'],
    ['Admin', 'admin', '123', 'bpkad/persetujuanspmhome', 'ALLOWED (HTTP 200)'],
    ['Admin', 'admin', '123', 'bpkad/persetujuansp2dhome', 'ALLOWED (HTTP 200)'],
    ['Admin', 'admin', '123', 'spm/pengajuanspmhome', 'ALLOWED (HTTP 200)'],
];

foreach ($tests as $t) {
    list($role, $user, $pass, $url, $expected) = $t;
    $res = testUserAccess($user, $pass, $url);
    $status = ($res['code'] == 200) ? 'HTTP 200 OK' : "HTTP {$res['code']} -> {$res['redirect']}";
    $passFail = ($res['code'] == 200 && strpos($expected, 'ALLOWED') !== false) ||
                 ($res['code'] == 302 && strpos($expected, 'BLOCKED') !== false) ? 'PASS' : 'FAIL';
    
    echo sprintf("[%s] %-12s on %-25s | Result: %-50s | Expected: %s\n", 
        $passFail, $role, $url, $status, $expected);
}

echo "\nRBAC Test Complete!\n";
