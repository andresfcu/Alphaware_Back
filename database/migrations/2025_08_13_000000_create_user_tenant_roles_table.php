<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('user_tenant_roles', function (Blueprint $t) {
      $t->unsignedBigInteger('user_id');
      $t->unsignedBigInteger('tenant_id');
      $t->unsignedBigInteger('role_id');
      $t->timestamps();

      $t->primary(['user_id','tenant_id','role_id']);

      // FK compuesta a user_tenant(user_id, tenant_id)
      $t->foreign(['user_id','tenant_id'])
        ->references(['user_id','tenant_id'])
        ->on('user_tenant')
        ->cascadeOnDelete();

      $t->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
    });
  }

  public function down(): void {
    Schema::dropIfExists('user_tenant_roles');
  }
};