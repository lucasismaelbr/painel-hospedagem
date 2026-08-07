<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarefas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('titulo');
            $table->enum('categoria', ['prospeccao_whatsapp', 'prospeccao_maps', 'follow_up', 'upsell', 'geral'])->default('geral');
            $table->boolean('concluida')->default(false);
            $table->date('data_objetivo')->default(now()->toDateString());
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarefas');
    }
};
