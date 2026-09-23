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

        $db = \Config\Database::connect();
        $listSkpd = $db->table('ms_skpd')->orderBy('NM_SKPD', 'ASC')->get()->getResultArray();

        $data = [
            'Header'       => 'MONITORING PAGU ANGGARAN',
            'Title'        => 'Monitoring Pagu Anggaran',
            'Keterangan'   => 'Daftar pagu anggaran dan realisasi belanja per OPD / rekening belanja.',
            'NAMA_LENGKAP' => $loginData['NAMA_LENGKAP'],
            'NM_UNITKER'   => $loginData['NM_UNITKER'],
            'JENIS_USER'   => $loginData['JENIS_USER'],
            'KD_UNITKER'   => $loginData['KD_UNITKER'] ?? '',
            'ListSKPD'     => $listSkpd,
            'LAST_LOGIN'   => '[' . date('d-m-Y H:i:s', strtotime($loginData['LAST_LOGIN'] ?? 'now')) . ']'
        ];

        return view('pagu/monitoring_pagu', $data);
    }

    public function getPaguAnggaran()
    {
        $session = session();
        $loginData = $session->get('LoginData');
        if (!$loginData) {
            return $this->response->setJSON([]);
        }

        $kdSkpdParam = $this->request->getGet('KD_SKPD');

        $builder = $this->rekeningModel->builder();

        // If user is OPD ('User'), lock to their own KD_UNITKER
        if ($loginData['JENIS_USER'] === 'User' && !empty($loginData['KD_UNITKER'])) {
            $builder->where('KD_SKPD', $loginData['KD_UNITKER']);
        } elseif (!empty($kdSkpdParam)) {
            $builder->where('KD_SKPD', $kdSkpdParam);
        }

        $list = $builder->orderBy('KD_SKPD', 'ASC')
            ->orderBy('LENGTH(KD_REKENING_BELANJA)', 'ASC')
            ->orderBy('KD_REKENING_BELANJA', 'ASC')
            ->get()->getResultArray();
        $currentYear = date('Y');

        $db = \Config\Database::connect();
        
        // Track usage in approved SPM (KD_STATUS in [3, 4]) in current year
        $spmQuery = $db->table('tb_spm')
            ->whereIn('KD_STATUS', [3, 4])
            ->where('YEAR(TGL_PENGAJUAN)', $currentYear);

        if ($loginData['JENIS_USER'] === 'User' && !empty($loginData['KD_UNITKER'])) {
            $spmQuery->where('KD_SKPD', $loginData['KD_UNITKER']);
        } elseif (!empty($kdSkpdParam)) {
            $spmQuery->where('KD_SKPD', $kdSkpdParam);
        }

        $approvedSpm = $spmQuery->get()->getResultArray();

        $usage = [];
        $allSpmIds = [];
        $allNpdIdsFromSpm = [];

        foreach ($approvedSpm as $spm) {
            $skpdKey = $spm['KD_SKPD'] ?? '';
            if (!empty($spm['ID_PENGAJUAN'])) {
                $allSpmIds[] = $spm['ID_PENGAJUAN'];
            }
            if (!empty($spm['ID_NPD'])) {
                $allNpdIdsFromSpm[] = $spm['ID_NPD'];
            }
            if (empty($spm['ID_NPD']) && !empty($spm['KD_REKENING_BELANJA'])) {
                $kd = $spm['KD_REKENING_BELANJA'];
                $key = $skpdKey . '_' . $kd;
                $usage[$key] = ($usage[$key] ?? 0) + (float)$spm['ANGGARAN'];
                $usage[$kd] = ($usage[$kd] ?? 0) + (float)$spm['ANGGARAN'];
            }
        }

        $allTrackedIds = array_unique(array_merge($allSpmIds, $allNpdIdsFromSpm));
        if (!empty($allTrackedIds)) {
            $details = $db->table('tb_data_detail d')
                ->select('d.*, COALESCE(s.KD_SKPD, n.KD_SKPD) as KD_SKPD')
                ->join('tb_spm s', 's.ID_PENGAJUAN = d.NO_NPD_SPM', 'left')
                ->join('tb_npd n', 'n.ID_PENGAJUAN = d.NO_NPD_SPM', 'left')
                ->whereIn('d.NO_NPD_SPM', $allTrackedIds)
                ->where('d.KD_REKENING_BELANJA IS NOT NULL')
                ->get()
                ->getResultArray();

            foreach ($details as $d) {
                $kd = $d['KD_REKENING_BELANJA'];
                $skpdKey = $d['KD_SKPD'] ?? '';
                $key = $skpdKey . '_' . $kd;
                $usage[$key] = ($usage[$key] ?? 0) + (float)$d['ANGGARAN'];
                $usage[$kd] = ($usage[$kd] ?? 0) + (float)$d['ANGGARAN'];
            }
        }

        foreach ($list as &$item) {
            $kd = $item['KD_REKENING_BELANJA'];
            $skpdKey = $item['KD_SKPD'] ?? '';
            $key = $skpdKey . '_' . $kd;
            $used = !empty($skpdKey) && isset($usage[$key]) ? (float)$usage[$key] : (float)($usage[$kd] ?? 0);
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

            $db = \Config\Database::connect();
            $rows = [];

            // Helper to clean and parse float
            $parsePagu = function ($val, $fmtVal = null) {
                if (is_numeric($val)) {
                    return (float)$val;
                }
                $str = trim((string)($fmtVal ?: $val));
                $clean = preg_replace('/[^\d.,]/', '', $str);
                if (empty($clean)) return 0.0;
                // US style: 121,751,406,880 or 121,751.50
                if (preg_match('/^\d{1,3}(,\d{3})+(\.\d+)?$/', $clean)) {
                    return (float)str_replace(',', '', $clean);
                }
                // ID style: 121.751.406.880 or 121.751,50
                if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $clean)) {
                    return (float)str_replace(['.', ','], ['', '.'], $clean);
                }
                return (float)str_replace(',', '.', preg_replace('/[^\d,.]/', '', $clean));
            };

            // Lookup SKPD map (NM_SKPD => KD_SKPD and vice versa)
            $skpdList = $db->table('ms_skpd')->get()->getResultArray();
            $skpdMapByName = [];
            $skpdMapByCode = [];
            foreach ($skpdList as $s) {
                $skpdMapByCode[$s['KD_SKPD']] = $s['NM_SKPD'];
                $skpdMapByName[strtoupper(trim($s['NM_SKPD']))] = $s['KD_SKPD'];
            }

            if ($ext === 'csv') {
                $handle = fopen($file->getTempName(), 'r');
                $firstRow = fgetcsv($handle);
                $is5Col = count($firstRow) >= 5;
                while (($data = fgetcsv($handle)) !== false) {
                    if ($is5Col && count($data) >= 5) {
                        $kdSkpd = trim($data[0]);
                        $nmSkpd = trim($data[1]);
                        $kdRek  = trim($data[2]);
                        $nmRek  = trim($data[3]);
                        $pagu   = $parsePagu($data[4]);
                    } elseif (count($data) >= 3) {
                        $kdSkpd = null;
                        $nmSkpd = null;
                        $kdRek  = trim($data[0]);
                        $nmRek  = trim($data[1]);
                        $pagu   = $parsePagu($data[2]);
                    } else {
                        continue;
                    }
                    if (!empty($kdRek)) {
                        $rows[] = [
                            'KD_SKPD'             => $kdSkpd,
                            'NM_SKPD'             => $nmSkpd,
                            'KD_REKENING_BELANJA' => $kdRek,
                            'NM_REKENING_BELANJA' => $nmRek,
                            'PAGU'                => $pagu
                        ];
                    }
                }
                fclose($handle);
            } else {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
                $worksheet = $spreadsheet->getActiveSheet();
                $highestRow = $worksheet->getHighestRow();
                $highestCol = $worksheet->getHighestColumn();

                $colKdSkpd = null;
                $colNmSkpd = null;
                $colKdRek = null;
                $colNmRek = null;
                $colPagu = null;

                $headerRow = $worksheet->rangeToArray("A1:{$highestCol}1", null, true, true, true)[1] ?? [];
                foreach ($headerRow as $colLetter => $val) {
                    $valClean = strtoupper(trim((string)$val));
                    if (str_contains($valClean, 'SKPD') && (str_contains($valClean, 'KD') || str_contains($valClean, 'KODE'))) {
                        $colKdSkpd = $colLetter;
                    } elseif (str_contains($valClean, 'SKPD') && (str_contains($valClean, 'NM') || str_contains($valClean, 'NAMA'))) {
                        $colNmSkpd = $colLetter;
                    } elseif (str_contains($valClean, 'REKENING') && (str_contains($valClean, 'KD') || str_contains($valClean, 'KODE'))) {
                        $colKdRek = $colLetter;
                    } elseif (str_contains($valClean, 'REKENING') && (str_contains($valClean, 'NM') || str_contains($valClean, 'NAMA'))) {
                        $colNmRek = $colLetter;
                    } elseif (str_contains($valClean, 'PAGU') || str_contains($valClean, 'ANGGARAN')) {
                        $colPagu = $colLetter;
                    }
                }

                // Fallbacks if header names are slightly different
                if (!$colKdRek) {
                    if ($colKdSkpd && $colKdSkpd === 'A') {
                        $colKdRek = 'C';
                        $colNmRek = $colNmRek ?: 'D';
                        $colPagu  = $colPagu ?: 'E';
                    } else {
                        $colKdRek = 'A';
                        $colNmRek = $colNmRek ?: 'B';
                        $colPagu  = $colPagu ?: 'C';
                    }
                }

                for ($r = 2; $r <= $highestRow; $r++) {
                    $cellKdRek = $worksheet->getCell($colKdRek . $r);
                    $kdRek = trim((string)$cellKdRek->getValue());
                    if (empty($kdRek)) {
                        continue;
                    }

                    $cellNmRek = $colNmRek ? $worksheet->getCell($colNmRek . $r) : null;
                    $nmRek = trim((string)($cellNmRek ? $cellNmRek->getValue() : ''));

                    $kdSkpd = $colKdSkpd ? trim((string)$worksheet->getCell($colKdSkpd . $r)->getValue()) : null;
                    $nmSkpd = $colNmSkpd ? trim((string)$worksheet->getCell($colNmSkpd . $r)->getValue()) : null;

                    // If nmSkpd is empty but kdSkpd exists, resolve from ms_skpd
                    if (!empty($kdSkpd) && empty($nmSkpd) && isset($skpdMapByCode[$kdSkpd])) {
                        $nmSkpd = $skpdMapByCode[$kdSkpd];
                    }
                    // If kdSkpd is empty but nmSkpd exists, resolve from ms_skpd
                    if (empty($kdSkpd) && !empty($nmSkpd) && isset($skpdMapByName[strtoupper($nmSkpd)])) {
                        $kdSkpd = $skpdMapByName[strtoupper($nmSkpd)];
                    }

                    $cellPagu = $colPagu ? $worksheet->getCell($colPagu . $r) : null;
                    $pagu = $cellPagu ? $parsePagu($cellPagu->getValue(), $cellPagu->getFormattedValue()) : 0.0;

                    $rows[] = [
                        'KD_SKPD'             => $kdSkpd,
                        'NM_SKPD'             => $nmSkpd,
                        'KD_REKENING_BELANJA' => $kdRek,
                        'NM_REKENING_BELANJA' => $nmRek,
                        'PAGU'                => $pagu
                    ];
                }
            }

            if (!empty($rows)) {
                // Upsert per (KD_SKPD + KD_REKENING_BELANJA)
                foreach ($rows as $item) {
                    $builder = $db->table('ms_rekening_belanja');
                    if (!empty($item['KD_SKPD'])) {
                        $builder->where('KD_SKPD', $item['KD_SKPD']);
                    } else {
                        $builder->where('KD_SKPD IS NULL', null, false);
                    }
                    $builder->where('KD_REKENING_BELANJA', $item['KD_REKENING_BELANJA']);
                    $existing = $builder->get()->getRowArray();

                    if ($existing) {
                        $updateData = [
                            'NM_REKENING_BELANJA' => $item['NM_REKENING_BELANJA'],
                            'PAGU'                => $item['PAGU']
                        ];
                        if (!empty($item['NM_SKPD'])) {
                            $updateData['NM_SKPD'] = $item['NM_SKPD'];
                        }
                        $db->table('ms_rekening_belanja')
                            ->where('ID_REKENING_BELANJA', $existing['ID_REKENING_BELANJA'])
                            ->update($updateData);
                    } else {
                        // Check if an existing record has the same NM_REKENING_BELANJA with legacy dummy code
                        $byNameBuilder = $db->table('ms_rekening_belanja')
                            ->where('NM_REKENING_BELANJA', $item['NM_REKENING_BELANJA']);
                        if (!empty($item['KD_SKPD'])) {
                            $byNameBuilder->groupStart()
                                ->where('KD_SKPD', $item['KD_SKPD'])
                                ->orWhere('KD_SKPD IS NULL', null, false)
                                ->groupEnd();
                        }
                        $existingByName = $byNameBuilder->get()->getRowArray();

                        if ($existingByName && (!str_contains($existingByName['KD_REKENING_BELANJA'] ?? '', '.') || empty($existingByName['KD_SKPD']))) {
                            $oldCode = $existingByName['KD_REKENING_BELANJA'];
                            $updateData = [
                                'KD_REKENING_BELANJA' => $item['KD_REKENING_BELANJA'],
                                'PAGU'                => $item['PAGU']
                            ];
                            if (!empty($item['KD_SKPD'])) {
                                $updateData['KD_SKPD'] = $item['KD_SKPD'];
                            }
                            if (!empty($item['NM_SKPD'])) {
                                $updateData['NM_SKPD'] = $item['NM_SKPD'];
                            }
                            $db->table('ms_rekening_belanja')
                                ->where('ID_REKENING_BELANJA', $existingByName['ID_REKENING_BELANJA'])
                                ->update($updateData);

                            // Sync past tb_data_detail references if any
                            if (!empty($oldCode)) {
                                $db->table('tb_data_detail')
                                    ->where('KD_REKENING_BELANJA', $oldCode)
                                    ->update(['KD_REKENING_BELANJA' => $item['KD_REKENING_BELANJA']]);
                            }
                        } else {
                            $db->table('ms_rekening_belanja')->insert($item);
                        }
                    }
                }
            }

            return $this->response->setJSON(['status' => '00', 'total' => count($rows)]);
        } catch (\Exception $e) {
            return $this->response->setBody('90');
        }
    }
}
