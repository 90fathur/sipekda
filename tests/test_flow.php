<?php

function testLoginAndEndpoints() {
    $cookieFile = __DIR__ . '/cookie.txt';
    if (file_exists($cookieFile)) unlink($cookieFile);

    echo "1. Testing Login as 'admin'...\n";
    $ch = curl_init('http://localhost/sipekda/public/user/validasilogin');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['username' => 'admin', 'password' => '123']));
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "HTTP Status: " . $httpCode . "\n";
    echo "Login Response: " . trim($res) . "\n";
    if (strpos($res, 'http') === false) {
        echo "FAILED: Login failed!\n";
        return;
    }
    echo "SUCCESS: Logged in! Redirect target: " . trim($res) . "\n";

    $endpoints = [
        'user/userhome',
        'user/getlistuser',
        'dashboards/main',
        'dashboards/totalsp2d',
        'dashboards/nominalspmmonthly',
        'dashboards/nominalspmyearly',
        'dashboards/getpagugrafik',
        'dashboards/getdatagrafik',
        'dashboards/top5belanjachart',
        'dashboards/rincianrealisasianggaran',
        'spm/pengajuanspmhome',
        'spm/pengajuannpdhome',
        'spm/statuspengajuanhome',
        'spm/statuspengajuannpdhome',
        'spm/monitoringhome',
        'bpkad/persetujuanspmhome',
        'bpkad/persetujuannpdhome',
        'bpkad/persetujuansp2dhome',
        'pagu/monitoringpaguhome',
        'pagu/getpaguanggaran',
        'sipd/monitoringsipdhome',
        'monitoring/daftartransaksi',
        'monitoring/daftarrtgs'
    ];

    echo "\n2. Testing Protected Endpoints...\n";
    foreach ($endpoints as $ep) {
        $ch = curl_init('http://localhost/sipekda/public/' . $ep);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        $content = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        echo sprintf("Endpoint [%-35s] -> HTTP %d (Length: %d)\n", $ep, $code, strlen($content));
        if ($code >= 400) {
            echo "ERROR: Unexpected status code for $ep\n";
        }
    }

    $roles = [
        'user'        => 'dashboards/main',
        'verifikasi1' => 'dashboards/main',
        'verifikasi2' => 'dashboards/main',
        'bpkd'        => 'dashboards/main'
    ];

    echo "\n3. Testing Login for other roles...\n";
    foreach ($roles as $u => $expectedTarget) {
        $cf = __DIR__ . "/cookie_{$u}.txt";
        if (file_exists($cf)) unlink($cf);
        $ch = curl_init('http://localhost/sipekda/public/user/validasilogin');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['username' => $u, 'password' => '123']));
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cf);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cf);
        $res = curl_exec($ch);
        echo sprintf("User [%-12s] -> Target: %s\n", $u, trim($res));
        if (file_exists($cf)) unlink($cf);
    }

    if (file_exists($cookieFile)) unlink($cookieFile);
    echo "\nAll Tests Complete!\n";
}

testLoginAndEndpoints();
