<?php

namespace App\Models;

use CodeIgniter\Model;

class UserLogModel extends Model
{
    protected $table            = 'tb_user_log';
    protected $primaryKey       = 'ID_LOG';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'TANGGAL_JAM',
        'NAMA_USER',
        'IP_ADDRESS',
        'STATUS',
        'KET'
    ];
    protected $useTimestamps    = false;
}
