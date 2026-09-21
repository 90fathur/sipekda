<?php

echo "Testing Bank Sulselbar API Endpoints...\n\n";

$endpoints = [
    'LIMIT=0' => 'https://apidev.banksulselbar.co.id/api/v1/getTransaksi?KODE_WILAYAH=76.04&LIMIT=0&PROSES=1&CODE=000',
    'LIMIT=50' => 'https://apidev.banksulselbar.co.id/api/v1/getTransaksi?KODE_WILAYAH=76.04&LIMIT=50&PROSES=1&CODE=000',
];

foreach ($endpoints as $name => $url) {
    echo "--- Testing $name ---\nURL: $url\n";
    $start = microtime(true);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    $time = round(microtime(true) - $start, 3);
    curl_close($ch);

    echo "HTTP Status : $httpCode (Time: {$time}s)\n";
    if ($err) {
        echo "cURL Error  : $err\n";
    }
    echo "Response    : " . (strlen($response) > 500 ? substr($response, 0, 500) . '... (truncated)' : $response) . "\n\n";
}
