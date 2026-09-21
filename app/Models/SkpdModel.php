<?php

namespace App\Models;

use CodeIgniter\Model;

class SkpdModel extends Model
{
    protected $table            = 'ms_skpd';
    protected $primaryKey       = 'ID_SKPD';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['KD_SKPD', 'NM_SKPD'];
    protected $useTimestamps    = false;
}
