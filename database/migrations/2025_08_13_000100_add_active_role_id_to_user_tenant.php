<?php
// database/migrations/2025_08_13_000100_add_active_role_id_to_user_tenant.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
  public function up(): void {
    Schema::table('user_tenant', function (Blueprint $t) {
      $t->unsignedBigInteger('active_role_id')->nullable()->after('tenant_id');
    });

    // Backfill desde la columna vieja (si existe role_id en user_tenant)
    if (Schema::hasColumn('user_tenant', 'role_id')) {
      DB::statement("
        UPDATE user_tenant ut
        SET ut.active_role_id = ut.role_id
        WHERE ut.role_id IS NOT NULL
      ");

      // También crear registros en user_tenant_roles con el rol previo
      DB::statement("
        INSERT IGNORE INTO user_tenant_roles (user_id, tenant_id, role_id, created_at, updated_at)
        SELECT ut.user_id, ut.tenant_id, ut.role_id, NOW(), NOW()
        FROM user_tenant ut
        WHERE ut.role_id IS NOT NULL
      ");
    }

    Schema::table('user_tenant', function (Blueprint $t) {
      $t->foreign('active_role_id')->references('id')->on('roles')->nullOnDelete();
      // Opcional: cuando confirmes todo, puedes dropear la vieja columna `role_id`
      // $t->dropColumn('role_id');
    });
  }

  public function down(): void {
    Schema::table('user_tenant', function (Blueprint $t) {
      $t->dropForeign(['active_role_id']);
      $t->dropColumn('active_role_id');
    });
  }
};
