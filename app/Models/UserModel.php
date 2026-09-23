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
        'NO_HP',
        'AKTIF',
        'STS_BLOCK',
        'JAM_BLOCK'
    ];
    protected $useTimestamps = false;

    private static bool $isSchemaChecked = false;

    public function __construct()
    {
        parent::__construct();
        $this->ensureSchema();
    }

    public function ensureSchema(): void
    {
        if (self::$isSchemaChecked) {
            return;
        }
        self::$isSchemaChecked = true;

        try {
            $db = \Config\Database::connect();
            $forge = \Config\Database::forge();

            if (!$db->fieldExists('NO_HP', $this->table)) {
                $forge->addColumn($this->table, [
                    'NO_HP' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 30,
                        'null'       => true,
                        'after'      => 'KD_UNITKER'
                    ]
                ]);
            }
        } catch (\Throwable $e) {
            // Silently ignore if already exists or db not ready
        }
    }
}
