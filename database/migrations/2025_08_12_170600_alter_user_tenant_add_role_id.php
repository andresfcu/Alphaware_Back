<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::table('user_tenant', function(Blueprint $t) {
            $t->unsignedBigInteger('role_id')->nullable()->after('tenant_id');
        });

        // Mapear strings existentes a roles por slug
        if (Schema::hasTable('roles') && Schema::hasColumn('user_tenant','role')) {
            $map = DB::table('roles')->pluck('id','slug'); // slug => id

            $rows = DB::table('user_tenant')->select('user_id','tenant_id','role')->get();
            foreach ($rows as $r) {
                $roleId = $map[$r->role] ?? null;
                if ($roleId) {
                    DB::table('user_tenant')
                      ->where('user_id',$r->user_id)
                      ->where('tenant_id',$r->tenant_id)
                      ->update(['role_id' => $roleId]);
                }
            }
        }

        Schema::table('user_tenant', function(Blueprint $t) {
            $t->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            // Puedes borrar la columna vieja cuando confirmes:
            // $t->dropColumn('role');
        });
    }

    public function down(): void {
        Schema::table('user_tenant', function(Blueprint $t) {
            $t->dropForeign(['role_id']);
            $t->dropColumn('role_id');
        });
    }
};
