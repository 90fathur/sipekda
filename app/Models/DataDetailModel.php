<?php

namespace App\Models;

use CodeIgniter\Model;

class DataDetailModel extends Model
{
    protected $table            = 'tb_data_detail';
    protected $primaryKey       = 'ID_DETAIL';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'NO_NPD_SPM',
        'KD_REKENING_BELANJA',
        'ANGGARAN',
        'NM_REKENING_BELANJA'
    ];
    protected $useTimestamps    = false;
}
