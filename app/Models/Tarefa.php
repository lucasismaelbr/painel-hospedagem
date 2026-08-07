<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tarefa extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'titulo',
        'categoria',
        'concluida',
        'data_objetivo',
    ];

    protected $casts = [
        'concluida' => 'boolean',
        'data_objetivo' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
