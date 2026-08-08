<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    protected $fillable = [
        'usuario_id', 'acao', 'tabela_afetada',
        'registro_id', 'ip_address', 'user_agent',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Helper estático para registrar logs de qualquer lugar:
     * Log::registrar('criou', 'clientes', $cliente->id);
     */
    public static function registrar(string $acao, ?string $tabela = null, $registroId = null): void
    {
        try {
            self::create([
                'usuario_id'     => auth()->id(),
                'acao'           => substr($acao, 0, 50),
                'tabela_afetada' => $tabela ? substr($tabela, 0, 50) : null,
                'registro_id'    => is_numeric($registroId) ? (int)$registroId : null,
                'ip_address'     => request()->ip(),
                'user_agent'     => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Log::registrar erro: ' . $e->getMessage());
        }
    }

}
