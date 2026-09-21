<?php

namespace App\Models;

use CodeIgniter\Model;

class SpmModel extends Model
{
    protected $table            = 'tb_spm';
    protected $primaryKey       = 'ID_SPM';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'ID_PENGAJUAN',
        'TGL_PENGAJUAN',
        'KD_SKPD',
        'NM_PROGRAM_KEGIATAN_SUBKEGIATAN',
        'KD_REKENING_BELANJA',
        'ANGGARAN',
        'KD_SUMBER_DANA',
        'TOTAL_ANGGARAN',
        'KD_STATUS',
        'ALASAN_PENOLAKAN',
        'TGL_VEIFIKASI',
        'TGL_SP2D',
        'ID_NPD'
    ];
    protected $useTimestamps    = false;

    public function generateID(): string
    {
        $last = $this->orderBy('ID_PENGAJUAN', 'DESC')->first();
        $num = 1;
        if ($last && !empty($last['ID_PENGAJUAN'])) {
            $parts = explode('.', $last['ID_PENGAJUAN']);
            if (isset($parts[0]) && is_numeric($parts[0])) {
                $num = intval($parts[0]) + 1;
            }
        }
        $formattedNum = str_pad((string)$num, 6, '0', STR_PAD_LEFT);
        $month = date('m');
        $year = date('Y');
        return "{$formattedNum}.{$month}.{$year}";
    }
}
