<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->string('title');
            $table->string('path');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('checksum')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->string('status')->default('active');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->index(['tenant_id','folder_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('documents'); }
};
