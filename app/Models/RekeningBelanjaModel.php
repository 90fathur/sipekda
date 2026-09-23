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
        'KD_SKPD',
        'NM_SKPD',
        'KD_REKENING_BELANJA',
        'NM_REKENING_BELANJA',
        'PAGU'
    ];
    protected $useTimestamps    = false;

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

            if (!$db->fieldExists('KD_SKPD', $this->table)) {
                $forge->addColumn($this->table, [
                    'KD_SKPD' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 50,
                        'null'       => true,
                        'after'      => 'ID_REKENING_BELANJA'
                    ]
                ]);
            }

            if (!$db->fieldExists('NM_SKPD', $this->table)) {
                $forge->addColumn($this->table, [
                    'NM_SKPD' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 150,
                        'null'       => true,
                        'after'      => 'KD_SKPD'
                    ]
                ]);
            }
        } catch (\Throwable $e) {
            // Silently ignore if already exists or db not ready
        }
    }
}
