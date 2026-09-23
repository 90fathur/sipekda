<?php

namespace App\Libraries;

use App\Models\WaGatewayModel;

class WaGateway
{
    protected WaGatewayModel $model;

    public function __construct()
    {
        $this->model = new WaGatewayModel();
    }

    /**
     * Normalize Indonesian phone number to standard 628xxx
     */
    public static function cleanPhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Remove all non-numeric characters
        $clean = preg_replace('/[^\d]/', '', $phone);

        if (empty($clean)) {
            return null;
        }

        // 08xx -> 628xx
        if (str_starts_with($clean, '08')) {
            $clean = '628' . substr($clean, 2);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '628' . substr($clean, 1);
        }

        return (strlen($clean) >= 10 && strlen($clean) <= 16) ? $clean : null;
    }

    /**
     * Send WhatsApp message to single phone number
     */
    public function send(string $phone, string $message, ?string $recipientName = null): array
    {
        $target = self::cleanPhone($phone);
        if (!$target) {
            return ['status' => 'error', 'message' => 'Nomor HP tidak valid.'];
        }

        $config = $this->model->getConfig();
        $isActive = (int)($config['IS_ACTIVE'] ?? 0);
        $apiKey = trim((string)($config['API_KEY'] ?? ''));
        $provider = strtolower(trim((string)($config['PROVIDER'] ?? 'cloudchat')));
        $endpoint = trim((string)($config['ENDPOINT_URL'] ?? ''));

        if ($isActive !== 1 || empty($apiKey)) {
            $reason = ($isActive !== 1) ? 'WhatsApp Gateway sedang NONAKTIF di menu Pengaturan.' : 'API Key WhatsApp Gateway belum diisi di menu Pengaturan.';
            try {
                $db = \Config\Database::connect();
                $db->table('tb_wa_logs')->insert([
                    'NO_TUJUAN'      => $target,
                    'NAMA_PENERIMA'  => $recipientName,
                    'PESAN'          => $message,
                    'STATUS'         => 'FAILED',
                    'RESPON_GATEWAY' => $reason,
                    'CREATED_AT'     => date('Y-m-d H:i:s')
                ]);
            } catch (\Throwable $e) {}
            return ['status' => 'disabled', 'message' => $reason];
        }

        $status = 'FAILED';
        $responseBody = '';

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            if ($provider === 'cloudchat' || $provider === 'chatbot' || $provider === 'chatbot_id') {
                // CloudChat / Chatbot.id Developer API (https://app.cloudchat.id/developer/docs)
                $url = !empty($endpoint) ? $endpoint : 'https://app.cloudchat.id/api/public/v1/messages';
                $bearer = str_starts_with($apiKey, 'Bearer ') ? $apiKey : 'Bearer ' . $apiKey;

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                    'channel' => 'whatsapp',
                    'type'    => 'text',
                    'to'      => $target,
                    'content' => [
                        'text' => $message
                    ]
                ]));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: ' . $bearer,
                    'Content-Type: application/json',
                    'Accept: application/json'
                ]);
            } elseif ($provider === 'fonnte') {
                $url = !empty($endpoint) ? $endpoint : 'https://api.fonnte.com/send';
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, [
                    'target'  => $target,
                    'message' => $message
                ]);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: ' . $apiKey
                ]);
            } elseif ($provider === 'wablas') {
                $url = rtrim($endpoint, '/') . '/api/send-message';
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, [
                    'phone'   => $target,
                    'message' => $message
                ]);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: ' . $apiKey
                ]);
            } elseif ($provider === 'starsender') {
                $url = !empty($endpoint) ? $endpoint : 'https://starsender.online/api/sendText';
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                    'tujuan' => $target,
                    'pesan'  => $message
                ]));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'apikey: ' . $apiKey,
                    'Content-Type: application/json'
                ]);
            } else {
                // Custom generic endpoint
                $url = !empty($endpoint) ? $endpoint : 'https://app.cloudchat.id/api/public/v1/messages';
                $bearer = str_starts_with($apiKey, 'Bearer ') ? $apiKey : 'Bearer ' . $apiKey;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                    'channel' => 'whatsapp',
                    'to'      => $target,
                    'target'  => $target,
                    'message' => $message,
                    'content' => ['text' => $message]
                ]));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: ' . $bearer,
                    'Content-Type: application/json',
                    'Accept: application/json'
                ]);
            }

            $responseBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                $json = json_decode($responseBody, true);
                if (is_array($json) && isset($json['success']) && $json['success'] === false) {
                    $status = 'FAILED';
                } else {
                    $status = 'SENT';
                }
            } else {
                $status = 'FAILED';
                if ($curlError) {
                    $responseBody = 'CURL Error: ' . $curlError . ' | ' . $responseBody;
                }
            }
        } catch (\Throwable $e) {
            $status = 'FAILED';
            $responseBody = 'Exception: ' . $e->getMessage();
        }

        // Log to tb_wa_logs
        try {
            $db = \Config\Database::connect();
            $db->table('tb_wa_logs')->insert([
                'NO_TUJUAN'      => $target,
                'NAMA_PENERIMA'  => $recipientName,
                'PESAN'          => $message,
                'STATUS'         => $status,
                'RESPON_GATEWAY' => substr($responseBody, 0, 1000),
                'CREATED_AT'     => date('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) {
            // Silently ignore logging failures
        }

        return [
            'status'   => ($status === 'SENT') ? 'success' : 'failed',
            'response' => $responseBody
        ];
    }

    /**
     * Send notification to all active users with specific role
     */
    public function sendToRole(string $roleName, string $message, ?string $kdSkpd = null): int
    {
        $db = \Config\Database::connect();
        $targetUsers = [];

        // For Verifikasi 1, check assigned SKPD if applicable
        if ($roleName === 'Verifikasi 1' && !empty($kdSkpd)) {
            $assigned = $db->table('tb_user_role r')
                ->select('u.ID_USER, u.NAMA_LENGKAP, u.NO_HP')
                ->join('tb_users u', 'r.USERNAME = u.USER_NAME', 'inner')
                ->where('r.KD_SKPD', $kdSkpd)
                ->where('u.AKTIF', 1)
                ->where('u.NO_HP IS NOT NULL', null, false)
                ->where('u.NO_HP !=', '')
                ->get()
                ->getResultArray();

            if (!empty($assigned)) {
                $targetUsers = $assigned;
            }
        }

        // If no specific assignment or for other roles (Verifikasi 2, Persetujuan), get all active users in that role
        if (empty($targetUsers)) {
            $targetUsers = $db->table('tb_users')
                ->select('ID_USER, NAMA_LENGKAP, NO_HP')
                ->where('JENIS_USER', $roleName)
                ->where('AKTIF', 1)
                ->where('NO_HP IS NOT NULL', null, false)
                ->where('NO_HP !=', '')
                ->get()
                ->getResultArray();
        }

        if (empty($targetUsers)) {
            // Log informative message to tb_wa_logs
            try {
                $db->table('tb_wa_logs')->insert([
                    'NO_TUJUAN'      => '-',
                    'NAMA_PENERIMA'  => $roleName . ' (' . ($kdSkpd ?? 'Semua') . ')',
                    'PESAN'          => $message,
                    'STATUS'         => 'FAILED',
                    'RESPON_GATEWAY' => "Peringatan: Tidak ada akun {$roleName} dengan Nomor HP terdaftar di Manajemen User.",
                    'CREATED_AT'     => date('Y-m-d H:i:s')
                ]);
            } catch (\Throwable $e) {}
        }

        $sentCount = 0;
        foreach ($targetUsers as $user) {
            $phone = $user['NO_HP'] ?? '';
            if (!empty($phone)) {
                $res = $this->send($phone, $message, $user['NAMA_LENGKAP'] ?? $roleName);
                if (($res['status'] ?? '') === 'success') {
                    $sentCount++;
                }
            }
        }

        return $sentCount;
    }

    /**
     * Send notification to active Admin OPD / User in specified SKPD
     */
    public function sendToOpd(string $kdSkpd, string $message): int
    {
        $db = \Config\Database::connect();
        $users = $db->table('tb_users')
            ->select('ID_USER, NAMA_LENGKAP, NO_HP')
            ->where('KD_UNITKER', $kdSkpd)
            ->where('AKTIF', 1)
            ->where('NO_HP IS NOT NULL', null, false)
            ->where('NO_HP !=', '')
            ->get()
            ->getResultArray();

        if (empty($users)) {
            // Log informative message to tb_wa_logs
            try {
                $db->table('tb_wa_logs')->insert([
                    'NO_TUJUAN'      => '-',
                    'NAMA_PENERIMA'  => 'Admin OPD (' . $kdSkpd . ')',
                    'PESAN'          => $message,
                    'STATUS'         => 'FAILED',
                    'RESPON_GATEWAY' => "Peringatan: Tidak ada akun Admin OPD (KD: {$kdSkpd}) dengan Nomor HP terdaftar di Manajemen User.",
                    'CREATED_AT'     => date('Y-m-d H:i:s')
                ]);
            } catch (\Throwable $e) {}
        }

        $sentCount = 0;
        foreach ($users as $u) {
            $phone = $u['NO_HP'] ?? '';
            if (!empty($phone)) {
                $res = $this->send($phone, $message, $u['NAMA_LENGKAP'] ?? 'Admin OPD');
                if (($res['status'] ?? '') === 'success') {
                    $sentCount++;
                }
            }
        }

        return $sentCount;
    }

    /**
     * Trigger 1: OPD submits new NPD / SPM -> Notifies Verifikasi 1 and sends receipt to OPD
     */
    public function notifyNewSubmission(string $type, string $idPengajuan, string $nmSkpd, string $kegiatan, float $anggaran, string $kdSkpd): void
    {
        $formattedNominal = 'Rp ' . number_format($anggaran, 2, ',', '.');
        $date = date('d-m-Y H:i');

        // 1. Notifikasi tugas ke Verifikator 1
        $messageVerif = "🔔 *NOTIFIKASI SIPEKDA POLMAN*\n"
            . "Halo Bapak/Ibu *Verifikator 1*,\n\n"
            . "Terdapat pengajuan baru yang membutuhkan verifikasi Anda:\n"
            . "• *Jenis*: " . strtoupper($type) . "\n"
            . "• *No. Pengajuan*: {$idPengajuan}\n"
            . "• *OPD*: {$nmSkpd}\n"
            . "• *Kegiatan*: {$kegiatan}\n"
            . "• *Nilai*: {$formattedNominal}\n"
            . "• *Waktu*: {$date} WITA\n\n"
            . "Silakan login ke SIPEKDA untuk memeriksa dokumen pengajuan. Terima kasih.";

        $this->sendToRole('Verifikasi 1', $messageVerif, $kdSkpd);

        // 2. Notifikasi konfirmasi tanda terima ke Admin OPD pengirim
        $messageOpd = "📤 *PENGIRIMAN PENGAJUAN BERHASIL - SIPEKDA*\n"
            . "Halo Rekan Pengelola Keuangan *{$nmSkpd}*,\n\n"
            . "Pengajuan Anda telah berhasil dikirim ke BPKAD dan sedang menunggu antrean *Verifikasi 1*:\n"
            . "• *Jenis*: " . strtoupper($type) . "\n"
            . "• *No. Pengajuan*: {$idPengajuan}\n"
            . "• *Kegiatan*: {$kegiatan}\n"
            . "• *Nilai*: {$formattedNominal}\n"
            . "• *Status*: Menunggu Verifikasi 1\n"
            . "• *Waktu*: {$date} WITA\n\n"
            . "Anda akan menerima notifikasi WhatsApp kembali saat pengajuan diverifikasi/disetujui. Terima kasih.";

        $this->sendToOpd($kdSkpd, $messageOpd);
    }

    /**
     * Trigger 2: Verification progress (e.g. Verif 1 approved -> notify Verif 2; Verif 2 approved -> notify KBUD)
     */
    public function notifyVerificationProgress(string $type, string $idPengajuan, string $nmSkpd, string $kegiatan, float $anggaran, string $targetRole, string $kdSkpd): void
    {
        $formattedNominal = 'Rp ' . number_format($anggaran, 2, ',', '.');
        $date = date('d-m-Y H:i');

        $roleTitle = ($targetRole === 'Persetujuan') ? 'Kuasa BUD (KBUD)' : 'Verifikator 2';
        $tahapDesc = ($targetRole === 'Persetujuan') ? 'Persetujuan Akhir' : 'Verifikasi Tahap 2';

        $message = "🔔 *NOTIFIKASI SIPEKDA POLMAN*\n"
            . "Halo Bapak/Ibu *{$roleTitle}*,\n\n"
            . "Pengajuan berikut telah lolos verifikasi tahap sebelumnya dan sekarang membutuhkan *{$tahapDesc}* dari Anda:\n"
            . "• *Jenis*: " . strtoupper($type) . "\n"
            . "• *No. Pengajuan*: {$idPengajuan}\n"
            . "• *OPD*: {$nmSkpd}\n"
            . "• *Kegiatan*: {$kegiatan}\n"
            . "• *Nilai*: {$formattedNominal}\n"
            . "• *Waktu*: {$date} WITA\n\n"
            . "Silakan login ke SIPEKDA untuk menindaklanjuti pengajuan ini. Terima kasih.";

        $this->sendToRole($targetRole, $message, $kdSkpd);
    }

    /**
     * Trigger 3: Rejection -> Notifies OPD with reason
     */
    public function notifyRejection(string $type, string $idPengajuan, string $nmSkpd, string $kegiatan, float $anggaran, string $alasan, string $kdSkpd): void
    {
        $formattedNominal = 'Rp ' . number_format($anggaran, 2, ',', '.');
        $date = date('d-m-Y H:i');

        $message = "⚠️ *PEMBERITAHUAN PENOLAKAN - SIPEKDA*\n"
            . "Halo Rekan Pengelola Keuangan *{$nmSkpd}*,\n\n"
            . "Pengajuan Anda telah *DITOLAK* pada tahap verifikasi BPKAD:\n"
            . "• *Jenis*: " . strtoupper($type) . "\n"
            . "• *No. Pengajuan*: {$idPengajuan}\n"
            . "• *Kegiatan*: {$kegiatan}\n"
            . "• *Nilai*: {$formattedNominal}\n"
            . "• *Waktu*: {$date} WITA\n\n"
            . "📌 *Alasan Penolakan*:\n"
            . "👉 \"{$alasan}\"\n\n"
            . "Silakan login ke SIPEKDA untuk memeriksa catatan dan melakukan perbaikan pengajuan. Terima kasih.";

        $this->sendToOpd($kdSkpd, $message);
    }

    /**
     * Trigger 4: Final Approval -> Notifies OPD (SP2D Selesai)
     */
    public function notifyApprovalFinal(string $type, string $idPengajuan, string $nmSkpd, string $kegiatan, float $anggaran, string $kdSkpd): void
    {
        $formattedNominal = 'Rp ' . number_format($anggaran, 2, ',', '.');
        $date = date('d-m-Y H:i');

        $statusText = ($type === 'SPM') ? 'Selesai / Terbit SP2D' : 'Disetujui BPKAD (Siap Ditarik ke SPM)';

        $message = "✅ *PENGAJUAN DISETUJUI - SIPEKDA*\n"
            . "Halo Rekan Pengelola Keuangan *{$nmSkpd}*,\n\n"
            . "Kabar baik! Pengajuan Anda telah mendapatkan *Persetujuan Akhir* dari BPKAD:\n"
            . "• *Jenis*: " . strtoupper($type) . "\n"
            . "• *No. Pengajuan*: {$idPengajuan}\n"
            . "• *Kegiatan*: {$kegiatan}\n"
            . "• *Nilai*: {$formattedNominal}\n"
            . "• *Status*: {$statusText}\n"
            . "• *Waktu*: {$date} WITA\n\n"
            . "Proses pencairan dana akan segera diteruskan ke Kas Daerah (Bank Sulselbar). Terima kasih.";

        $this->sendToOpd($kdSkpd, $message);
    }
}
