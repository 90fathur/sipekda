<?php

namespace App\Controllers;

use App\Models\RekeningBelanjaModel;
use App\Models\SpmModel;
use App\Models\DataDetailModel;

class Pagu extends BaseController
{
    protected RekeningBelanjaModel $rekeningModel;
    protected SpmModel $spmModel;
    protected DataDetailModel $detailModel;

    public function __construct()
    {
        $this->rekeningModel = new RekeningBelanjaModel();
        $this->spmModel = new SpmModel();
        $this->detailModel = new DataDetailModel();
        helper(['url', 'form', 'menu']);
    }

    public function monitoringPaguHome()
    {
        $session = session();
        $loginData = $session->get('LoginData');
        if (empty($loginData)) {
            return redirect()->to(base_url('user/login'));
        }

        $data = [
            'Header'       => 'MONITORING PAGU ANGGARAN',
            'Title'        => 'Monitoring Pagu Anggaran',
            'Keterangan'   => 'Daftar seluruh pagu anggaran untuk setiap rekening belanja.',
            'NAMA_LENGKAP' => $loginData['NAMA_LENGKAP'],
            'NM_UNITKER'   => $loginData['NM_UNITKER'],
            'LAST_LOGIN'   => '[' . date('d-m-Y H:i:s', strtotime($loginData['LAST_LOGIN'] ?? 'now')) . ']'
        ];

        return view('pagu/monitoring_pagu', $data);
    }

    public function getPaguAnggaran()
    {
        $list = $this->rekeningModel->findAll();
        $currentYear = date('Y');

        $db = \Config\Database::connect();
        $approvedSpm = $db->table('tb_spm')
            ->where('KD_STATUS', 3)
            ->where('YEAR(TGL_PENGAJUAN)', $currentYear)
            ->get()
            ->getResultArray();

        $usage = [];
        $allSpmIds = [];
        $allNpdIdsFromSpm = [];

        foreach ($approvedSpm as $spm) {
            if (!empty($spm['ID_PENGAJUAN'])) {
                $allSpmIds[] = $spm['ID_PENGAJUAN'];
            }
            if (!empty($spm['ID_NPD'])) {
                $allNpdIdsFromSpm[] = $spm['ID_NPD'];
            }
            if (empty($spm['ID_NPD']) && !empty($spm['KD_REKENING_BELANJA'])) {
                $kd = $spm['KD_REKENING_BELANJA'];
                $usage[$kd] = ($usage[$kd] ?? 0) + (float)$spm['ANGGARAN'];
            }
        }

        $allTrackedIds = array_unique(array_merge($allSpmIds, $allNpdIdsFromSpm));
        if (!empty($allTrackedIds)) {
            $details = $db->table('tb_data_detail')
                ->whereIn('NO_NPD_SPM', $allTrackedIds)
                ->where('KD_REKENING_BELANJA IS NOT NULL')
                ->get()
                ->getResultArray();

            foreach ($details as $d) {
                $kd = $d['KD_REKENING_BELANJA'];
                $usage[$kd] = ($usage[$kd] ?? 0) + (float)$d['ANGGARAN'];
            }
        }

        foreach ($list as &$item) {
            $kd = $item['KD_REKENING_BELANJA'];
            $used = (float)($usage[$kd] ?? 0);
            $pagu = (float)($item['PAGU'] ?? 0);
            $sisa = max(0, $pagu - $used);
            $persen = $pagu > 0 ? round(($used / $pagu) * 100, 2) : 0;

            $item['PAGU'] = $pagu;
            $item['REALISASI'] = $used;
            $item['SISA_PAGU'] = $sisa;
            $item['PERSEN'] = $persen;
            $item['PAGU_FORMAT'] = number_format($pagu, 2, ',', '.');
            $item['REALISASI_FORMAT'] = number_format($used, 2, ',', '.');
            $item['SISA_PAGU_FORMAT'] = number_format($sisa, 2, ',', '.');
        }

        return $this->response->setJSON($list);
    }

    public function import()
    {
        $file = $this->request->getFile('excelFile');
        if (!$file || !$file->isValid()) {
            return $this->response->setBody('80');
        }

        try {
            $ext = strtolower($file->getClientExtension() ?: $file->getExtension());
            if (!in_array($ext, ['xls', 'xlsx', 'csv'])) {
                return $this->response->setBody('80');
            }

            $rows = [];

            if ($ext === 'csv') {
                $handle = fopen($file->getTempName(), 'r');
                $header = fgetcsv($handle);
                while (($data = fgetcsv($handle)) !== false) {
                    if (count($data) >= 3) {
                        $kd = trim($data[0]);
                        $nm = trim($data[1]);
                        $pagu = (float)str_replace(['.', ',', ' '], ['', '.', ''], $data[2]);
                        if (!empty($kd)) {
                            $rows[] = [
                                'KD_REKENING_BELANJA' => $kd,
                                'NM_REKENING_BELANJA' => $nm,
                                'PAGU'                => $pagu
                            ];
                        }
                    }
                }
                fclose($handle);
            } else {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
                $worksheet = $spreadsheet->getActiveSheet();
                $sheetData = $worksheet->toArray(null, true, true, true);

                // Check header in row 1
                $firstRow = reset($sheetData);
                $isHeader = true;

                $colKd = 'A';
                $colNm = 'B';
                $colPagu = 'C';

                // Detect headers if named
                if ($firstRow) {
                    foreach ($firstRow as $colLetter => $val) {
                        $valClean = strtoupper(trim((string)$val));
                        if (str_contains($valClean, 'KD_REKENING') || str_contains($valClean, 'KODE')) {
                            $colKd = $colLetter;
                        } elseif (str_contains($valClean, 'NM_REKENING') || str_contains($valClean, 'NAMA')) {
                            $colNm = $colLetter;
                        } elseif (str_contains($valClean, 'PAGU')) {
                            $colPagu = $colLetter;
                        }
                    }
                }

                $rowIndex = 0;
                foreach ($sheetData as $row) {
                    $rowIndex++;
                    if ($rowIndex === 1) {
                        // Skip header row if it contains text headers
                        $firstVal = strtoupper(trim((string)($row[$colKd] ?? '')));
                        if (str_contains($firstVal, 'KD') || str_contains($firstVal, 'KODE') || str_contains($firstVal, 'REKENING')) {
                            continue;
                        }
                    }

                    $kd = trim((string)($row[$colKd] ?? ''));
                    $nm = trim((string)($row[$colNm] ?? ''));
                    $paguRaw = trim((string)($row[$colPagu] ?? '0'));
                    $pagu = (float)str_replace(['.', ',', ' '], ['', '.', ''], $paguRaw);

                    if (!empty($kd)) {
                        $rows[] = [
                            'KD_REKENING_BELANJA' => $kd,
                            'NM_REKENING_BELANJA' => $nm,
                            'PAGU'                => $pagu
                        ];
                    }
                }
            }

            if (!empty($rows)) {
                $this->rekeningModel->truncate();
                $this->rekeningModel->insertBatch($rows);
            }

            return $this->response->setJSON($this->rekeningModel->findAll());
        } catch (\Exception $e) {
            return $this->response->setBody('90');
        }
    }
}
