<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MenuService
{
    /**
     * Build the 3-level sidebar menu tree from DASH.MST_MENU, mirroring the
     * legacy CodeIgniter sidebar query (SUB_MENU 0/1/2 + GROUP_MENU parent).
     */
    public function tree(): array
    {
        return Cache::remember('dash.menu.tree', now()->addMinutes(5), function () {
            $top = $this->fetch(0);

            foreach ($top as &$group) {
                $children = $this->fetch(1, $group->ID_MENU);

                foreach ($children as &$child) {
                    $child->children = $this->fetch(2, $child->ID_MENU);
                }

                $group->children = $children;
            }

            return $top;
        });
    }

    private function fetch(int $subMenu, ?int $groupMenu = null): array
    {
        $sql = 'SELECT ID_MENU, GROUP_MENU, SUB_MENU, ICON, NAMA_MENU, ALAMAT
                FROM DASH.MST_MENU
                WHERE STATUS = \'Aktif\' AND SUB_MENU = ?';
        $bindings = [$subMenu];

        if ($groupMenu !== null) {
            $sql .= ' AND GROUP_MENU = ?';
            $bindings[] = $groupMenu;
        }

        $sql .= ' ORDER BY ID_MENU';

        return DB::select($sql, $bindings);
    }
}
