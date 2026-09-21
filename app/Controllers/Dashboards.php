<?php

namespace App\Controllers;

use App\Models\RekeningBelanjaModel;
use App\Models\SpmModel;

class Dashboards extends BaseController
{
    protected RekeningBelanjaModel $rekeningModel;
    protected SpmModel $spmModel;

    public function __construct()
    {
        $this->rekeningModel = new RekeningBelanjaModel();
        $this->spmModel = new SpmModel();
        helper(['url', 'menu']);
    }

    public function main()
    {
        $session = session();
        $loginData = $session->get('LoginData');
        if (empty($loginData)) {
            return redirect()->to(base_url('user/login'));
        }

        $data = [
            'Title'        => 'Dashboard v.4',
            'NAMA_LENGKAP' => $loginData['NAMA_LENGKAP'],
            'NM_UNITKER'   => $loginData['NM_UNITKER'],
            'LAST_LOGIN'   => '[' . date('d-m-Y H:i:s', strtotime($loginData['LAST_LOGIN'] ?? 'now')) . ']'
        ];

        return view('dashboards/main', $data);
    }

    public function totalSP2D()
    {
        $session = session();
        $loginData = $session->get('LoginData');
        if (empty($loginData)) {
            return $this->response->setJSON(0);
        }

        $currentYear = date('Y');
        $builder = $this->spmModel->builder();
        $builder->where('KD_STATUS', 3)
                ->where('YEAR(TGL_PENGAJUAN)', $currentYear);

        if (!in_array($loginData['JENIS_USER'], ['Verifikasi 1', 'Verifikasi 2', 'Persetujuan', 'Admin'])) {
            $builder->where('KD_SKPD', $loginData['KD_UNITKER']);
        }

        return $this->response->setJSON($builder->countAllResults());
    }

    public function nominalSPMMonthly()
    {
        $session = session();
        $loginData = $session->get('LoginData');
        if (empty($loginData)) {
            return $this->response->setBody('0');
        }

        $currentMonth = date('m');
        $currentYear = date('Y');

        $builder = $this->spmModel->builder();
        $builder->selectSum('ANGGARAN')
                ->where('KD_STATUS', 3)
                ->where('MONTH(TGL_PENGAJUAN)', $currentMonth)
                ->where('YEAR(TGL_PENGAJUAN)', $currentYear);

        if (!in_array($loginData['JENIS_USER'], ['Verifikasi 1', 'Verifikasi 2', 'Persetujuan', 'Admin'])) {
            $builder->where('KD_SKPD', $loginData['KD_UNITKER']);
        }

        $res = $builder->get()->getRow();
        $total = $res ? (float)$res->ANGGARAN : 0;

        return $this->response->setBody(number_format($total, 0, ',', '.'));
    }

    public function nominalSPMYearly()
    {
        $session = session();
        $loginData = $session->get('LoginData');
        if (empty($loginData)) {
            return $this->response->setBody('0');
        }

        $currentYear = date('Y');
        $builder = $this->spmModel->builder();
        $builder->selectSum('ANGGARAN')
                ->where('KD_STATUS', 3)
                ->where('YEAR(TGL_PENGAJUAN)', $currentYear);

        if (!in_array($loginData['JENIS_USER'], ['Verifikasi 1', 'Verifikasi 2', 'Persetujuan', 'Admin'])) {
            $builder->where('KD_SKPD', $loginData['KD_UNITKER']);
        }

        $res = $builder->get()->getRow();
        $total = $res ? (float)$res->ANGGARAN : 0;

        return $this->response->setBody(number_format($total, 0, ',', '.'));
    }

