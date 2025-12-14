<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'ZetaCuts',
            'email' => 'zetacuts@gmail.com',
            'password' => Hash::make('12345678'),
            'points' => 0,
            'phone' => null,
            'is_admin' => true,
        ]);
    }
}
