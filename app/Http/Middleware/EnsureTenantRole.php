<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnsureTenantRole
{
    /**
     * Uso: ->middleware(['auth:sanctum','tenant','role:admin']) o role:admin,manager
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        $tenantId = $request->attributes->get('tenant_id');

        if (!$user || !$tenantId) {
            return response()->json(['message' => 'Tenant o usuario no resuelto'], 400);
        }

        $role = DB::table('user_tenant')
            ->where('user_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->value('role');

        if (!$role || (!empty($roles) && !in_array($role, $roles, true))) {
            return response()->json(['message' => 'No autorizado (rol requerido)'], 403);
        }

        return $next($request);
    }
}
