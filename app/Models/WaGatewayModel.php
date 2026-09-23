<?php

namespace App\Models;

use CodeIgniter\Model;

class WaGatewayModel extends Model
{
    protected $table            = 'tb_wa_gateway';
    protected $primaryKey       = 'ID_SETTING';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'IS_ACTIVE',
        'PROVIDER',
        'API_KEY',
        'SENDER_NUMBER',
        'ENDPOINT_URL',
        'UPDATED_AT'
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

            // 1. Create tb_wa_gateway if not exists
            if (!$db->tableExists('tb_wa_gateway')) {
                $forge->addField([
                    'ID_SETTING' => [
                        'type'           => 'INT',
                        'constraint'     => 11,
                        'unsigned'       => true,
                        'auto_increment' => true
                    ],
                    'IS_ACTIVE' => [
                        'type'       => 'TINYINT',
                        'constraint' => 1,
                        'default'    => 0
                    ],
                    'PROVIDER' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 50,
                        'default'    => 'cloudchat'
                    ],
                    'API_KEY' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 255,
                        'null'       => true
                    ],
                    'SENDER_NUMBER' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 30,
                        'null'       => true
                    ],
                    'ENDPOINT_URL' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 255,
                        'null'       => true
                    ],
                    'UPDATED_AT' => [
                        'type' => 'DATETIME',
                        'null' => true
                    ]
                ]);
                $forge->addKey('ID_SETTING', true);
                $forge->createTable('tb_wa_gateway', true);

                // Insert default initial row
                $db->table('tb_wa_gateway')->insert([
                    'IS_ACTIVE'    => 0,
                    'PROVIDER'     => 'cloudchat',
                    'API_KEY'      => '',
                    'ENDPOINT_URL' => 'https://app.cloudchat.id/api/public/v1/messages',
                    'UPDATED_AT'   => date('Y-m-d H:i:s')
                ]);
            }

            // 2. Create tb_wa_logs if not exists
            if (!$db->tableExists('tb_wa_logs')) {
                $forge->addField([
                    'ID_LOG' => [
                        'type'           => 'INT',
                        'constraint'     => 11,
                        'unsigned'       => true,
                        'auto_increment' => true
                    ],
                    'NO_TUJUAN' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 30
                    ],
                    'NAMA_PENERIMA' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 100,
                        'null'       => true
                    ],
                    'PESAN' => [
                        'type' => 'TEXT'
                    ],
                    'STATUS' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 20,
                        'default'    => 'PENDING'
                    ],
                    'RESPON_GATEWAY' => [
                        'type' => 'TEXT',
                        'null' => true
                    ],
                    'CREATED_AT' => [
                        'type' => 'DATETIME'
                    ]
                ]);
                $forge->addKey('ID_LOG', true);
                $forge->createTable('tb_wa_logs', true);
            }
        } catch (\Throwable $e) {
            // Silently ignore if already exists or db not ready
        }
    }

    public function getConfig(): array
    {
        $this->ensureSchema();
        $config = $this->first();
        if (!$config) {
            return [
                'IS_ACTIVE'     => 0,
                'PROVIDER'      => 'cloudchat',
                'API_KEY'       => '',
                'SENDER_NUMBER' => '',
                'ENDPOINT_URL'  => 'https://app.cloudchat.id/api/public/v1/messages'
            ];
        }

        // Auto-migrate previous default fonnte to cloudchat (Chatbot.id) if api key is not yet set
        if (empty($config['API_KEY']) && ($config['PROVIDER'] === 'fonnte' || empty($config['PROVIDER']))) {
            $this->update($config['ID_SETTING'], [
                'PROVIDER'     => 'cloudchat',
                'ENDPOINT_URL' => 'https://app.cloudchat.id/api/public/v1/messages'
            ]);
            $config['PROVIDER'] = 'cloudchat';
            $config['ENDPOINT_URL'] = 'https://app.cloudchat.id/api/public/v1/messages';
        }

        return $config;
    }
}
