<?php

namespace App\Models;

use CodeIgniter\Model;

class NpdModel extends Model
{
    protected $table            = 'tb_npd';
    protected $primaryKey       = 'ID_NPD';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'ID_PENGAJUAN',
        'TGL_PENGAJUAN',
        'KD_SKPD',
        'NM_PROGRAM_KEGIATAN_SUBKEGIATAN',
        'KD_REKENING_BELANJA',
        'ANGGARAN',
        'TOTAL_ANGGARAN',
        'KD_SUMBER_DANA',
        'KD_STATUS',
        'ALASAN_PENOLAKAN',
        'TGL_VEIFIKASI',
        'TGL_SP2D'
    ];
    protected $useTimestamps    = false;

    public function generateID(): string
    {
        $last = $this->orderBy('ID_PENGAJUAN', 'DESC')->first();
        $num = 1;
        if ($last && !empty($last['ID_PENGAJUAN'])) {
            $parts = explode('.', $last['ID_PENGAJUAN']);
            // e.g. NPD.000001.03.2025 -> parts[1] is number
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $num = intval($parts[1]) + 1;
            }
        }
        $formattedNum = str_pad((string)$num, 6, '0', STR_PAD_LEFT);
        $month = date('m');
        $year = date('Y');
        return "NPD.{$formattedNum}.{$month}.{$year}";
    }
}
