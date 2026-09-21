<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// 1. Create a dummy test excel file
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'KD_REKENING_BELANJA');
$sheet->setCellValue('B1', 'NM_REKENING_BELANJA');
$sheet->setCellValue('C1', 'PAGU');

$sheet->setCellValue('A2', '5.1.02.01.01.0024');
$sheet->setCellValue('B2', 'Belanja Alat/Bahan untuk Kegiatan Kantor- Alat Tulis Kantor');
$sheet->setCellValue('C2', '50000000');

$sheet->setCellValue('A3', '5.1.02.01.01.0025');
$sheet->setCellValue('B3', 'Belanja Kertas dan Cover');
$sheet->setCellValue('C3', '25000000');

$excelPath = __DIR__ . '/test_pagu.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($excelPath);
echo "1. Created test Excel: $excelPath (" . filesize($excelPath) . " bytes)\n";

// 2. Test import via curl
$cookieFile = __DIR__ . '/cookie_admin.txt';
$ch = curl_init('http://localhost/sipekda/public/user/validasilogin');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['username' => 'admin', 'password' => '123']));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_exec($ch);

$ch = curl_init('http://localhost/sipekda/public/pagu/import');
$cfile = new CURLFile($excelPath, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'test_pagu.xlsx');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['excelFile' => $cfile]);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "2. Import Response Code: $httpCode\n";
echo "Response Length: " . strlen($res) . "\n";
echo "Response Preview: " . substr($res, 0, 200) . "\n";

// Cleanup
if (file_exists($excelPath)) unlink($excelPath);
if (file_exists($cookieFile)) unlink($cookieFile);
echo "\nImport Test Finished Successfully!\n";
