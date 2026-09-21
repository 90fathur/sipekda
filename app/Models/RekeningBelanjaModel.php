<?php

namespace App\Models;

use CodeIgniter\Model;

class RekeningBelanjaModel extends Model
{
    protected $table            = 'ms_rekening_belanja';
    protected $primaryKey       = 'ID_REKENING_BELANJA';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'KD_REKENING_BELANJA',
        'NM_REKENING_BELANJA',
        'PAGU'
    ];
    protected $useTimestamps    = false;
}
