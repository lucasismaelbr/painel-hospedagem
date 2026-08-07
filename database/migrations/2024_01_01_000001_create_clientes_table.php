<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('email', 150)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('empresa', 150)->nullable();
            $table->string('cnpj_cpf', 18)->nullable()->unique();
            $table->text('endereco')->nullable();
            $table->enum('status', ['prospect', 'ativo', 'inativo'])->default('prospect');
            $table->string('origem_lead', 100)->nullable(); // ex: indicação, instagram, google
            $table->timestamps();

            $table->index('status');
            $table->index('nome');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
