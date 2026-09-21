<?php
$cookieFile = __DIR__ . '/cookie_kominfo_test.txt';

echo "1. Logging in as kominfo...\n";
$ch = curl_init('http://localhost/sipekda/public/user/validasilogin');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['CryptUSER_NAME' => 'kominfo', 'CryptPASSWORD' => '123']);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
$res = curl_exec($ch);
echo "Login response: $res\n\n";

$tests = [
    'Default (no date)' => 'http://localhost/sipekda/public/sipd/getstatussipd?LIMIT=100',
    '30 Days (2026-08-21 to 2026-09-20)' => 'http://localhost/sipekda/public/sipd/getstatussipd?LIMIT=100&TMP_TGL=2026-08-21|2026-09-20',
    'Full Year 2026' => 'http://localhost/sipekda/public/sipd/getstatussipd?LIMIT=100&TMP_TGL=2026-01-01|2026-12-31',
    'LIMIT=0 (All records)' => 'http://localhost/sipekda/public/sipd/getstatussipd?LIMIT=0',
];

foreach ($tests as $title => $url) {
    echo "--- Test: $title ---\nURL: $url\n";
    $start = microtime(true);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    $res = curl_exec($ch);
    $dur = round(microtime(true) - $start, 3);
    $data = json_decode($res, true);
    if (is_array($data)) {
        echo "Found " . count($data) . " items (Time: {$dur}s)\n";
        if (count($data) > 0) {
            echo "  First item: NO_SP2D=" . ($data[0]['NO_SP2D'] ?? '') . " | OPD=" . ($data[0]['NOTE'] ?? '') . "\n";
        }
    } else {
        echo "Response error: " . substr($res, 0, 200) . "\n";
    }
    echo "\n";
}