    public function getPaguGrafik()
    {
        $db = \Config\Database::connect();
        $totalPagu = (float)($db->table('ms_rekening_belanja')->selectSum('PAGU')->get()->getRow()->PAGU ?? 0);
        $totalBelanja = (float)($db->table('tb_spm')->selectSum('ANGGARAN')->where('KD_STATUS', 3)->get()->getRow()->ANGGARAN ?? 0);
        $sisaPagu = max(0, $totalPagu - $totalBelanja);

        return $this->response->setJSON([
            'labels' => ['TOTAL BELANJA', 'SISA PAGU'],
            'datasets' => [
                [
                    'label' => 'Rekap Anggaran',
                    'backgroundColor' => ['rgba(255, 99, 132, 0.5)', 'rgba(255, 206, 86, 0.5)'],
                    'borderColor' => ['rgba(255, 99, 132, 0.7)', 'rgba(255, 206, 86, 0.7)'],
                    'data' => [$totalBelanja, $sisaPagu]
                ]
            ]
        ]);
    }

    public function getDataGrafik()
    {
        $db = \Config\Database::connect();
        $totalPagu = (float)($db->table('ms_rekening_belanja')->selectSum('PAGU')->get()->getRow()->PAGU ?? 0);
        $totalBelanja = (float)($db->table('tb_spm')->selectSum('ANGGARAN')->where('KD_STATUS', 3)->get()->getRow()->ANGGARAN ?? 0);
        $sisaPagu = max(0, $totalPagu - $totalBelanja);

        return $this->response->setJSON([
            'labels' => ['Anggaran'],
            'datasets' => [
                [
                    'label' => 'TOTAL PAGU',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor' => 'rgba(54, 162, 235, 0.7)',
                    'data' => [$totalPagu]
                ],
                [
                    'label' => 'TOTAL BELANJA',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
                    'borderColor' => 'rgba(255, 99, 132, 0.7)',
                    'data' => [$totalBelanja]
                ],
                [
                    'label' => 'SISA PAGU',
                    'backgroundColor' => 'rgba(255, 206, 86, 0.5)',
                    'borderColor' => 'rgba(255, 206, 86, 0.7)',
                    'data' => [$sisaPagu]
                ]
            ]
        ]);
    }

    public function top5BelanjaChart()
    {
        $db = \Config\Database::connect();
        $rows = $db->table('ms_rekening_belanja rbl')
            ->select('rbl.NM_REKENING_BELANJA as Nama, COALESCE(SUM(spm.ANGGARAN), 0) as TotalBelanja')
            ->join('tb_spm spm', 'rbl.KD_REKENING_BELANJA = spm.KD_REKENING_BELANJA AND spm.KD_STATUS = 3', 'left')
            ->groupBy('rbl.KD_REKENING_BELANJA, rbl.NM_REKENING_BELANJA')
            ->orderBy('TotalBelanja', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        $datasets = [];
        foreach ($rows as $r) {
            $datasets[] = [
                'label'           => $r['Nama'],
                'backgroundColor' => sprintf('rgba(%d, %d, %d, 0.5)', rand(0, 255), rand(0, 255), rand(0, 255)),
                'borderColor'     => 'rgba(0,0,0,0.1)',
                'borderWidth'     => 1,
                'data'            => [(float)$r['TotalBelanja']]
            ];
        }

        return $this->response->setJSON([
            'labels'   => ['Total Belanja'],
            'datasets' => $datasets
        ]);
    }

    public function rincianRealisasiAnggaran()
    {
        $db = \Config\Database::connect();
        $list = $db->table('ms_rekening_belanja rbl')
            ->select('rbl.KD_REKENING_BELANJA, rbl.NM_REKENING_BELANJA, rbl.PAGU, COALESCE(SUM(spm.ANGGARAN), 0) as TOTAL_BELANJA')
            ->join('tb_spm spm', 'rbl.KD_REKENING_BELANJA = spm.KD_REKENING_BELANJA AND spm.KD_STATUS = 3', 'left')
            ->groupBy('rbl.KD_REKENING_BELANJA, rbl.NM_REKENING_BELANJA, rbl.PAGU')
            ->get()
            ->getResultArray();

        foreach ($list as &$item) {
            $item['SISA_PAGU'] = (float)$item['PAGU'] - (float)$item['TOTAL_BELANJA'];
        }

        return $this->response->setJSON($list);
    }
}
