<?php

$cookieFile = __DIR__ . '/test_cookie.txt';

echo "1. Logging in as admin...\n";
$ch = curl_init('http://localhost/sipekda/public/user/validasilogin');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['CryptUSER_NAME' => 'admin', 'CryptPASSWORD' => '123']);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
$res = curl_exec($ch);
echo "Login response: $res\n\n";

echo "2. Testing /sipd/getstatussipd?LIMIT=3...\n";
$start = microtime(true);
$ch = curl_init('http://localhost/sipekda/public/sipd/getstatussipd?LIMIT=3');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$dur = round(microtime(true) - $start, 3);
echo "HTTP Status : $httpCode (Duration: {$dur}s)\n";
echo "Response Length: " . strlen($res) . " bytes\n";

$data = json_decode($res, true);
if (is_array($data)) {
    echo "Found " . count($data) . " items returned to front-end:\n";
    foreach (array_slice($data, 0, 3) as $idx => $row) {
        echo "  Item #" . ($idx + 1) . ":\n";
        echo "    NO_SP2D       : " . ($row['NO_SP2D'] ?? '-') . "\n";
        echo "    OPD (NOTE)    : " . ($row['NOTE'] ?? '-') . "\n";
        echo "    NOMINAL_SP2D  : Rp " . number_format($row['NOMINAL_SP2D'] ?? 0, 0, ',', '.') . "\n";
        echo "    AMOUNT        : Rp " . number_format($row['AMOUNT'] ?? 0, 0, ',', '.') . "\n";
        echo "    BANK_NAME     : " . ($row['RE_ACCOUNT_BANK_NAME'] ?? '-') . "\n";
    }
} else {
    echo "Raw response: " . substr($res, 0, 300) . "\n";
}
