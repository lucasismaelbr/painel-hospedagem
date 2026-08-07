<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagamento extends Model
{
    protected $fillable = [
        'cliente_id', 'site_id', 'tipo', 'valor', 'status',
        'data_vencimento', 'data_pagamento', 'metodo_pagamento',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function scopeAtrasados($query)
    {
        return $query->where('status', '!=', 'pago')
                     ->where('data_vencimento', '<', now());
    }
}
