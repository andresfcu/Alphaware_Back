<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('module_permission', function(Blueprint $t) {
            $t->foreignId('module_id')->constrained()->cascadeOnDelete();
            $t->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $t->primary(['module_id','permission_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('module_permission'); }
};
