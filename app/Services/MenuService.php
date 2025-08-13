<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class MenuService
{
    public static function forUser(int $userId, int $tenantId): array
    {
        $perms = PermissionService::effective($userId, $tenantId);
        if (empty($perms)) return ['categories' => []];

        $modules = DB::table('modules')
            ->join('module_permission', 'modules.id', '=', 'module_permission.module_id')
            ->join('permissions', 'permissions.id', '=', 'module_permission.permission_id')
            ->where('modules.is_active', true)
            ->whereIn('permissions.slug', $perms)
            ->select('modules.*')
            ->distinct()
            ->orderBy('modules.sort')
            ->get();

        $cats = DB::table('categories')->orderBy('sort')->get()->keyBy('id');
        $grouped = [];
        foreach ($modules as $m) {
            $cat = $cats[$m->category_id] ?? null;
            $slug = $cat->slug ?? '_';
            if (!isset($grouped[$slug])) {
                $grouped[$slug] = [
                    'name' => $cat->name ?? 'Otros',
                    'slug' => $slug,
                    'sort' => $cat->sort ?? 999,
                    'modules' => [],
                ];
            }
            $grouped[$slug]['modules'][] = [
                'name'  => $m->name,
                'slug'  => $m->slug,
                'route' => $m->route,
                'icon'  => $m->icon,
                'sort'  => $m->sort,
            ];
        }

        // ordenar categorías por sort
        uasort($grouped, fn($a,$b) => $a['sort'] <=> $b['sort']);
        return ['categories' => array_values($grouped)];
    }
}
