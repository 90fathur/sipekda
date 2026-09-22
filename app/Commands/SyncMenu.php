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
        CLI::write('Synchronizing tb_menu_items...', 'yellow');
        try {
            $model = new \App\Models\MenuItemModel();
            $model->ensureMenuSynced();
            CLI::write('Menu synchronized successfully for KBUD, Verifikator, and Admin!', 'green');
        } catch (\Throwable $e) {
            CLI::error('Error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
        }
    }
}
