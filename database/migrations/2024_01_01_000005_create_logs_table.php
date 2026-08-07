<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('acao', 50); // criou, atualizou, excluiu, login, login_falhou
            $table->string('tabela_afetada', 50)->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->string('ip_address', 45)->nullable(); // 45 = suporta IPv6
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['tabela_afetada', 'registro_id']);
            $table->index('acao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
