<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@painel.com'],
            [
                'nome' => 'Admin',
                'password' => 'senha123', // cast 'hashed' no Model aplica bcrypt automaticamente
                'nivel' => 'admin',
            ]
        );
    }
}
