<?php

namespace App\Controllers;

use App\Models\SpmModel;
use App\Models\NpdModel;
use App\Models\DataDetailModel;
use App\Models\SkpdModel;
use App\Models\MataAnggaranModel;
use App\Models\RekeningBelanjaModel;
use App\Models\UserRoleModel;

class SPM extends BaseController
{
    protected SpmModel $spmModel;
    protected NpdModel $npdModel;
    protected DataDetailModel $detailModel;
    protected SkpdModel $skpdModel;
    protected MataAnggaranModel $mataAnggaranModel;
    protected RekeningBelanjaModel $rekeningModel;
    protected UserRoleModel $userRoleModel;

    public function __construct()
    {
        $this->spmModel = new SpmModel();
        $this->npdModel = new NpdModel();
        $this->detailModel = new DataDetailModel();
        $this->skpdModel = new SkpdModel();
        $this->mataAnggaranModel = new MataAnggaranModel();
        $this->rekeningModel = new RekeningBelanjaModel();
        $this->userRoleModel = new UserRoleModel();
        helper(['url', 'form', 'menu']);
    }

    protected function getLoginData(): ?array
    {
        return session()->get('LoginData');
    }

    public function pengajuanSPMHome()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return redirect()->to(base_url('user/login'));
        }

        // Clean up temp details for drafting
        $this->detailModel->where('NO_NPD_SPM', (string)$loginData['ID_USER'])->delete();

        // Clean up stale unsubmitted draft files for this user to prevent test files from attaching
        $uploadDir = FCPATH . 'uploads/pdf/';
        if (is_dir($uploadDir) && !empty($loginData['USER_NAME'])) {
            $draftPrefix = preg_replace('/[^a-zA-Z0-9_\-]/', '', $loginData['USER_NAME']) . '_';
            $oldDrafts = glob($uploadDir . $draftPrefix . '*');
            if (!empty($oldDrafts)) {
                foreach ($oldDrafts as $f) {
                    @unlink($f);
                }
            }
        }

        $data = [
            'Header'              => 'PENGAJUAN SPP/SPM',
            'Title'               => 'Users',
            'Keterangan'          => 'Form pengajuan SPP/SPM ke BPKAD.',
            'NAMA_LENGKAP'        => $loginData['NAMA_LENGKAP'],
            'NM_UNITKER'          => $loginData['NM_UNITKER'],
            'LAST_LOGIN'          => '[' . date('d-m-Y H:i:s', strtotime($loginData['LAST_LOGIN'] ?? 'now')) . ']',
            'ListSKPD'            => $this->skpdModel->orderBy('NM_SKPD', 'ASC')->findAll(),
            'ListMataAnggaran'    => $this->mataAnggaranModel->orderBy('KD_MATA_ANGGARAN', 'ASC')->findAll(),
            'ListRekeningBelanja' => $this->rekeningModel->orderBy('KD_REKENING_BELANJA', 'ASC')->findAll()
        ];

        return view('spm/pengajuan_spm', $data);
    }

    public function getNPDSuksesList()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return $this->response->setJSON([]);
        }

        $db = \Config\Database::connect();
        // NPD status = 3 or 4 (Approved by BPKAD / Persetujuan Akhir), matching current user's SKPD, and not yet in tb_spm
        $list = $db->table('tb_npd n')
            ->select('n.ID_PENGAJUAN, n.NM_PROGRAM_KEGIATAN_SUBKEGIATAN')
            ->whereIn('n.KD_STATUS', [3, 4])
            ->where('n.KD_SKPD', $loginData['KD_UNITKER'])
            ->where("NOT EXISTS (SELECT 1 FROM tb_spm s WHERE s.ID_NPD = n.ID_PENGAJUAN)", null, false)
            ->get()
            ->getResultArray();

        return $this->response->setJSON($list);
    }

    public function getViewSPM()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return $this->response->setBody('<script>window.location.href = "' . base_url('user/login') . '";</script>');
        }

        $idNpd = $this->request->getGet('ID_NPD');
        $npd = null;
        if (!empty($idNpd)) {
            $npd = $this->npdModel->where('ID_PENGAJUAN', $idNpd)->first();
        }

        $data = [
            'ListSKPD'            => $this->skpdModel->orderBy('NM_SKPD', 'ASC')->findAll(),
            'ListMataAnggaran'    => $this->mataAnggaranModel->orderBy('KD_MATA_ANGGARAN', 'ASC')->findAll(),
            'ListRekeningBelanja' => $this->getRekeningPaguList(),
            'npd'                 => $npd,
            'KD_SKPD'             => $loginData['KD_UNITKER']
        ];

        return view('spm/partials/_get_view_spm', $data);
    }

    public function savePengajuan()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return $this->response->setBody('#Silakan login terlebih dahulu.');
        }

        try {
            $idNpd = $this->request->getPost('ID_NPD');
            $npd = $this->npdModel->where('ID_PENGAJUAN', $idNpd)->first();

            if (!$npd) {
                return $this->response->setBody('#Data NPD tidak ditemukan.');
            }

            $idPengajuan = $this->spmModel->generateID();

            $spmData = [
                'ID_PENGAJUAN'                   => $idPengajuan,
                'TGL_PENGAJUAN'                  => date('Y-m-d H:i:s'),
                'KD_SKPD'                        => $loginData['KD_UNITKER'],
                'NM_PROGRAM_KEGIATAN_SUBKEGIATAN'=> $npd['NM_PROGRAM_KEGIATAN_SUBKEGIATAN'],
                'KD_REKENING_BELANJA'            => $npd['KD_REKENING_BELANJA'],
                'ANGGARAN'                       => (float)$npd['ANGGARAN'],
                'KD_SUMBER_DANA'                 => $npd['KD_SUMBER_DANA'],
                'KD_STATUS'                      => 1,
                'ID_NPD'                         => $idNpd
            ];

            $this->spmModel->insert($spmData);

            // Rename uploaded temp files
            $this->renameFiles($idPengajuan);

            // Copy/reassign details from drafting user or npd
            $draftDetails = $this->detailModel->where('NO_NPD_SPM', (string)$loginData['ID_USER'])->findAll();
            $totalDetail = 0;
            if (!empty($draftDetails)) {
                foreach ($draftDetails as $item) {
                    $kd = trim((string)($item['KD_REKENING_BELANJA'] ?? ''));
                    if (!str_starts_with($kd, '5.')) {
                        $this->detailModel->delete($item['ID_DETAIL']);
                        continue;
                    }
                    $this->detailModel->update($item['ID_DETAIL'], ['NO_NPD_SPM' => $idPengajuan]);
                    $totalDetail += (float)$item['ANGGARAN'];
                }
                $this->spmModel->where('ID_PENGAJUAN', $idPengajuan)->set(['ANGGARAN' => $totalDetail])->update();
            } else {
                // If no draft details, copy from NPD (hanya rekening SIPD 5.x)
                $npdDetails = $this->detailModel->where('NO_NPD_SPM', $idNpd)->findAll();
                foreach ($npdDetails as $d) {
                    $kd = trim((string)($d['KD_REKENING_BELANJA'] ?? ''));
                    if (!str_starts_with($kd, '5.')) {
                        continue;
                    }
                    $this->detailModel->insert([
                        'NO_NPD_SPM'          => $idPengajuan,
                        'KD_REKENING_BELANJA' => $d['KD_REKENING_BELANJA'],
                        'NM_REKENING_BELANJA' => $d['NM_REKENING_BELANJA'],
                        'ANGGARAN'            => $d['ANGGARAN']
                    ]);
                    $totalDetail += (float)$d['ANGGARAN'];
                }
                $this->spmModel->where('ID_PENGAJUAN', $idPengajuan)->set(['ANGGARAN' => $totalDetail])->update();
            }

            // Trigger WhatsApp Gateway notification to Verifikator 1
            try {
                $wa = new \App\Libraries\WaGateway();
                $nmSkpd = $loginData['NM_UNITKER'] ?? 'OPD';
                $submitterId = !empty($loginData['ID_USER']) ? (int)$loginData['ID_USER'] : null;
                $wa->notifyNewSubmission('SPM', $idPengajuan, $nmSkpd, (string)$this->request->getPost('NM_PROGRAM_KEGIATAN_SUBKEGIATAN'), (float)$totalDetail, (string)$loginData['KD_UNITKER'], $submitterId);
            } catch (\Throwable $e) {}

            return $this->response->setBody('00');
        } catch (\Exception $e) {
            return $this->response->setBody('#' . $e->getMessage());
        }
    }

    public function pengajuanNPDHome()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return redirect()->to(base_url('user/login'));
        }

        // Clean up drafting details
        $this->detailModel->where('NO_NPD_SPM', (string)$loginData['ID_USER'])->delete();

        // Clean up stale unsubmitted draft files for this user so old test files don't leak into new submissions
        $uploadDir = FCPATH . 'uploads/pdf/';
        if (is_dir($uploadDir) && !empty($loginData['USER_NAME'])) {
            $draftPrefix = preg_replace('/[^a-zA-Z0-9_\-]/', '', $loginData['USER_NAME']) . '_';
            $oldDrafts = glob($uploadDir . $draftPrefix . '*');
            if (!empty($oldDrafts)) {
                foreach ($oldDrafts as $f) {
                    @unlink($f);
                }
            }
        }

        $data = [
            'Header'              => 'PENGAJUAN NPD',
            'Title'               => 'Users',
            'Keterangan'          => 'Form pengajuan NPD ke BPKAD.',
            'NAMA_LENGKAP'        => $loginData['NAMA_LENGKAP'],
            'NM_UNITKER'          => $loginData['NM_UNITKER'],
            'LAST_LOGIN'          => '[' . date('d-m-Y H:i:s', strtotime($loginData['LAST_LOGIN'] ?? 'now')) . ']',
            'ListSKPD'            => $this->skpdModel->orderBy('NM_SKPD', 'ASC')->findAll(),
            'ListMataAnggaran'    => $this->mataAnggaranModel->orderBy('KD_MATA_ANGGARAN', 'ASC')->findAll(),
            'ListRekeningBelanja' => $this->getRekeningPaguList(),
            'KD_SKPD'             => $loginData['KD_UNITKER']
        ];

        return view('spm/pengajuan_npd', $data);
    }

    public function savePengajuanNPD()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return $this->response->setBody('#Silakan login terlebih dahulu.');
        }

        try {
            $idNpd = $this->npdModel->generateID();

            $sumberDana = trim($this->request->getPost('KD_SUMBER_DANA') ?? $this->request->getPost('KD_REKENING_BELANJA') ?? '');

            $npdData = [
                'ID_PENGAJUAN'                   => $idNpd,
                'TGL_PENGAJUAN'                  => date('Y-m-d H:i:s'),
                'KD_SKPD'                        => $loginData['KD_UNITKER'],
                'NM_PROGRAM_KEGIATAN_SUBKEGIATAN'=> $this->request->getPost('NM_PROGRAM_KEGIATAN_SUBKEGIATAN'),
                'KD_REKENING_BELANJA'            => $sumberDana,
                'KD_SUMBER_DANA'                 => $sumberDana,
                'ANGGARAN'                       => 0,
                'KD_STATUS'                      => 1
            ];

            $this->npdModel->insert($npdData);

            // Reassign draft details (hanya rekening SIPD 5.x)
            $draftDetails = $this->detailModel->where('NO_NPD_SPM', (string)$loginData['ID_USER'])->findAll();
            $total = 0;
            foreach ($draftDetails as $d) {
                $kd = trim((string)($d['KD_REKENING_BELANJA'] ?? ''));
                if (!str_starts_with($kd, '5.')) {
                    $this->detailModel->delete($d['ID_DETAIL']);
                    continue;
                }
                $this->detailModel->update($d['ID_DETAIL'], ['NO_NPD_SPM' => $idNpd]);
                $total += (float)$d['ANGGARAN'];
            }

            $this->npdModel->where('ID_PENGAJUAN', $idNpd)->set(['ANGGARAN' => $total])->update();

            // Rename uploaded files
            $this->renameFiles($idNpd);

            // Trigger WhatsApp Gateway notification to Verifikator 1
            try {
                $wa = new \App\Libraries\WaGateway();
                $nmSkpd = $loginData['NM_UNITKER'] ?? 'OPD';
                $submitterId = !empty($loginData['ID_USER']) ? (int)$loginData['ID_USER'] : null;
                $wa->notifyNewSubmission('NPD', $idNpd, $nmSkpd, (string)$this->request->getPost('NM_PROGRAM_KEGIATAN_SUBKEGIATAN'), (float)$total, (string)$loginData['KD_UNITKER'], $submitterId);
            } catch (\Throwable $e) {}

            return $this->response->setBody('00');
        } catch (\Exception $e) {
            return $this->response->setBody('#' . $e->getMessage());
        }
    }

    public function statusPengajuanHome()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return redirect()->to(base_url('user/login'));
        }

        $data = [
            'Header'       => 'STATUS PENGAJUAN (SPM/SPP)',
            'Title'        => 'Users',
            'Keterangan'   => 'Monitoring status dari pengajuan SPP/SPM.',
            'NAMA_LENGKAP' => $loginData['NAMA_LENGKAP'],
            'NM_UNITKER'   => $loginData['NM_UNITKER'],
            'LAST_LOGIN'   => '[' . date('d-m-Y H:i:s', strtotime($loginData['LAST_LOGIN'] ?? 'now')) . ']'
        ];

        return view('spm/status_spm', $data);
    }

    public function statusPengajuanNPDHome()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return redirect()->to(base_url('user/login'));
        }

        $data = [
            'Header'       => 'STATUS PENGAJUAN (NPD)',
            'Title'        => 'Users',
            'Keterangan'   => 'Monitoring status dari pengajuan NPD.',
            'NAMA_LENGKAP' => $loginData['NAMA_LENGKAP'],
            'NM_UNITKER'   => $loginData['NM_UNITKER'],
            'LAST_LOGIN'   => '[' . date('d-m-Y H:i:s', strtotime($loginData['LAST_LOGIN'] ?? 'now')) . ']'
        ];

        return view('spm/status_npd', $data);
    }

    public function getListPengajuan()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return $this->response->setJSON([]);
        }

        $isNPD = filter_var($this->request->getGet('IsNPD'), FILTER_VALIDATE_BOOLEAN);
        $db = \Config\Database::connect();
        $table = $isNPD ? 'tb_npd' : 'tb_spm';

        $builder = $db->table($table . ' spm')
            ->select('spm.ID_PENGAJUAN, spm.TGL_PENGAJUAN, skpd.NM_SKPD as KD_SKPD, spm.NM_PROGRAM_KEGIATAN_SUBKEGIATAN, COALESCE(blanja.KD_REKENING_BELANJA, spm.KD_REKENING_BELANJA) as KD_REKENING_BELANJA, spm.ANGGARAN, COALESCE(blanja.NM_REKENING_BELANJA, spm.KD_REKENING_BELANJA) as NM_REKENING_BELANJA, spm.KD_STATUS, spm.ALASAN_PENOLAKAN')
            ->join('ms_skpd skpd', 'spm.KD_SKPD = skpd.KD_SKPD', 'left')
            ->join('ms_rekening_belanja blanja', 'spm.KD_REKENING_BELANJA = blanja.KD_REKENING_BELANJA', 'left');

        $jenisUser = $loginData['JENIS_USER'];
        $isVerifikator = in_array($jenisUser, ['Verifikasi 1', 'Verifikasi 2']);

        if ($isVerifikator) {
            // Verifikator 1 & 2: riwayat pengajuan berdasarkan OPD yang ditugaskan di tb_user_role
            $roles = $this->userRoleModel->where('USERNAME', $loginData['USER_NAME'])->findAll();
            $assignedSkpd = array_values(array_unique(array_filter(array_column($roles, 'KD_SKPD'))));

            if (!empty($assignedSkpd)) {
                $builder->whereIn('spm.KD_SKPD', $assignedSkpd);
            }
        } elseif ($jenisUser !== 'Admin' && $jenisUser !== 'Persetujuan') {
            // User OPD: hanya pengajuan dari OPD sendiri
            $builder->where('spm.KD_SKPD', $loginData['KD_UNITKER']);
        }
        // Admin dan Persetujuan: dapat melihat semua riwayat

        $list = $builder->orderBy('spm.TGL_PENGAJUAN', 'DESC')->get()->getResultArray();
        return $this->response->setJSON($list);
    }

    public function monitoringHome()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return redirect()->to(base_url('user/login'));
        }

        $data = [
            'Header'       => 'STATUS PENGAJUAN (NPD)',
            'Title'        => 'Users',
            'Keterangan'   => 'Monitoring status dari pengajuan NPD.',
            'NAMA_LENGKAP' => $loginData['NAMA_LENGKAP'],
            'NM_UNITKER'   => $loginData['NM_UNITKER'],
            'LAST_LOGIN'   => '[' . date('d-m-Y H:i:s', strtotime($loginData['LAST_LOGIN'] ?? 'now')) . ']'
        ];

        return view('spm/monitoring_home', $data);
    }

    public function getListMonitoring()
    {
        $isNPD = filter_var($this->request->getGet('IsNPD'), FILTER_VALIDATE_BOOLEAN);
        $db = \Config\Database::connect();
        $table = $isNPD ? 'tb_npd' : 'tb_spm';

        $list = $db->table($table . ' spm')
            ->select('spm.ID_PENGAJUAN, spm.TGL_PENGAJUAN, skpd.NM_SKPD as KD_SKPD, spm.NM_PROGRAM_KEGIATAN_SUBKEGIATAN, COALESCE(blanja.KD_REKENING_BELANJA, spm.KD_REKENING_BELANJA) as KD_REKENING_BELANJA, spm.ANGGARAN, COALESCE(blanja.NM_REKENING_BELANJA, spm.KD_REKENING_BELANJA) as NM_REKENING_BELANJA, spm.KD_STATUS')
            ->join('ms_skpd skpd', 'spm.KD_SKPD = skpd.KD_SKPD', 'left')
            ->join('ms_rekening_belanja blanja', 'spm.KD_REKENING_BELANJA = blanja.KD_REKENING_BELANJA', 'left')
            ->whereNotIn('spm.KD_STATUS', [3, 4, 5])
            ->orderBy('spm.TGL_PENGAJUAN', 'DESC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON($list);
    }

    public function uploadFile()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return $this->response->setBody('#Sesi berakhir.');
        }

        $file = $this->request->getFile('fileUpload');
        if (!$file || !$file->isValid()) {
            return $this->response->setBody('#File tidak ditemukan atau tidak valid.');
        }

        // 1. Validasi ekstensi yang diizinkan secara ketat
        $ext = strtolower($file->getClientExtension() ?: $file->getExtension());
        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowedExts, true)) {
            return $this->response->setBody('#Format file tidak diizinkan. Hanya berkas PDF, JPG, dan PNG yang diperbolehkan.');
        }

        // 2. Validasi MIME type
        $mime = $file->getMimeType();
        $allowedMimes = ['application/pdf', 'application/x-pdf', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png'];
        if (!in_array($mime, $allowedMimes, true)) {
            return $this->response->setBody('#Tipe berkas tidak valid.');
        }

        // 3. Batas ukuran berkas (maksimal 15 MB)
        if ($file->getSizeByUnit('mb') > 15) {
            return $this->response->setBody('#Ukuran berkas melebihi batas maksimal 15 MB.');
        }

        try {
            $uploadDir = FCPATH . 'uploads/pdf/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Sanitasi nama berkas asli mencegah path traversal dan double extension
            $rawName = pathinfo($file->getClientName(), PATHINFO_FILENAME);
            $cleanName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $rawName);
            $cleanName = preg_replace('/(php|phtml|phar|sh|exe|asp|jsp)/i', '', $cleanName);
            $cleanName = trim($cleanName, '_');
            if (empty($cleanName)) {
                $cleanName = 'berkas';
            }
            $cleanName = substr($cleanName, 0, 50);

            $cleanUsername = preg_replace('/[^a-zA-Z0-9_\-]/', '', $loginData['USER_NAME']);
            $filename = $cleanUsername . '_' . $cleanName . '.' . $ext;

            $file->move($uploadDir, $filename, true);

            return $this->response->setJSON(['message' => $filename]);
        } catch (\Exception $e) {
            return $this->response->setBody('#' . $e->getMessage());
        }
    }

    protected function renameFiles(string $idPengajuan)
    {
        $loginData = $this->getLoginData();
        $dir = FCPATH . 'uploads/pdf/';
        if (!is_dir($dir)) {
            return;
        }

        $prefix = preg_replace('/[^a-zA-Z0-9_\-]/', '', $loginData['USER_NAME'] ?? '') . '_';
        $files = glob($dir . $prefix . '*');
        foreach ($files as $file) {
            $baseName = basename($file);
            $realName = substr($baseName, strlen($prefix));
            $newName = $idPengajuan . '_' . $realName;
            @rename($file, $dir . $newName);
        }
    }

    public function deleteFile()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return $this->response->setBody('9');
        }

        $fileName = $this->request->getPost('fileName') ?? $this->request->getGet('fileName');
        if (empty($fileName)) {
            return $this->response->setBody('9');
        }

        $cleanFile = basename($fileName);
        $path = FCPATH . 'uploads/pdf/' . $cleanFile;

        if (!file_exists($path)) {
            return $this->response->setBody('0');
        }

        $prefix = preg_replace('/[^a-zA-Z0-9_\-]/', '', $loginData['USER_NAME'] ?? '') . '_';
        $jenisUser = $loginData['JENIS_USER'] ?? '';
        $allowed = false;

        // 1. Admin dan tim verifikator/persetujuan berhak menghapus berkas (misal berkas uji coba/salah lampir)
        if (in_array($jenisUser, ['Admin', 'Verifikasi 1', 'Verifikasi 2', 'Persetujuan'])) {
            $allowed = true;
        } elseif (str_starts_with($cleanFile, $prefix)) {
            // 2. Draft milik user sendiri
            $allowed = true;
        } else {
            // 3. Berkas yang sudah diajukan oleh OPD milik user sendiri
            $parts = explode('_', $cleanFile, 2);
            if (!empty($parts[0])) {
                $idPengajuan = $parts[0];
                $userUnit = $loginData['KD_UNITKER'] ?? '';
                $npd = $this->npdModel->where('ID_PENGAJUAN', $idPengajuan)->first();
                $spm = $this->spmModel->where('ID_PENGAJUAN', $idPengajuan)->first();
                $skpdPengajuan = $npd['KD_SKPD'] ?? ($spm['KD_SKPD'] ?? '');

                if (!empty($skpdPengajuan) && !empty($userUnit) && $skpdPengajuan === $userUnit) {
                    $allowed = true;
                }
            }
        }

        if ($allowed) {
            @unlink($path);
            return $this->response->setBody('0');
        }

        return $this->response->setBody('9');
    }

    public function getAllFiles()
    {
        $loginData = $this->getLoginData();
        $idPengajuan = $this->request->getGet('idPengajuan');
        if (empty($idPengajuan)) {
            $idPengajuan = $loginData['USER_NAME'] ?? '';
        }

        $dir = FCPATH . 'uploads/pdf/';
        $result = [];
        if (is_dir($dir)) {
            $prefixes = [$idPengajuan];
            if (!empty($idPengajuan)) {
                $spm = $this->spmModel->where('ID_PENGAJUAN', $idPengajuan)->first();
                if ($spm && !empty($spm['ID_NPD'])) {
                    $prefixes[] = $spm['ID_NPD'];
                }
            }
            $prefixes = array_unique(array_filter($prefixes));

            $seen = [];
            foreach ($prefixes as $pfx) {
                $files = glob($dir . $pfx . '_*');
                if ($files) {
                    foreach ($files as $f) {
                        $base = basename($f);
                        if (isset($seen[$base])) continue;
                        $seen[$base] = true;
                        $origName = substr($base, strpos($base, '_') + 1);
                        $result[] = [
                            'Name'         => $origName,
                            'FileName'     => $base,
                            'Size'         => filesize($f),
                            'Created'      => date('Y-m-d H:i:s', filectime($f)),
                            'LastModified' => date('Y-m-d H:i:s', filemtime($f))
                        ];
                    }
                }
            }
        }
        return $this->response->setJSON($result);
    }

    public function spmView()
    {
        $id = $this->request->getGet('idPengajuan');
        $spm = $this->spmModel->where('ID_PENGAJUAN', $id)->first();
        if (!$spm) {
            return $this->response->setBody('Data tidak ditemukan.');
        }

        $data = [
            'Header'              => 'INFORMASI SPP/SPM',
            'Keterangan'          => 'Informasi pengajuan SPP/SPM ke BPKAD.',
            'ListSKPD'            => $this->skpdModel->orderBy('NM_SKPD', 'ASC')->findAll(),
            'ListMataAnggaran'    => $this->mataAnggaranModel->orderBy('KD_MATA_ANGGARAN', 'ASC')->findAll(),
            'ListRekeningBelanja' => $this->rekeningModel->orderBy('KD_REKENING_BELANJA', 'ASC')->findAll(),
            'spm'                 => $spm
        ];

        return view('spm/partials/_spm_view', $data);
    }

    public function spmViewNPD()
    {
        $id = $this->request->getGet('idPengajuan');
        $npd = $this->npdModel->where('ID_PENGAJUAN', $id)->first();
        if (!$npd) {
            return $this->response->setBody('Data tidak ditemukan.');
        }

        $data = [
            'Header'              => 'INFORMASI NPD',
            'Keterangan'          => 'Informasi pengajuan NPD ke BPKAD.',
            'ListSKPD'            => $this->skpdModel->orderBy('NM_SKPD', 'ASC')->findAll(),
            'ListMataAnggaran'    => $this->mataAnggaranModel->orderBy('KD_MATA_ANGGARAN', 'ASC')->findAll(),
            'ListRekeningBelanja' => $this->rekeningModel->orderBy('KD_REKENING_BELANJA', 'ASC')->findAll(),
            'npd'                 => $npd
        ];

        return view('spm/partials/_spm_view_npd', $data);
    }

    public function getDataDetail()
    {
        $loginData = $this->getLoginData();
        $id = $this->request->getGet('NO_NPD_SPM');
        if (empty($id)) {
            $id = (string)($loginData['ID_USER'] ?? '');
        }

        $details = $this->detailModel->where('NO_NPD_SPM', $id)->findAll();
        if (empty($details) && !empty($id)) {
            $spm = $this->spmModel->where('ID_PENGAJUAN', $id)->first();
            if ($spm && !empty($spm['ID_NPD'])) {
                $details = $this->detailModel->where('NO_NPD_SPM', $spm['ID_NPD'])->findAll();
            }
        }

        // Auto-heal: jika pengajuan memiliki rekening belanja riil SIPD (kode berawalan 5.),
        // bersihkan otomatis data rincian dummy / nomor uji coba lama (seperti '2', '227', '98') yang tidak sengaja tertinggal
        $hasReal = false;
        $dummyIds = [];
        foreach ($details as $d) {
            $kd = trim((string)($d['KD_REKENING_BELANJA'] ?? ''));
            if (str_starts_with($kd, '5.')) {
                $hasReal = true;
            } else {
                $dummyIds[] = $d['ID_DETAIL'];
            }
        }

        if ($hasReal && !empty($dummyIds)) {
            $this->detailModel->whereIn('ID_DETAIL', $dummyIds)->delete();
            $details = array_values(array_filter($details, function($d) use ($dummyIds) {
                return !in_array($d['ID_DETAIL'], $dummyIds);
            }));

            // Sinkronkan nominal ANGGARAN di tabel pengajuan
            if (str_contains($id, '.')) {
                $realTotal = array_sum(array_column($details, 'ANGGARAN'));
                $db = \Config\Database::connect();
                $db->table('tb_npd')->where('ID_PENGAJUAN', $id)->set(['ANGGARAN' => $realTotal])->update();
                $db->table('tb_spm')->where('ID_PENGAJUAN', $id)->set(['ANGGARAN' => $realTotal])->update();
            }
        }

        return $this->response->setJSON($details);
    }

    public function addDataDetail()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return $this->response->setBody('#Sesi berakhir.');
        }

        try {
            $kdRekening = $this->request->getPost('KD_REKENING_BELANJA');
            $anggaran = (float)$this->request->getPost('ANGGARAN');

            $rekList = $this->getRekeningPaguList();
            $rekFound = null;
            foreach ($rekList as $r) {
                if ($r['KD_REKENING_BELANJA'] === $kdRekening) {
                    $rekFound = $r;
                    break;
                }
            }

            $nmRekening = '-';
            if ($rekFound) {
                $nmRekening = $rekFound['NM_REKENING_BELANJA'];
                // Check draft details sum
                $draftSum = (float)($this->detailModel->where('NO_NPD_SPM', (string)$loginData['ID_USER'])
                    ->where('KD_REKENING_BELANJA', $kdRekening)
                    ->selectSum('ANGGARAN')
                    ->get()
                    ->getRow()->ANGGARAN ?? 0);

                $sisa = (float)$rekFound['SISA_PAGU'] - $draftSum;
                if ($sisa < $anggaran) {
                    return $this->response->setBody('#Sisa pagu tidak cukup. Sisa: Rp ' . number_format($sisa, 2, ',', '.'));
                }
            }

            $this->detailModel->insert([
                'NO_NPD_SPM'          => (string)$loginData['ID_USER'],
                'KD_REKENING_BELANJA' => $kdRekening,
                'NM_REKENING_BELANJA' => $nmRekening,
                'ANGGARAN'            => $anggaran
            ]);

            return $this->response->setBody('00');
        } catch (\Exception $e) {
            return $this->response->setBody('#' . $e->getMessage());
        }
    }

    public function deleteDataDetail()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return $this->response->setBody('#Sesi berakhir.');
        }

        $id = $this->request->getPost('id');
        if (empty($id)) {
            return $this->response->setBody('#ID detail tidak valid.');
        }

        $detail = $this->detailModel->find($id);
        if (!$detail) {
            return $this->response->setBody('#Data rincian tidak ditemukan.');
        }

        $noNpdSpm = $detail['NO_NPD_SPM'];
        $db = \Config\Database::connect();

        // Cek jika rincian sudah terhubung ke pengajuan yang telah diajukan (bukan ID_USER draft)
        $isSubmitted = ($noNpdSpm !== (string)$loginData['ID_USER']);
        if ($isSubmitted) {
            $allowedRoles = ['Admin', 'Verifikasi 1', 'Verifikasi 2', 'Persetujuan'];
            $allowed = in_array($loginData['JENIS_USER'], $allowedRoles, true);

            // OPD pemilik pengajuan boleh menghapus jika pengajuan masih status 1 (Menunggu Verifikasi 1)
            if (!$allowed) {
                $npd = $this->npdModel->where('ID_PENGAJUAN', $noNpdSpm)->first();
                $spm = $this->spmModel->where('ID_PENGAJUAN', $noNpdSpm)->first();
                $itemStatus = (int)($npd['KD_STATUS'] ?? ($spm['KD_STATUS'] ?? 0));
                $itemSkpd = $npd['KD_SKPD'] ?? ($spm['KD_SKPD'] ?? '');

                if ($itemStatus === 1 && !empty($loginData['KD_UNITKER']) && $itemSkpd === $loginData['KD_UNITKER']) {
                    $allowed = true;
                }
            }

            if (!$allowed) {
                return $this->response->setBody('#Anda tidak memiliki izin menghapus rincian pada pengajuan ini.');
            }
        }

        $this->detailModel->delete($id);

        $newTotal = 0;
        if ($isSubmitted) {
            $newTotal = (float)($db->table('tb_data_detail')
                ->where('NO_NPD_SPM', $noNpdSpm)
                ->selectSum('ANGGARAN')
                ->get()
                ->getRow()->ANGGARAN ?? 0);

            $db->table('tb_npd')->where('ID_PENGAJUAN', $noNpdSpm)->set(['ANGGARAN' => $newTotal])->update();
            $db->table('tb_spm')->where('ID_PENGAJUAN', $noNpdSpm)->set(['ANGGARAN' => $newTotal])->update();
        }

        return $this->response->setJSON([
            'status'         => '00',
            'newTotal'       => $newTotal,
            'formattedTotal' => number_format($newTotal, 2, ',', '.')
        ]);
    }

    public function getRekeningPaguList(): array
    {
        $loginData = $this->getLoginData();
        $builder = $this->rekeningModel->builder();
        $db = \Config\Database::connect();
        if ($loginData && $loginData['JENIS_USER'] === 'User' && !empty($loginData['KD_UNITKER'])) {
            $targetUnit = $loginData['KD_UNITKER'];
            $shortPrefix = preg_replace('/(\.0000)+$/', '', $targetUnit);
            $skpdRow = $db->table('ms_skpd')->where('KD_SKPD', $targetUnit)->get()->getRowArray();
            $nmUnit = $skpdRow['NM_SKPD'] ?? ($loginData['NM_UNITKER'] ?? '');

            $hasRealForUser = $db->table('ms_rekening_belanja')
                ->groupStart()
                    ->where('KD_SKPD', $targetUnit)
                    ->orLike('KD_SKPD', $shortPrefix, 'after')
                    ->orWhere('NM_SKPD', $nmUnit)
                ->groupEnd()
                ->countAllResults() > 0;

            if ($hasRealForUser) {
                $builder->groupStart()
                    ->where('KD_SKPD', $targetUnit)
                    ->orLike('KD_SKPD', $shortPrefix, 'after');
                if (!empty($nmUnit)) {
                    $builder->orWhere('NM_SKPD', $nmUnit);
                }
                $builder->groupEnd();
            } else {
                $builder->groupStart()
                    ->where('KD_SKPD', $targetUnit)
                    ->orWhere('KD_SKPD IS NULL', null, false)
                    ->orWhere('KD_SKPD', '')
                ->groupEnd();
            }
        }

        $rekeningList = $builder->orderBy("(CASE WHEN KD_REKENING_BELANJA LIKE '5.%' THEN 0 ELSE 1 END)", 'ASC')
            ->orderBy('KD_REKENING_BELANJA', 'ASC')
            ->get()->getResultArray();
        $currentYear = date('Y');

        $db = \Config\Database::connect();
        // Approved SPM in current year (status 3 or 4)
        $spmQuery = $db->table('tb_spm')
            ->whereIn('KD_STATUS', [3, 4])
            ->where('YEAR(TGL_PENGAJUAN)', $currentYear);

        if ($loginData && $loginData['JENIS_USER'] === 'User' && !empty($loginData['KD_UNITKER'])) {
            $spmQuery->where('KD_SKPD', $loginData['KD_UNITKER']);
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

        foreach ($rekeningList as &$r) {
            $kd = $r['KD_REKENING_BELANJA'];
            $skpdKey = $r['KD_SKPD'] ?? '';
            $key = $skpdKey . '_' . $kd;
            $used = !empty($skpdKey) && isset($usage[$key]) ? (float)$usage[$key] : (float)($usage[$kd] ?? 0);
            $r['SISA_PAGU'] = max(0, (float)$r['PAGU'] - $used);
            $r['DISPLAY_NAME'] = $r['NM_REKENING_BELANJA'] . ' (Sisa Pagu: Rp ' . number_format($r['SISA_PAGU'], 2, ',', '.') . ')';
        }

        return $rekeningList;
    }
}
