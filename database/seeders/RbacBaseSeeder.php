<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RbacBaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        DB::table('roles')->upsert([
            ['slug'=>'admin','name'=>'Administrador','is_active'=>true],
            ['slug'=>'manager','name'=>'Gestor','is_active'=>true],
            ['slug'=>'viewer','name'=>'Lector','is_active'=>true],
        ], ['slug'], ['name','is_active']);

        // Permissions
        $perms = [
            ['slug'=>'profile.read','description'=>'Ver perfil'],
            ['slug'=>'profile.update','description'=>'Editar perfil'],
            ['slug'=>'user.read','description'=>'Listar usuarios'],
            ['slug'=>'user.create','description'=>'Crear usuario'],
            ['slug'=>'user.update','description'=>'Editar usuario'],
            ['slug'=>'user.delete','description'=>'Eliminar usuario'],
        ];
        DB::table('permissions')->upsert($perms, ['slug'], ['description']);

        // Categories
        DB::table('categories')->upsert([
            ['slug'=>'system','name'=>'Cuenta','sort'=>10],
            ['slug'=>'admin','name'=>'Administración','sort'=>20],
        ], ['slug'], ['name','sort']);

        // Modules
        $catIds = DB::table('categories')->pluck('id','slug');
        DB::table('modules')->upsert([
            ['slug'=>'profile','name'=>'Perfil','route'=>'/perfil','icon'=>'solar:user-bold','sort'=>10,'category_id'=>$catIds['system'] ?? null,'is_active'=>true],
            ['slug'=>'users','name'=>'Usuarios','route'=>'/usuarios','icon'=>'solar:users-group-two-rounded-bold','sort'=>20,'category_id'=>$catIds['admin'] ?? null,'is_active'=>true],
        ], ['slug'], ['name','route','icon','sort','category_id','is_active']);

        // Vincular módulos a permisos (visibilidad requiere al menos 1 permiso del módulo)
        $permIds = DB::table('permissions')->pluck('id','slug');
        $modIds  = DB::table('modules')->pluck('id','slug');

        $mp = [
            ['module_id'=>$modIds['profile'] ?? 0, 'permission_id'=>$permIds['profile.read'] ?? 0],
            ['module_id'=>$modIds['users'] ?? 0,   'permission_id'=>$permIds['user.read'] ?? 0],
        ];
        $mp = array_filter($mp, fn($r) => $r['module_id'] && $r['permission_id']);
        foreach ($mp as $row) {
            DB::table('module_permission')->updateOrInsert($row, $row);
        }

        // Concesiones por rol (ejemplo de arranque)
        $roleIds = DB::table('roles')->pluck('id','slug');
        $grant = [
            'admin'   => ['profile.read','profile.update','user.read','user.create','user.update','user.delete'],
            'manager' => ['profile.read','profile.update','user.read','user.update'],
            'viewer'  => ['profile.read','user.read'],
        ];
        foreach ($grant as $rSlug => $pSlugs) {
            $rid = $roleIds[$rSlug] ?? null;
            if (!$rid) continue;
            foreach ($pSlugs as $ps) {
                $pid = $permIds[$ps] ?? null;
                if ($pid) {
                    DB::table('role_permission')->updateOrInsert([
                        'role_id' => $rid,
                        'permission_id' => $pid,
                    ], [
                        'role_id' => $rid,
                        'permission_id' => $pid,
                    ]);
                }
            }
        }
    }
}
