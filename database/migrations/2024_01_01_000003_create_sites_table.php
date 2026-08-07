<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('plano_id')->nullable()->constrained('planos')->nullOnDelete();
            $table->string('dominio', 255)->unique();
            $table->string('nome_site', 150);
            $table->enum('status', ['em_construcao', 'ativo', 'suspenso'])->default('em_construcao');
            $table->date('data_publicacao')->nullable();
            $table->string('url_vercel', 255)->nullable();
            $table->date('data_renovacao')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('data_renovacao'); // consultas de "renovações próximas"
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
