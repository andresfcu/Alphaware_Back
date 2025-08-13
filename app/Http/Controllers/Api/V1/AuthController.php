<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Services\PermissionService;

class AuthController extends Controller
{
    // POST /api/v1/auth/login  -> { token, user }
    public function login(LoginRequest $request)
{
    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Credenciales inválidas'], 401);
    }

    $token = $user->createToken('spa')->plainTextToken;

    // Buscar tenant principal (o el primero asociado)
    $tenant = DB::table('user_tenant')
        ->join('tenants', 'tenants.id', '=', 'user_tenant.tenant_id')
        ->where('user_tenant.user_id', $user->id)
        ->value('tenants.slug'); // Devuelve el slug, ej: 'demo'

    return response()->json([
        'token' => $token,
        'user'  => [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
        ],
        'tenant' => $tenant, // 👈 lo nuevo
    ]);
}

    // GET /api/v1/auth/me  -> { user, roles:[], tenant:slug }
   public function me(Request $request)
{
    $user = $request->user();
    if (!$user) {
        return response()->json(['message' => 'No autorizado'], 401);
    }

    // Resolver tenant slug
    $tenantSlug = $request->attributes->get('tenant_slug')
        ?? $request->header('X-Tenant')
        ?? null;

    if (!$tenantSlug) {
        $tenantSlug = DB::table('user_tenant')
            ->join('tenants', 'tenants.id', '=', 'user_tenant.tenant_id')
            ->where('user_tenant.user_id', $user->id)
            ->orderBy('user_tenant.created_at', 'asc')
            ->value('tenants.slug');
    }

    $tenantId = $tenantSlug
        ? DB::table('tenants')->where('slug', $tenantSlug)->value('id')
        : null;

    if (!$tenantId) {
        return response()->json([
            'user'        => $user,
            'tenant'      => null,
            'active_role' => null,
            'permissions' => [],
        ]);
    }

    // Lista completa de roles asignados (multi-rol)
    $roles = DB::table('user_tenant_roles')
        ->join('roles', 'roles.id', '=', 'user_tenant_roles.role_id')
        ->where('user_tenant_roles.user_id', $user->id)
        ->where('user_tenant_roles.tenant_id', $tenantId)
        ->select('roles.id','roles.slug','roles.name')
        ->orderBy('roles.name')
        ->get();

    // Rol activo (si no hay, autopick = primero y lo guardamos)
    $active = DB::table('user_tenant')
        ->where('user_id', $user->id)
        ->where('tenant_id', $tenantId)
        ->value('active_role_id');

    $activeRole = null;
    if ($active) {
        $activeRole = DB::table('roles')->select('id','slug','name')->find($active);
    } else {
        $first = $roles->first();
        if ($first) {
            $activeRole = $first;
            DB::table('user_tenant')
                ->where('user_id', $user->id)
                ->where('tenant_id', $tenantId)
                ->update(['active_role_id' => $first->id]);
        }
    }

    // Permisos del rol activo (no unión)
    $perms = \App\Services\PermissionService::effective($user->id, $tenantId, false);

    return response()->json([
        'user'        => $user,
        'tenant'      => $tenantSlug,
        'active_role' => $activeRole,
        'permissions' => $perms,
        // Si quieres, también devuelve todos los roles para ahorrar una llamada:
        // 'roles'       => $roles,
    ]);
}

    // POST /api/v1/auth/logout  -> 200
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['message' => 'ok']);
    }
}
