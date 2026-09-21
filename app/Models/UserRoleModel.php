<?php

namespace App\Models;

use CodeIgniter\Model;

class UserRoleModel extends Model
{
    protected $table            = 'tb_user_role';
    protected $primaryKey       = 'ID_USER_ROLE';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['USERNAME', 'KD_SKPD'];
    protected $useTimestamps    = false;
}
