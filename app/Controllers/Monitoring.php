<?php

namespace App\Controllers;

class Monitoring extends BaseController
{
    public function daftarTransaksi()
    {
        $loginData = session()->get('LoginData');
        if (!$loginData) {
            return redirect()->to(base_url('user/login'));
        }

        $data = [
            'title'        => 'Daftar Transaksi',
            'header'       => 'DAFTAR TRANSAKSI',
            'keterangan'   => 'Daftar seluruh permintaan transaksi.',
            'NAMA_LENGKAP' => $loginData['NAMA_LENGKAP'] ?? '',
            'NM_UNITKER'   => $loginData['NM_UNITKER'] ?? '',
            'LAST_LOGIN'   => !empty($loginData['LAST_LOGIN']) ? '[' . date('d-m-Y H:i:s', strtotime($loginData['LAST_LOGIN'])) . ']' : '',
        ];

        return view('monitoring/daftar_transaksi', $data);
    }

    public function daftarRTGS()
    {
        $loginData = session()->get('LoginData');
        if (!$loginData) {
            return redirect()->to(base_url('user/login'));
        }

        $data = [
            'title'        => 'Daftar RTGS',
            'header'       => 'DAFTAR RTGS',
            'keterangan'   => 'Daftar transaksi RTGS perbankan.',
            'NAMA_LENGKAP' => $loginData['NAMA_LENGKAP'] ?? '',
            'NM_UNITKER'   => $loginData['NM_UNITKER'] ?? '',
            'LAST_LOGIN'   => !empty($loginData['LAST_LOGIN']) ? '[' . date('d-m-Y H:i:s', strtotime($loginData['LAST_LOGIN'])) . ']' : '',
        ];

        return view('monitoring/daftar_rtgs', $data);
    }
}
