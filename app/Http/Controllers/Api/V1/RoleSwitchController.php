<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PermissionService;

class RoleSwitchController extends Controller
{
    public function list(Request $request)
    {
        $user = $request->user();
        $tenantId = DB::table('tenants')->where('slug',
            $request->attributes->get('tenant_slug') ?? $request->header('X-Tenant')
        )->value('id');

        if (!$tenantId) return response()->json(['roles' => []]);

        $rows = DB::table('user_tenant_roles')
            ->join('roles','roles.id','=','user_tenant_roles.role_id')
            ->where('user_tenant_roles.user_id',$user->id)
            ->where('user_tenant_roles.tenant_id',$tenantId)
            ->select('roles.id','roles.slug','roles.name')
            ->orderBy('roles.name')
            ->get();

        return response()->json(['roles' => $rows]);
    }

    // POST /api/v1/me/roles/switch  { role_id }
    public function switch(Request $request)
    {
        $request->validate(['role_id' => ['required','integer','exists:roles,id']]);

        $user = $request->user();
        $tenantSlug = $request->attributes->get('tenant_slug') ?? $request->header('X-Tenant');
        $tenantId = DB::table('tenants')->where('slug',$tenantSlug)->value('id');

        if (!$tenantId) return response()->json(['message'=>'Tenant inválido'], 422);

        // verificar que ese rol está asignado al usuario en ese tenant
        $has = DB::table('user_tenant_roles')
            ->where('user_id',$user->id)
            ->where('tenant_id',$tenantId)
            ->where('role_id',$request->integer('role_id'))
            ->exists();

        if (!$has) return response()->json(['message'=>'Rol no asignado a este usuario/tenant'], 422);

        DB::table('user_tenant')
            ->where('user_id',$user->id)
            ->where('tenant_id',$tenantId)
            ->update(['active_role_id' => $request->integer('role_id')]);

        PermissionService::clearCache($user->id, $tenantId);

        return response()->json(['message' => 'ok']);
    }
}