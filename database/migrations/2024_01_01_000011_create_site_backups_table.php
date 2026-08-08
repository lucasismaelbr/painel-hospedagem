<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('site_backups')) {
            Schema::create('site_backups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->enum('type', ['manual', 'automatico'])->default('manual');
                $table->string('filename');
                $table->unsignedBigInteger('file_size_bytes')->default(0);
                $table->string('storage_driver')->default('local'); // local, s3, r2, b2
                $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
                $table->text('error_message')->nullable();
                $table->integer('retention_days')->default(30);
                $table->timestamps();

                $table->index(['site_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_backups');
    }
};
