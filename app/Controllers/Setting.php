<?php

namespace App\Controllers;

use App\Models\WaGatewayModel;
use App\Libraries\WaGateway;

class Setting extends BaseController
{
    protected WaGatewayModel $waModel;
    protected WaGateway $waGateway;

    public function __construct()
    {
        $this->waModel = new WaGatewayModel();
        $this->waGateway = new WaGateway();
        helper(['url', 'form', 'menu']);
    }

    public function waGatewayHome()
    {
        $session = session();
        $loginData = $session->get('LoginData');

        $config = $this->waModel->getConfig();

        $data = [
            'Header'       => 'PENGATURAN WHATSAPP GATEWAY',
            'Title'        => 'WhatsApp Gateway',
            'Keterangan'   => 'Konfigurasi integrasi WhatsApp Bot Notifikasi Pengajuan, Verifikasi, dan Penolakan Berjenjang.',
            'NAMA_LENGKAP' => $loginData['NAMA_LENGKAP'] ?? '',
            'NM_UNITKER'   => $loginData['NM_UNITKER'] ?? '',
            'config'       => $config
        ];

        return view('setting/wa_gateway', $data);
    }

    public function saveWaGateway()
    {
        $request = $this->request;
        $isActive = $request->getPost('IS_ACTIVE') ? 1 : 0;
        $provider = trim($request->getPost('PROVIDER') ?? 'fonnte');
        $apiKey = trim($request->getPost('API_KEY') ?? '');
        $senderNumber = trim($request->getPost('SENDER_NUMBER') ?? '');
        $endpointUrl = trim($request->getPost('ENDPOINT_URL') ?? '');

        // Default endpoint fallback based on provider
        if (empty($endpointUrl)) {
            if ($provider === 'fonnte') {
                $endpointUrl = 'https://api.fonnte.com/send';
            } elseif ($provider === 'starsender') {
                $endpointUrl = 'https://starsender.online/api/sendText';
            }
        }

        $existing = $this->waModel->first();
        $data = [
            'IS_ACTIVE'     => $isActive,
            'PROVIDER'      => $provider,
            'API_KEY'       => $apiKey,
            'SENDER_NUMBER' => $senderNumber,
            'ENDPOINT_URL'  => $endpointUrl,
            'UPDATED_AT'    => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            $this->waModel->update($existing['ID_SETTING'], $data);
        } else {
            $this->waModel->insert($data);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Pengaturan WhatsApp Gateway berhasil disimpan!'
        ]);
    }

    public function testSendWa()
    {
        $phone = trim($this->request->getPost('phone') ?? '');
        $message = trim($this->request->getPost('message') ?? '');

        if (empty($phone)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Nomor WhatsApp tujuan wajib diisi.'
            ]);
        }

        if (empty($message)) {
            $message = "Halo! Ini adalah pesan uji coba dari SIPEKDA Kab. Polewali Mandar. WhatsApp Gateway berhasil terkoneksi dengan baik! (" . date('d-m-Y H:i:s') . ")";
        }

        $res = $this->waGateway->send($phone, $message, 'Test Admin');

        if (($res['status'] ?? '') === 'success') {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Pesan uji coba berhasil dikirim ke ' . esc($phone) . '!'
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Gagal mengirim pesan: ' . ($res['message'] ?? $res['response'] ?? 'Terjadi kesalahan.')
        ]);
    }

    public function getWaLogs()
    {
        $db = \Config\Database::connect();
        $this->waModel->ensureSchema();

        $logs = $db->table('tb_wa_logs')
            ->orderBy('ID_LOG', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();

        return $this->response->setJSON($logs);
    }
}
