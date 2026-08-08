<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('site_connections')) {
            Schema::create('site_connections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->unique()->constrained('sites')->cascadeOnDelete();
                $table->enum('type', ['sftp', 'ftp', 'ftps', 'ssh', 'api'])->default('sftp');
                $table->string('host');
                $table->integer('port')->default(22);
                $table->string('username');
                $table->text('encrypted_credential'); // Senha ou Chave SSH privada Criptografada (AES-256)
                $table->text('passphrase_encrypted')->nullable(); // Senha da chave SSH se houver
                $table->string('root_path')->default('/public_html');
                $table->enum('status', ['conectado', 'desconectado', 'erro'])->default('desconectado');
                $table->timestamp('last_check_at')->nullable();
                $table->integer('latency_ms')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['type', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_connections');
    }
};
