<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@softwerp.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@softwerp.com'],
            [
                'name' => 'Usuário Teste',
                'password' => Hash::make('password'),
            ]
        );
    }
}
