<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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

        // El middleware "tenant" ya puso tenant_id en los atributos
        $tenantId = $request->attributes->get('tenant_id');

        // Rol del usuario en ese tenant (tabla pivot user_tenant)
        $role = null;
        if ($tenantId) {
            $role = DB::table('user_tenant')
                ->where('user_id', $user->id)
                ->where('tenant_id', $tenantId)
                ->value('role');
        }
        $roles = $role ? [$role] : [];

        // Slug del tenant (útil para el front)
        $tenantSlug = $tenantId ? Tenant::where('id', $tenantId)->value('slug') : null;

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
            'roles'  => $roles,       // <-- el front usa esto para el menú/guards
            'tenant' => $tenantSlug,  // <-- para mostrar/cambiar unidad de negocio
        ]);
    }

    // POST /api/v1/auth/logout  -> 200
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['message' => 'ok']);
    }
}
