<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plano extends Model
{
    protected $fillable = [
        'nome', 'preco_mensal', 'preco_anual', 'descricao', 'recursos',
    ];

    protected $casts = [
        'recursos' => 'array', // JSON <-> array automático
        'preco_mensal' => 'decimal:2',
        'preco_anual' => 'decimal:2',
    ];

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }
}
