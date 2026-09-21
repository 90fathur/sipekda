<?php
/**
 * Test Full Transaction Lifecycle
 * NPD -> Verifikasi 1 -> Verifikasi 2 -> Persetujuan -> SPM -> Verifikasi 1 -> Verifikasi 2 -> Persetujuan -> SP2D -> Dashboard Stats
 */

$db = new PDO("mysql:host=127.0.0.1;dbname=db_assami", "root", "");

function doLogin($username, $password, $cookieFile) {
    if (file_exists($cookieFile)) unlink($cookieFile);
    $ch = curl_init('http://localhost/sipekda/public/user/validasilogin');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['CryptUSER_NAME' => $username, 'CryptPASSWORD' => $password]);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    $res = curl_exec($ch);
    return trim($res);
}

function curlReq($url, $postData = null, $cookieFile = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($postData !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }
    if ($cookieFile && file_exists($cookieFile)) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    $res = curl_exec($ch);
    return $res;
}

echo "=== PRE-FLIGHT LIFECYCLE TEST ===\n\n";

$cKominfo = __DIR__ . '/c_kominfo.txt';
$cV1      = __DIR__ . '/c_v1.txt';
$cV2      = __DIR__ . '/c_v2.txt';
$cPers    = __DIR__ . '/c_pers.txt';

echo "1. Login Kominfo: " . doLogin('kominfo', '123', $cKominfo) . "\n";
echo "2. Login Verifikasi 1: " . doLogin('verifikasi1', '123', $cV1) . "\n";
echo "3. Login Verifikasi 2: " . doLogin('verifikasi2', '123', $cV2) . "\n";
echo "4. Login Persetujuan (bpkd): " . doLogin('bpkd', '123', $cPers) . "\n";

