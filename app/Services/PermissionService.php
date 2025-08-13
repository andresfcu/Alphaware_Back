<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class PermissionService
{
  public static function effective(int $userId, int $tenantId, bool $unionAllRoles = false): array
    {
        if ($unionAllRoles) {
            $roleIds = DB::table('user_tenant_roles')
                ->where('user_id', $userId)
                ->where('tenant_id', $tenantId)
                ->pluck('role_id');
        } else {
            $activeRoleId = DB::table('user_tenant')
                ->where('user_id', $userId)
                ->where('tenant_id', $tenantId)
                ->value('active_role_id');
            $roleIds = collect($activeRoleId ? [$activeRoleId] : []);
        }

        if ($roleIds->isEmpty()) return [];

        return DB::table('permissions')
            ->join('role_permission', 'permissions.id', '=', 'role_permission.permission_id')
            ->whereIn('role_permission.role_id', $roleIds)
            ->pluck('permissions.slug')
            ->unique()
            ->values()
            ->all();
    }

    public static function clearCache(int $userId, int $tenantId): void
    {
        // Opcional: si usas tag-based cache, podrías invalidar por tag.
        // Aquí asumimos que cambia el role_id, así que borramos todas las variantes simples.
        foreach (cache()->getMultiple([]) as $k => $_) {
            if (is_string($k) && str_starts_with($k, "perm:$userId:$tenantId:")) {
                cache()->forget($k);
            }
        }
    }
}
