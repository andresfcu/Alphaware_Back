<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('modules', function(Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->nullable()->constrained();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('route');
            $t->string('icon')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('modules'); }
};
