<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder {
    public function run(): void {
        $user = User::firstOrCreate(['email' => 'admin@demo.com'], [
            'name' => 'Admin Demo',
            'password' => Hash::make('Admin123*'),
        ]);
        $tenantId = DB::table('tenants')->where('slug','demo')->value('id');
        if ($tenantId && !DB::table('user_tenant')->where('user_id', $user->id)->where('tenant_id',$tenantId)->exists()) {
            DB::table('user_tenant')->insert([
                'user_id' => $user->id,
                'tenant_id' => $tenantId,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
