<?php

namespace App\Controllers;

use App\Models\SkpdModel;

class SIPD extends BaseController
{
    protected SkpdModel $skpdModel;

    public function __construct()
    {
        $this->skpdModel = new SkpdModel();
        helper(['url', 'form', 'menu']);
    }

    public function monitoringSIPDHome()
    {
        $session = session();
        $loginData = $session->get('LoginData');
        if (empty($loginData)) {
            return redirect()->to(base_url('user/login'));
        }

        $data = [
            'Header'       => 'MONITORING SP2D',
            'Title'        => 'Monitoring SIPD',
            'Keterangan'   => 'Monitoring seluruh pencairan SP2D dari setiap OPD.',
            'NAMA_LENGKAP' => $loginData['NAMA_LENGKAP'],
            'NM_UNITKER'   => $loginData['NM_UNITKER'],
            'LAST_LOGIN'   => '[' . date('d-m-Y H:i:s', strtotime($loginData['LAST_LOGIN'] ?? 'now')) . ']'
        ];

        return view('sipd/monitoring_sipd', $data);
    }

    public function getStatusSIPD()
    {
        $session = session();
        $loginData = $session->get('LoginData');
        if (empty($loginData)) {
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
                // If offline / mock in dev
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
}
