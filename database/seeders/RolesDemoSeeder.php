<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Tenant;

class RolesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo'],
            ['name' => 'Demo Company', 'plan' => 'basic', 'status' => 'active']
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            ['name' => 'Admin Demo', 'password' => Hash::make('Admin123*')]
        );
        DB::table('user_tenant')->updateOrInsert(
            ['user_id' => $admin->id, 'tenant_id' => $tenant->id],
            ['role' => 'admin', 'created_at' => now(), 'updated_at' => now()]
        );

        $manager = User::firstOrCreate(
            ['email' => 'manager@demo.com'],
            ['name' => 'Manager Demo', 'password' => Hash::make('Manager123*')]
        );
        DB::table('user_tenant')->updateOrInsert(
            ['user_id' => $manager->id, 'tenant_id' => $tenant->id],
            ['role' => 'manager', 'created_at' => now(), 'updated_at' => now()]
        );
    }
}
