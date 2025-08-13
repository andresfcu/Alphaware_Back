<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnsureTenantRole
{
    /**
     * Uso: ->middleware(['auth:sanctum','tenant','role:admin,manager'])
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        $tenantId = $request->attributes->get('tenant_id');

        if (!$user || !$tenantId) {
            return response()->json(['message' => 'No autenticado o tenant ausente'], 401);
        }
        $activeRoleId = DB::table('user_tenant')
        ->where('user_id', $user->id)
        ->where('tenant_id', $tenantId)
        ->value('active_role_id');

        if (!$activeRoleId) return response()->json(['message'=>'No autorizado'],403);

        if (!empty($roles)) {
        $ok = DB::table('roles')->whereIn('slug',$roles)->where('id',$activeRoleId)->exists();
        if (!$ok) return response()->json(['message'=>'No autorizado (rol requerido)'],403);
        }

        return $next($request);
    }
}
