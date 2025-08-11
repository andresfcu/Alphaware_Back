<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_tenant', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('tenant_id');
            $table->string('role')->default('member');
            $table->timestamps();
            $table->primary(['user_id','tenant_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('user_tenant'); }
};
