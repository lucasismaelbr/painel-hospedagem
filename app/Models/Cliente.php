<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    protected $fillable = [
        'nome', 'email', 'telefone', 'empresa',
        'cnpj_cpf', 'endereco', 'status', 'origem_lead',
    ];

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class);
    }

    public function pagamentosPendentes(): HasMany
    {
        return $this->pagamentos()->whereIn('status', ['pendente', 'atrasado']);
    }

    // Scopes úteis
    public function scopeAtivos($query)
    {
        return $query->where('status', 'ativo');
    }
}
