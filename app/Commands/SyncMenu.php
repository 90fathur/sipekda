<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SyncMenu extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'App';
    protected $name = 'menu:sync';
    protected $description = 'Synchronize menu items in tb_menu_items';

    public function run(array $params)
    {
        CLI::write('Synchronizing tb_menu_items & ms_rekening_belanja...', 'yellow');
        try {
            $menuModel = new \App\Models\MenuItemModel();
            $menuModel->ensureMenuSynced();

            $rekModel = new \App\Models\RekeningBelanjaModel();
            $rekModel->ensureSchema();

            $waModel = new \App\Models\WaGatewayModel();
            $waModel->ensureSchema();

            $userModel = new \App\Models\UserModel();
            $userModel->ensureSchema();

            CLI::write('Menu and database schema synchronized successfully!', 'green');
        } catch (\Throwable $e) {
            CLI::error('Error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
        }
    }
}
