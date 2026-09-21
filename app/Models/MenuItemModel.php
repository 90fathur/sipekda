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

    public function getHeaderMenu(string $jenisUser)
    {
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
