<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = [
        'cliente_id', 'plano_id', 'dominio', 'nome_site', 'status',
        'data_publicacao', 'url_vercel', 'data_renovacao', 'observacoes',
    ];

    protected $casts = [
        'data_publicacao' => 'date',
        'data_renovacao' => 'date',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class);
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class);
    }

    public function connection(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SiteConnection::class);
    }

    public function backups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SiteBackup::class)->orderBy('created_at', 'desc');
    }



    // Sites com renovação nos próximos X dias
    public function scopeRenovacaoProxima($query, int $dias = 30)
    {
        return $query->whereBetween('data_renovacao', [now(), now()->addDays($dias)]);
    }
}
