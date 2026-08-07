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
    public static function registrar(string $acao, ?string $tabela = null, ?int $registroId = null): void
    {
        self::create([
            'usuario_id' => auth()->id(),
            'acao' => $acao,
            'tabela_afetada' => $tabela,
            'registro_id' => $registroId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
