<?php

namespace App\Models;

use CodeIgniter\Model;

class MataAnggaranModel extends Model
{
    protected $table            = 'ms_mata_anggaran';
    protected $primaryKey       = 'ID_MATA_ANGGARAN';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'KD_MATA_ANGGARAN',
        'NM_MATA_ANGGARAN'
    ];
    protected $useTimestamps    = false;
}
