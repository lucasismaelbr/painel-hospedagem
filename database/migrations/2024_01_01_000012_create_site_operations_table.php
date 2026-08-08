<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('site_operations')) {
            Schema::create('site_operations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('operation_type', 50); // backup, restore, sync, upload
                $table->enum('status', ['pending', 'running', 'completed', 'failed', 'cancelled'])->default('pending');
                $table->integer('progress_percent')->default(0);
                $table->text('details')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index(['site_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_operations');
    }
};
