<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'nome', 
        'email', 
        'password', 
        'nivel', 
        'foto_perfil'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password' => 'hashed', // bcrypt automático ao salvar
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class, 'usuario_id');
    }

    public function isAdmin(): bool
    {
        return $this->nivel === 'admin';
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->foto_perfil && file_exists(public_path('uploads/avatars/' . $this->foto_perfil))) {
            return asset('uploads/avatars/' . $this->foto_perfil);
        }
        return '';
    }
}
