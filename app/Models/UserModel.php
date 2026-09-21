<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'tb_users';
    protected $primaryKey       = 'ID_USER';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'USER_NAME',
        'NAMA_LENGKAP',
        'PASSWORD',
        'JENIS_USER',
        'KD_UNITKER',
        'AKTIF',
        'STS_BLOCK',
        'JAM_BLOCK'
    ];
    protected $useTimestamps = false;
}
