<?php

namespace App\Controllers;

use App\Models\SpmModel;
use App\Models\NpdModel;
use App\Models\SkpdModel;
use App\Models\MataAnggaranModel;
use App\Models\RekeningBelanjaModel;
use App\Models\UserRoleModel;

class BPKAD extends BaseController
{
    protected SpmModel $spmModel;
    protected NpdModel $npdModel;
    protected SkpdModel $skpdModel;
    protected MataAnggaranModel $mataAnggaranModel;
    protected RekeningBelanjaModel $rekeningModel;
    protected UserRoleModel $userRoleModel;

    public function __construct()
    {
        $this->spmModel = new SpmModel();
        $this->npdModel = new NpdModel();
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

    public function persetujuanSPMHome()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return redirect()->to(base_url('user/login'));
        }

        $data = [
            'Header'       => 'PERSETUJUAN SPP/SPM',
            'Title'        => 'Users',
            'Keterangan'   => 'Daftar pengajuan SPP/SPM yang akan di setujui atau di tolak.',
            'NAMA_LENGKAP' => $loginData['NAMA_LENGKAP'],
            'NM_UNITKER'   => $loginData['NM_UNITKER'],
            'LAST_LOGIN'   => '[' . date('d-m-Y H:i:s', strtotime($loginData['LAST_LOGIN'] ?? 'now')) . ']'
        ];

