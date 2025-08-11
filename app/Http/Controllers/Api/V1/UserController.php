<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\{StoreUserRequest, UpdateUserRequest, UpdateUserRoleRequest};
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->attributes->get('tenant_id');

        $users = DB::table('users')
            ->join('user_tenant', 'users.id', '=', 'user_tenant.user_id')
            ->where('user_tenant.tenant_id', $tenantId)
            ->select('users.id','users.name','users.email','user_tenant.role','users.created_at')
            ->orderBy('users.name')
            ->paginate(15);

        return response()->json($users);
    }

    public function store(StoreUserRequest $request)
    {
        $tenantId = $request->attributes->get('tenant_id');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        DB::table('user_tenant')->updateOrInsert(
            ['user_id' => $user->id, 'tenant_id' => $tenantId],
            ['role' => $request->role, 'created_at' => now(), 'updated_at' => now()]
        );

        return response()->json(['message' => 'Usuario creado', 'id' => $user->id], 201);
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $tenantId = $request->attributes->get('tenant_id');

        $attached = DB::table('user_tenant')
            ->where('user_id', $id)
            ->where('tenant_id', $tenantId)
            ->exists();

        if (!$attached) {
            return response()->json(['message' => 'Usuario no pertenece a este tenant'], 404);
        }

        $user = User::findOrFail($id);
        $user->fill($request->only('name','email'));

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return response()->json(['message' => 'Usuario actualizado']);
    }

    public function updateRole(UpdateUserRoleRequest $request, $id)
    {
        $tenantId = $request->attributes->get('tenant_id');

        $updated = DB::table('user_tenant')
            ->where('user_id', $id)
            ->where('tenant_id', $tenantId)
            ->update(['role' => $request->role, 'updated_at' => now()]);

        if (!$updated) {
            return response()->json(['message' => 'Usuario no pertenece a este tenant'], 404);
        }

        return response()->json(['message' => 'Rol actualizado']);
    }

    public function detach($id, Request $request)
    {
        $tenantId = $request->attributes->get('tenant_id');
        DB::table('user_tenant')
            ->where('user_id', $id)
            ->where('tenant_id', $tenantId)
            ->delete();

        return response()->json(['message' => 'Usuario removido del tenant']);
    }
}
