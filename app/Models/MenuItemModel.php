<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuItemModel extends Model
{
    protected $table            = 'tb_menu_items';
    protected $primaryKey       = 'ID_MENU';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'NM_MENU',
        'NM_CONTROLLER',
        'NM_ACTION',
        'KD_MENU',
        'MASTER_MENU',
        'CHILD',
        'PENGATURAN',
        'DASHBOARD',
        'ICON',
        'ADMIN',
        'USER',
        'VERIFIKASI_1',
        'VERIFIKASI_2',
        'PERSETUJUAN'
    ];
    protected $useTimestamps    = false;

    private static bool $isSynced = false;

    public function ensureMenuSynced(): void
    {
        if (self::$isSynced) {
            return;
        }
        self::$isSynced = true;

        try {
            $db = \Config\Database::connect();

            // 1. Ensure Persetujuan SPP / SPM has PERSETUJUAN = 1, VERIFIKASI_1 = 1, VERIFIKASI_2 = 1, ADMIN = 1
            $db->table($this->table)
                ->where('NM_ACTION', 'PersetujuanSPMHome')
                ->set([
                    'PERSETUJUAN'  => 1,
                    'VERIFIKASI_1' => 1,
                    'VERIFIKASI_2' => 1,
                    'ADMIN'        => 1
                ])->update();

            // 2. Ensure Persetujuan Pencairan SP2D has PERSETUJUAN = 0 (KBUD does not need this menu because SP2D is transferred to SIPD)
            $db->table($this->table)
                ->where('NM_ACTION', 'PersetujuanSP2DHome')
                ->set([
                    'PERSETUJUAN'  => 0,
                    'VERIFIKASI_1' => 0,
                    'VERIFIKASI_2' => 0,
                    'ADMIN'        => 1
                ])->update();

            // 3. Ensure Persetujuan NPD exists under BPKAD (MASTER_MENU = 11)
            $persetujuanNpd = $db->table($this->table)->where('NM_ACTION', 'PersetujuanNPDHome')->get()->getRowArray();
            if (!$persetujuanNpd) {
                $db->table($this->table)->insert([
                    'NM_MENU'       => 'Persetujuan NPD',
                    'NM_CONTROLLER' => 'BPKAD',
                    'NM_ACTION'     => 'PersetujuanNPDHome',
                    'KD_MENU'       => 1,
                    'MASTER_MENU'   => 11,
                    'CHILD'         => 0,
                    'PENGATURAN'    => 0,
                    'DASHBOARD'     => 0,
                    'ICON'          => '',
                    'ADMIN'         => 1,
                    'USER'          => 0,
                    'VERIFIKASI_1'  => 1,
                    'VERIFIKASI_2'  => 1,
                    'PERSETUJUAN'   => 1
                ]);
            } else {
                $db->table($this->table)
                    ->where('NM_ACTION', 'PersetujuanNPDHome')
                    ->set([
                        'NM_MENU'       => 'Persetujuan NPD',
                        'NM_CONTROLLER' => 'BPKAD',
                        'KD_MENU'       => 1,
                        'MASTER_MENU'   => 11,
                        'CHILD'         => 0,
                        'ADMIN'         => 1,
                        'VERIFIKASI_1'  => 1,
                        'VERIFIKASI_2'  => 1,
                        'PERSETUJUAN'   => 1
                    ])->update();
            }

            // 4. Ensure Pengajuan NPD exists under Pengajuan (MASTER_MENU = 10)
            $pengajuanNpd = $db->table($this->table)->where('NM_ACTION', 'PengajuanNPDHome')->get()->getRowArray();
            if (!$pengajuanNpd) {
                $db->table($this->table)->insert([
                    'NM_MENU'       => 'NPD',
                    'NM_CONTROLLER' => 'SPM',
                    'NM_ACTION'     => 'PengajuanNPDHome',
                    'KD_MENU'       => 1,
                    'MASTER_MENU'   => 10,
                    'CHILD'         => 0,
                    'PENGATURAN'    => 0,
                    'DASHBOARD'     => 0,
                    'ICON'          => '',
                    'ADMIN'         => 1,
                    'USER'          => 1,
                    'VERIFIKASI_1'  => 0,
                    'VERIFIKASI_2'  => 0,
                    'PERSETUJUAN'   => 0
                ]);
            }

            // 5. Ensure BPKAD header (MASTER_MENU = 11, KD_MENU = 11) is visible to PERSETUJUAN
            $db->table($this->table)
                ->where('MASTER_MENU', 11)
                ->where('KD_MENU', 11)
                ->set([
                    'PERSETUJUAN'  => 1,
                    'VERIFIKASI_1' => 1,
                    'VERIFIKASI_2' => 1,
                    'ADMIN'        => 1
                ])->update();

            // 6. Ensure Monitoring SP2D is visible to all verification roles and User
            $db->table($this->table)
                ->where('NM_ACTION', 'StatusPengajuanMonitoringHome')
                ->set([
                    'USER'         => 1,
                    'VERIFIKASI_1' => 1,
                    'VERIFIKASI_2' => 1,
                    'PERSETUJUAN'  => 1,
                    'ADMIN'        => 1
                ])->update();

            // 7. Ensure Pengaturan WhatsApp exists under Pengaturan (MASTER_MENU = 14) for Admin
            $waMenu = $db->table($this->table)->where('NM_ACTION', 'WaGatewayHome')->get()->getRowArray();
            if (!$waMenu) {
                $db->table($this->table)->insert([
                    'NM_MENU'       => 'Pengaturan WhatsApp',
                    'NM_CONTROLLER' => 'Setting',
                    'NM_ACTION'     => 'WaGatewayHome',
                    'KD_MENU'       => 4,
                    'MASTER_MENU'   => 14,
                    'CHILD'         => 0,
                    'PENGATURAN'    => 0,
                    'DASHBOARD'     => 0,
                    'ICON'          => '',
                    'ADMIN'         => 1,
                    'USER'          => 0,
                    'VERIFIKASI_1'  => 0,
                    'VERIFIKASI_2'  => 0,
                    'PERSETUJUAN'   => 0
                ]);
            } else {
                $db->table($this->table)
                    ->where('NM_ACTION', 'WaGatewayHome')
                    ->set([
                        'NM_MENU'       => 'Pengaturan WhatsApp',
                        'NM_CONTROLLER' => 'Setting',
                        'KD_MENU'       => 4,
                        'MASTER_MENU'   => 14,
                        'ADMIN'         => 1
                    ])->update();
            }
        } catch (\Throwable $e) {
            // Silently ignore if db is not ready
        }
    }

    public function getHeaderMenu(string $jenisUser)
    {
        $this->ensureMenuSynced();

        $builder = $this->builder();
        $builder->groupStart()
                ->where('KD_MENU = MASTER_MENU', null, false)
                ->orWhere('DASHBOARD', 1)
                ->orWhere('PENGATURAN', 1)
                ->groupEnd();

        $roleCol = match ($jenisUser) {
            'Admin'        => 'ADMIN',
            'User'         => 'USER',
            'Verifikasi 1' => 'VERIFIKASI_1',
            'Verifikasi 2' => 'VERIFIKASI_2',
            'Persetujuan'  => 'PERSETUJUAN',
            default        => 'USER'
        };

        $builder->where($roleCol, 1);
        $builder->orderBy('MASTER_MENU', 'ASC');
        $builder->orderBy('DASHBOARD', 'DESC');
        $builder->orderBy('PENGATURAN', 'ASC');

        return $builder->get()->getResultArray();
    }

    public function getItemMenu(string $jenisUser)
    {
        $this->ensureMenuSynced();

        $builder = $this->builder();
        $builder->where('KD_MENU != MASTER_MENU', null, false);
        $builder->whereIn('PENGATURAN', [0, 2]);
        $builder->where('DASHBOARD', 0);

        $roleCol = match ($jenisUser) {
            'Admin'        => 'ADMIN',
            'User'         => 'USER',
            'Verifikasi 1' => 'VERIFIKASI_1',
            'Verifikasi 2' => 'VERIFIKASI_2',
            'Persetujuan'  => 'PERSETUJUAN',
            default        => 'USER'
        };

        $builder->where($roleCol, 1);
        $builder->orderBy('MASTER_MENU', 'ASC');
        $builder->orderBy('KD_MENU', 'ASC');

        return $builder->get()->getResultArray();
    }
}