        return view('bpkad/persetujuan_spm', $data);
    }

    public function persetujuanNPDHome()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return redirect()->to(base_url('user/login'));
        }

        $data = [
            'Header'       => 'PERSETUJUAN NPD',
            'Title'        => 'Users',
            'Keterangan'   => 'Daftar pengajuan NPD yang akan di setujui atau di tolak.',
            'NAMA_LENGKAP' => $loginData['NAMA_LENGKAP'],
            'NM_UNITKER'   => $loginData['NM_UNITKER'],
            'LAST_LOGIN'   => '[' . date('d-m-Y H:i:s', strtotime($loginData['LAST_LOGIN'] ?? 'now')) . ']'
        ];

        return view('bpkad/persetujuan_npd', $data);
    }

    public function persetujuanSP2DHome()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return redirect()->to(base_url('user/login'));
        }

        $data = [
            'Header'       => 'PERSETUJUAN PENCAIRAN SP2D',
            'Title'        => 'Users',
            'Keterangan'   => 'Daftar pencairan SP2D yang akan di setujui atau di tolak.',
            'NAMA_LENGKAP' => $loginData['NAMA_LENGKAP'],
            'NM_UNITKER'   => $loginData['NM_UNITKER'],
            'LAST_LOGIN'   => '[' . date('d-m-Y H:i:s', strtotime($loginData['LAST_LOGIN'] ?? 'now')) . ']'
        ];

        return view('bpkad/persetujuan_sp2d', $data);
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

        // Get SKPD assigned to current user
        $roles = $this->userRoleModel->where('USERNAME', $loginData['USER_NAME'])->findAll();
        $assignedSkpd = array_column($roles, 'KD_SKPD');

        $builder = $db->table($table . ' spm')
            ->select('spm.ID_PENGAJUAN, spm.TGL_PENGAJUAN, skpd.NM_SKPD as KD_SKPD, spm.NM_PROGRAM_KEGIATAN_SUBKEGIATAN, COALESCE(blanja.KD_REKENING_BELANJA, spm.KD_REKENING_BELANJA) as KD_REKENING_BELANJA, spm.ANGGARAN, COALESCE(blanja.NM_REKENING_BELANJA, spm.KD_REKENING_BELANJA) as NM_REKENING_BELANJA, spm.KD_STATUS, spm.ALASAN_PENOLAKAN')
            ->join('ms_skpd skpd', 'spm.KD_SKPD = skpd.KD_SKPD', 'left')
            ->join('ms_rekening_belanja blanja', 'spm.KD_REKENING_BELANJA = blanja.KD_REKENING_BELANJA', 'left');

        $jenisUser = $loginData['JENIS_USER'];
        if ($jenisUser === 'Verifikasi 1') {
            if (!empty($assignedSkpd)) {
                $builder->whereIn('spm.KD_SKPD', $assignedSkpd);
            }
            $builder->where('spm.KD_STATUS', 1);
        } elseif ($jenisUser === 'Verifikasi 2') {
            $builder->where('spm.KD_STATUS', 2);
        } elseif ($jenisUser === 'Persetujuan') {
            $builder->where('spm.KD_STATUS', 3);
        } elseif ($jenisUser === 'Admin') {
            // Admin can view all pending statuses
            $builder->whereIn('spm.KD_STATUS', [1, 2, 3]);
        } else {
            return $this->response->setJSON([]);
        }

        $list = $builder->orderBy('spm.TGL_PENGAJUAN', 'DESC')->get()->getResultArray();
        return $this->response->setJSON($list);
    }

    public function acceptPengajuan()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return $this->response->setBody('Login');
        }

        try {
            $id = $this->request->getPost('id');
            $spm = $this->spmModel->where('ID_PENGAJUAN', $id)->first();
            if (!$spm) {
                return $this->response->setBody('#Data tidak ditemukan.');
            }

            $userRole = $loginData['JENIS_USER'];
            if ($userRole === 'Verifikasi 1') {
                $nextStatus = 2;
            } elseif ($userRole === 'Verifikasi 2') {
                $nextStatus = 3;
            } else {
                // Persetujuan (KBUD Persetujuan Akhir) or Admin
                $nextStatus = 4;
            }

            $updateData = [
                'KD_STATUS'     => $nextStatus,
                'TGL_VEIFIKASI' => date('Y-m-d H:i:s')
            ];

            if ($nextStatus === 4) {
                $updateData['TGL_SP2D'] = date('Y-m-d H:i:s');
            }

            $this->spmModel->where('ID_PENGAJUAN', $id)->set($updateData)->update();

            // Trigger WhatsApp Gateway notification (progress / final approval)
            try {
                $wa = new \App\Libraries\WaGateway();
                $skpd = $this->skpdModel->where('KD_SKPD', $spm['KD_SKPD'])->first();
                $nmSkpd = $skpd['NM_SKPD'] ?? $spm['KD_SKPD'];
                $kegiatan = $spm['NM_PROGRAM_KEGIATAN_SUBKEGIATAN'] ?? '-';
                $anggaran = (float)($spm['ANGGARAN'] ?? 0);
                $kdSkpd = (string)$spm['KD_SKPD'];

                if ($nextStatus === 2) {
                    $wa->notifyVerificationProgress('SPM', $id, $nmSkpd, $kegiatan, $anggaran, 'Verifikasi 2', $kdSkpd);
                } elseif ($nextStatus === 3) {
                    $wa->notifyVerificationProgress('SPM', $id, $nmSkpd, $kegiatan, $anggaran, 'Persetujuan', $kdSkpd);
                } elseif ($nextStatus === 4) {
                    $wa->notifyApprovalFinal('SPM', $id, $nmSkpd, $kegiatan, $anggaran, $kdSkpd);
                }
            } catch (\Throwable $e) {}

            return $this->response->setBody('00');
        } catch (\Exception $e) {
            return $this->response->setBody('#' . $e->getMessage());
        }
    }

    public function rejectPengajuan()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return $this->response->setBody('Login');
        }

        try {
            $id = $this->request->getPost('id');
            $alasan = $this->request->getPost('alasan');

            $spm = $this->spmModel->where('ID_PENGAJUAN', $id)->first();

            $this->spmModel->where('ID_PENGAJUAN', $id)->set([
                'KD_STATUS'        => 5,
                'ALASAN_PENOLAKAN' => $alasan,
                'TGL_VEIFIKASI'    => date('Y-m-d H:i:s')
            ])->update();

            // Trigger WhatsApp Gateway rejection notification to OPD
            if ($spm) {
                try {
                    $wa = new \App\Libraries\WaGateway();
                    $skpd = $this->skpdModel->where('KD_SKPD', $spm['KD_SKPD'])->first();
                    $nmSkpd = $skpd['NM_SKPD'] ?? $spm['KD_SKPD'];
                    $kegiatan = $spm['NM_PROGRAM_KEGIATAN_SUBKEGIATAN'] ?? '-';
                    $anggaran = (float)($spm['ANGGARAN'] ?? 0);
                    $kdSkpd = (string)$spm['KD_SKPD'];
                    $wa->notifyRejection('SPM', $id, $nmSkpd, $kegiatan, $anggaran, (string)$alasan, $kdSkpd);
                } catch (\Throwable $e) {}
            }

            return $this->response->setBody('00');
        } catch (\Exception $e) {
            return $this->response->setBody('#' . $e->getMessage());
        }
    }

    public function acceptPengajuanNPD()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return $this->response->setBody('Login');
        }

        try {
            $id = $this->request->getPost('id');
            $userRole = $loginData['JENIS_USER'];
            if ($userRole === 'Verifikasi 1') {
                $nextStatus = 2;
            } elseif ($userRole === 'Verifikasi 2') {
                $nextStatus = 3;
            } else {
                // Persetujuan (KBUD Persetujuan Akhir) or Admin
                $nextStatus = 4;
            }

            $updateData = [
                'KD_STATUS'     => $nextStatus,
                'TGL_VEIFIKASI' => date('Y-m-d H:i:s')
            ];

            if ($nextStatus === 4) {
                $updateData['TGL_SP2D'] = date('Y-m-d H:i:s');
            }

            $npd = $this->npdModel->where('ID_PENGAJUAN', $id)->first();
            $this->npdModel->where('ID_PENGAJUAN', $id)->set($updateData)->update();

            // Trigger WhatsApp Gateway notification
            if ($npd) {
                try {
                    $wa = new \App\Libraries\WaGateway();
                    $skpd = $this->skpdModel->where('KD_SKPD', $npd['KD_SKPD'])->first();
                    $nmSkpd = $skpd['NM_SKPD'] ?? $npd['KD_SKPD'];
                    $kegiatan = $npd['NM_PROGRAM_KEGIATAN_SUBKEGIATAN'] ?? '-';
                    $anggaran = (float)($npd['ANGGARAN'] ?? 0);
                    $kdSkpd = (string)$npd['KD_SKPD'];

                    if ($nextStatus === 2) {
                        $wa->notifyVerificationProgress('NPD', $id, $nmSkpd, $kegiatan, $anggaran, 'Verifikasi 2', $kdSkpd);
                    } elseif ($nextStatus === 3) {
                        $wa->notifyVerificationProgress('NPD', $id, $nmSkpd, $kegiatan, $anggaran, 'Persetujuan', $kdSkpd);
                    } elseif ($nextStatus === 4) {
                        $wa->notifyApprovalFinal('NPD', $id, $nmSkpd, $kegiatan, $anggaran, $kdSkpd);
                    }
                } catch (\Throwable $e) {}
            }

            return $this->response->setBody('00');
        } catch (\Exception $e) {
            return $this->response->setBody('#' . $e->getMessage());
        }
    }

    public function rejectPengajuanNPD()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return $this->response->setBody('Login');
        }

        try {
            $id = $this->request->getPost('id');
            $alasan = $this->request->getPost('alasan');

            $npd = $this->npdModel->where('ID_PENGAJUAN', $id)->first();

            $this->npdModel->where('ID_PENGAJUAN', $id)->set([
                'KD_STATUS'        => 5,
                'ALASAN_PENOLAKAN' => $alasan,
                'TGL_VEIFIKASI'    => date('Y-m-d H:i:s')
            ])->update();

            // Trigger WhatsApp Gateway rejection notification to OPD
            if ($npd) {
                try {
                    $wa = new \App\Libraries\WaGateway();
                    $skpd = $this->skpdModel->where('KD_SKPD', $npd['KD_SKPD'])->first();
                    $nmSkpd = $skpd['NM_SKPD'] ?? $npd['KD_SKPD'];
                    $kegiatan = $npd['NM_PROGRAM_KEGIATAN_SUBKEGIATAN'] ?? '-';
                    $anggaran = (float)($npd['ANGGARAN'] ?? 0);
                    $kdSkpd = (string)$npd['KD_SKPD'];
                    $wa->notifyRejection('NPD', $id, $nmSkpd, $kegiatan, $anggaran, (string)$alasan, $kdSkpd);
                } catch (\Throwable $e) {}
            }

            return $this->response->setBody('00');
        } catch (\Exception $e) {
            return $this->response->setBody('#' . $e->getMessage());
        }
    }

    public function setProses()
    {
        $noSp2d = $this->request->getGet('NO_SP2D') ?? $this->request->getPost('NO_SP2D');
        $serviceAddress = env('banksulselbar.serviceAddress', 'https://apidev.banksulselbar.co.id');
        $url = $serviceAddress . '/api/v1/SetProses?NO_SP2D=' . urlencode($noSp2d);

        try {
            $client = \Config\Services::curlrequest(['timeout' => 30, 'http_errors' => false, 'verify' => false]);
            $response = $client->get($url);
            $body = json_decode($response->getBody(), true);

            if (isset($body['code']) && $body['code'] === '00') {
                $this->spmModel->where('ID_PENGAJUAN', $noSp2d)->set([
                    'KD_STATUS' => 4,
                    'TGL_SP2D'  => date('Y-m-d H:i:s')
                ])->update();
                return $this->response->setBody('00');
            }

            // In local/dev offline environment fallback: update status directly if test
            if (ENVIRONMENT === 'development' && empty($body)) {
                $this->spmModel->where('ID_PENGAJUAN', $noSp2d)->set([
                    'KD_STATUS' => 4,
                    'TGL_SP2D'  => date('Y-m-d H:i:s')
                ])->update();
                return $this->response->setBody('00');
            }

            $msg = $body['message'] ?? 'Respon tidak dikenal';
            return $this->response->setBody('Gagal mengirim data ke SIPD: ' . $msg);
        } catch (\Exception $e) {
            return $this->response->setBody('Gagal mengirim data ke SIPD: ' . $e->getMessage());
        }
    }

    public function cancelProses()
    {
        $noSp2d = $this->request->getGet('NO_SP2D') ?? $this->request->getPost('NO_SP2D');
        $serviceAddress = env('banksulselbar.serviceAddress', 'https://apidev.banksulselbar.co.id');
        $url = $serviceAddress . '/api/v1/CancelProses?NO_SP2D=' . urlencode($noSp2d);

        try {
            $client = \Config\Services::curlrequest(['timeout' => 30, 'http_errors' => false, 'verify' => false]);
            $response = $client->get($url);
            $body = json_decode($response->getBody(), true);

            if (isset($body['code']) && $body['code'] === '00') {
                $this->spmModel->where('ID_PENGAJUAN', $noSp2d)->set(['KD_STATUS' => 5])->update();
                return $this->response->setBody('00');
            }

            if (ENVIRONMENT === 'development' && empty($body)) {
                $this->spmModel->where('ID_PENGAJUAN', $noSp2d)->set(['KD_STATUS' => 5])->update();
                return $this->response->setBody('00');
            }

            $msg = $body['message'] ?? 'Respon tidak dikenal';
            return $this->response->setBody('Gagal mengirim data ke SIPD: ' . $msg);
        } catch (\Exception $e) {
            return $this->response->setBody('Gagal mengirim data ke SIPD: ' . $e->getMessage());
        }
    }

    public function getStatusSIPD()
    {
        $loginData = $this->getLoginData();
        if (!$loginData) {
            return $this->response->setJSON([]);
        }

        $limit = (int)($this->request->getGet('LIMIT') ?? 50);
        if ($limit <= 0) {
            $limit = 50;
        }
        $code = $this->request->getGet('CODE') ?? '000';
        $tmpTgl = $this->request->getGet('TMP_TGL');

        $serviceAddress = env('banksulselbar.serviceAddress', 'https://apidev.banksulselbar.co.id');
        $url = $serviceAddress . "/api/v1/getTransaksi?KODE_WILAYAH=76.04&LIMIT={$limit}&PROSES=1&CODE={$code}";

        if ($loginData['JENIS_USER'] === 'User') {
            $url .= '&KD_SKPD=' . urlencode($loginData['KD_UNITKER']);
        }

        if (!empty($tmpTgl)) {
            $dates = explode('|', $tmpTgl);
            if (count($dates) === 2 && strtotime($dates[0]) && strtotime($dates[1])) {
                $url .= '&START_DATE=' . date('Y-m-d', strtotime($dates[0])) . '&END_DATE=' . date('Y-m-d', strtotime($dates[1]));
            }
        }

        try {
            $client = \Config\Services::curlrequest(['timeout' => 30, 'http_errors' => false, 'verify' => false]);
            $res = $client->get($url);
            $items = json_decode($res->getBody(), true);

            if (!is_array($items)) {
                return $this->response->setJSON([]);
            }

            $skpdMap = [];
            foreach ($this->skpdModel->findAll() as $s) {
                $skpdMap[$s['KD_SKPD']] = $s['NM_SKPD'];
            }

            $result = [];
            $regex = '/\b(?:\d{1,2}\.){7}\d{4}\b/';

            foreach ($items as $item) {
                $noSp2d = $item['NO_SP2D'] ?? '';
                if (preg_match($regex, $noSp2d, $match)) {
                    $kode = $match[0];
                    if ($loginData['JENIS_USER'] === 'User' && $loginData['KD_UNITKER'] !== $kode) {
                        continue;
                    }
                    if (isset($skpdMap[$kode])) {
                        $item['NOTE'] = $skpdMap[$kode];
                        $result[] = $item;
                    }
                }
            }

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            return $this->response->setJSON([]);
        }
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

        return view('bpkad/partials/_spm_view', $data);
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

        return view('bpkad/partials/_spm_view_npd', $data);
    }
}

