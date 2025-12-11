<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
{
    \App\Models\User::firstOrCreate(
        ['email' => 'admin@palletopia.test'],
        [
            'name' => 'Admin Palettopia',
            'phone' => '081234567890',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]
    );
}
}
