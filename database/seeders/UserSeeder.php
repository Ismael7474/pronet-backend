<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Compte temporaire pour le développement
        User::create([
            'nom'       => 'Admin Test',
            'email'     => 'admin@test.com',
            'password'  => Hash::make('admin123'),
            'role'      => 'admin',
            'telephone' => '70000001',
        ]);
    }
}
