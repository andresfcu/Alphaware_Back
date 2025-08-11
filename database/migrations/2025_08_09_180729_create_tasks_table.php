<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('document_id')->nullable();
            $table->unsignedBigInteger('assignee_id');
            $table->string('title');
            $table->text('notes')->nullable();
            $table->string('status')->default('open');
            $table->dateTime('due_date')->nullable();
            $table->timestamps();
            $table->index(['tenant_id','status']);
        });
    }
    public function down(): void { Schema::dropIfExists('tasks'); }
};
