<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->enum('tipo', ['site', 'mensalidade', 'anuidade']);
            $table->decimal('valor', 10, 2);
            $table->enum('status', ['pendente', 'pago', 'atrasado'])->default('pendente');
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->string('metodo_pagamento', 50)->nullable(); // pix, boleto, cartao, transferencia
            $table->timestamps();

            $table->index('status');
            $table->index('data_vencimento');
            $table->index(['cliente_id', 'status']); // "pagamentos pendentes do cliente X"
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};