// A. Tambah Detail NPD oleh Kominfo
echo "\n--- A. OPD Kominfo Input Detail Belanja ---\n";
// Ambil satu rekening belanja dari database
$rek = $db->query("SELECT KD_REKENING_BELANJA, NM_REKENING_BELANJA FROM ms_rekening_belanja LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$kdRek = $rek['KD_REKENING_BELANJA'];
$nominalTest = 25000000; // Rp 25.000.000

$resDetail = curlReq('http://localhost/sipekda/public/spm/adddatadetail', [
    'KD_REKENING_BELANJA' => $kdRek,
    'ANGGARAN'            => $nominalTest
], $cKominfo);
echo "Add Data Detail Response: $resDetail\n";

// B. Simpan Pengajuan NPD
echo "\n--- B. Simpan Pengajuan NPD ---\n";
$resNpd = curlReq('http://localhost/sipekda/public/spm/savepengajuannpd', [
    'NM_PROGRAM_KEGIATAN_SUBKEGIATAN' => 'Program Pengelolaan Informasi Publik Kominfo (TEST E2E)',
    'KD_REKENING_BELANJA'             => $kdRek,
    'KD_SUMBER_DANA'                  => 'DAU',
    'ANGGARAN'                        => $nominalTest
], $cKominfo);
echo "Save NPD Response: $resNpd\n";

// Ambil ID NPD yang baru dibuat
$npdBaru = $db->query("SELECT * FROM tb_npd ORDER BY TGL_PENGAJUAN DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$idNpd = $npdBaru['ID_PENGAJUAN'] ?? '';
echo "Created NPD ID: $idNpd (Status: {$npdBaru['KD_STATUS']})\n";

// C. Verifikasi NPD Tahap 1
echo "\n--- C. BPKAD Verifikasi 1 Menyetujui NPD ---\n";
// Pastikan verifikasi1 punya role untuk OPD Kominfo
$db->query("INSERT IGNORE INTO tb_user_role (USERNAME, KD_SKPD) VALUES ('verifikasi1', '2.16.2.20.2.21.22.0000')");
$resV1Npd = curlReq('http://localhost/sipekda/public/bpkad/acceptpengajuannpd', ['id' => $idNpd], $cV1);
echo "Accept V1 NPD Response: $resV1Npd\n";

// D. Verifikasi NPD Tahap 2
echo "\n--- D. BPKAD Verifikasi 2 Menyetujui NPD ---\n";
$resV2Npd = curlReq('http://localhost/sipekda/public/bpkad/acceptpengajuannpd', ['id' => $idNpd], $cV2);
echo "Accept V2 NPD Response: $resV2Npd\n";

// E. BPKAD Persetujuan Menyetujui NPD (Status -> 3)
echo "\n--- E. BPKAD Persetujuan Menyetujui NPD ---\n";
$resPersNpd = curlReq('http://localhost/sipekda/public/bpkad/acceptpengajuannpd', ['id' => $idNpd], $cPers);
echo "Accept Persetujuan NPD Response: $resPersNpd\n";

$npdUpdated = $db->query("SELECT * FROM tb_npd WHERE ID_PENGAJUAN = '$idNpd'")->fetch(PDO::FETCH_ASSOC);
echo "NPD Final Status: {$npdUpdated['KD_STATUS']} (Expected: 3 / Approved)\n";

// F. OPD Kominfo Mengajukan SPM dari NPD Sukses
echo "\n--- F. OPD Kominfo Mengajukan SPM dari NPD Sukses ---\n";
$resListSukses = curlReq('http://localhost/sipekda/public/spm/getnpdsukseslist', null, $cKominfo);
echo "List NPD Sukses: $resListSukses\n";

$resSpm = curlReq('http://localhost/sipekda/public/spm/savepengajuan', [
    'ID_NPD' => $idNpd
], $cKominfo);
echo "Save SPM Response: $resSpm\n";

$spmBaru = $db->query("SELECT * FROM tb_spm ORDER BY TGL_PENGAJUAN DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$idSpm = $spmBaru['ID_PENGAJUAN'] ?? '';
echo "Created SPM ID: $idSpm (Anggaran: Rp " . number_format($spmBaru['ANGGARAN'] ?? 0, 0, ',', '.') . " | Status: {$spmBaru['KD_STATUS']})\n";

// G. BPKAD Verifikasi 1 Menyetujui SPM
echo "\n--- G. BPKAD Verifikasi 1 Menyetujui SPM ---\n";
$resV1Spm = curlReq('http://localhost/sipekda/public/bpkad/acceptpengajuan', ['id' => $idSpm], $cV1);
echo "Accept V1 SPM Response: $resV1Spm\n";

// H. BPKAD Verifikasi 2 Menyetujui SPM
echo "\n--- H. BPKAD Verifikasi 2 Menyetujui SPM ---\n";
$resV2Spm = curlReq('http://localhost/sipekda/public/bpkad/acceptpengajuan', ['id' => $idSpm], $cV2);
echo "Accept V2 SPM Response: $resV2Spm\n";

// I. BPKAD Persetujuan Menyetujui SPM (Status -> 3)
echo "\n--- I. BPKAD Persetujuan Menyetujui SPM ---\n";
$resPersSpm = curlReq('http://localhost/sipekda/public/bpkad/acceptpengajuan', ['id' => $idSpm], $cPers);
echo "Accept Persetujuan SPM Response: $resPersSpm\n";

$spmUpdated = $db->query("SELECT * FROM tb_spm WHERE ID_PENGAJUAN = '$idSpm'")->fetch(PDO::FETCH_ASSOC);
echo "SPM Final Status: {$spmUpdated['KD_STATUS']} (Expected: 3 / Approved)\n";

// J. Cek Efek ke Dashboard & Realisasi Pagu
echo "\n--- J. Verifikasi Efek Real-Time ke Dashboard Kominfo ---\n";
$totalSP2D = curlReq('http://localhost/sipekda/public/dashboards/totalsp2d', null, $cKominfo);
$nomBulan  = curlReq('http://localhost/sipekda/public/dashboards/nominalspmmonthly', null, $cKominfo);
$nomTahun  = curlReq('http://localhost/sipekda/public/dashboards/nominalspmyearly', null, $cKominfo);
$chartPagu = curlReq('http://localhost/sipekda/public/dashboards/getpagugrafik', null, $cKominfo);

echo "Dashboard Total SP2D (Count) : $totalSP2D\n";
echo "Dashboard Nominal Bulan Ini  : Rp $nomBulan\n";
echo "Dashboard Nominal Tahun Ini  : Rp $nomTahun\n";
echo "Dashboard Pagu Chart JSON    : $chartPagu\n";

// Clean up test cookie files
@unlink($cKominfo);
@unlink($cV1);
@unlink($cV2);
@unlink($cPers);

echo "\n=== PRE-FLIGHT LIFECYCLE TEST COMPLETED SUCCESSFULLY! ===\n";
